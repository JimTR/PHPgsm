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
if(is_cli()) {
$cmds = convert_to_argv($argv,"",true);
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
echo print_r($cmds,true);
if (isset($cmds['query'])) {
$cmd =  $cmds['url'].$cmds['query'];
}
else {
	$cmd = $cmds['url'];
}
echo "cmd = $cmd".PHP_EOL;
$options['phpgsm_auth'] ="true";
$options['HTTP_HOST']= 'cgi.localhost';
//$cmd = 'https://api.noideersoftware.co.uk/rp.php?_=1633778352841';
echo geturl($cmd,$settings['secure_user'],$settings['secure_password'],$options).PHP_EOL;

?>
