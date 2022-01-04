<?php
//echo "\033[7A\033[1;35m BASH \033[7B\033[6D";
$build = "666-3456153727";
$version = "1.01";
$time = "1641021099";
if (!isset($argv[2])) {
	$argv[2]='';
}
if (!isset($argv[3])) {
        $argv[3]='';
}

cursor($argv[1],$argv[2],$argv[3]);
echo 'hello';
function cursor($cmd,$x=0,$y='') {
$esc = "\033[";

switch ($cmd) {
	case 'off':
	echo "$esc?25l".$y;
	return;
	case 'on':
	echo "$esc?25h".$y;
	return;
	case 'up':
	$up =$esc.$x.'A'.$y;
	echo "$up";
	return;
	case 'down':
	$down = $esc.$x.'B';
	echo $down;
	return;
	case 'left':
	$left = $esc.$x.'D';
	echo $left;
	return;
	case 'right':
	$right = $esc.$x.'C';
	echo $right;
	return;
	case 'home':
	echo $esc."H";
	return;
	case 'deline':
	echo $esc."2K";
	return;
}
}
?>
