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
    
    private $warnings = array();
    
    function __construct($tree) {
        $this->parseTree = $tree;
    }
    
    private function analyse(){
        if(empty($this->warnings)){
            foreach($this->parseTree as $statement){
                
            }
        }
    }
}
