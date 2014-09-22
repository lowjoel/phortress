<?php
namespace Phortress;

/**
 * Represents a mapping of symbols to its actual values (functions, constants, etc.)
 * Symbol tables can be chained for use in nested environments (namespaces, function scopes or
 * closures)
 *
 * @package Phortress
 */
class Environment {
	/**
	 * The parent environment, or null if this is the global environment.
	 *
	 * @var Environment
	 */
	private $parent;

	/**
	 * The variables defined in the current environment.
	 *
	 * @var AbstractNode[]
	 */
	private $variables = array();

	/**
	 * Constructs a new, empty environment.
	 */
	public function __construct() {
	}

	/**
	 * Gets the parent environment for the current environment.
	 *
	 * @return IEnvironment The parent environment, or null if this is the global environment.
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Resolves the symbol to its real type.
	 *
	 * This is a peculiar function because if we have an absolute path,
	 * we have to go to the global environment before we can resolve downwards. If we are
	 * relative then we go downwards.
	 *
	 * @param string $symbol The symbol to resolve. This can be fully or relatively qualified.
	 *
	 * @exception Exception\UnboundIdentifierException The identifier cannot be resolved.
	 * @return AbstractNode The node representing the symbol
	 */
	public function resolve($symbol) {
		$parent = $this->getParent();

		if (is_null($parent)) {
			$symbol = self::makeRelativelyQualifiedTo($symbol, '\\');
		} else if (self::isAbsolutelyQualified($symbol)) {
			return $parent->resolve($symbol);
		}

		return self::resolveRelative($symbol);
	}

	/**
	 * @param string $symbol The symbol to resolve. This must be relatively qualified or
	 *                       unqualified.
	 *
	 * @exception Exception\UnboundIdentifierException The identifier cannot be resolved.
	 * @return AbstractNode the node representing the symbol.
	 */
	private function resolveRelative($symbol) {
		assert(self::isRelativelyQualified($symbol) || self::isUnqualified($symbol));

		list($current, $residue) = self::dequalifyOne($symbol);

		$child = $this->resolveSelf($current);
		if (!is_null(child)) {

		}
		return $child->resolve($residue);
	}

	/**
	 * Resolves a symbol in the current environment.
	 *
	 * @param string $symbol An unqualified symbol to resolve.
	 * @return AbstractNode The node representing the symbol or null if the symbol cannot be
	 *                      resolved.
	 */
	private function resolveSelf($symbol) {
		assert(self::isUnqualified($symbol), 'Symbol must be unqualified.');

		return $this->variables[$symbol];
	}

	/**
	 * Constructs a new environment with the current environment as a parent.
	 *
	 * @return Environment The new child environment.
	 */
	public function createChild() {
		$environment = new Environment();
		$environment->parent = $this;

		return $environment;
	}

	/**
	 * Defines the symbol in the given environment.
	 *
	 * @param string $symbol The symbol to register. This must be relatively qualified.
	 * @param AbstractNode $node The node to associate with the symbol.
	 */
	public function define($symbol, $node) {
		assert(self::isUnqualified($symbol), 'Symbol must be unqualified.');
	}

	/**
	 * Takes a relatively qualified name and removes the first component of the name.
	 *
	 * @param $symbol The relatively or unqualified name to dequalify.
	 * @return string[] The current component, and the residue after stripping the prefix.
	 */
	private static function dequalifyOne($symbol) {
		return array('lol', 'others');
	}

	/**
	 * @todo Implement
	 * @param $symbol
	 * @return bool
	 */
	private static function isAbsolutelyQualified($symbol) {
		return false;
	}

	/**
	 * @todo Implement
	 * @param $symbol
	 * @return bool
	 */
	private static function isRelativelyQualified($symbol) {
		return false;
	}

	/**
	 * Checks whether the given symbol is unqualified.
	 *
	 * @param string $symbol The symbol to check.
	 * @return bool True if the symbol is unqualified.
	 */
	private static function isUnqualified($symbol) {
		return !self::isAbsolutelyQualified($symbol) && !self::isRelativelyQualified($symbol);
	}

	/**
	 * Makes one symbol relative to the given namespace.
	 * @todo implement
	 *
	 * @param string $symbol The symbol to make relative. This must be an absolutely qualified
	 *                       symbol.
	 * @param string $relativeTo The symbol to make relative to. This must be an absolutely
	 *                           qualified symbol.
	 * @return string The new, relatively qualified symbol.
	 */
	private static function makeRelativelyQualfiedTo($symbol, $relativeTo) {

	}
}
