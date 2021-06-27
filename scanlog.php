#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * scanlog.php
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
 * scan game logs
 * SELECT * FROM `players` WHERE `server` LIKE 'fofserver2'
 * SELECT country,COUNT(*) as total FROM players GROUP BY country order by total desc limit 10 ;
 * SELECT name,country,log_ons from players order by log_ons desc limit 0,10
 * Major re work January 2021 
 */
//echo cr;
error_reporting( -1 );
define('cr',PHP_EOL);
define ('VERSION',2.13);
	$build = "13883-2154975256";
require ('includes/master.inc.php');
require 'includes/class.emoji.php';
require 'includes/class.steamid.php';
if(isset($argv[1])){
	switch (strtolower($argv[1])) {
		case 'v':
		case '-v':
		echo 'Scanlog V'.VERSION.' - '.$build.' © NoIdeer Software '.date('Y').cr;
		exit;
	}
}
if (empty( $settings['ip_key'] )) {
	echo 'Fatal Error - api key missing'.cr;
	exit(7);
}
$key = $settings['ip_key'] ;
if (!isset($argv)){
echo 'wrong enviroment';
exit;
}
if(empty($argv[1])) {
	echo 'Scanlog V'.VERSION.' - '.$build.' © NoIdeer Software '.date('Y').cr;
	echo 'Please supply a Server to scan'.cr;
	echo 'Examples :- '.cr."\t".$argv[0].' <server id>'.cr;
	echo "\t".$argv[0].' <server id> <file to scan>'.cr;
	echo "\t".$argv[0].' <all> this will scan all servers using the default log '.cr;
	exit(0);
}
$asql = 'select * from players where steam_id64="'; // sql stub for user updates
$update_done= array();
$file =$argv[1];
if ($file == 'all') {
	
		$allsql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
		$game_results = $database->get_results($allsql);
		$display='';
	//print_r ($game_results);
	foreach ($game_results as $run) {
		//bulid path
		$server_key = md5( ip2long($run['ipaddr'])) ;
		//$path = $run['url'].':'.$run['bport'].'/ajax.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log&key='.$server_key; //used for screen log
		// /ajaxv2.php?action=lsof&filter=fofserver&loc=/home/nod/games/fof/fof&return=content
		$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=lsof&filter='.$run['host_name'].'&loc='.$run['location'].'/'.$run['game'].'&return=content'; //used for steam log
		$tmp = file_get_contents($path);
		//echo $run['host_name'].' '.$path.cr; // debug code
				
		if (!empty($tmp)) {
			//echo $tmp.cr; //debug code
		$display .= do_all($run['host_name'],$tmp);
	}
	}
	echo $display;
}
else {
	// do supplied file
	if(isset($argv[3])) {
	if (!file_exists($argv[3])) {
		echo 'could not open '.$argv[3].cr;
		exit (1);
	}
}
	$allsql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.host_name="'.$argv[1].'"';
		//echo $allsql.cr;
	$run = $database->get_row($allsql);
	if(empty($run)) {
		echo 'Invalid server id '.$argv[1].' correct & try again'.cr;
		exit(2);
	}
	$server_key = md5( ip2long($run['ipaddr'])) ;
	//$path = $argv[1];
	//print_r($run);
	if (empty($argv[3])) {
	$path = $run['url'].':'.$run['bport'].'/ajax.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log&key='.$server_key;
	}
	else {
		// assume run local 
		$path = $argv[3];
	}
		
		
		$tmp = file_get_contents($path);
		echo do_all($argv[1],$tmp);
}


