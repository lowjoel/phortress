<?php
$tainted = $_GET['tainted'];
function func($a, $b) {
	if($a > 3){
		return $b;
	}else{
		return 1;
	}
}

$a = func($_GET['tainted'], 5);
$b = func(5, 5);
$a = func(5, $_GET['tainted']);