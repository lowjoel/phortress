<?php
namespace Phortress;

class FunctionEnvironment extends Environment {
	public function __construct($name, Environment $parent) {
		parent::__construct($name);
		$this->parent = $parent;
		self::copyValueReferences($parent->getGlobal()->getSuperglobals(),
			$this->variables);
	}

	public function shouldResolveVariablesInParentEnvironment() {
		return get_class($this->getParent()) === '\Phortress\FunctionEnvironment';
	}

	public function createChild() {
		$environment = new FunctionEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}
} 
