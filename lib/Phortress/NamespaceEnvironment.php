<?php
namespace Phortress;

use Phortress\Exception\UnboundIdentifierException;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;

class NamespaceEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait,
		EnvironmentHasConstantsTrait;

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

	public function resolveNamespace(Name $namespaceName = null) {
		if ($namespaceName === null) {
			return $this;
		} else if (self::isAbsolutelyQualified($namespaceName)) {
			return $this->getGlobal()->resolveNamespace($namespaceName);
		} else if (self::isUnqualified($namespaceName)) {
			assert(count($namespaceName->parts) === 1, 'Must be unqualified name.');
			$namespaceName = $namespaceName->parts[0];
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

	public function resolveClass(Name $className) {
		if (self::isAbsolutelyQualified($className)) {
			return $this->getGlobal()->resolveClass($className);
		} else if (self::isUnqualified($className)) {
			assert(count($className->parts) === 1, 'Must be unqualified name.');
			$className = $className->parts[0];
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

	public function createChild() {
		$environment = new NamespaceContinuationEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}

	/**
	 * Creates a new Child namespace.
	 *
	 * @param Namespace_ $namespace The name of the namespace. This must be unqualified.
	 * @return NamespaceEnvironment The new namespace environment, with the parent properly set.
	 */
	public function createNamespace(Namespace_ $namespace) {
		$namespaceEnvironment = $this;
		foreach ($namespace->name->parts as $part) {
			// Create the intermediate environment if it doesn't exist.
			if (!array_key_exists($part, $namespaceEnvironment->namespaces)) {
				$namespaceEnvironment->namespaces[$part] =
					new NamespaceEnvironment(
						sprintf('%s\%s', $namespaceEnvironment->name, $part),
						$this);
			}

			$namespaceEnvironment = $namespaceEnvironment->namespaces[$part];
		}

		return $namespaceEnvironment;
	}

	/**
	 * Creates a new Class in this namespace.
	 *
	 * @param Class_ $class The class parse tree node.
	 * @return ClassEnvironment The new class environment, with the parent properly set.
	 */
	public function createClass(Class_ $class) {
		$this->classes[$class->name] = $class;
		$result = new ClassEnvironment(sprintf('%s\%s', $this->name, $class->name), $this);
		return $result;
	}
}
