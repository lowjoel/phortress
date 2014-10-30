<?php
namespace Phortress;

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
		parent::__construct('Global');

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

	public function createChild() {
		$result = parent::createChild();
		self::copyValueReferences($result->variables, $this->superglobals);

		return $result;
	}

	/**
	 * Copy the values by reference from one array to another.
	 */
	private static function copyValueReferences($to, $from) {
		// Copy the superglobals.
		foreach ($from as $key => &$value) {
			$to[$key] = &$value;
		}
	}
}
