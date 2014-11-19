<?php
$tainted = $_GET["tainted"];
function func($a, $b) {
	if($a > 3){
		$ret = $b;
	}else{
		$ret = "hello";
	}
	echo("stuff " . $ret);
	return $ret;
}

$a = func(5, $tainted);