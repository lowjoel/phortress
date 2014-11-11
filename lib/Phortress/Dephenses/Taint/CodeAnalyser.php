<?php

namespace Phortress\Dephenses\Taint;

/**
 * Description of CodeAnalyser
 *
 * @author naomileow
 */
class CodeAnalyser {
    private $parseTree;
    
    private $warnings;
    
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
}
