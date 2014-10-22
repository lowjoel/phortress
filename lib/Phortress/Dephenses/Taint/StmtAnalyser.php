<?php
namespace Phortress\Dephenses\Taint;

/**
 *  Most basic unit of the taint analyser. Takes in a statement and outputs 
 * an array of variables modified by the statement, annotated based on a set 
 * of rules
 * @author naomileow
 */
class StmtAnalyser {
    private $statement;
    
    /**
     * Array of variables modified by the statement
     * @var AnnotVariable[]
     */
    private $annotations;
    
    public function __construct() {
        
    }
    
    public function reduce(){
        
    }
    
    private function applyBasicRule(\PhpParser\Node\Expr\AssignOp $assignOp, $resolvedVars){
        $var = $assignOp->var;
        $exp = $assignOp->expr;
        $LHSName = $var->name;
        
        $annot = $this->createAnnotVariable($var, UNASSIGNED);
        if($exp instanceof \PhpParser\Node\Scalar){
            $annot->annotation = SAFE;
        }elseif ($exp instanceof \PhpParser\Node\Variable) {
            $RHSName = $exp->name;
            $RHSAnnot = array_filter($resolvedVars, 
                            function($v) use ($RHSName){
                                return $v->name == $RHSName;
                            });
            if(!empty($RHSAnnot)){
                $RHS = $RHSAnnot[0];
                $annot->annotation = $RHS->annotation;
            }else{
                $annot->annotation = UNKNOWN;
            }
        }
        return $this->mergeAnnotationState($resolvedVars, $annot);
    }
    
    private function mergeAnnotationState($resolvedVars, $annotVariable){
        $name = $annotVariable->name;
        $merged = array_Filter($resolvedVars,
                    function($v) use ($name){
                        return $v->name != $name;
                    }
                );
        $merged[] = $annotVariable;
        return $merged;
    }
    
    private function createAnnotVariable($var, $annot){
        return new Node\AnnotVariable($var->name, $var.getAttributes(), $annot);
    }
}
