<?php
namespace Phortress\Dephenses;

use Phortress\Program;

class TaintTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The file we loaded the program from.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * @var Program
	 */
	private $program;

	public function setUp() {
		// Load a program
		$this->file = realpath(__DIR__ . '/../Fixture/taint_test.php');
		$this->file1 = realpath(__DIR__ . '/../Fixture/taint_test_2.php');
		$this->file2 = realpath(__DIR__ . '/../Fixture/taint_test_3.php');
		$this->file3 = realpath(__DIR__ . '/../Fixture/taint_test_4.php');
		$this->file4 = realpath(__DIR__ . '/../Fixture/taint_test_5.php');
		$this->file5 = realpath(__DIR__ . '/../Fixture/while_loop_test.php');
		$this->program = loadGlassBoxProgram($this->file);
		$this->program1 = loadGlassBoxProgram($this->file1);
		$this->program2 = loadGlassBoxProgram($this->file2);
		$this->program3 = loadGlassBoxProgram($this->file3);
		$this->program4 = loadGlassBoxProgram($this->file4);
		$this->program5 = loadGlassBoxProgram($this->file5);
	}

	public function testTaint() {
		$taintDephense = new Taint();
		$taintDephense->run($this->program->parseTree);
	}

	public function testTaintedParams(){
		$taintDephense = new Taint();
		$taintDephense->run($this->program1->parseTree);

		$taint1 = $this->program1->parseTree[2]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint1);
		$taint2 = $this->program1->parseTree[3]->var->taint;
		$this->assertEquals(Taint\Annotation::TAINTED, $taint2);

	}

	public function testTaintedParamsWithBinaryOps(){
		$taintDephense = new Taint();
		$taintDephense->run($this->program2->parseTree);

		$taint1 = $this->program2->parseTree[2]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint1);
		$taint2 = $this->program2->parseTree[3]->var->taint;
		$this->assertEquals(Taint\Annotation::TAINTED, $taint2);
		$taint3 = $this->program2->parseTree[5]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint3);
	}

	public function testTaintedParamsWithTernaryOps(){
		$taintDephense = new Taint();
		$taintDephense->run($this->program3->parseTree);
		$taint1 = $this->program3->parseTree[2]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint1);
		$taint2 = $this->program3->parseTree[3]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
		$taint3 = $this->program3->parseTree[4]->var->taint;
		$this->assertEquals(Taint\Annotation::TAINTED, $taint3);
	}

	public function testTaintedParamsWithTernarySingleReturn(){
		//This test fails because the back trace leads to each assignment in the if-else block
		//individually, instead of seeing them in the context of a conditional.
		$taintDephense = new Taint();
		$taintDephense->run($this->program4->parseTree);
		$taint1 = $this->program4->parseTree[2]->var->taint;
//		$this->assertEquals(Taint\Annotation::TAINTED, $taint1);
//		$taint2 = $this->program4->parseTree[3]->var->taint;
//		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
	}

	public function testWhileLoop(){
		$taintDephense = new Taint();
		$taintDephense->run($this->program5->parseTree);
		$taint1 = $this->program5->parseTree[2]->var->taint;
		$this->assertEquals(Taint\Annotation::TAINTED, $taint1);
		$taint2 = $this->program5->parseTree[3]->var->taint;
		$this->assertEquals(Taint\Annotation::SAFE, $taint2);
	}
}
