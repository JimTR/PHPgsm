<?php
/*
 * cron.php
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
 *  use of file run cron jobs
 * 
 */
include '../includes/master.inc.php';
include '../functions.php';
define ("cr",PHP_EOL);
	$build = "2704-2022334202";
if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	
	if (isset($cmds['debug'])) {
		error_reporting( -1 );
		foreach ($cmds as $key=>$value) {
			if ($key=='debug'){continue;}
			echo $key.' => '.$value.cr;
		}
	}
	else {error_reporting( 0 );}
	
}
else {
	die ('Wrong enviroment');
}
$ip = file_get_contents("http://ipecho.net/plain"); // get ip
//$servers = $database->get_results('select * from server1 where base_ip like "'.$ip.'"'); 
//print_r($servers);
if(empty($cmds['action'])) {
	die( 'invalid action'.cr);
}
switch (strtolower($cmds['action'])) {
	case 'reboot':
	// nothing can be running at this point so clear up the database in case the server crashed out
		echo 'reboot called'.cr;
		reboot();
		exit;
	case 'hourly':
			echo 'hourly called'.cr;
			exit;
	default:
        echo $cmds['action'].' is an invalid option'.cr;		
}
function getChineseZodiac($year){

    switch ($year % 12) :
        case  0: return 'Monkey';  // Years 0, 12, 1200, 2004...
        case  1: return 'Rooster';
        case  2: return 'Dog';
        case  3: return 'Boar';
        case  4: return 'Rat';
        case  5: return 'Ox';
        case  6: return 'Tiger';
        case  7: return 'Rabit';
        case  8: return 'Dragon';
        case  9: return 'Snake';
        case 10: return 'Horse';
        case 11: return 'Lamb';
    endswitch;
}

function reboot() {
	// boot stuff
	echo 'in reboot function'.cr;
	global $database,$cmds,$ip;
	$sql= 'UPDATE servers SET running = 0, starttime="" where base_ip="'.$ip.'"';
	echo $sql.cr;
	$sql = 'select * from server1';
	
	$servers = $database->get_results($sql);
	//print_r($servers);
}
if(isset($cmds['year'])) {
echo getChineseZodiac($cmds['year']).cr;
}
?>
