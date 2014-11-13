<?php

namespace Phortress\Dephenses\Taint;

/**
 * Description of CodeAnalyser
 *
 * @author naomileow
 */
class CodeAnalyser {
    private $parseTree;
    
    private $warnings = array();
    
    function __construct($tree) {
        $this->parseTree = $tree;
    }
    
    public function analyse(){
        assert(!empty($this->parseTree));
        foreach($this->parseTree as $statement){
            StmtAnalyser::reduce($statement);
        }
        return array();
    }
}
