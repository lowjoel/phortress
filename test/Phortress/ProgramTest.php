<?php
namespace Phortress;

class ProgramTest extends \PHPUnit_Framework_TestCase {
	public function testParsesEntryFile() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$program = loadGlassBoxProgram($file);

		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(2, $program->files[$file]->getStatementCount());
	}

	public function testParsesRequires() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/require_program.php');
		$included_file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$program = loadGlassBoxProgram($file);

		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(1, count($program->files[$file]));
		$this->assertEquals(2, count($program->files[$included_file]));
	}

	public function testGeneratesEnvironment() {
		// Load a program
		$file = realpath(__DIR__ . '/Fixture/environment_test.php');
		$program = loadGlassBoxProgram($file);

		// Check that we can find a()
		$function = $program->environment->resolveFunction('a');
		$this->assertEquals('a', $function->name);
		$this->assertEquals(0, count($function->params));
	}
}
