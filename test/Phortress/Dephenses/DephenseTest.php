<?php
namespace Phortress\Dephenses;

use Phortress\Dephenses\Taint\TaintEnvironment;
use PhpParser\Node\Expr\Variable;

class DephenseTest extends \PHPUnit_Framework_TestCase {
	public function setUp(){
		$this->file = realpath(__DIR__ . '/../Fixture/taint_test_1.php');
		$this->file1 =  realpath(__DIR__ . '/../Fixture/taint_statement_test.php');
		$this->program = loadGlassBoxProgram($this->file);
		$this->program1 = loadGlassBoxProgram($this->file1);
	}

	public function testGetsAll() {
		$result = Dephense::getAll();
		$this->assertEquals(1, count($result));
	}

//	public function testBasicAnnotation(){
//            $this->analyser = new Taint\CodeAnalyser($this->program->parseTree);
//            $this->analyser->analyse();
//            $taint = $this->program->parseTree[1]->var->taint;
//            $this->assertEquals(Taint\Annotation::TAINTED, $taint);
//            $taint1 = $this->program->parseTree[2]->var->taint;
//            $this->assertEquals(Taint\Annotation::SAFE, $taint1);
//            $taint2 = $this->program->parseTree[3]->var->taint;
//            $this->assertEquals(Taint\Annotation::TAINTED, $taint2);
//            $taint3 = $this->program->parseTree[4]->var->taint;
//            $this->assertEquals(Taint\Annotation::TAINTED, $taint3);
//	}

	public function testBasicAnnotation(){
		$this->analyser = new Taint\CodeAnalyser($this->program->parseTree);
		$this->analyser->analyse();
		$taint = $this->getVariableTaint($this->program->parseTree[1]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint);
		$taint1 = $this->getVariableTaint($this->program->parseTree[2]->var);
		$this->assertEquals(Taint\Annotation::SAFE, $taint1);
		$taint2 = $this->getVariableTaint($this->program->parseTree[3]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint2);
		$taint3 = $this->getVariableTaint($this->program->parseTree[4]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint3);
	}

	public function getVariableTaint(Variable $var){
		$assignEnv = $var->environment->resolveVariable($var->name)->environment;
		$taintEnv = TaintEnvironment::getTaintEnvironmentFromEnvironment($assignEnv);
		$taintResult = $taintEnv->getTaintResult($var->name);
		return $taintResult->getTaint();
	}

//	public function testChangingAnnotation(){
//		$taintDephense = new Taint();
//		$taintDephense->run($this->program1->parseTree);
//
//		$taint1 = $this->program1->parseTree[3]->var->taint;
//		$this->assertEquals(Taint\Annotation::TAINTED, $taint1);
//		$taint2 = $this->program1->parseTree[6]->var->taint;
//		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
//		$taint2 = $this->program1->parseTree[7]->var->taint;
//		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
//	}

	public function testChangingAnnotation(){
		$taintDephense = new Taint();
		$taintDephense->run($this->program1->parseTree);

		$taint1 = $this->getVariableTaint($this->program1->parseTree[3]->var);
		$this->assertEquals(Taint\Annotation::TAINTED, $taint1);
		$taint2 = $this->getVariableTaint($this->program1->parseTree[6]->var);
		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
		$taint2 = $this->getVariableTaint($this->program1->parseTree[7]->var);
		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
	}
}
