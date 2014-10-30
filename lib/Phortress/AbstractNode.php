<?php
namespace Phortress;

/**
 * Represents a node in the program.
 *
 * @package Phortress
 */
class AbstractNode {
	/**
	 * The environment at the point that this node is created.
	 *
	 * @var Environment
	 */
	protected $environment;

	/**
	 * The raw parser node.
	 *
	 * @var \PhpParser\NodeAbstract
	 */
	protected $node;
} 
