<?php
namespace Phortress;
use Phortress\Exception\IOException;
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
	 * @param string $file The path to the file.
	 * @return \PhpParser\Node[] The parse tree for the given file.
	 * @throws IOException When the file cannot be opened.
	 */
	public function parseFile($file) {
		if (!is_string($file) || !file_exists($file)) {
			throw new IOException($file);
		}
		$result = $this->parse(file_get_contents($file));

		$traverser = new NodeTraverser;
		$traverser->addVisitor(new FileAssignerVisitor($file));
		return $traverser->traverse($result);
	}
} 
