<?php
namespace Phortress;

use Phortress\Dephenses\Error;

class CliTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var Cli
	 */
	private $cli;

	public function setUp() {
		$this->cli = new \TestObject(new Cli());
	}

	/**
	 * Tests whether the arguments from getopt are correctly handled.
	 */
	public function testArgParsing() {
		// Force a parse of the options we give.
		$this->cli->parseOptions(array('f' => 'test.php'));

		// Check that they are as we expect.
		$this->assertEquals(array('test.php'), $this->cli->getClass()->files);
	}

	public function testErrorPrinting() {
		$this->expectOutputString(
			"\033[31m[Error]   \033[0mTest at \033[33mline 1\033[0m" . PHP_EOL
		);

		$parser = new Parser;
		$this->cli->getClass()->printResults(array(
				new Error("Test", $parser->parse('<?php $x = 3;')[0])
			));
	}

	public function testCheck() {
		$this->cli->parseOptions(array('f' => __DIR__ . '/Fixture/basic_program_test.php'));
		$this->cli->check();
	}
}
