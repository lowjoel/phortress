<?php
namespace Phortress;

class EnvironmentTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The program we will be testing.
	 * @var \Phortress\Program
	 */
	private $program;

	public function setUp() {
		$file = realpath(__DIR__ . '/Fixture/environment_test.php');
		$this->program = loadGlassBoxProgram($file);
	}

	public function testCanFindFunction() {
		$this->assertTrue($this->program->environment->resolveFunction('a')
			->environment->getParent() instanceof GlobalEnvironment);
	}

	public function testCanFindVariableDefinitionInTopLevel() {
		$this->assertArrayHasKey('glob',
			(new \TestObject($this->program->parseTree[0]->environment))->variables);
		$this->assertArrayHasKey('b',
			(new \TestObject($this->program->parseTree[2]->environment))->variables);
		$this->assertNotEmpty(
			$this->program->parseTree[2]->environment->resolveVariable('glob'));
		$this->assertNotEmpty(
			$this->program->parseTree[2]->environment->resolveVariable('b'));
	}

	public function testCanFindVariableDefinitionInFunction() {
		$this->assertArrayHasKey('glob',
			(new \TestObject($this->program->parseTree[1]->stmts[0]->environment))->variables);
	}
}
