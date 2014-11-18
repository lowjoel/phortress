<?php

namespace Phortress\Dephenses\Taint;
use Phortress\Dephenses\Engine\SQLVulnerabilityFinder;
use Phortress\Dephenses\Engine\VulnerabilityReporter;

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
	    $vulnerabilityReporter = new VulnerabilityReporter();
	    $currentTaintEnv = new TaintEnvironment();
	    $nodeAnalyser = new NodeAnalyser($vulnerabilityReporter);
        foreach($this->parseTree as $statement){
	        $nodeTaintEnv = $nodeAnalyser->analyse($statement, $currentTaintEnv);
	        $currentTaintEnv->updateTaintEnvironment($nodeTaintEnv);
        }
    }

	public function runVulnerabilityChecks(){
//		$sql_vul_finder = new SQLVulnerabilityFinder($this->parseTree);
//		return $sql_vul_finder->findVulnerabilities();
	}
}
