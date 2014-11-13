<?php
namespace Phortress;

class FunctionEnvironment extends Environment {
	public function __construct($name, Environment $parent) {
		parent::__construct($name, $parent);
		self::copyValueReferences($this->variables,
			$parent->getGlobal()->getSuperglobals());
	}

	public function shouldResolveVariablesInParentEnvironment() {
		return get_class($this->getParent()) === '\Phortress\FunctionEnvironment';
	}

	public function createChild() {
		return new FunctionEnvironment($this->name, $this);
	}
} 
