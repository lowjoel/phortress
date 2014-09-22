<?php
namespace Phortress;

/**
 * Represents a PHP program. A program is a compilation of all source files needed to run
 * the program.
 *
 * @package Phortress
 */
class Program {
	/**
	 * The source files comprising the program.
	 *
	 * @var SourceFile[]
	 */
	private $files = array();

	/**
	 * The global environment for this program.
	 *
	 * @var Environment
	 */
	private $environment;

	/**
	 * Constructs a new program, with the specified entry file.
	 *
	 * @param string $entryFile The point of entry in the program.
	 */
	public function __construct($entryFile) {
		$this->environment = new Environment();
		$this->files[] = $entryFile;
	}

	/**
	 * Parses the given program. This will load all dependent files.
	 */
	public function parse() {
	}

	/**
	 * Verifies the program using the given Dephenses.
	 *
	 * @param string[] $dephenses The Dephenses to execute, or null to execute all.
	 */
	public function verify(array $dephenses = null) {

	}
} 
