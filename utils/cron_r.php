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
require_once DOC_ROOT.'/includes/master.inc.php';
$version = "2.04";
$build = "6313-1092783628"; 
include  DOC_ROOT.'/functions.php';
require  DOC_ROOT.'/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
define( 'LOG',DOC_ROOT.'/logs/cron_r.log'); 
$done = array();
$Query = new SourceQuery( ); 
if(isset($argv[1])) {
	if ($argv[1] =='v' || $argv[1] == '-v' ) {
		echo 'Cron_R v'.VERSION.' - '.BUILD.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
		exit; 
	}
}

$sql = "SELECT * FROM `server1` WHERE running=1 ORDER BY`host_name` ASC";

$games = $database->get_results($sql);
foreach ($games as $game) {
$uri = parse_url($game['url']);
$url = $uri['scheme']."://".$uri['host'].':'.$game['bport'];
if (isset($uri['path'])) {
	$url .= $uri['path'];
}

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
		$error = date("d/m/Y H:i:s").' ('.$game['host'].':'.$game['port'].') '.$Exception;
		$mask = "%17.17s %-30.30s \n";
		log_to(LOG,$error);
	}
	$Query->Disconnect( );
		if (isset($info['Players'])) {
			$game['restart'] = "$url/ajaxv2.php?action=exetmux&server=".$game['host_name'].'&cmd=';
			$restart[] = $game;
		}
		elseif (isset($info['Bots'])) {
			if ($info['Bots'] == $info['Players']) {
				$game['restart'] = "$url/ajaxv2.php?action=exetmux&server=".$game['host_name'].'&cmd=';
				$restart[] = $game;
			}
		}
		else  {
			$game['restart'] = "$url/ajax.php?action=exetmux&server=".$game['host_name'].'&cmd=';
			$check[] = $game; 
		}
	}

if (!isset($game)) {
$game=$check;
}
echo 'Restarting '.count($restart).'/'.count($games).' server(s)'.cr;
foreach ($restart as $game) {
	$now =  date("d-m-Y H:i:s");
	$logline = "$now stopping with ".$game['restart'].'q';
	log_to(LOG,$logline);
	echo geturl($game['restart'].'q').cr; // stop server
	$exe = './scanlog.php  -s'.$game['host_name'];
	$cmd =  "$url/ajaxv2.php?action=exe&cmd=".urlencode ($exe); // run scanlog
	$now = date("d-m-Y H:i:s");
	log_to(LOG,"$now running scanlog with $cmd and sending $exe to it");
	$result = geturl($cmd);
	$now =  date("d-m-Y H:i:s");
	if (!empty($result) ) {
		log_to(LOG,"$now Scanlog returned some data for ".$game['host_name']);
		echo trim($result).cr;
	}
	else {
		log_to(LOG,"$now Scanlog failed for ".$game['host_name']);
	}
	// check updates
	if (in_array($game['install_dir'],$done)) {
	}
	else{
		$steamcmd = trim(shell_exec('which steamcmd')); // is steamcmd in the path ? if so great we can sudo
		if(empty($steamcmd)) {
			$steamcmd = './steamcmd'; // need to fix this as steamcmd may need to run as root
			chdir(dirname($game['install_dir'])); // move to install dir root steamcmd should be there
			$log_line = "$now moved to ".getcwd ( );
			log_to(LOG,$log_line);
		}
		$install_dir = $game['install_dir'];
		$server_id = $game['server_id'];
		$exe = urlencode("$steamcmd +force_install_dir $install_dir +login anonymous  +app_update $server_id +quit");
		$now =  date("d-m-Y H:i:s");
		log_to (LOG, "$now  $steamcmd +force_install_dir $install_dir +login anonymous  +app_update $server_id +quit");
		$cmd = "$url/ajaxv2.php?action=exe&cmd=$exe";
		$output = geturl($cmd);
		$output = trim(preg_replace('/\^\[\[0m/', '', $output));
		$output = explode(cr, $output);
		echo trim(end($output)).cr;
		$now =  date("d-m-Y H:i:s");
		log_to(LOG,end($output)); //see what is comming back
		$done[]=$game['install_dir']; // use this to test if update on core files has been done
	}
	// log prune
	$exe = urlencode('/usr/sbin/tmpreaper  --mtime 1d '.$game['location'].'/log/console/');
	$log_line = $now ." Prune console logs for  tmpreaper  --mtime 1d ".$game['location']."/log/console/";
	log_to(LOG,$log_line);
	$cmd = "$url/ajaxv2.php?action=exe&cmd=$exe&debug=true";
	geturl($cmd);
	log_to(LOG,urldecode($cmd));
	$exe = urlencode('/usr/sbin/tmpreaper  --mtime 1d '.$game['location'].'/'.$game['game'].'/logs/');
	$cmd = "$url/ajaxv2.php?action=exe&cmd=$exe&debug=true";
	geturl($cmd);
	$log_line = 'Prune steam log files for '.$exe;
	log_to(LOG,urldecode($log_line));
	sleep(1);
	$now = date("d-m-Y H:i:s");
	$logline = "$now restarting with ".$game['restart'].'s';
	log_to(LOG,$logline);
	echo geturl($game['restart'].'s').cr; // start server
}
$log_line = print_r($done,true); //test array
log_to(LOG,$log_line);
if (isset($check)) { 
	echo 'Defered '.count($check).'/'.count($games).' server(s)'.cr;
	foreach ($check as $restart) {
		echo  $restart['server_name'].cr;
	}
}

?>
