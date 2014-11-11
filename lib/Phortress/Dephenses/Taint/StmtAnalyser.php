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
        
        if($var instanceof Expr\Variable){
            if($exp instanceof Scalar){
                $this->annotateVariable($var, Annotation::SAFE, $exp);
            } else if($exp instanceof  Expr){
                $taint = $this->resolveExprTaint($exp);
                $this->annotateVariable($var, $taint);
            }
        }else{
            
        }
    }
    
    private function resolveExprTaint(Expr $exp){
        if ($exp instanceof Variable) {
            return $this->resolveVariableTaint($exp);
        }else if($exp instanceof ClassConstFetch || ConstFetch){
            return Annotation::SAFE;
        }else if($exp instanceof PreInc || $exp instanceof PreDec || $exp instanceof PostInc || $exp instanceof PostDec){
            $var = $exp->var;
            return resolveExprTaint($var);
        }else if($exp instanceof ArrayDimFetch){
            
            
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
        }else{
            //Other expressions we will not handle.
            return Annotation::UNKNOWN;
        }
    }
    private function resolveArrayFieldTaint(ArrayDimFetch $exp){
        $array_var = $exp->var->name;
        $array_field = $exp->var->dim;
        if(Dephenses\InputSources::isInputVariableName($array_var)){
            $this->annotateVariable($exp, Annotation::TAINTED);
            return $exp->taint;
        }
        //TODO:
    }
    private function resolveClassPropertyTaint(StaticPropertyFetch $exp){
        //$exp->class is of type Name|Expr
        $classEnv = $exp->environment->resolveClass($exp->class);
        $name = $exp->name;
        $assign = $classEnv->resolveVariable($name);
        $this->applyAssignmentRule($assign);
        return $assign->var->taint;
    }
    
    private function resolveVariableTaint(Variable $exp){
        //This should apply the taint value of $exp to $var. 
        //If $exp is not marked, go up the environment chain to mark the taint value of $exp,
        //marking the taint value of the variables along the way.
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
            $assign = $env->resolveVariable($exp->name);
            //$exp name is actiually of type string|Expr, but not handling dynamic naming for now.
            $this->applyAssignmentRule($assign);
            return $exp->taint;
        }
    }
    private function resolveTernaryTaint(Ternary $exp){
        
    }
    
    private function resolveFuncResultTaint(FuncCall $exp){
        //$exp->name is of type Node|Name|Expr
    }
    
    private function resolveMethodResultTaint(MethodCall $exp){
        
    }
    
    private function annotateVariable($var, $annot, $source=NULL){
        $var->taint = $annot;
        $var->taintSource = $source;
    }
    
}
