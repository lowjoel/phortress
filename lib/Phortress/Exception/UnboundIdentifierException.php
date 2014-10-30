<?php
namespace Phortress\Exception;

/**
 * Represents a failed attempt to resolve an identifier in an environment.
 *
 * @package Phortress\Exception
 */
class UnboundIdentifierException extends ParserException {
	/**
	 * The symbol which we were trying to look up.
	 *
	 * @var string
	 */
	private $symbol;

	/**
	 * The environment in which we attempted look up.
	 * @var Environment
	 */
	private $environment;

	/**
	 * @param string $symbol
	 * @param \Phortress\Environment $environment
	 */
	public function __construct($symbol, $environment) {
		$this->symbol = $symbol;
		$this->environment = $environment;
	}
}
