<?php
/*
 * ajaxv2.php
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
 * ajax v2 to go with xml v2 
 */
require_once 'includes/master.inc.php';
include 'functions.php';
error_reporting (0);
$ip =$_SERVER['SERVER_ADDR']; // get calling IP
$sql = 'select * from base_servers where base_servers.ip ="'.$_SERVER['REMOTE_ADDR'].'"'; // do we know this ip ? mybb sets this at login
$valid = $database->num_rows($sql); // get result if the ip can use the data the return value >0

if(is_cli()) {
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	$valid = 1; // we trust the console
	$sec = true;
	$type= $argv;
	$cmds =convert_to_argv($type,"",true);
	$logline  = date("d-m-Y H:i:s").' localhost accessed ajax with '.print_r($cmds,true).PHP_EOL;
	//file_put_contents('ajax.log',$logline,FILE_APPEND);
	if (isset($cmds['debug'])) {
		error_reporting( -1 );
	}
	else {error_reporting( 0 );}
	
}
else {
	define ('CR',"<br>");
	define ('cr',"<br>");
	error_reporting( 0 );
	if (!empty($_POST)) {
		$cmds =convert_to_argv($_POST,"",true);
	}
	else {
		$cmds =convert_to_argv($_GET,"",true);
	}
}
if(!$valid) { 
	echo 'invalid request'.cr;
	die();
}
echo 'Ajax v2'.cr;
print_r($cmds);
// do what's needed
	switch (strtolower($cmds['action'])) {
		case "boottime" :
			echo get_boot_time();
			exit;
			
		case "get_file" :
			if (isset($cmds['post'])) {
				file_put_contents('logs/gf.log',print_r($cmds,true),FILE_APPEND);
				file_put_contents($cmds['file'],$cmds['data']);
			}
			
			else { 
					echo file_get_contents($cmds['file']);
				}
			exit;	
			
		case "ps_file" :
			if (isset($cmds['filter'])) {
				// add the grep filter
				echo shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg');
			}
			else {
				echo shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd'); 
			}
			exit;	
			
			case "top" :
				if (isset($cmds['filter'])) {
					//do stuff
					echo shell_exec('top -b -n 1 -p '.$cmds['filter'].' | sed 1,7d');
				}
			 	exit;
			 	
			case "lsof" : 
					lsof($cmds);
					exit;
}

function lsof($cmds) {
						if (isset($cmds['lsof_file'])) {
						// return the open file,  the interface should format this correctly not ajax's job
						// what ajax needs is the full path to where the file resides
						// note, this will only return an open file 
						// this runs only on the local server, must be called on each server
						//ls -l /proc/pid/fd
						$x =strpos($cmds['lsof_file'],'-con');
						$v = substr($cmds['lsof_file'],0,$x);
						$tpid = shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg');
						//echo $tpid;
						$get_pid = explode(' ',trim($tpid));
						$pid= $get_pid[0];
						//echo $pid.cr;
						$tmp = shell_exec('ls -l /proc/'.$pid.'/fd |grep '.$cmds['loc'].'/logs');
						echo $tmp.cr; // debug code
						$x = explode(' ',$tmp);
							foreach ($x as $k=>$v) 
								if (empty(trim($v))) {
									unset ($x[$k]);
								}
								else {
									$x[$k]=trim($v);
								}
							}
						$c = count($x); // need this to check file size
						$x = array_values($x); // re-number array
						//print_r($x);
						
						// now do stuff return either the path or contents ?
						// sending back the contents will save a call but maybe wrong 
						$filename = $x[10]; //got file name
						if (!empty($cmds['return'])) {
							echo 'get contents of '.$filename.'    '.filesize($filename).cr;
							echo file_get_contents($filename);
						}
						else {
							echo $filename;
							
						}
						if (is_cli()) {echo cr;}
				exit;
			}
?>
