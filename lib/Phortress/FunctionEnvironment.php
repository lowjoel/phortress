<?php
namespace Phortress;

class FunctionEnvironment extends Environment {
	public function shouldResolveVariablesInParentEnvironment() {
		return get_class($this->getParent()) === '\Phortress\FunctionEnvironment';
	}

	public function createChild() {
		$environment = new FunctionEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}
} 
