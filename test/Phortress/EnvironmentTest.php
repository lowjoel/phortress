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
		$globalNamespace = $this->program->parseTree[0]->stmts;
		$this->assertArrayHasKey('glob',
			(new \TestObject($globalNamespace[0]->environment))->variables);
		$this->assertArrayHasKey('b',
			(new \TestObject($globalNamespace[2]->environment))->variables);
		$this->assertNotEmpty(
			$globalNamespace[2]->environment->resolveVariable('glob'));
		$this->assertNotEmpty(
			$globalNamespace[2]->environment->resolveVariable('b'));
	}

	public function testCanFindVariableDefinitionInFunction() {
		$globalNamespace = $this->program->parseTree[0]->stmts;
		$this->assertArrayHasKey('glob',
			(new \TestObject($globalNamespace[1]->stmts[0]->environment))->variables);
	}

	public function testCanFindClassInTopLevel() {
		$this->assertArrayHasKey('A',
			(new \TestObject($this->program->environment))->classes);
	}

	public function testCanFindClassProperty(){
		$classEnvironment = new \TestObject(
			(new \TestObject($this->program->environment))->classes['A']->environment);
		$this->assertArrayHasKey('c', $classEnvironment->variables);
		$instanceEnvironment = new \TestObject(
			$classEnvironment->instanceEnvironment);
		$this->assertArrayHasKey('b', $instanceEnvironment->variables);
	}

	public function testCanFindClassMethodDefinition() {
		$classEnvironment = new \TestObject(
			(new \TestObject($this->program->environment))->classes['A']->environment);
		$this->assertArrayHasKey('testB', $classEnvironment->functions);
		$instanceEnvironment = new \TestObject(
			$classEnvironment->instanceEnvironment);
		$this->assertArrayHasKey('testA', $instanceEnvironment->functions);
	}

	/**
	 * @expectedException \Phortress\Exception\UnboundIdentifierException
	 */
	public function testCanFindNamespaceDefinition() {
		$namespaceTestNamespace =
			$this->program->environment->resolveNamespace(new Name('TestNamespace'));
		$this->assertEquals($namespaceTestNamespace,
			$this->program->parseTree[1]->environment);
		$this->assertEquals('Global\TestNamespace',
			$namespaceTestNamespace->getName());
		$this->assertNotEmpty(
			$namespaceTestNamespace->resolveFunction(new Name('A')));

		$namespaceTestTestNamespaceTestNamespace =
			$this->program->environment->resolveNamespace(new Name\FullyQualified(
				array('TestTestNamespace', 'TestNamespace')));
		$this->assertEquals('Global\TestTestNamespace\TestNamespace',
			$namespaceTestTestNamespaceTestNamespace->getName());
		$this->assertNotEmpty(
			$namespaceTestTestNamespaceTestNamespace->resolveClass(new Name('C')));
		$namespaceTestTestNamespaceTestNamespace->resolveClass(new Name('B'));
	}
}
