<?php
namespace Phortress;

class NamespaceEnvironment extends Environment {
	public function resolveClass($className) {
		$parent = $this->getParent();

		if (is_null($parent)) {
			$symbol = self::makeRelativelyQualifiedTo($symbol, '\\');
		} else if (self::isAbsolutelyQualified($symbol)) {
			return $parent->resolve($symbol, $typeHint);
		}

		return self::resolveRelative($symbol, $typeHint);
	}

	public function resolveFunction($functionName) {
	}
}
