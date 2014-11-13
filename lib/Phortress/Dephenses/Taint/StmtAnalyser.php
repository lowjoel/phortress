<?php
namespace Phortress\Dephenses\Taint;

use \Phortress\Dephenses;
use PhpParser\Node;
use PhpParser\Node\Expr;
/**
 *  Most basic unit of the taint analyser. Takes in a statement and outputs 
 * an array of variables modified by the statement, annotated based on a set 
 * of rules
 * @author naomileow
 */
class StmtAnalyser {
    /**
     * Runs the analyser on a node in theparse tree.
     *
     * @param \PhpParser\Node $node
     */
    public static function reduce(Node $node){
        if($node instanceof Expr\Assign){
            self::applyAssignmentRule($node);
        }else if($node instanceof Expr\AssignOp){
            self::applyAssignmentOpRule($node);
        }
    }
    
    public static function getVariableTerminalReference(Expr\Variable $var){
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
                 return self::getVariableTerminalReference($exp);
             }else{
                 return $exp;
             }
        }
    }
    
    private static function applyAssignmentOpRule(Expr\AssignOp $assignOp){
        $var = $assignOp->var;
        $exp = $assignOp->expr;
        
        $currTaint = $var->taint;
        $expTaint = self::resolveExprTaint($exp);
        $newTaint = self::mergeTaintValues($currTaint, $expTaint);
        $var->taint = $newTaint;
    }
    
    private static function applyAssignmentRule(Expr\Assign $assign){
        $var = $assign->var;
        $exp = $assign->expr;
        
        if($var->taintSource == $exp){
            return;
        }
        
        if($var instanceof Expr\Variable){
            $taint = self::resolveExprTaint($exp);
            self::annotateVariable($var, $taint, $exp);
        }else if($var instanceof Expr\List_){
            self::resolveListAssignment($assign);
        }else{
            $taint = self::resolveExprTaint($var);
            self::annotateVariable($var, $taint, $exp);
        }
        
    }
    
    private static function resolveListAssignment(Expr\Assign $assign){
        assert($assign->var instanceof Expr\List_);
        $list_of_vars = $assign->var->vars;
        $exp = $assign->expr;
        if($exp instanceof Variable){
            $exp = getVariableTerminalReference($exp);
        }
        
        if($exp instanceof Expr\Array_){
            $taint_vals = self::resolveTaintOfExprsInArray($exp);
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
            self::annotateVariable($var, $taint, $assignment);
        }
        
    }
    
    public static function resolveExprTaint(Expr $exp){
        if($exp instanceof Node\Scalar){
            return Annotation::SAFE;
        }else if ($exp instanceof Expr\Variable) {
            return self::resolveVariableTaint($exp);
        }else if (($exp instanceof Expr\ClassConstFetch) || ($exp instanceof Expr\ConstFetch)){
            return Annotation::SAFE;
        }else if($exp instanceof Expr\PreInc || $exp instanceof Expr\PreDec || $exp instanceof Expr\PostInc || $exp instanceof Expr\PostDec){
            $var = $exp->var;
            return resolveExprTaint($var);
        }else if($exp instanceof Expr\BinaryOp){
            return self::resolveBinaryOpTaint($exp);
        }else if($exp instanceof Expr\UnaryMinus || $exp instanceof Expr\UnaryPlus){
            $var = $exp->expr;
            return self::resolveExprTaint($var);
        }else if($exp instanceof Expr\Array_){
            $taint_values = self::resolveTaintOfExprsInArray($exp);
            return max($taint_values);
        }else if($exp instanceof Expr\ArrayDimFetch){
            return self::resolveArrayFieldTaint($exp);
        }else if($exp instanceof Expr\PropertyFetch){
            $var = $exp->var;
            return self::resolveVariableTaint($var);
        }else if($exp instanceof Expr\StaticPropertyFetch){
            return self::resolveClassPropertyTaint($exp);
        }else if($exp instanceof Expr\FuncCall){
            return self::resolveFuncResultTaint($exp);
        }else if($exp instanceof Expr\MethodCall){
            return self::resolveMethodResultTaint($exp);
        }else if($exp instanceof Expr\Ternary){
            //If-else block
            return self::resolveTernaryTaint($exp);
        }else if($exp instanceof Expr\Eval_){
            return self::resolveExprTaint($exp->expr);
        }else{
            //Other expressions we will not handle.
            return Annotation::UNKNOWN;
        }
    }
    
    /**
     * Takes in an array of Nodes and resolves their taint values if they are are variables.
     * Returns an array containing the taint value of each item in the array.
     */
    private static function resolveTaintOfExprsInArray(Expr\Array_ $arr){
        $arr_items = $arr->items;
        $taint_vals = array();
        foreach($arr_items as $item){
            $exp = $item->value;
            $taint_val = Annotation::UNKNOWN;
            if($exp instanceof Expr){
                $taint_val = self::resolveExprTaint($exp);
            }
            $taint_vals[] = $taint_val;
        }
        return $taint_vals;
    }
    
    private static function resolveBinaryOpTaint(Expr\BinaryOp $exp){
        $left = $exp->left;
        $right = $exp->right;
        $left_taint = self::resolveExprTaint($left);
        $right_taint = self::resolveExprTaint($right);
        return self::mergeTaintValues($left_taint, $right_taint);
    }
    
    private static function resolveArrayFieldTaint(Expr\ArrayDimFetch $exp){
        //TODO: This is merely a stub which treats all the fields in an array as a single entity
        //i.e. They all have the same taint value.
        $array_var = $exp->var;
        $array_var_name = $array_var->name;
//        $array_field = $exp->var->dim;
        if(InputSources::isInputVariableName($array_var_name)){
            self::annotateVariable($exp, Annotation::TAINTED);
            return $exp->taint;
        }
        $env = $array_var->environment;
        if(!empty($env)){
            return resolveVariableTaintInEnvironment($env, $array_var);
        }else{
            self::annotateVariable($array_var, Annotation::UNASSIGNED);
            return Annotation::UNASSIGNED;
        }
    }
    private static function resolveClassPropertyTaint(Expr\StaticPropertyFetch $exp){
        $classEnv = $exp->environment->resolveClass($exp->class);
        return self::resolveVariableTaintInEnvironment($classEnv, $exp);
    }
    
    private static function resolveVariableTaint(Expr\Variable $exp){
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
        
        if(InputSources::isInputVariable($exp)){
            self::annotateVariable($exp, Annotation::TAINTED);
            return $exp->taint;
        }
        $env = $exp->environment;
        
        if(!empty($env)){
            return self::resolveVariableTaintInEnvironment($env, $exp);
        }else{
            //TODO:
            self::annotateVariable($exp, Annotation::UNASSIGNED);
            return Annotation::UNASSIGNED;
        }
    }
    
    private static function resolveVariableTaintInEnvironment(\Phortress\Environment $env, Expr\Variable $var){
        $name = $var->name;
        if($name instanceof Expr){
            self::annotateVariable($var, Annotation::UNKNOWN);
            return Annotation::UNKNOWN;
        }else{
            try{
                $assign = $env->resolveVariable($name);
                self::applyAssignmentRule($assign);
                return $var->taint;
            }catch(UnboundIdentifierException $e){
                self::annotateVariable($var, Annotation::UNASSIGNED);
                return Annotation::UNASSIGNED;
            }
        }
    }
    
    private static function mergeTaintValues(){
        $taints = func_get_args();
        return max($taints);
    }
    
    private static function resolveTernaryTaint(Expr\Ternary $exp){
        $if = $exp->if;
        $else = $exp->else;
        $if_taint = self::resolveExprTaint($if);
        $else_taint = self::resolveExprTaint($else);
        return self::mergeTaintValues($if_taint, $else_taint);
    }
    
    private static function resolveFuncResultTaint(Expr\FuncCall $exp){
        //$exp->name is of type Name|Expr
        if(InputSources::isInputRead($exp)){
            return Annotation::TAINTED;
        }else{
            $func_name = $exp->name;
            if($func_name instanceof Expr){
                return Annotation::UNKNOWN;
            }
            $func_analyser = FunctionAnalyser::getFunctionAnalyser($exp->environment, $func_name);
            $analysis_res = $func_analyser->analyseFunctionCall($exp->args); //TODO: figure out how to include the sanitising functions
            return $analysis_res[0];
        }
    }
    
    private static function resolveMethodResultTaint(Expr\MethodCall $exp){
        
    }
    
    private static function annotateVariable($var, $annot, $source=NULL){
        $var->taint = $annot;
        $var->taintSource = $source;
    }
    
}
