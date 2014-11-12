<?php
namespace Phortress;

use Phortress\Exception\IOException;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr;

class IncludeResolverVisitor extends NodeVisitorAbstract {
	/**
	 * The parser to use for parsing includes.
	 * @var Parser
	 */
	private $parser;

	/**
	 * The files and the statements including them.
	 * @var array(String => \PhpParser\Node[])
	 */
	private $files;

	public function __construct(Parser $parser, &$files = array()) {
		$this->parser = $parser;
		$this->files = &$files;
	}

	/**
	 * Gets the mapping from files to statements.
	 *
	 * @return array(string => Node[])
	 */
	public function getFiles() {
		return $this->files;
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Expr\Include_) {
			return $this->includeFile($node->type, $node->expr);
		}

		return null;
	}

	/**
	 * Includes the given expression.
	 *
	 * @param int $type the type of include.
	 * @param Expr $expression The expression.
	 * @return Boolean|Node[] The nodes from the include.
	 */
	private function includeFile($type, Expr $expression) {
		$file = self::evaluateIncludeExpr($expression);
		$file = $this->resolveIncludePath($file);
		switch ($type) {
			case Expr\Include_::TYPE_INCLUDE_ONCE:
			case Expr\Include_::TYPE_REQUIRE_ONCE:
				if (array_key_exists($file, $this->files)) {
					return true;
				}
				// fall through
			case Expr\Include_::TYPE_INCLUDE:
			case Expr\Include_::TYPE_REQUIRE:
				$this->files[$file] = $this->parse($file);
				return $this->files[$file];
			default:
				return null;
		}
	}

	/**
	 * @param Expr $expression The expression to evaluate.
	 * @return string The expression result.
	 */
	private static function evaluateIncludeExpr(Expr $expression) {
		if ($expression instanceof Expr\BinaryOp\Concat) {
			return self::evaluateIncludeExpr($expression->left) .
				self::evaluateIncludeExpr($expression->right);
		} else if ($expression instanceof Node\Scalar\MagicConst\Dir) {
			return dirname($expression->file);
		} else if ($expression instanceof Node\Scalar\MagicConst\File) {
			return basename($expression->file);
		} else if ($expression instanceof Node\Scalar) {
			return (string)$expression->value;
		} else {
			return assert(false, 'Unknown expression type for static evaluation');
		}
	}

	/**
	 * Resolve the given path to the fully qualified path.
	 *
	 * @param string $path The path to resolve,
	 * @return string The fully qualified path to the file.
	 */
	private function resolveIncludePath($path) {
		if (self::ignoresIncludePath($path)) {
			$realPath = realpath($path);
			if ($realPath === false) {
				throw new IOException($path);
			}
			return $realPath;
		} else {
			return assert(false, 'Include path not currently supported');
		}
	}

	/**
	 * Parses the given file, and visits all nodes in the file and includes them too.
	 *
	 * @param string $path The path to the file to parse.
	 * @return \PhpParser\Node[] The parse tree for the file, with all the includes expanded.
	 */
	private function parse($path) {
		$includer = new NodeTraverser;
		$includer->addVisitor($this);
		return $includer->traverse($this->parser->parseFile($path));
	}

	/**
	 * Checks if the given path will ignore looking up the include path.
	 *
	 * @param string $path The path to check.
	 * @return bool True if the path will cause skipping the check for include paths.
	 */
	private static function ignoresIncludePath($path) {
		return empty($path) ||
			$path[0] === '/' || // Unix
			$path[0] === '\\' || // Windows
			ctype_alpha($path[0]) && substr($path, 1, 2) === ':\\'; // Windows drive letter
	}
}
