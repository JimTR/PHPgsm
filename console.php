<?php
/*
 * console.php
 * 
 * Copyright 2020 Jim Richardson <jim@noideersoftware.co.uk>
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
 * 
 * This runs as a frame the url is not visable to the browser
 * we cannot run an instance per game server as the malloc & cpu go sky high
 */
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff
include("functions.php"); // add functions
define ("CR", "<br>");
$template = new Template; // load template class
if (empty($Auth->id)) {
		
		$template->load('html/login.html');
		$template->replace('servername', $_SERVER['SERVER_NAME']);
		$template->publish();
		exit;
	}
 if (!empty($_POST)) {
	 $cmds = $_POST;
	 echo "post set".CR;
 }
 else {
	 $cmds = $_GET;
 }
 //print_r($cmds);
 require_once('GameQ/Autoloader.php'); //load GameQ
$database = new db(); // connect to database
$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.host_name ="'.$cmds['id'].'"';
$results = $database->get_results($sql);
foreach ($results as $server) {
	
		 $key = $server['host_name'];
		 $x2['id'] = $key;
	     $x2['host'] = $server['host'].':'.$server['port'] ;
	     $x2['type'] = $server['type'];
	     $GameQ = new \GameQ\GameQ();
		//include ("server-info.php");
          $GameQ->addServer($x2);
          $sresults = $GameQ->process();
         

$page['path'] = $server['location'].'/log/console/'.$server['host_name'].'-console.log';
$page['url'] = $server['url'].':'.$server['bport'];
$page['host'] = $server['host'].':'.$server['port'];
$page['id'] = $server['host_name'];
$page['type'] = $server['type'];
$page['logo'] = $template->load('html/logo.html');
$page['name'] =  $sresults[$key]['gq_hostname'];
$page['title'] = $sresults[$key]['gq_hostname'].' Console';
}


$template->load('html/console.html'); // load blank template
$template->replace_vars($page);
$template->publish();

?>
