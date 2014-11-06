<?php
namespace Phortress;

class ProgramWithIncludesTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/Fixture/require_program_test.php');
		$this->program = loadGlassBoxProgram($this->file);
	}

	public function testParsesRequires() {
		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(1, count($this->program->files[$this->file]));
		$this->assertEquals(2, count($this->program->files[$this->included_file]));
	}
}
