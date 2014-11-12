<?php
namespace Phortress\Dephenses\Taint;

use \Phortress\Dephenses;
use PhpParser\Node\Expr;
/**
 *  Most basic unit of the taint analyser. Takes in a statement and outputs 
 * an array of variables modified by the statement, annotated based on a set 
 * of rules
 * @author naomileow
 */
class StmtAnalyser {
    private $statement;    
    
    public function __construct() {
        
    }
    
    public function reduce(){
        
    }
    
    private function applyAssignmentOpRule(AssignOp $assignOp){
        
    }
    
    private function applyAssignmentRule(Assign $assign){
        $var = $assign->var;
        $exp = $assign->expr;
        
        if($var->taintSource == $exp){
            return;
        }
        
        if($var instanceof Variable){
            $taint = $this->resolveExprTaint($exp);
            $this->annotateVariable($var, $taint, $exp);
        }else if($var instanceof List_){
            $this->resolveListAssignment($assign);
        }else{
            $taint = $this->resolveExprTaint($var);
            $this->annotateVariable($var, $taint, $exp);
        }
        
    }
    
    private function resolveListAssignment(Assign $assign){
        assert($assign->var instanceof List_);
        $list_of_vars = $assign->var->vars;
        $exp = $assign->expr;
        if($exp instanceof Variable){
            $exp = getVariableTerminalReference($exp);
        }
        
        if($exp instanceof Array_){
            $taint_vals = $this->resolveTaintOfExprsInArray($exp);
        }
        
        for($i = 0; $i < count(list_of_vars); $i++){
            $var = $list_of_vars[$i];
            $assignment = $exp[$i];
            if(empty($taint_vals)){
                //For now we do not handle the case where the RHS of the list 
                //assignment cannot be resolved to an array
                $taint = Annotation::UNKNOWN;
            }else{
                $taint = $taint_vals[$i];
            }
            $this->annotateVariable($var, $taint, $assignment);
        }
        
    }
    
    private function getVariableTerminalReference(Variable $var){
        $name = $var->name;
        if($name instanceof Expr){
            //If the name of the variable is not a string, we cannot resolve 
            //the variable's assignment statically.
            return $var;
        }
        $env = $var->environment;
        if(empty($env)){
            return $var;
        }else{
             $assign = $env->resolveVariable($name);
             $exp = $assign->expr;
             if($exp instanceof Variable){
                 return $this->getVariableTerminalReference($exp);
             }else{
                 return $exp;
             }
        }
    }
    
    private function resolveExprTaint(Expr $exp){
        if($exp instanceof Scalar){
            return Annotation::SAFE;
        }else if ($exp instanceof Variable) {
            return $this->resolveVariableTaint($exp);
        }else if($exp instanceof ClassConstFetch || ConstFetch){
            return Annotation::SAFE;
        }else if($exp instanceof PreInc || $exp instanceof PreDec || $exp instanceof PostInc || $exp instanceof PostDec){
            $var = $exp->var;
            return resolveExprTaint($var);
        }else if($exp instanceof BinaryOp){
            return $this->resolveBinaryOpTaint($exp);
        }else if($exp instanceof UnaryMinus || $exp instanceof UnaryPlus){
            $var = $exp->expr;
            return $this->resolveExprTaint($exp);
        }else if($exp instanceof Array_){
            $taint_values = $this->resolveTaintOfExprsInArray($exp);
            return max($taint_values);
        }else if($exp instanceof ArrayDimFetch){
            return $this->resolveArrayFieldTaint($exp);
        }else if($exp instanceof PropertyFetch){
            $var = $exp->var;
            return $this->resolveVariableTaint($exp);
        }else if($exp instanceof StaticPropertyFetch){
            return $this->resolveClassPropertyTaint($exp);
        }else if($exp instanceof FuncCall){
            return $this->resolveFuncResultTaint($exp);
        }else if($exp instanceof MethodCall){
            return $this->resolveMethodResultTaint($exp);
        }else if($exp instanceof Ternary){
            //If-else block
            return $this->resolveTernaryTaint($exp);
        }else if($exp instanceof Eval_){
            return $this->resolveExprTaint($exp->expr);
        }else if($exp instanceof ClosureUse){
            return $this->resolveClosureResultTaint($exp);
        }else{
            //Other expressions we will not handle.
            return Annotation::UNKNOWN;
        }
    }
    
