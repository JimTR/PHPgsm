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
require ('inc/master.inc.php');
//include ('inc/functions.lin.php');
// map ajaxv30.php to somewhere else (so ajaxv30.php's actual file name & location is only known to this script )
//print_r($settings);
//print_r($_SERVER);
//$cmds =convert_to_argv($argv,"",true);
$build = "3018-3237046157";
$version = "3.00";
$time = "1639728131";

	
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
	if ($query['output'] == 'xml'){header('Content-Type: text/xml');}
	if ($query['output'] == 'json') {header('Content-Type: application/json');}
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
if (isset($_SERVER['REMOTE_ADDR'] )) {
	// check a valid IP your login code needs to set this
	$rip = ip2long($_SERVER['REMOTE_ADDR']);
	$sql = "select * from allowed_ip where ip = \"$rip\"";
	$valid = $database->num_rows($sql);
	if (!$valid) {
		$output[] = 'invalid IP call';
		switch ($query['output']) {
			// send the error back in the correct format
			case 'json' :
				echo json_encode($output,true);
				break;
			case 'xml' :
				// make xml
				break;
			default :
				// send just text back
		}
	}
}
$options['phpgsm-auth'] = "true";

echo geturl($cmd,$settings['secure_user'],$settings['secure_password'],$options,$query); //password set file

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
