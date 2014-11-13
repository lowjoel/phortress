<?php
namespace {

$glob = 1;
function a() {
	$glob = 4;
	return $glob;
}

$b = a();

class A {
	private $b;

	private static $c;

	public function testA() {
		TestNamespace\A();
	}

	public static function testB() {
		new \TestNamespace\B();
	}
}

}

namespace TestNamespace {
	function A() {
	}

	class B {
	}
}

namespace TestTestNamespace\TestNamespace {
	class C {}
}
