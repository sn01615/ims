<?php

$t = 0;

$temp = file_get_contents('imsjobs.txt');

$ff = preg_replace_callback('/00000/', function($matches) use(&$t) {
	$t += 0.1;
	return $t;
}, $temp);
file_put_contents('filename.txt', $ff);
