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
    
    private function applyBasicRule(Expr $assign){
        if(!$assign instanceof Assign || !$assign instanceof \PhpParser\Node\Expr\AssignOP){
            return;
        }
        
        $var = $assign->var;
        $exp = $assign->expr;
        $LHSName = $var->name;
        
        if($exp instanceof Scalar){
           $this->annotateVariable($var, Annotation::SAFE, $exp);
        } else if($exp instanceof  Expr){
           $taint = $this->resolveExprTaint($exp);
           $this->annotateVariable($var, $taint);
        }
    }
    
    private function resolveExprTaint(Expr $exp){
        if ($exp instanceof Variable) {
             //This should apply the taint value of $exp to $var. 
            //If $exp is not marked, go up the environment chain to mark the taint value of $exp,
            //marking the taint value of the variables along the way.
            $annot = $exp->taint;
            if(!empty($annot)){
                return $annot;
            }
            if(Dephenses\InputSources::isInputVariable($exp)){
                annotateVariable($exp, Annotation::TAINTED);
                return $exp->taint;
            }
            
        }else if($exp instanceof ArrayDimFetch){
            
        }else if($exp instanceof ClassConstFetch){
            
        }else if($exp instanceof ConstFetch){
            
        }else if($exp instanceof PropertyFetch){
            
        }else if($exp instanceof StaticPropertyFetch){
            
        }
    }
    
    
    private function annotateVariable($var, $annot, $source=NULL){
        $var->taint = $annot;
        $var->taintSource = $source;
    }
    
}
