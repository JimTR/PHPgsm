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
//echo print_r($settings,true).'<br>';
//print_r($_GET);
$cmds =convert_to_argv($_GET,"",true);
echo 'cmds= '.print_r($cmds,true).'<br>';
if (isset($cmds['query'])) {
$cmd =  $cmds['url'].$cmds['query'];
}
else {
	$cmd = $cmds['url'];
}
$options['test'] ="hello";
$options['HTTP_HOST']= 'cgi.localhost';
//$cmd = 'https://api.noideersoftware.co.uk/rp.php?_=1633778352841';
echo print_r(geturl($cmd,$settings['secure_user'],$settings['secure_password'],$options).'<br>',true);
echo "returned<br>";
?>
