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


	/**
	 * Checks if the given symbol is absolutely qualified.
	 *
	 * @param $symbol The name of the symbol.
	 * @return bool
	 */
	private static function isAbsolutelyQualified($symbol) {
		return substr($symbol, 0, 1) === '\\';
	}

	/**
	 * Checks if the given symbol is relatively qualfied.
	 *
	 * @param $symbol The name of the symbol.
	 * @return bool
	 */
	private static function isRelativelyQualified($symbol) {
		return !self::isAbsolutelyQualified($symbol);
	}

	/**
	 * Checks whether the given symbol is unqualified.
	 *
	 * @param string $symbol The symbol to check.
	 * @return bool True if the symbol is unqualified.
	 */
	private static function isUnqualified($symbol) {
		return self::isRelativelyQualified($symbol) &&
			strpos($symbol, '\\') === false
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
