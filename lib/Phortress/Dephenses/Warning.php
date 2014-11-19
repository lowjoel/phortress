<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 19/11/14
 * Time: 10:24 AM
 */

namespace Phortress\Dephenses;


class Warning extends Message{
	public function __construct($message, $node) {
		parent::__construct($message, $node);
	}
} 