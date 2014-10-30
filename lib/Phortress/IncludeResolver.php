<?php
namespace Phortress;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr;

class IncludeResolver extends NodeVisitorAbstract {
	/**
	 * The files and the statements including them.
	 * @var array(String => \PhpParser\Node[])
	 */
	private $files = array();

	/**
	 * Gets the mapping from files to statements.
	 *
	 * @return array(string => Node[])
	 */
	public function getFiles() {
		return $this->files;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Expr\Include_) {
			assert(false, 'Includes not currently supported');
		}
	}
} 
