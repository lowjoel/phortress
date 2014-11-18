<?php
namespace Phortress\Dephenses;

class Taint extends Dephense {
	public function run(array $parseTree) {
		$analyser = new Taint\CodeAnalyser($parseTree);

		return $analyser->analyse();
	}
}
