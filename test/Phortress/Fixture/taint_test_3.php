<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	$t = $a + $a;
	$s = $b + $b;
	return $s;
}

$a = func($tainted, 1);
$b = func(1, $tainted);
$c = func($tainted, 3);