function do_all($server,$data) {
	// cron code
	
	$count = 0;
	$done= 0;
	$update_users = 0;
	$uds = false;
	global $database, $key;
	$update_req = 'Your server needs to be restarted in order to receive the latest update.';
	$asql = 'select * from players where steam_id64="'; // sql stub for user updates
	$rt = 'Processing server '.$server.cr.cr;
	$log = explode(cr,$data);
    // echo 'Rows to process '.count($log).cr; //debug code
    foreach ($log as $value) {
		// loop lines, in here check for server needs a restart
		if ( strpos($data,$update_req)) {
			// server needs an update & restart
			$uds = true;
		}
		$bot = strpos($value,' connected, address "none');
		if($bot) {continue;} //remove bot lines
		$x = strpos($value,' connected, address ');
		if ($x >0) {
		// save output
		$value=trim($value);
	
		//preg_match($r, $value, $t); // get ip
		preg_match('/(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}/', $value, $t);
		//echo print_r ($t,true).cr;
		if( isset($t[0])) {
			$ip=$t[0];
			//echo $ip.cr;
		}
			else {unset($ip);
		     continue;}
		    $id=''; 
		    preg_match('/U:[0-9]:\d+/', $value, $t); // get steam id
		    //print_r ($t);
		    if (isset($t[0])){
			$id = trim($t[0]);
		}
		
		
		
	if(!empty($id)) {	
		//echo $id.' - ';
		try
{
		$s = new SteamID( '['.$id.']' );
		}
catch( InvalidArgumentException $e )
{
	$rt .= 'Given SteamID could not be parsed. in style 3 '.$id.cr;
	$rt .= 'from '.$value.cr;
	$rt .= 'extracted '.$id.cr;
}
		$id2 = $s->ConvertToUInt64();
}
if (empty($id)) {
			preg_match('/STEAM_[0-9]:[0-9]:\d+/', $value, $t);
			//print_r($t);
			$id = $t[0];
			$s = new SteamID( $id );
			$id = $s->RenderSteam3();
			//preg_match('/U:[0-9]:\d+/', $id2, $t);
			//$id= $t[0];
			//echo $id.' - ';
			$id2 = $s->ConvertToUInt64();
		}
		else {
			//unset ($id2);
		}

if(!empty($id2)){
	//echo $id2.cr;
	}
preg_match('/..\/..\/.... - ..:..:../', $value, $t); // get time
        $timestring = $t[0];
		$timestring = str_replace('-','',$timestring);
		preg_match('/(?<=")[^\<]+/', $value, $t); // get user
		$username = $t[0];
		//echo 'processing '.$username.' '.$ip.' '.$id2.cr;
		$la[$username]['ip']=$ip;
		$la[$username]['tst']=Emoji::Encode($username); // encode user name for db
		if (empty($la[$username]['tst'])) {$la[$username]['tst'] =trim($username);}
		$la[$username]['time']=$timestring;
		$la[$username]['id'] = $id;
		if (isset($id2)) {	$la[$username]['id2']=$id2;}
		
	}
		 
}
//if (isset($la)) {echo print_r($la,true).cr;return;} //debug code
if (!isset($la)) { 
	$pc = 0;
	} else {$pc = count($la);}
//echo 'Rows found '.$pc.cr;
if ( $pc == 0 ) {
	//echo "\t Nothing to do".cr;
	if ($uds == true) {
		$s = update_server($server);
		return $s;
	}
return;
}

foreach ($la as $user_data) {
	$logon = false; 
	// now do data
	$user = trim($user_data['id']);
	$user_search = $user_data['id2'].'"';
	//echo $sql.$user_search.cr; //debug code
	$username = $user_data['tst'];
	$ip = $user_data['ip'];
	$user_data['ip'] = ip2long($user_data['ip']);
	$modify = false;
	$added = false;
	; // start of log line
	$ut='';
	$result = $database->get_row($asql.$user_search);
	if (!empty($result)){
		$user_stub ="\t".$username.' ('.$result['country'].') ';
		unset($result['id']); // take out id
		unset($result['steam_id']);
		$where['steam_id64'] = $user_data['id2'];
		$last_logon = strtotime($user_data['time']);
		
		
		if ($last_logon >  $result['last_log_on']) {
			$result['last_log_on'] = $last_logon;
			$result['log_ons'] ++;
			$ut.= ' new logon  at '.$user_data['time'].' (total '.$result['log_ons'].')';
			$modify=true;
			$logon = true;
		}
		if (empty($result['steam_id64'])) {
		$ut .=' no ID64 (correcting)';
		$result['steam_id64'] = $user_data['id2'];
		$modify=true;
		}
		
		if ($user_data['ip'] <> $result['ip'] ) {
			$ut.= ' IP Changed from '.long2ip($result['ip']).' to '.long2ip($user_data['ip']);
			//check ip on change
			$ip_data = get_ip_detail($ip);
			$result['continent'] = $ip_data['continent_name'];
			$result['country_code'] = $ip_data['country_code'];
			$result['country'] = $ip_data['country_name'];
			$result['region'] = $ip_data['region'];
			$result['city'] = $ip_data['city'];
			$result['flag'] = Emoji::Encode($ip_data['emoji_flag']);
			$result['time_zone'] = $ip_data['time_zone']['name'];
			if (isset($ip_data['asn'])) {
				$result['type'] = $ip_data['asn']['type'];
		}
		else {
			$result['type'] ='n/a';
		}
			$result['threat'] = $ip_data['threat']['is_threat'];
			$result['ip'] = $user_data['ip'];
			$modify=true;
		}
		
		if (trim($username) <> $result['name']) {
			$ut.= ' User name change from '.$result['name'].' to '.$username;
			$result['name'] = trim($username);
			$modify=true;
		}
		
		if(strpos($result['server'],$server) === false) {
			$ut.= ' played a new server';
			$result['server'].=$server.'*';
			$modify=true;
			}
			
		if ($modify) {
		$result = $database->escape($result);
		$n = $database->update('players',$result,$where);
		if ($logon == true) { 
		$sql = 'call update_logins ('.$result['steam_id64'].',"'.$server.'",'.$result['last_log_on'].')';
		$database->query($sql);
		unset($logon);
		}
		if ($n === false) {
			//
			echo cr.'Database Update failed with'.cr;
			print_r($result);
			echo 'trying again';
			$database->query('SET character_set_results = binary;');
			$result['name'] = $database->filter($result['name']);
			unset($result['steam_id']);
			$n = $database->update('players',$result,$where);
					 
		}
		$update_users++;
		$ut .= cr;
	}
	else{
		//echo $rt.' no change'.cr;
	}
	}
	else {
		//echo 'adding '.$username.cr;
		$added = true;
		$ut .= $ut.' New user';
		$count ++;
		$last_logon = time();
		$ip_data = get_ip_detail($ip);
		$result['ip'] = $user_data['ip'];
		//$result['steam_id'] = $user;
		$result['steam_id64'] = $user_data['id2'];
		$result['name'] = $username;
		$result['first_log_on'] = $last_logon;
		$result['log_ons'] = 1;
		$result['last_log_on'] = $last_logon;
		$result['continent'] = $ip_data['continent_name'];
		$result['country_code'] = $ip_data['country_code'];
		$result['country'] = $ip_data['country_name'];
		$result['region'] = $ip_data['region'];
		$result['city'] = $ip_data['city'];
		$result['flag'] = Emoji::Encode($ip_data['emoji_flag']);
		$result['time_zone'] = $ip_data['time_zone']['name'];
		if (isset($ip_data['asn']['type'])) {
		$result['type'] = $ip_data['asn']['type'];
	}
	else {
		$result['type'] = 'N/A';
	}
		$result['threat'] = $ip_data['threat']['is_threat'];
		$result['server'] = $server.'*';
		
		
		$result = $database->escape($result);
	    $in = $database->insert('players',$result);
	    $user_stub ="\t".$username.' ('.$result['country'].') ';
	    if ($in === true ){
			 	 $done++;
			 	 $ut .=' Record added at '.$user_data['time'].cr;
			 	 $sql = 'call update_logins ('.$result['steam_id64'].',"'.$server.'",'.$result['last_log_on'].')';
			 	 //$ut .= $sql.cr;
			 	 $database->query($sql);
			 }
	   else {
		 echo 'Database Insertion failed with'.cr;
		 print_r($result);		 
		//echo cr;
}

 //$rt.=cr;			
	}
	// print_r($result); //debug code

	if (isset($ut)) {
		if ($modify || $added) {		
			$rt .= $user_stub.' '.$ut;
		}
	}
}


$mask = "%15.15s %4.4s \n";
if ($done || $update_users ) {
//echo $rt;
$rt .= sprintf($mask,'New Users',$done );
$rt .= sprintf($mask,'Modified Users',$update_users );
if ($uds == true) {
	$rt .= cr.'Warning '.$server.' needs updating & restarting'.cr;
	$rt .= update_server($server);
}
$rt .= cr.'Processed '.$server.cr;
//echo $rt;
return $rt;
}

}
function get_ip_detail($ip) {
	// return api data
	global $key;
	
	$cmd =  'https://api.ipdata.co/'.$ip.'?api-key='.$key;
	 //echo $cmd.cr; //debug code
	$ip_data = json_decode(file_get_contents($cmd), true); //get the result
	 if (empty($ip_data['threat']['is_threat'])) {$ip_data['threat']['is_threat']=0;}
	 //print_r($ip_data); //debug code
	 return $ip_data;
}

