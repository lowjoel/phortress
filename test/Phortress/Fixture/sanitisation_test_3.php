<?php

function func($a, $b) {
	if($a > 3){
		$ret = $b;
	}else{
		$ret = "hello";
	}
	return $ret;
}

$tainted = $_GET["tainted"];
$result = func(1, $tainted);
$cleaned = mysql_real_escape_string($result);
mysql_query("Update stuff set stuff.store = " . $cleaned);