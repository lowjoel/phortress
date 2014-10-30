<?php
namespace Phortress;


use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr;

class EnvironmentResolver extends NodeVisitorAbstract {
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
		if ($node instanceof )
	}
} 
