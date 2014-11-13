<?php
function tainted() {
	return $_GET['tainted'];
}

function clean() {
	return 0;
}

$a = tainted();
$b = clean();
