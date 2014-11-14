<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	$ret = 5;
	while($ret < 10){
		$ret = $ret + $a;
	}
	$ret += 5;
	return $ret;
}

$a = func($_GET['tainted'], 5);
$b = func(5, 5);