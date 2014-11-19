<?php

function func($a, $b) {
	if($a > 3){
		$ret = $b;
	}else{
		$ret = "hello";
	}
	mysql_query("Update stuff set stuff.store = " . $ret);
	return $ret;
}

$tainted = $_GET["tainted"];
$a = func(5, $tainted);