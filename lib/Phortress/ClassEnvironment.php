<?php
namespace Phortress;

class ClassEnvironment extends Environment {
	use EnvironmentHasFunctionsTrait,
		EnvironmentHasConstantsTrait;

	/**
	 * Creates child environments for classes. This should never be called,
	 * so this returns itself.
	 *
	 * Defining variables in a class does not give a new environment.
	 *
	 * @return $this
	 */
	public function createChild() {
		return $this;
	}
}
