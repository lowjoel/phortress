<?php
namespace Phortress\Dephenses;

/**
 * An abstract message class that can be returned to the user-facing component.
 *
 * @package Phortress\Dephenses
 */
class Message {
	/**
	 * @var String The message to show.
	 */
	protected $message;

	/**
	 * @var \PhpParser\Node The node which triggered the error.
	 */
	protected $node;

	/**
	 * @param String $message The message to show.
	 * @param \PhpParser\Node $node The node which triggered the error.
	 */
	protected function __construct($message, $node) {
		$this->message = $message;
		$this->node = $node;
	}

	/**
	 * Gets the message to display.
	 *
	 * @return String
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Gets the node triggering the error.
	 *
	 * @return \PhpParser\Node
	 */
	public function getNode() {
		return $this->node;
	}
}
