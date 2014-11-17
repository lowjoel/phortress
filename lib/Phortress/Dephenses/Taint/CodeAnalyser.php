<?php

namespace Phortress\Dephenses\Taint;
use Phortress\Dephenses\Engine\SQLVulnerabilityFinder;

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
        foreach($this->parseTree as $statement){
//            StmtAnalyser::reduce($statement);
	        NodeAnalyser::analyse($statement);
        }
        return array();
    }

	public function runVulnerabilityChecks(){
		$sql_vul_finder = new SQLVulnerabilityFinder($this->parseTree);
		return $sql_vul_finder->findVulnerabilities();
	}
}
