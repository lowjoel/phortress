<?php
namespace Phortress;
use Phortress\Exception\UnboundIdentifierException;

/**
 * Represents a mapping of symbols to its actual values: functions, constants,
 * variables. Symbol tables can be chained for use in nested environments
 * (namespaces, function scopes or closures)
 *
 * Functions and Namespaces
 *
 * Functions and namespaces are the simplest. They are accessible anywhere in
 * the parse tree. For this reason, they are not stored in our environment
 * mapping, instead it is searched whenever we look for one.
 *
 * Constants and Variables
 *
 * Constants and variables are strange creatures in PHP. Constants are available
 * only when they have been evaluated and is dependent on 'program order.'
 * Variables are the same; however, where constants are accessible from any
 * scope after they have been evaluated, variables can only be accessed inside
 * the local environment. Globals need to be accessed with global $var or using
 * superglobals (discussed later)
 *
 * For this reason, environments are mutable. To represent this in a static
 * analyser, one can assume that the environments available at each expression
 * is different. This is a waste of space, as such, in Phortress we represent
 * them as chains of immutable environments. This allows us to preserve the
 * behaviour that variables are undeclared until they have been evaluated.
 *
 * Furthermore, when a variable has been unset(), a dummy value is placed in the
 * environment to indicate that the identifier is no longer bound (@see UNSET_).
 * PHP has function-level variable scoping, but does not have hoisting like
 * JavaScript does, and has unset(), hence the need for this strange behaviour.
 *
 * As a convention, Phortress stores variables as '$var' and constants without
 * the preceding $.
 *
 * Globals and Superglobals
 *
 * There is also the concern of superglobals. Because PHP functions cannot
 * access global variables unless using the $_GLOBALS or global keyword, all
 * functions start out with an empty environment. The only variable which are
 * bound at the start of the function are the superglobals, which are pointing
 * by reference to the global environment's symbol table entry.
 *
 * Lambdas
 *
 * The next level of complexity arises from lambdas. Lambdas are able to either
 * capture a variable by value or by reference. A closure is not formed in the
 * normal sense. In this case, a new environment is created, with the variable
 * captured either copied by value or by reference, depending on the capture,
 * and included in the initial environment of the closure.
 *
 * @package Phortress
 */
class Environment {
	/**
	 * The variable has been unset.
	 */
	const UNSET_ = null;

	/**
	 * We are trying to resolve a namespace.
	 */
	const NAMESPACE_ = 1;

	/**
	 * We are trying to resolve a class name.
	 */
	const CLASS_     = 2;

	/**
	 * We are trying to resolve a function.
	 */
	const FUNCTION_  = 3;

	/**
	 * We are trying to resolve a function.
	 */
	const CONSTANT   = 4;

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
	 * Furthermore, PHP allows us to define the same symbol with different types in the same
	 * scope...
	 *
	 * @param string $symbol The symbol to resolve. This can be fully or relatively qualified.
	 * @param int $typeHint The type of the symbol we want to retrieve.
	 *
	 * @throws Exception\UnboundIdentifierException The identifier cannot be resolved.
	 * @return AbstractNode The node representing the symbol
	 */
	public function resolve($symbol, $typeHint) {
		$parent = $this->getParent();

		if (is_null($parent)) {
			$symbol = self::makeRelativelyQualifiedTo($symbol, '\\');
		} else if (self::isAbsolutelyQualified($symbol)) {
			return $parent->resolve($symbol, $typeHint);
		}

		return self::resolveRelative($symbol, $typeHint);
	}

	/**
	 * @param string $symbol The symbol to resolve. This must be relatively qualified or
	 *                       unqualified.
	 * @param int $typeHint The type of the symbol we want to retrieve.
	 *
	 * @throws Exception\UnboundIdentifierException The identifier cannot be resolved.
	 * @return AbstractNode the node representing the symbol.
	 */
	private function resolveRelative($symbol, $typeHint) {
		assert(self::isRelativelyQualified($symbol) || self::isUnqualified($symbol));

		list($current, $residue) = self::dequalifyOne($symbol);

		$child = $this->resolveSelf($current, $typeHint);
		if (empty($residue)) {
			return $child;
		} else if (!is_null(child)) {
			return $child->resolve($residue);
		} else {
			throw new Exception\UnboundIdentifierException($current, $this);
		}
	}

	/**
	 * Resolves a symbol in the current environment.
	 * @todo Implement type hint checking.
	 *
	 * @param string $symbol An unqualified symbol to resolve.
	 * @param int $typeHint The type of the symbol we want to retrieve.
	 *
	 * @return AbstractNode The node representing the symbol or null if the symbol cannot be
	 *                      resolved.
	 */
	private function resolveSelf($symbol, $typeHint) {
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
