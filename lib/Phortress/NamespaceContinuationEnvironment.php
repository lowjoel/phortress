<?php
namespace Phortress;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;

/**
 * Namespace Continuation Environments: these are continuation of namespaces
 * for the purposes of variable declarations. When defining namespace-visible
 * identifiers, e.g constants or functions, this sets it on the actual
 * namespace.
 */
class NamespaceContinuationEnvironment extends NamespaceEnvironment {
	public function createNamespace($namespaceName) {
		$this->getNamespaceEnvironment()->createNamespace($namespaceName);
	}

	public function createClass(Class_ $class) {
		return $this->getNamespaceEnvironment()->createClass($class);
	}

	public function createFunction(Stmt $function) {
		return $this->getNamespaceEnvironment()->createFunction($function);
	}

	public function resolveFunction($functionName) {
		return $this->getNamespaceEnvironment()->resolveFunction($functionName);
	}

	public function resolveClass($className) {
		return $this->getNamespaceEnvironment()->resolveClass($className);
	}

	public function resolveNamespace($namespaceName) {
		return $this->getNamespaceEnvironment()->resolveNamespace($namespaceName);
	}

	public function resolveConstant($constantName) {
		return $this->getNamespaceEnvironment()->resolveConstant($constantName);
	}

	/**
	 * Gets the namespace environment for this environment. This is a shorthand
	 * for defining functions and constants.
	 *
	 * @return NamespaceEnvironment
	 */
	private function getNamespaceEnvironment() {
		$parent = $this->getParent();
		while ($parent && get_class($parent) === '\Phortress\NamespaceContinuationEnvironment') {
			$parent = $parent->getParent();
		}

		assert($parent, 'NamespaceContinuationEnvironments must be enclosed by a ' .
			'NamespaceEnvironment');
		return $parent;
	}
}
