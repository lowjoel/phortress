<?php
$_GET["param"] = "hello";
$a = $_GET;
$b = 1 + 8;
$c = $a["param"];
$d = "stuff" + $a["param"];
$e = $a["param"] + "stuff";