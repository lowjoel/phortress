<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	$t = $a;
	$s = $b;
	return $s;
}

$a = func($tainted, 1);
$b = func(1, $tainted);