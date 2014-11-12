<?php
namespace Phortress;

use PhpParser\NodeVisitorAbstract;

class FileAssignerVisitor extends NodeVisitorAbstract {
	/**
	 * @var String The path to the file to assign.
	 */
	private $path;

	public function __construct($path) {
		$this->path = $path;
	}

	public function enterNode(Node $node) {
		$node->file = $this->path;
	}
}
