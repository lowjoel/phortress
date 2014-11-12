<?php
namespace Phortress;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr;

class IncludeResolver extends NodeVisitorAbstract {
	/**
	 * The files and the statements including them.
	 * @var array(String => \PhpParser\Node[])
	 */
	private $files;

	public function __construct(&$files = array()) {
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
	}

	/**
	 * Includes the given expression.
	 *
	 * @param int $type the type of include.
	 * @param Expr $expression The expression.
	 * @return Boolean|Node[] The nodes from the include.
	 */
	private function includeFile($type, Expr $expression) {
		$file = $this->evaluateIncludeExpr($expression);
		$file = $this->resolveIncludePath($file);
		switch ($type) {
			case Expr\Include_::TYPE_INCLUDE_ONCE:
			case Expr\Include_::TYPE_REQUIRE_ONCE:
				if (array_key_exists($file, $this->files)) {
					return true;
				}
			case Expr\Include_::TYPE_INCLUDE:
			case Expr\Include_::TYPE_REQUIRE:
				$this->files[$file] = $this->parse($file);
				return $this->files[$file];
		}
	}

	/**
	 * @param Expr $expression The expression to evaluate.
	 * @return string The expression result.
	 */
	private function evaluateIncludeExpr(Expr $expression) {
		assert(false, 'Includes not currently supported');
	}

	/**
	 * Resolve the given path to the fully qualified path.
	 *
	 * @param string $path The path to resolve,
	 * @return string The fully qualified path to the file.
	 */
	private function resolveIncludePath($path) {
		if (!self::ignoresIncludePath($file)) {
			return realpath($path);
		}
		assert(false, 'Include path not currently supported');
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
