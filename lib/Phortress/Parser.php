<?php
namespace Phortress;

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
		parent::__construct(new \PhpParser\Lexer);
	}
} 
