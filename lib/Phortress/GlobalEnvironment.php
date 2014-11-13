<?php
namespace Phortress;
use PhpParser\Node\Name;
use PhpParser\Node\Name\Relative;

/**
 * The global environment for a program.
 *
 * @package Phortress
 */
class GlobalEnvironment extends NamespaceEnvironment {
	/**
	 * The superglobals for this environment.
	 *
	 * @var array
	 */
	private $superglobals;

	public function __construct() {
		parent::__construct('Global', null);

		$this->superglobals = array(
			'GLOBALS' => &$this->variables,
			'_SERVER' => array(),
			'_GET' => array(),
			'_POST' => array(),
			'_FILES' => array(),
			'_COOKIE' => array(),
			'_SESSION' => array(),
			'_REQUEST' => array(),
			'_ENV' => array()
		);

		self::copyValueReferences($this->variables, $this->superglobals);
	}

	/**
	 * Gets the superglobals in this global environment.
	 *
	 * @return array
	 */
	public function &getSuperglobals() {
		return $this->superglobals;
	}

	public function getGlobal() {
		return $this;
	}

	public function resolveNamespace(Name $namespaceName = null) {
		if ($namespaceName !== null &&
			self::isAbsolutelyQualified($namespaceName)) {
			$namespaceName = new Relative(
				$namespaceName->parts,
				$namespaceName->getAttributes());
		}

		return parent::resolveNamespace($namespaceName);
	}

	public function resolveClass(Name $className) {
		if (self::isAbsolutelyQualified($className)) {
			$className = new Relative(
				$className->parts,
				$className->getAttributes());
		}

		return parent::resolveClass($className);
	}
}
