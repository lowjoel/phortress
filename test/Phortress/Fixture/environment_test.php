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
	}

	public static function testB() {
	}
}

}

namespace TestNamespace {
	function A() {
	}
}
