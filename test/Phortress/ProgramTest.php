<?php
namespace Phortress;

class ProgramTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The file we loaded the program from.
	 *
	 * @var String
	 */
	private $file;

	/**
	 * The file which the program is supposed to include.
	 *
	 * @var String
	 */
	private $included_file;

	/**
	 * @var Program
	 */
	private $program;

	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$this->included_file = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$this->program = loadGlassBoxProgram($this->file);
	}

	public function testParsesEntryFile() {
		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(2,
			$this->program->files[$this->file]->getStatementCount());
	}

	public function testParsesRequires() {
		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(1, count($this->program->files[$this->file]));
		$this->assertEquals(2, count($this->program->files[$this->included_file]));
	}

	public function testGeneratesEnvironment() {
		// Check that we can find hello()
		$function = $this->program->environment->resolveFunction('hello');
		$this->assertEquals('hello', $function->name);
		$this->assertEquals(0, count($function->params));
	}
}
