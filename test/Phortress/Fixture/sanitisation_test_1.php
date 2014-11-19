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
$result = md5($result);
mysql_query("Update stuff set stuff.store = " . $result);
