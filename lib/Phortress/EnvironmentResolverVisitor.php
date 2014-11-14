<?php
namespace Phortress;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;

class EnvironmentResolverVisitor extends NodeVisitorAbstract {
	/**
	 * The global environment for the program.
	 *
	 * @var GlobalEnvironment
	 */
	private $globalEnvironment;

	/**
	 * The stack of environments while traversing the tree.
	 *
	 * @var Environment[]
	 */
	private $environmentStack = array();

	/**
	 * Constructor.
	 *
	 * @param GlobalEnvironment $globalEnvironment The global environment to use
	 * for traversal.
	 */
	public function __construct(GlobalEnvironment $globalEnvironment) {
		$this->globalEnvironment = $globalEnvironment;
	}

	public function beforeTraverse(array $nodes) {
		$this->environmentStack = array($this->globalEnvironment);
	}

	public function enterNode(Node $node) {
		if ($node instanceof Stmt\Namespace_) {
			if ($node->name === null) {
				// Global namespace
				$node->environment = $this->currentEnvironment();
			} else {
				$node->environment = $this->currentEnvironment()->
					createNamespace($node);
			}
			$this->pushEnvironment($node->environment);
		} else if ($node instanceof Stmt\Function_) {
			$node->environment = $this->currentEnvironment()->
				createFunction($node);
			$this->pushEnvironment($node->environment);
		} else if ($node instanceof Stmt\Class_) {
			$node->environment = $this->currentEnvironment()->
				createClass($node);
			$this->pushEnvironment($node->environment);
		} else if ($node instanceof Stmt\ClassMethod) {
			$node->environment = $this->currentEnvironment()->createFunction($node);
			$this->pushEnvironment($node->environment);
		} else if ($node instanceof Stmt\Property) {
			$this->currentEnvironment()->defineVariableByValue($node);
		} else if ($node instanceof Expr\Assign) {
			$node->environment = $this->currentEnvironment()->
				defineVariableByValue($node);
			$this->setCurrentEnvironment($node->environment);
		} else if ($node instanceof Node\Expr) {
			$node->environment = $this->currentEnvironment();
		} else {
			$className = get_class($node);
			switch ($className) {
				case 'PhpParser\Node\Name':
				case 'PhpParser\Node\Name\FullyQualified':
				case 'PhpParser\Node\Name\Relative':
				case 'PhpParser\Node\Arg':
				case 'PhpParser\Node\Param':
				case 'PhpParser\Node\Stmt\PropertyProperty':
				case 'PhpParser\Node\Stmt\Echo_':
				case 'PhpParser\Node\Stmt\If_':
				case 'PhpParser\Node\Stmt\Else_':
				case 'PhpParser\Node\Stmt\Return_':
					break;
				default:
					printf('Unknown node type: %s, ignored.'."\n", $className);
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Stmt\Namespace_) {
			$this->popEnvironment();
		} else if ($node instanceof Stmt\Function_) {
			$this->popEnvironment();
		} else if ($node instanceof Stmt\Class_) {
			$this->popEnvironment();
		} else if ($node instanceof Stmt\ClassMethod) {
			$this->popEnvironment();
		}
	}

	/**
	 * Gets the current environment.
	 *
	 * @return Environment
	 */
	private function &currentEnvironment() {
		return $this->environmentStack[count($this->environmentStack) - 1];
	}

	/**
	 * Sets the new environment.
	 *
	 * @param Environment $environment The new environment to replace the topmost
	 *                                 environment.
	 */
	private function setCurrentEnvironment(Environment $environment) {
		assert(!empty($this->environmentStack), 'Environment stack cannot be empty.');
		$this->environmentStack[count($this->environmentStack) - 1] =
			$environment;
	}

	/**
	 * Pops the topmost environment from the environment stack.
	 */
	private function popEnvironment() {
		array_pop($this->environmentStack);
		assert(!empty($this->environmentStack), 'Cannot pop the global ' .
			'environment off the environment stack.');
	}

	/**
	 * Pushes a new environment to the top of the environment stack.
	 * @param Environment $environment
	 */
	private function pushEnvironment(Environment $environment) {
		array_push($this->environmentStack, $environment);
	}
} 
