<?php
namespace Phortress;

class ProgramTest extends \PHPUnit_Framework_TestCase {
	public function testParsesEntryFile() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$program = new Program($file);
		$program = new \TestObject($program);
		$program->parse();

		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(2, $program->files[$file]->getStatementCount());
	}

	public function testParsesRequires() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/require_program.php');
		$included_file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$program = new Program($file);
		$program = new \TestObject($program);
		$program->parse();

		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(1, count($program->files[$file]));
		$this->assertEquals(2, count($program->files[$included_file]));
	}
}
