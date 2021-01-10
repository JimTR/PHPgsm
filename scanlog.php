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
 */
$key = '14a382cdc7db50e856bd3f181ed45b585a58c858b4785c0dae4fa27f';
//echo PHP_EOL;
error_reporting( -1 );
require ('includes/master.inc.php');
require 'includes/Emoji.php';
require 'includes/class.steamid.php';
if (isset($_GET['path'])){
$argv[1] = $_GET['path'];
}
if(empty($argv[1])) {
	//print_r($argv);
	echo 'Please supply a file to scan'.PHP_EOL;
	echo 'Example :- '.$argv[0].' path/to/file <serverid>'.PHP_EOL;
	echo 'or - '.$argv[0].' all'.PHP_EOL;
	exit;
}
$sql = 'select * from players where steam_id="'; // sql stub for user updates

$file =$argv[1];
if ($file == 'all') {
	
		$allsql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
		$game_results = $database->get_results($allsql);
	//print_r ($game_results);
	foreach ($game_results as $run) {
		//bulid path
		$server_key = md5( ip2long($run['ipaddr'])) ;
		$path = $run['url'].':'.$run['bport'].'/ajax.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log&key='.$server_key;
		$tmp = file_get_contents($path);
		//echo $path.PHP_EOL;
		//echo $tmp.PHP_EOL;
		//file_put_contents($run['host_name'],$tmp);
		do_all($run['host_name'],$tmp);
	}
	//$mask = "%15.15s %4.4s \n";
	//printf($mask,'Modified Users', $update_users);
	exit;
}
else {
	// do supplied file
	
}
function do_all($server,$data) {
	// cron code
	$count = 0;
	$done= 0;
	$update_users = 0;
	global $database, $key;
	$sql = 'select * from players where steam_id="'; // sql stub for user updates
	echo 'Processing server '.$server.PHP_EOL;
	$log = explode(PHP_EOL,$data);
    // echo 'Rows to process '.count($log).PHP_EOL; //debug code
    foreach ($log as $value) {
		// loop lines
		$bot = strpos($value,' connected, address "none');
		if($bot) {continue;} //remove bot lines
		$x = strpos($value,' connected, address ');
		if ($x >0) {
		// save output
		$value=trim($value);
	
		//preg_match($r, $value, $t); // get ip
		preg_match('/(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}/', $value, $t);
		//echo print_r ($t,true).PHP_EOL;
		if( isset($t[0])) {
			$ip=$t[0];
			//echo $ip.PHP_EOL;
		}
			else {unset($ip);
		     continue;}
		     
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
	echo 'Given SteamID could not be parsed. in style 3 '.$id.PHP_EOL;
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
	//echo $id2.PHP_EOL;
	}
preg_match('/..\/..\/.... - ..:..:../', $value, $t); // get time
        $timestring = $t[0];
		$timestring = str_replace('-','',$timestring);
		preg_match('/(?<=")[^\<]+/', $value, $t); // get user
		$username = $t[0];
		//echo 'processing '.$username.' '.$ip.' '.$id2.PHP_EOL;
		$la[$username]['ip']=$ip;
		$la[$username]['tst']=Emoji::Encode($username); // encode user name for db
		$la[$username]['time']=$timestring;
		$la[$username]['id'] = $id;
		if (isset($id2)) {	$la[$username]['id2']=$id2;}
		
	}
		 
}
// if (isset($la)) {echo print_r($la,true).PHP_EOL;} //debug code
if (!isset($la)) { 
	$pc = 0;
	} else {$pc = count($la);}
//echo 'Rows found '.$pc.PHP_EOL;
if ( $pc == 0 ) {
	echo "\t Nothing to do".PHP_EOL;
return;
}

