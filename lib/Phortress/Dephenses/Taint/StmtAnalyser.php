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
    
    private function applyBasicRule(\PhpParser\Node\Expr\AssignOp $assignOp){
        $var = $assignOp->var;
        $exp = $assignOp->expr;
        
        if($exp instanceof \PhpParser\Node\Scalar){
            $annot = new Node\AnnotVariable($var->name, $var.getAttributes(), SAFE);
        }elseif ($exp instanceof \PhpParser\Node\Variable) {
            
        }
    }
}
