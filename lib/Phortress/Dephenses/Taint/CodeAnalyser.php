<?php

namespace Phortress\Dephenses\Taint;

/**
 * Description of CodeAnalyser
 *
 * @author naomileow
 */
class CodeAnalyser {
    private $parseTree;
    
    /**
     * Array of annotated variables
     * @var AnnotVariable[]; 
     */
    private $variables = array();
    
    private $warnings;
    
    /**
     * Dictionary of function names to FunctionAnalysers
     * @var array(String => FunctionAnalyser)
     */
    private $functions;
    
    
    
    function __construct($tree) {
        $this->parseTree = $tree;
    }
    
    private function analyse(){
        if(!isset($this->warnings)){
            $this->warnings = array();
            foreach($this->parseTree as $statement){
                
            }
        }
    }
    
    private function createFunctionAnalyser($func){
        $name = $func->name;
        $analyser = new FunctionAnalyser($func);
        $this->functions[$name] = $analyser;
    }
}
