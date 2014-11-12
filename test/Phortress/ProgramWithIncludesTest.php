<?php
namespace Phortress;

class ProgramWithIncludesTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The file we are parsing.
	 * @var string
	 */
	private $file;

	/**
	 * The file we are including.
	 * @var string
	 */
	private $includedFile;

	/**
	 * The program which we loaded.
	 * @var Program
	 */
	private $program;

	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/Fixture/require_program_test.php');
		$this->includedFile = realpath(__DIR__ . '/Fixture/basic_program_test.php');
		$this->program = loadGlassBoxProgram($this->file);
	}

	public function testParsesRequires() {
		// Check that we have statements for basic_program_test.php.
		$this->assertEquals(1,
			$this->program->files[$this->file]->getStatementCount());
		$this->assertEquals(2,
			$this->program->files[$this->includedFile]->getStatementCount());
	}
}
