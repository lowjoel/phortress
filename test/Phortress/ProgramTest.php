<?php
namespace Phortress;

use PhpParser\Node\Name;

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

	public function testGeneratesEnvironment() {
		// Check that we can find hello()
		$function = $this->program->environment->resolveFunction(
			new Name('hello'));
		$this->assertEquals('hello', $function->name);
		$this->assertEquals(0, count($function->params));
	}
}
