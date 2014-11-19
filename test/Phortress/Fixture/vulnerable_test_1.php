<?php

function func($a, $b) {
	if($a > 3){
		$ret = $b;
	}else{
		$ret = "hello";
	}
//	mysql_query("Update stuff set stuff.store = " . $ret);
	return $ret;
}

$tainted = $_GET["tainted"];
$result = func(1, $tainted);
mysql_query("Update stuff set stuff.store = " . $result);
