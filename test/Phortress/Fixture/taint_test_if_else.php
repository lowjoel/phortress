<?php
$_GET["param"] = "hello";
$a = $_GET;
$b = 1 + 8;
if($b > 10){
	$c = $a["param"];
}else{
	$c = 10;
}
$d = $c;