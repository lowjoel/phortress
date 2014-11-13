<?php
namespace Phortress;

trait EnvironmentHasConstantsTrait {
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
}