foreach ($la as $user_data) {
	$rt='';
	// now do data
	$user = trim($user_data['id']);
	$username = $user_data['tst'];
	$ip = $user_data['ip'];
	$user_data['ip'] = ip2long($user_data['ip']);
	$modify = false;
	$rt ="\t". $user_data['id2'].' '.$username;
	$result = $database->get_row($sql.$user.'"');
	if (!empty($result)){
		unset($result['id']); // take out id
		$where['steam_id'] = $user_data['id'];
		$last_logon = strtotime($user_data['time']);
		if (empty($result['steam_id64'])) {
		$rt .=' no ID64 (correcting)';
		$result['steam_id64'] = $user_data['id2'];
		$modify=true;
		}
		//echo 'last played '.$last_logon.' Database sees '.$result['last_log_on'].PHP_EOL; // debug code
		if ($last_logon >  $result['last_log_on']) {
			$rt.= ' new logon ';
			$result['last_log_on'] = $last_logon;
			$result['log_ons'] ++;
			$modify=true;
		}
		
		if ($user_data['ip'] <> $result['ip'] ) {
			$rt.= ' IP Changed from '.long2ip($result['ip']).' to '.long2ip($user_data['ip']);
			//check ip on change
			$ip_data = get_ip_detail($ip);
			$result['continent'] = $ip_data['continent_name'];
			$result['country_code'] = $ip_data['country_code'];
			$result['country'] = $ip_data['country_name'];
			$result['region'] = $ip_data['region'];
			$result['city'] = $ip_data['city'];
			$result['flag'] = Emoji::Encode($ip_data['emoji_flag']);
			$result['time_zone'] = $ip_data['time_zone']['name'];
			$result['type'] = $ip_data['asn']['type'];
			$result['threat'] = $ip_data['threat']['is_threat'];
			$result['ip'] = $user_data['ip'];
			$modify=true;
		}
		
		if ($username <> $result['name']) {
			$rt.= ' User name change from '.$result['name'].' to '.$username;
			$result['name'] = $username;
			$modify=true;
		}
		
		if(strpos($result['server'],$server) === false) {
			$rt.= ' '.$username.' - '.$user_data['id']. ' played a different server ('.$server.')';
			$result['server'].=','.$server;
			}
			
		if ($modify) {
		$result = $database->escape($result);
		$database->update('players',$result,$where);
		$update_users++;
		echo $rt.PHP_EOL;
	}
	else{
		echo $rt.' no change'.PHP_EOL;
	}
	}
	else {
		$rt .=' New user';
		$count ++;
		$last_logon = time();
		$ip_data = get_ip_detail($ip);
		$result['ip'] = $user_data['ip'];
		$result['steam_id'] = $user;
		$result['steam_id64'] = $user_data['id2'];
		$result['name'] = $username;
		$result['log_ons'] = 1;
		$result['last_log_on'] = $last_logon;
		$result['continent'] = $ip_data['continent_name'];
		$result['country_code'] = $ip_data['country_code'];
		$result['country'] = $ip_data['country_name'];
		$result['region'] = $ip_data['region'];
		$result['city'] = $ip_data['city'];
		$result['flag'] = Emoji::Encode($ip_data['emoji_flag']);
		$result['time_zone'] = $ip_data['time_zone']['name'];
		$result['type'] = $ip_data['asn']['type'];
		$result['threat'] = $ip_data['threat']['is_threat'];
		$result['server'] = $server;
		
		
		$result = $database->escape($result);
	    $in = $database->insert('players',$result);
	    if ($in === true ){
			 	 $done++;
			 	 $rt .=' Record added';
			 }
	   else {
		 echo 'Database Insertion failed with'.PHP_EOL;
		 print_r($result);		 
		//echo PHP_EOL;
}			
	}
	// print_r($result); //debug code
	

}
}
function get_ip_detail($ip) {
	// return api data
	global $key;
	
	$cmd =  'https://api.ipdata.co/'.$ip.'?api-key='.$key;
	// echo $cmd.PHP_EOL; //debug code
	$ip_data = json_decode(file_get_contents($cmd), true); //get the result
	 if (empty($ip_data['threat']['is_threat'])) {$ip_data['threat']['is_threat']=0;}
	 //print_r($ip_data); //debug code
	 return $ip_data;
}

?>
