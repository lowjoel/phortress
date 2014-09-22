<?php
namespace Phortress\Exception;

/**
 * Represents a parse error.
 *
 * @package Phortress\Exception
 */
class ParseErrorException extends \Exception {
	/**
	 * The line number of the error.
	 *
	 * @var int|null
	 */
	private $line;

	/**
	 * Constructor.
	 *
	 * @param string   $message The error message.
	 * @param int|null $line The line number where the error is at.
	 * @param \PhpParser\Error $parent The parent exception causing this.
	 */
	public function __construct($message, $line = null, $parent = null) {
		if ($line < 0) {
			$line = null;
		}

		$this->message = $message;
		$this->line = $line;
		parent::__construct($message);
	}
}
