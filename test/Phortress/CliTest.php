<?php
namespace Phortress;

class CliTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Tests whether the arguments from getopt are correctly handled.
	 */
	public function testArgParsing() {
		// Force a parse of the options we give.
		$cli = new Cli();
		$cli = new \TestObject($cli);
		$cli->parseOptions(array('f' => 'test.php'));

		// Check that they are as we expect.
		$this->assertEquals(array('test.php'), $cli->getClass()->files);
	}
}
