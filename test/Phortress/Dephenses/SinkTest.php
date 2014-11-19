<?php
/**
 * Created by PhpStorm.
 * User: naomileow
 * Date: 19/11/14
 * Time: 9:37 AM
 */

namespace Phortress\Dephenses;


use Phortress\Dephenses\Taint\TaintEnvironment;
use PhpParser\Node\Expr\Variable;

class SinkTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/../Fixture/vulnerable_test_1.php');
		$this->program = loadGlassBoxProgram($this->file);
		$this->file1 = realpath(__DIR__ . '/../Fixture/vulnerable_test_2.php');
		$this->program1 = loadGlassBoxProgram($this->file1);
	}

	public function testSQLInjection(){
		$taintDephense = new Taint();
		$runResult = $taintDephense->run($this->program->parseTree);
		$this->assertEquals(1, count($runResult));
		$taint = $this->getVariableTaint($this->program->parseTree[2]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint);
	}

	public function testSQLInjectionInsideFuncCall(){
		$taintDephense = new Taint();
		$runResult = $taintDephense->run($this->program1->parseTree);
//		$this->assertGreaterThan(0, count($runResult));
		$taint = $this->getVariableTaint($this->program1->parseTree[2]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint);
	}

	public function getVariableTaint(Variable $var){
		$assignEnv = $var->environment->resolveVariable($var->name)->environment;
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
		$taintResult = $taintEnv->getTaintResult($var->name);
		return $taintResult->getTaint();
	}
}
 