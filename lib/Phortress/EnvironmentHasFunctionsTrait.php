<?php
namespace Phortress;

use PhpParser\Node\Stmt\Function_;

trait EnvironmentHasFunctionsTrait {
	/**
	 * The functions declared in this namespace.
	 *
	 * @var array(string => FunctionEnvironment)
	 */
	private $functions = array();

	public function resolveFunction($functionName) {
		if (self::isAbsolutelyQualified($functionName)) {
			return $this->getGlobal()->resolveFunction($functionName);
		} else if (self::isUnqualified($functionName)) {
			if (array_key_exists($functionName, $this->functions)) {
				return $this->functions[$functionName];
			} else {
				throw new UnboundIdentifierException($functionName, $this);
			}
		} else {
			list($nextNamespace, $functionName) =
				self::extractNamespaceComponent($functionName);
			return $this->resolveNamespace($nextNamespace)->
			resolveFunction($functionName);
		}
	}

	/**
	 * Creates a new Function environment.
	 *
	 * @param Function_ $function The function to create an environment for.
	 * @return FunctionEnvironment
	 */
	public function createChildFunction(Function_ $function) {
		$this->functions[$function->name] = $function;

		$result = new FunctionEnvironment(sprintf('%s\%s',
				$this->name, $function->name));
		$result->parent = $this;

		foreach ($function->params as $param) {
			$result->variables[$param->name] = $param;
		}

		return $result;
	}
}
