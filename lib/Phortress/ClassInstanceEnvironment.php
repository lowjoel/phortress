<?php
namespace Phortress;

class ClassInstanceEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait;

	/**
	 * The class environment for this instance.
	 *
	 * @var ClassEnvironment
	 */
	private $classEnvironment;

	/**
	 * @param string $name The name of the class.
	 * @param ClassEnvironment $classEnvironment The class environment for instances of this class.
	 */
	public function __construct($name, $classEnvironment) {
		parent::__construct($name, $classEnvironment);
		$this->classEnvironment = $classEnvironment;
	}

	public function shouldResolveVariablesInParentEnvironment() {
		// Only ourself.
		return false;
	}

	public function createChild() {
		return $this;
	}
}
