<?php
namespace Phortress;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Phortress\Dephenses\Dephense;

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
	 * The entry point of the program.
	 *
	 * @var string
	 */
	private $input = null;

	/**
	 * The source files comprising the program.
	 *
	 * This is null until @see parse is called.
	 *
	 * @var array(String => SourceFile)
	 */
	private $files;

	/**
	 * The parse tree for the entire program.
	 *
	 * @var \PhpParser\Node[]
	 */
	private $parseTree;

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
		$this->input = $entryFile;
	}

	/**
	 * Parses the given program. This will load all dependent files.
	 *
	 * This method is idempotent.
	 */
	public function parse() {
		if (!empty($this->parseTree)) {
			return;
		}

		// Parse the input recursively, resolving includes.
		list($files, $parseTree) = self::parseFile($this->input);

		// Adorn the parse tree with environment information.
		$parseTree = $this->addEnvironment($parseTree);

		// Map the raw statements into SourceFile objects.
		foreach ($files as $path => &$file) {
			$file = new SourceFile($path, $file);
		}

		// Memoise.
		$this->files = $files;
		$this->parseTree = $parseTree;
	}

	/**
	 * Parses the given file.
	 *
	 * @param string $file The path to the file.
	 * @return array The statements in all included files file, and the complete
	 * parse tree of the program after following requires and includes.
	 * @throws Exception\ParseErrorException When there is a syntax error in the
	 * input source.
	 */
	private static function parseFile($file) {
		$file = realpath($file);
		$parser = new Parser();
		try {
			$statements = $parser->parseFile($file);

			// Parse requires
			$includer = new NodeTraverser;
			$includeResolver = new IncludeResolverVisitor($parser);
			$includer->addVisitor($includeResolver);
			$parseTree = $includer->traverse(array_slice($statements, 0));

			// Convert to fully qualified names
			$traverser = new NodeTraverser;
			$traverser->addVisitor(new NameResolver);
			$statements = $traverser->traverse($statements);

			// Merge all the raw statements
			$files = array(
				$file => $statements
			);
			$files = array_merge($files, $includeResolver->getFiles());

			return array($files, $parseTree);
		} catch (Error $e) {
			throw new Exception\ParseErrorException($e->getMessage(),
				$e->getLine(), $e);
		}
	}

	/**
	 * Adds environment information to the given statements.
	 *
	 * @param \PhpParser\Node[] $statements The statements comprising the
	 * program.
	 * @return \PhpParser\Node[]
	 */
	private function addEnvironment(array $statements) {
		$traverser = new NodeTraverser;
		$environmentResolver = new EnvironmentResolverVisitor($this->environment);
		$traverser->addVisitor($environmentResolver);
		return $traverser->traverse($statements);
	}

	/**
	 * Verifies the program using the given Dephenses.
	 *
	 * @param Dephenses\Dephense[] $dephenses The Dephenses to execute, or null to execute all.
	 * @return Dephenses\Message[] The messages associated with running the dephenses on the
	 *                             given file.
	 */
	public function verify(array $dephenses = null) {
		if (is_null($dephenses)) {
			$dephenses = Dephense::getAll();
		}

		$errors = array();
		foreach ($dephenses as $dephense) {
			$errors += $dephense->run($this->parseTree);
		}

		return $errors;
	}
}