function update_server($server){
	// if found stop the server and update
	//Your server needs to be restarted in order to receive the latest update.
	global $database, $update_done,$settings;
	$s = 'Server Update via Scanlog '.VERSION.cr;
	$sql = 'select * from server1 where host_name="'.$server.'"';
	$steamcmd = '/usr/games/steamcmd';
	$game = $database->get_row($sql);
	$stub =  $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd='; // used to start & stop
	if (in_array($game['install_dir'],$update_done)) {
				$s .= 'Update already done'.cr;
			    //$cmd = $stub.'r';
			    //$s .=  file_get_contents($cmd).cr; 	
				return $s;
			}
	$cmd = $stub.'q';
	$s .= file_get_contents($cmd).cr; // stopped server
	
	$exe = urlencode($steamcmd.' +login anonymous +force_install_dir '.$game['install_dir'].' +app_update '.$game['server_id'].' +quit');
	$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
	$s .=file_get_contents($cmd);
	//echo 'updated server using '.$cmd.cr;
	//$cmd = $stub.'s';
	//$s .= file_get_contents($cmd).cr;
	//need to restart all that stem from this install dir
	$sql = "SELECT * FROM `server1` WHERE `game` like '".$game['game']."' and `install_dir` like '".$game['install_dir']."'";
		$restarts = $database->get_results($sql);
		foreach ($restarts as $restart) {
			// restart them all
			$cmd =  $game['url'].':'.$restart['bport'].'/ajaxv2.php?action=exescreen&server='.$restart['host_name'].'&key='.md5($restart['host']).'&cmd=r'; // used to restart
			$s .= file_get_contents($cmd).cr;
		}
		
	$update_done[] = $game['install_dir'];
	return $s;
}
?>
