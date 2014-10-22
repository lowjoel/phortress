<?php
namespace Phortress\Dephenses\Taint;
/**
 * This is a "template" for a defined function. Based on the Function object
 * given at initialisation, an image of how the function's inputs affects its
 * output and side effects will be created. 
 *
 * @author naomileow
 */
class FunctionAnalyser {
    
    private $name;
    
    private $retByRef;
    
    private $statements;
    
    private $parameters;
    
    /**
     * array(Return_Expression => 
     *          array("variables" => 
     *                      Names of variables the expression is dependent on 
     *                "annotation" => precomputed annotations, if any)
     */
    private $outputs;
    
    
    /**
     * Array containing names of possibly vulnerable functions and the input 
     * parameters they rely on
     */
    private $sideEffects;
    
    public function __construct($func) {
        $this->initialiseFunctionAttributes($func);
    }
    
    private function initialiseFunctionAttributes($func){
        $this->name = $func->name;
        $this->statements = $func->stmts;
        $this->retByRef = $func->byRef;
        $this->parameters = $func->params;
        
        $this->analyseFunctionParameters();
        $this->analyseFunction();
    }
    
    private function analyseFunctionParameters(){
        
    }
    
    private function analyseFunction(){
        
    }
    
    public function analyse($arguments, $variables){
        
    }
    
}
