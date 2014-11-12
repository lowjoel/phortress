<?php
namespace Phortress\Dephenses;

class DephenseTest extends \PHPUnit_Framework_TestCase {
	public function testGetsAll() {
		$result = Dephense::getAll();
		$this->assertEquals(1, count($result));
	}
}
