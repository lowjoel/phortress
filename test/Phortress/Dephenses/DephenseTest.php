<?php
namespace Phortress\Dephenses;

class DephenseTest extends \PHPUnit_Framework_TestCase {
	public function setUp(){
		$this->file = realpath(__DIR__ . '/../Fixture/taint_test_1.php');
                $this->program = loadGlassBoxProgram($this->file);
	}

	public function testGetsAll() {
		$result = Dephense::getAll();
		$this->assertEquals(1, count($result));
	}

	public function testBasicAnnotation(){
            var_dump($this->program->parseTree);
            $this->analyser = new Taint\CodeAnalyser($this->program->parseTree);
            $this->analyser->analyse();
	}
}
