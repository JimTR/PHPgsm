#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * check_l.php
 * 
 * Copyright 2021 Jim Richardson <jim@noideersoftware.co.uk>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *  example cat ~/games/gmod/serverfiles/steamapps/appmanifest_4020.acf | sed '3,17!d'
 */
define ('cr',PHP_EOL);
	$build = "1976-1300356041";
if (!isset($argv[1])) {
	echo 'supply a server ID !'.cr;
	exit;
} 
if (!is_numeric($argv[1])) {
	echo 'Server Id must be numeric'.cr;
	exit;
}
if(!isset($argv[2])) {
	echo 'supply sever locastion'.cr;
	exit;
}
echo 'Checking Server id '.$argv[1].cr;
$file = $argv[2].'/steamapps/appmanifest_'.$argv[1].'.acf';
if (file_exists($file)) {
    echo print_r(check_local($file),true).cr;
} else {
    echo 'Data for server id '.$argv[1].' is not present in the location '.$argv[2].cr;
}


function check_local($file) {
$data = shell_exec('cat '.$file.' |sed \'3,17!d\'');
$arry = explode(cr,trim($data));
foreach ($arry as $key=>$value) {
	// clear blanks
	if(empty(trim($value))) 
	{ 
		unset ($arry[$key]);
		continue;
		}
	else {
		$value =substr(trim($value),1);
		$z = strpos($value,'"');
		$nz = substr($value,0,$z);
		$value =trim(str_replace($nz.'"','',$value));
        $value=trim(str_replace('"','',$value));
        $arry[$key]=$value;
        $return[$nz]=$value;
	}	
	}
	return $return;
}


?>
