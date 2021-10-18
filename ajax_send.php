<?php
/*
 * ajax_send.php
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
 * send proxy requests to server
 */
require ('includes/master.inc.php');
include ('functions.php');
//print_r($settings);
//print_r($_SERVER);
//$cmds =convert_to_argv($argv,"",true);
	$build = "2326-1139582117";
$version= 1.01;
if(is_cli()) {
$cmds = convert_to_argv($argv,"",true);
define('cr',PHP_EOL);
}
if(!defined('cr')){
	define('cr','<br>');
}
	if (!empty($_POST)) {
		$cmds = convert_to_argv($_POST,"",true);
	}
	elseif (!empty($_GET)) {
		if (isset($cmds)) {
			$cmds = array_merge($cmds,convert_to_argv($_GET,"",true));
		}
		else {
			$cmds = convert_to_argv($_GET,"",true);
		}
	}
	if(isset($cmds['debug'])) {
		echo '$cmds = '.print_r($cmds,true);
}
if (isset($cmds['query'])) {
	$query = split_query($cmds['query']);
	if (isset($cmds['debug'])){
		print_r($query);
	}
$cmd =  $cmds['url'];
}
else {
	$cmd = $cmds['url'];
}
if (isset($cmds['debug'])) {
	echo "cmd = $cmd".cr;
}
$options['phpgsm-auth'] = "true";
//$cmd = 'https://api.noideersoftware.co.uk/rp.php?_=1633778352841';
echo geturl($cmd,$settings['secure_user'],$settings['secure_password'],$options,$query);

function split_query($query) {
	// split up query
	$return='';
	$items = preg_split('/:/', $query);
	//print_r($items);
	foreach ($items as $item) {
		//build query string
		if (empty($return)) {
			$return = '?'.$item;
		}
		else {
			$return .='&'.$item;
		}
		$split_item = preg_split('/=/',$item);
		$return_array[$split_item[0]] = $split_item[1];
	}
	//die(print_r($return_array));
	return $return_array;
}
?>
