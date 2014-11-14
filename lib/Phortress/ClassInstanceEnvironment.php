<?php
namespace Phortress;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

class ClassInstanceEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait {
		createFunction as traitCreateFunction;
	}

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

	/**
	 * @inheritdoc
	 * @param Stmt\ClassMethod $function
	 */
	public function createFunction(Stmt $function) {
		assert($function instanceof Stmt\ClassMethod, 'Only accepts class methods');
		$result = $this->traitCreateFunction($function);

		// Assign $this
		$result->variables['this'] = new Param(
			'this',
			null,
			new Name($this->classEnvironment->getName())
		);

		return $result;
	}
}
