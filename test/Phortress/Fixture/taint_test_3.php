<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	$t = $a + $a;
	$s = $b + $b;
	return $s;
}

$a = func($tainted, 1);
$b = func(1, $tainted);

// function func($a, $b) {
// 	$t = $a + $a;
// 	$s = $b + 1;
// 	return $s;
// }

// $c = func(1, 1);