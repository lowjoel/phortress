<?php
namespace Phortress\Dephenses;

class Taint extends Dephense {
	public function run(array $parseTree) {
		$analyser = new Taint\CodeAnalyser($parseTree);

		return $analyser->analyse();
	}

	public function runChecks(array $parseTree){
		$analyser = new Taint\CodeAnalyser($parseTree);
		return $analyser->runVulnerabilityChecks();
	}
}
