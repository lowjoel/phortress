<?php
namespace Phortress;

/**
 * Represents a PHP program. A program is a compilation of all source files needed to run
 * the program.
 *
 * This also manages loading requires and the include path.
 *
 * @package Phortress
 */
class Program {
	/**
	 * The source files comprising the program.
	 *
	 * @var string[]
	 */
	private $input = array();

	/**
	 * The source files comprising the program.
	 *
	 * This is null until @see parse is called.
	 *
	 * @var SourceFile[]
	 */
	private $files;

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
		$this->environment = new GlobalEnvironment();
		$this->input[] = $entryFile;
	}

	/**
	 * Parses the given program. This will load all dependent files.
	 */
	public function parse() {
		// This function is idempotent.
		if (empty($this->input)) {
			return;
		}

		$this->files = array();
		while (!empty($this->input)) {
			$this->parseFile(array_shift($this->input));
		}

		// Don't run again.
		$this->input = null;
	}

	/**
	 * Parses the given file.
	 *
	 * @param string $file The path to the file.
	 *
	 * @throws Exception\ParseErrorException When there is a syntax error in the input source.
	 */
	private function parseFile($file) {
		$file = realpath($file);
		$parser = new Parser($file);
		try {
			$statements = $parser->parse(file_get_contents($file));

			// Convert to fully qualified names
			$traverser = new \PhpParser\NodeTraverser;
			$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver);
			$statements = $traverser->traverse($statements);

			$this->files[$file] = $statements;
		} catch (\PhpParser\Error $e) {
			throw new Exception\ParseErrorException($e->getMessage(), $e->getLine(), $e);
		}
	}

	/**
	 * Verifies the program using the given Dephenses.
	 *
	 * @param string[] $dephenses The Dephenses to execute, or null to execute all.
	 */
	public function verify(array $dephenses = null) {

	}
} 
