<?php
namespace Phortress\Dephenses;

/**
 * An error to be returned to the user-facing component.
 *
 * @package Phortress\Dephenses
 */
class Error extends Message {
	public function __construct($message, $node) {
		parent::__construct($message, $node);
	}
}
