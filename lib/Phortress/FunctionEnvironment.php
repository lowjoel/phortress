<?php
namespace Phortress;

use PhpParser\Node\Stmt\Global_;

class FunctionEnvironment extends Environment {
	public function __construct($name, Environment $parent) {
		parent::__construct($name, $parent);
		if (!($parent instanceof FunctionEnvironment)) {
			self::copyValueReferences($this->variables,
				$parent->getGlobal()->getSuperglobals());
		}
	}

	public function shouldResolveVariablesInParentEnvironment() {
		return get_class($this->getParent()) === 'Phortress\FunctionEnvironment';
	}

	public function createChild() {
		return new FunctionEnvironment($this->name, $this);
	}

	/**
	 * Defines a new variable by reference.
	 * @param Global_ $node
	 * @return FunctionEnvironment
	 */
	public function defineVariableByReference(Global_ $node) {
		$result = $this->createChild();
		$superglobals = &$this->getGlobal()->getSuperglobals();
		$globals = &$superglobals['GLOBALS'];
		foreach ($node->vars as $var) {
			$result->variables[$var->name] = &$globals[$var->name];
		}

		return $result;
	}
} 
