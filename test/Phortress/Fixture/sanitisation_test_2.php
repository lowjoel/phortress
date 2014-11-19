<?php

function func($a, $b) {
	if($a > 3){
		$ret = $b;
	}else{
		$ret = "hello";
	}
	$clean = md5($ret);
	mysql_query("Update stuff set stuff.store = " . $clean);
	return $ret;
}

$tainted = $_GET["tainted"];
$a = func(5, $tainted);