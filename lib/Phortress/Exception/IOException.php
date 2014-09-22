<?php
namespace Phortress\Exception;

/**
 * The exception raised when an I/O exception occurs.
 *
 * @package Phortress\Exception
 */
class IOException extends \Exception {
	/**
	 * The path to the file.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Constructor.
	 *
	 * @param string $path The path to the file.
	 */
	public function __construct($path) {
		parent::__construct(sprintf('The file %s cannot be opened.', $path));
	}
} 
