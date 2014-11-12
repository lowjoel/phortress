<?php
namespace Phortress;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;

/**
 * Represents the PHP parser used to parse PHP code.
 *
 * @package Phortress
 */
class Parser extends \PhpParser\Parser {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(new Lexer);
	}

	/**
	 * Parses the given file. Adds another attribute 'file'.
	 *
	 * @param String $file The path to the file.
	 * @return \PhpParser\Node[] The parse tree for the given file.
	 */
	public function parseFile($file) {
		$result = $this->parse(file_get_contents($file));

		$traverser = new NodeTraverser;
		$traverser->addVisitor(new FileAssignerVisitor($file));
		return $traverser->traverse($result);
	}
} 
