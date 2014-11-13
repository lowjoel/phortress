<?php
namespace Phortress;
use Phortress\Exception\UnboundIdentifierException;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * Represents a mapping of symbols to its actual values: functions, constants,
 * variables. Symbol tables can be chained for use in nested environments
 * (namespaces, function scopes or closures)
 *
 * The base Environment class only deals with variables and constants. There
 * is a Namespace Environment which deals with classes and other namespaces,
 * as well as a Global Environment which deals with superglobals and global
 * constants.
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
abstract class Environment {
	/**
	 * The variable has been unset.
	 */
	const UNSET_ = null;

	/**
	 * The parent environment, or null if this is the global environment.
	 *
	 * @var Environment
	 */
	protected $parent;

	/**
	 * The name of this environment, mainly for debugging.
	 *
	 * @var String
	 */
	protected $name;

	/**
	 * The variables defined in the current environment.
	 *
	 * Remember that all variables are prefixed with $.
	 *
	 * @var array(String => \PhpParser\Node)
	 */
	protected $variables = array();

	/**
	 * Constructs a new, empty environment.
	 *
	 * @param string $name The name of the environment.
	 */
	protected function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Gets the parent environment for the current environment.
	 *
	 * @return Environment The parent environment, or null if this is the global
	 * environment.
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Gets the namespace that this environment is in. For example, if this is
	 * a function, it will get the namespace that this function is declared in.
	 *
	 * @return NamespaceEnvironment The namespace environment.
	 */
	public function getNamespace() {
		$parent = $this->getParent();
		while ($parent !== null) {
			if ($parent instanceof NamespaceEnvironment) {
				break;
			}
		}

		assert($parent !== null, 'The namespace of any environment cannot be ' .
			'null');
		return $parent;
	}

	/**
	 * Gets the global environment.
	 *
	 * @return GlobalEnvironment The global environment.
	 */
	public function getGlobal() {
		$parent = $this->getParent();
		while ($parent !== null) {
			if ($parent instanceof GlobalEnvironment) {
				break;
			}
		}

		assert($parent !== null, 'The global environment of any environment ' .
			'cannot be null');
		return $parent;
	}

	/**
	 * Resolves the declaration of a class.
	 *
	 * @param Name $className The name of a class to resolve. This can either
	 *                        be fully qualified, or relatively qualified.
	 * @return \PhpParser\Node
	 * @throws UnboundIdentifierException When the class has not been declared.
	 */
	public function resolveClass(Name $className) {
		return $this->getNamespace()->resolveClass($className);
	}

	/**
	 * Resolves the declaration of a function.
	 *
	 * @param Name $functionName The name of a function to resolve. This can either be fully
	 *                           qualified, or relatively qualified.
	 * @return \PhpParser\Node
	 * @throws UnboundIdentifierException When the function has not been
	 * declared.
	 */
	public function resolveFunction(Name $functionName) {
		return $this->getNamespace()->resolveFunction($functionName);
	}

	/**
	 * Resolves the declaration of a variable.
	 *
	 * @param Name|string $variableName The name of a variable to resolve.
	 * @return \PhpParser\Node
	 * @throws UnboundIdentifierException When the variable has not been declared.
	 */
	public function resolveVariable($variableName) {
		if (is_object($variableName)) {
			assert($variableName instanceof Name, 'Variable names must be string or Name.');
			assert(count($variableName->parts) === 1, 'Must be unqualified name');
			$variableName = $variableName->parts;
		} else {
			assert(true);
		}

		if (array_key_exists($variableName, $this->variables)) {
			$result = $this->variables[$variableName];
			if ($result === self::UNSET_) {
				throw new UnboundIdentifierException($variableName, $this);
			} else {
				return $result;
			}

		// We can only check our own local environment. We cannot pass a
		// function environment and check our namespace for variables.
		} else if (!is_null($this->getParent()) &&
			$this->shouldResolveVariablesInParentEnvironment()) {
			return $this->getParent()->resolveVariable($variableName);
		} else {
			throw new UnboundIdentifierException($variableName, $this);
		}
	}

	/**
	 * Check if the current environment should check the parent environment for variable
	 * resolutions.
	 *
	 * @return bool
	 */
	protected function shouldResolveVariablesInParentEnvironment() {
		return true;
	}

	/**
	 * Resolves the declaration of a constant.
	 *
	 * @param Name $constantName The name of a constant to resolve. This can
	 * either be fully qualified, or relatively qualified.
	 * @return \PhpParser\Node
	 * @throws UnboundIdentifierException When the constant has not been
	 * declared.
	 */
	public function resolveConstant(Name $constantName) {
		return $this->getNamespace()->resolveConstant($constantName);
	}

	/**
	 * Constructs a new environment with the current environment as a parent.
	 * This is used for variable definitions.
	 *
	 * @return Environment The new child environment.
	 */
	public abstract function createChild();

	/**
	 * Defines the given variable.
	 *
	 * @param Expr\Assign|Stmt\Property $node The symbol to register.
	 * @return Environment A new environment with the given symbol defined
	 * and parent environment set.
	 */
	public function defineVariableByValue(Node $node) {
		assert($node instanceof Expr\Assign || $node instanceof Stmt\Property);
		$result = $this->createChild();
		if ($node instanceof Expr\Assign) {
			$result->variables[$node->var->name] = $node;
		} else if ($node instanceof Stmt\Property) {
			foreach ($node->props as $prop) {
				$result->variables[$prop->name] = $node;
			}
		}

		return $result;
	}

	/**
	 * Checks if the given symbol is absolutely qualified.
	 *
	 * @param Name $symbol The name of the symbol.
	 * @return bool
	 */
	protected static function isAbsolutelyQualified(Name $symbol) {
		return $symbol instanceof Name\FullyQualified;
	}

	/**
	 * Checks if the given symbol is relatively qualfied.
	 *
	 * @param Name $symbol The name of the symbol.
	 * @return bool
	 */
	protected static function isRelativelyQualified(Name $symbol) {
		return !self::isAbsolutelyQualified($symbol);
	}

	/**
	 * Checks whether the given symbol is unqualified.
	 *
	 * @param Name $symbol The symbol to check.
	 * @return bool True if the symbol is unqualified.
	 */
	protected static function isUnqualified(Name $symbol) {
		return self::isRelativelyQualified($symbol) &&
			!($symbol instanceof Name\Relative);
	}

	/**
	 * Extracts the first namespace component from the given symbol, and returns
	 * the namespace and the tail of the symbol.
	 *
	 * @param Name $symbol
	 * @return array(string, Name)
	 */
	protected static function extractNamespaceComponent(Name $symbol) {
		assert(!self::isAbsolutelyQualified($symbol));

		return array(
			count($symbol->parts) === 1 ?
				null :
				new Name(array_slice($symbol->parts, 1), $symbol->getAttributes()),
			$symbol->parts[0]
		);
	}
}
