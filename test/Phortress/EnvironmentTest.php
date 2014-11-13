<?php
namespace Phortress;

use PhpParser\Node\Name;

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
		$this->assertTrue($this->program->environment->resolveFunction(new Name('a'))
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

	public function testCanFindClassInTopLevel() {
		$this->assertArrayHasKey('A',
			(new \TestObject($this->program->environment))->classes);
	}

	public function testCanFindClassProperty(){
		$classEnvironment = new \TestObject(
			(new \TestObject($this->program->environment))->classes['A']->environment);
		$instanceEnvironment = new \TestObject(
			$classEnvironment->instanceEnvironment);
		$this->assertArrayHasKey('b', $instanceEnvironment->variables);
	}

	public function testCanFindClassMethodDefinition(){
		$classEnvironment = new \TestObject(
			(new \TestObject($this->program->environment))->classes['A']->environment);
		$instanceEnvironment = new \TestObject(
			$classEnvironment->instanceEnvironment);
		$this->assertArrayHasKey('testA', $instanceEnvironment->functions);
	}
}
