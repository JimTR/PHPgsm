#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cron_r.php
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
 * restart servers at a given time
 *  
 */
if (!defined('DOC_ROOT')) {
    	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
    }

 define('cr',PHP_EOL);
 define('plus','%2B');
 define('space','%20');  
 define('VERSION',2.03);
 if (!defined('BUILD')) {
	$build = "6260-3808767217";
}
else {
	//
	define('NEW_ALLEGRO_ID', 5);
	//use const NEW_ALLEGRO_ID as BUILD;
}
require_once DOC_ROOT.'/includes/master.inc.php';
 
 include  DOC_ROOT.'/functions.php';
require  DOC_ROOT.'/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
define( 'LOG',DOC_ROOT.'/logs/cron.log'); 
$done = array();
$Query = new SourceQuery( ); 
if(isset($argv[1])) {
	if ($argv[1] =='v' || $argv[1] == '-v' ) {
		echo 'Cron_R v'.VERSION.' - '.BUILD.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
		exit; 
	}
}
$sql = 'select * from servers where running=1';
//$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr, base_servers.base_ip,base_servers.password FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
$sql = "SELECT * FROM `server1` WHERE running=1 ORDER BY`host_name` ASC";
$games = $database->get_results($sql);
foreach ($games as $game) {
		
		try
														{
															$Query->Connect( $game['host'], $game['port'], SQ_TIMEOUT, SQ_ENGINE );
															$sub_cmd = 'GetInfo';
															$info = $Query->GetInfo();
															//echo print_r($info1,true).cr;
															
															}
													catch( Exception $e )
														{
															$Exception = $e;
															if (strpos($Exception,'Failed to read any data from socket')) {
																$Exception = 'Failed to read any data from socket Module (Cron_r - Game Detail '.$sub_cmd.')';
														}
														$error = date("d/m/Y h:i:sa").' ('.$game['host'].':'.$game['port'].') '.$Exception;
														//sprintf("[%14.14s]",$str2)
														$mask = "%17.17s %-30.30s \n";
														file_put_contents(LOG,$error.cr,FILE_APPEND);
														}
		$Query->Disconnect( );
		if (isset($info['Players'])) {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$restart[] = $game;
		}

		elseif ($info['Bots'] == $info['Players']) {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$restart[] = $game;
		}
		else  {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$check[] = $game; 
		}
	}
	//else { continue;}
	

	echo 'Restarting '.count($restart).'/'.count($games).' server(s)'.cr;
	foreach ($restart as $game) {
			echo geturl($game['restart'].'q').cr; // stop server
			//print_r($game);
			$exe = './scanlog.php --silent -s'.$game['host_name'];
			$cmd =  $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.urlencode ($exe); // run scanlog
			$result = geturl($cmd);
			if (!empty($result) ) {
				file_put_contents(LOG,'Got some data back from '.$game['host_name'].cr,FILE_APPEND);
				echo $result.cr;
			}
			else {
				file_put_contents(LOG,'Scanlog failed for '.$game['host_name'].cr,FILE_APPEND);
			}
					 
			// check updates
			if (in_array($game['install_dir'],$done)) {
				//echo 'update already checked'.cr;
			}
			else{
				$steamcmd = trim(shell_exec('which steamcmd')); // is steamcmd in the path ?
				if(empty($steamcmd)) {
					$steamcmd = './steamcmd';
					chdir(dirname($game['install_dir'])); // move to install dir root steamcmd should be there
					$log_line = 'moved to '.getcwd ( );
					file_put_contents(LOG,$log_line.cr,FILE_APPEND);
				}
				
				$exe = urlencode($steamcmd.' +login anonymous +force_install_dir '.$game['install_dir'].' +app_update '.$game['server_id'].' +quit');
				$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.$exe;
				//echo 'will execute '.$cmd.cr; // update full url
				echo geturl($cmd);
				$done[]=$game['install_dir']; // use this to test if update on core files has been done
			}
			// log prune
			$exe = urlencode('tmpreaper  --mtime 1d '.$game['location'].'/log/console/');
			$log_line = 'Prune console logs for  '.$exe;
			file_put_contents(LOG,$log_line.cr,FILE_APPEND);
			$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
			echo file_get_contents($cmd);
			$exe = urlencode('tmpreaper  --mtime 1d '.$game['location'].'/'.$game['game'].'/logs/');
			$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
			echo file_get_contents($cmd);
			$log_line = 'Prune steam log files for '.$exe;
			file_put_contents(LOG,$log_line.cr,FILE_APPEND);
			sleep(1);
			echo file_get_contents($game['restart'].'s').cr; // start server
			}
	     $log_line = print_r($done,true); //test array
	     file_put_contents(LOG,$log_line.cr,FILE_APPEND);
	
	
	if (isset($check)) { 
		echo 'Defered '.count($check).'/'.count($games).' server(s)'.cr;
	foreach ($check as $restart) {
		echo  $restart['server_name'].cr;
	}
}

?>
