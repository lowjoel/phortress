<?php
namespace Phortress\Dephenses;

use Phortress\Program;

class TaintTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The file we loaded the program from.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * @var Program
	 */
	private $program;

	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/../Fixture/taint_test.php');
		$this->program = loadGlassBoxProgram($this->file);
	}

	public function testTaint() {
		$taintDephense = new Taint();
		$taintDephense->run($this->program->parseTree);
	}
}