    /**
     * Takes in an array of Nodes and resolves their taint values if they are are variables.
     * Returns an array containing the taint value of each item in the array.
     */
    private function resolveTaintOfExprsInArray(Array_ $arr){
        $arr_items = $arr->items;
        $taint_vals = array();
        foreach($arr_items as $item){
            $exp = $item->value;
            $taint_val = Annotation::UNKNOWN;
            if($exp instanceof Expr){
                $taint_val = $this->resolveExprTaint($exp);
            }
            $taint_vals[] = $taint_val;
        }
        return $taint_vals;
    }
    
    private function resolveBinaryOpTaint(BinaryOp $exp){
        $left = $exp->left;
        $right = $exp->right;
        $left_taint = $this->resolveExprTaint($left);
        $right_taint = $this->resolveExprTaint($right);
        return $this->mergeTaintValues($left_taint, $right_taint);
    }
    
    private function resolveArrayFieldTaint(ArrayDimFetch $exp){
        //TODO: This is merely a stub which treats all the fields in an array as a single entity
        //i.e. They all have the same taint value.
        $array_var = $exp->var;
        $array_var_name = $array_var->name;
//        $array_field = $exp->var->dim;
        if(Dephenses\InputSources::isInputVariableName($array_var_name)){
            $this->annotateVariable($exp, Annotation::TAINTED);
            return $exp->taint;
        }
        $env = $array_var->environment;
        if(!empty($env)){
            return resolveVariableTaintInEnvironment($env, $array_var);
        }else{
            $this->annotateVariable($array_var, Annotation::UNASSIGNED);
            return Annotation::UNASSIGNED;
        }
    }
    private function resolveClassPropertyTaint(StaticPropertyFetch $exp){
        $classEnv = $exp->environment->resolveClass($exp->class);
        return $this->resolveVariableTaintInEnvironment($classEnv, $exp);
    }
    
    private function resolveVariableTaint(Variable $exp){
        //This should apply the taint value of $exp to $var. 
        //If $exp is not marked, go up the environment chain to mark the taint value of $exp,
        //marking the taint value of the variables along the way.
        if(!isset($exp)){
            return Annotation::UNASSIGNED;
        }
        
        $annot = $exp->taint;
        if(!empty($annot)){
            return $annot;
        }
        
        if(Dephenses\InputSources::isInputVariable($exp)){
            $this->annotateVariable($exp, Annotation::TAINTED);
            return $exp->taint;
        }
        $env = $exp->environment;
        
        if(!empty($env)){
            return $this->resolveVariableTaintInEnvironment($env, $exp);
        }else{
            //TODO:
            $this->annotateVariable($exp, Annotation::UNASSIGNED);
            return Annotation::UNASSIGNED;
        }
    }
    
    private function resolveVariableTaintInEnvironment(Environment $env, Variable $var){
        $name = $var->name;
        if($name instanceof Expr){
            $this->annotateVariable($var, Annotation::UNKNOWN);
            return Annotation::UNKNOWN;
        }else{
            try{
                $assign = $env->resolveVariable($name);
                $this->applyAssignmentRule($assign);
                return $var->taint;
            }catch(UnboundIdentifierException $e){
                $this->annotateVariable($var, Annotation::UNASSIGNED);
                return Annotation::UNASSIGNED;
            }
        }
    }
    
    private function mergeTaintValues(){
        $taints = func_get_args();
        return max($taints);
    }
    
    private function resolveTernaryTaint(Ternary $exp){
        $if = $exp->if;
        $else = $exp->else;
        $if_taint = $this->resolveExprTaint($if);
        $else_taint = $this->resolveExprTaint($else);
        return $this->mergeTaintValues($if_taint, $else_taint);
    }
    
    private function resolveFuncResultTaint(FuncCall $exp){
        //$exp->name is of type Name|Expr
        if(InputSources::isInputRead($exp)){
            return Annotation::TAINTED;
        }else{
            
        }
    }
    
    private function resolveClosureResultTaint(ClosureUse $exp){
        
    }
    
    private function resolveMethodResultTaint(MethodCall $exp){
        
    }
    
    private function annotateVariable($var, $annot, $source=NULL){
        $var->taint = $annot;
        $var->taintSource = $source;
    }
    
}
