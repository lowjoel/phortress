<?php
namespace Phortress;

class EnvironmentTest extends \PHPUnit_Framework_TestCase {
	public function testCanFindFunction() {
		$file = realpath(__DIR__ . '/Fixture/environment_test.php');
		$program = loadGlassBoxProgram($file);

		$this->assertTrue($program->environment->resolveFunction('a')
			->environment->getParent() instanceof GlobalEnvironment);
	}
} 
