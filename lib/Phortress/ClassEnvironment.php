<?php
namespace Phortress;

use PhpParser\Node;
use \PhpParser\Node\Stmt;

/**
 * Handles the static methods and variables of a class. For the instance methods and variables, see
 * @see ClassInstanceEnvironment.
 *
 * @package Phortress
 */
class ClassEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait,
		EnvironmentHasConstantsTrait {
		createFunction as traitCreateFunction;
	}

	/**
	 * The instance environment for class instances.
	 * @var ClassInstanceEnvironment
	 */
	private $instanceEnvironment;

	public function __construct($name, Environment $parent) {
		parent::__construct($name, $parent);
		$this->instanceEnvironment = new ClassInstanceEnvironment($name, $this);
	}

	/**
	 * Creates child environments for classes. This should never be called,
	 * so this returns itself.
	 *
	 * Defining variables in a class does not give a new environment.
	 *
	 * @return $this
	 */
	public function createChild() {
		return $this;
	}

	/**
	 * @inheritdoc
	 * @param Stmt\Property $node
	 */
	public function defineVariableByValue(Node $node) {
		assert($node instanceof Stmt\Property, 'Only accepts class properties');
		if ($node->isStatic()) {
			return parent::defineVariableByValue($node);
		} else {
			$this->instanceEnvironment->defineVariableByValue($node);
			return $this;
		}
	}

	/**
	 * @inheritdoc
	 * @param Stmt\ClassMethod $function
	 */
	public function createFunction(Stmt $function) {
		assert($function instanceof Stmt\ClassMethod, 'Only accepts class methods');
		if ($function->isStatic()) {
			return $this->traitCreateFunction($function);
		} else {
			return $this->instanceEnvironment->createFunction($function);
		}
	}
}
