<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	$ret = 0;
	if($a > 3){
		$ret = $b;
	}else{
		$ret = 1;
	}
	return $ret;
}

$a = func(5, $_GET['tainted']);
$b = func(5, 5);