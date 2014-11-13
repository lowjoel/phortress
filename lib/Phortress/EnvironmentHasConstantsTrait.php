<?php
namespace Phortress;

use PhpParser\Node\Const_;
use PhpParser\Node\Name;

trait EnvironmentHasConstantsTrait {
	/**
	 * The constants declared in this namespace.
	 *
	 * @var array(string => Const_)
	 */
	protected $constants = array();

	public abstract function getGlobal();
	public abstract function resolveNamespace(Name $namespace);

	public function resolveConstant(Name $constantName) {
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
	 * Creates a new Function environment.
	 *
	 * @param Const_ $value The value to create a constant for.
	 * @return Environment
	 */
	public function createConstant(Const_ $value) {
		$this->constants[$value->name] = $value;

		return $this;
	}
}
