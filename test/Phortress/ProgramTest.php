<?php
namespace Phortress;

class ProgramTest extends \PHPUnit_Framework_TestCase {
	public function testParsesEntryFile() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$program = new Program($file);
		$program = new \TestObject($program);
		$program->parse();

		// Check that we have statements for test.php.
		$this->assertEquals(2, count($program->files[$file]));

		// And that it is idempotent
		$this->assertEquals(null, $program->input);
	}
}
