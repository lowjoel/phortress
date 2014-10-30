<?php
namespace Phortress;

class NamespaceEnvironment extends Environment {
	public function resolveClass($className) {
		if (self::isAbsolutelyQualified($className)) {
			return $this->getGlobal()->resolveClass($className);
		} else {
			list($nextNamespace, $className) =
				self::extractNamespaceComponent($className);
			return $this->resolveNamespace($nextNamespace)->
				resolveClass($className);
		}
	}

	public function resolveFunction($functionName) {
		if (self::isAbsolutelyQualified($functionName)) {
			return $this->getGlobal()->resolveFunction($functionName);
		} else {
			list($nextNamespace, $functionName) =
				self::extractNamespaceComponent($functionName);
			return $this->resolveNamespace($nextNamespace)->
				resolveFunction($functionName);
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


	public function createChild() {
		$environment = new NamespaceEnvironment($this->name);
		$environment->parent = $this;

		return $environment;
	}

	/**
	 * Creates a new Child namespace.
	 *
	 * @param string $namespaceName The name of the namespace. This must be
	 * unqualified.
	 * @return NamespaceEnvironment The new namespace environment, with the
	 * parent properly set.
	 */
	public function createChildNamespace($namespaceName) {
		$result = new NamespaceEnvironment(sprintf('%s\%s',
			$this->name, $namespaceName));
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
