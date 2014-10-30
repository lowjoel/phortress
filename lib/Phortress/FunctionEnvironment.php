<?php
namespace Phortress;

class FunctionEnvironment extends Environment {
	public function resolveVariablesInParentEnvironment() {
		return get_class($this->getParent()) !== '\Phortress\Environment';
	}

	public function createChild() {
		$environment = new FunctionEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}
} 
