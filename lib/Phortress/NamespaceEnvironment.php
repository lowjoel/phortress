<?php
namespace Phortress;

use Phortress\Exception\UnboundIdentifierException;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;

class NamespaceEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait;

	/**
	 * The namespaces declared in this namespace.
	 *
	 * @var array(string => NamespaceEnvironment)
	 */
	protected $namespaces = array();

	/**
	 * The classes declared in this namespace.
	 *
	 * @var array(string => Environment)
	 */
	protected $classes = array();

	/**
	 * Resolves the given namespace to an environment.
	 *
	 * @param string $namespaceName The name of the namespace to resolve. This
	 * can either be fully qualified, or relatively qualified.
	 * @return NamespaceEnvironment
	 * @throws UnboundIdentifierException When the identifier cannot be found.
	 */
	public function resolveNamespace($namespaceName) {
		if ($namespaceName === null) {
			return $this;
		} else if (self::isAbsolutelyQualified($namespaceName)) {
			return $this->getGlobal()->resolveNamespace($namespaceName);
		} else if (self::isUnqualified($namespaceName)) {
			if (array_key_exists($namespaceName, $this->namespaces)) {
				return $this->namespaces[$namespaceName];
			} else {
				throw new UnboundIdentifierException($namespaceName, $this);
			}
		} else {
			list($nextNamespace, $namespaceName) =
				self::extractNamespaceComponent($namespaceName);
			return $this->resolveNamespace($nextNamespace)->
				resolveNamespace($namespaceName);
		}
	}

	public function resolveClass($className) {
		if (self::isAbsolutelyQualified($className)) {
			return $this->getGlobal()->resolveClass($className);
		} else if (self::isUnqualified($className)) {
			if (array_key_exists($className, $this->classes)) {
				return $this->classes[$className];
			} else {
				throw new UnboundIdentifierException($className, $this);
			}
		} else {
			list($nextNamespace, $className) =
				self::extractNamespaceComponent($className);
			return $this->resolveNamespace($nextNamespace)->
				resolveClass($className);
		}
	}

	public function resolveConstant($constantName) {
		if (self::isAbsolutelyQualified($constantName)) {
			return $this->getGlobal()->resolveConstant($constantName);
		} else {
			list($nextNamespace, $constantName) =
				self::extractNamespaceComponent($constantName);
			return $this->resolveNamespace($nextNamespace)->
				resolveConstant($constantName);
		}
	}

	/**
	 * Extracts the first namespace component from the given symbol, and returns
	 * the namespace and the tail of the symbol.
	 *
	 * @param Name|string $symbol
	 * @return String[]
	 */
	private static function extractNamespaceComponent($symbol) {
		assert(!self::isAbsolutelyQualified($symbol));

		if (is_string($symbol)) {
			$firstSlash = strpos($symbol, '\\');
			if ($firstSlash === false) {
				return array(null, $symbol);
			} else {
				return array(
					substr($symbol, 0, $firstSlash),
					substr($symbol, $firstSlash + 1)
				);
			}
		} else {
			return array(
				count($symbol->parts) === 1 ?
					null :
					new Name(array_slice($symbol->parts, 1), $symbol->getAttributes()),
				$symbol->parts[0]
			);
		}
	}

	public function createChild() {
		$environment = new NamespaceContinuationEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}

	/**
	 * Creates a new Child namespace.
	 *
	 * @param string $namespaceName The name of the namespace. This must be unqualified.
	 * @return NamespaceEnvironment The new namespace environment, with the parent properly set.
	 */
	public function createNamespace($namespaceName) {
		$result = new NamespaceEnvironment(sprintf('%s\%s',
			$this->name, $namespaceName));
		$result->parent = $this;

		return $result;
	}

	/**
	 * Creates a new Class in this namespace.
	 *
	 * @param Class_ $class The class parse tree node.
	 * @return ClassEnvironment The new class environment, with the parent properly set.
	 */
	public function createClass(Class_ $class) {
		$this->classes[$class->name] = $class;
		$result = new ClassEnvironment(sprintf('%s\%s', $this->name, $class->name));
		$result->parent = $this;
		return $result;
	}

	/**
	 * Copy the values by reference from one array to another.
	 */
	protected static function copyValueReferences($to, $from) {
		foreach ($from as $key => &$value) {
			$to[$key] = &$value;
		}
	}
}
