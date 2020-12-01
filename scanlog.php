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
echo PHP_EOL;
require ('includes/master.inc.php');
require 'includes/Emoji.php';
require 'includes/class.steamid.php';
require_once('GameQ/Autoloader.php');
if(empty($argv[1])) {
	//print_r($argv);
	echo 'Please supply a file to scan'.PHP_EOL;
	echo 'Example :- '.$argv[0].' path/to/file'.PHP_EOL;
	exit;
}
$sql = 'select * from players where steam_id="'; // sql stub for user updates
$GameQ = new \GameQ\GameQ();
$file =$argv[1];
$x = strpos($file,'-');
$server = substr($file,0,$x);
$x = strrpos($server,'/');
$server = substr($server,$x);
echo $x.PHP_EOL;
echo 'Processing server '.$server.PHP_EOL;
$log = file_get_contents($file);
$data = explode(PHP_EOL,$log);
echo 'Rows to process '.count($data).PHP_EOL;
foreach ($data as $value) {
	// loop
	$x = strpos($value,'" connected, address "');
	if ($x >0) {
		// save output
		$value=trim($value);
		//preg_match($r, $value, $t); // get ip
		preg_match('/(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}/', $value, $t);
		if( isset($t[0])) {
			$ip=$t[0];
				//print_r($t);
		preg_match('/U:[0-9]:\d+/', $value, $t); // get steam id
		$id = trim($t[0]);
	}
		if (empty($id)) {
			preg_match('/STEAM_[0-9]:[0-9]:\d+/', $value, $t);
			$id = $t[0];
			$s = new SteamID( $id );
			$id2 = $s->RenderSteam3();
			preg_match('/U:[0-9]:\d+/', $id2, $t);
			$id2= $t[0];
		}
		else {
			unset ($id2);
		}
		preg_match('/..\/..\/.... - ..:..:../', $value, $t); // get time
        $timestring = $t[0];
		$timestring = str_replace('-','',$timestring);
		preg_match('/(?<=")[^\<]+/', $value, $t); // get user
		$username = $t[0];
		$la[$username]['ip']=$ip;
		$la[$username]['tst']=Emoji::Encode($username); // encode user name for db
		$la[$username]['time']=$timestring;
		$la[$username]['id'] = $id;
		if (isset($id2)) {	$la[$username]['id2']=$id2;}
		//print_r($la);
	}
}
//exit;
echo 'Rows found '.count($la).PHP_EOL;
echo 'Processing'.PHP_EOL;
$rows = $database->get_results( 'DESCRIBE players' );
 foreach ($rows as $row) {
    $fields[] = $row['Field'];
  }
  
  unset($fields[0]); // take out id 

foreach ($la as $data) {
	// second loop
	$user = trim($data['id']);
	//$max =1; // debug
    //$user= $database->escape($user);
	$ql = $sql.$user.'"';
	$result = $database->get_row($sql.$user.'"');
	//print_r($result);
	
	if (!empty($result)){
		//echo 'Updating  '.Emoji::Decode($result['flag'])."\t".$data['tst'].' - '.$data['id'].PHP_EOL;
		$where['steam_id'] = $data['id'];
		
		unset($result['id']); // take out id
		
		if($result['last_log_on'] < strtotime($data['time'])) {
			$result['last_log_on'] = strtotime($data['time']);
			$result['log_ons'] =$result['log_ons']+1;
			
	}
		$result['name'] = $data['tst'];
		//$result['country_code'] = $data['country_code'];
		if(strpos($result['server'],$server) === false) {
			echo $data['tst'].' - '.$data['id']. ' played a different server'.PHP_EOL;
			$result['server'].=','.$server;
		}
		
		$result = $database->escape($result);
		$database->update('players',$result,$where);
		
		// update
		
	}
	else {
		if (!empty($data['id'])) {
		echo 'inserting '.$data['tst'].PHP_EOL;
		if (empty($max)) {
		// get read for insert
		$count ++;
		$ip ='/'.$data['ip'];
		 $ch = curl_init();
         $cmd =  'https://api.ipdata.co'.$ip.'?api-key='.$key;
         //echo $cmd.PHP_EOL;
         curl_setopt($ch, CURLOPT_URL, 'https://api.ipdata.co'.$ip.'?api-key='.$key);
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $data1 = curl_exec($ch);
		 curl_close($ch);
		 $ip_data = json_decode($data1, true);
		 if (empty($ip_data['threat']['is_threat'])) {$ip_data['threat']['is_threat']=0;}
		
		 $records['ip'] = ip2long ($data['ip']);
		 $records['steam_id'] = $data['id'];
		 $records['name'] =$data['tst'];
		 $records['log_ons'] = 1;
		 $records['last_log_on'] = strtotime($data['time']);
		 $records['continent'] = $ip_data['continent_name'];
		 $records['country_code'] = $ip_data['country_code'];
		 $records['country'] = $ip_data['country_name'];
		 $records['region'] = $ip_data['region'];
		 $records['city'] = $ip_data['city'];
		 $records['flag'] =Emoji::Encode($ip_data['emoji_flag']);
		 $records['time_zone'] = $ip_data['time_zone']['name'];
		 $records['type'] = $ip_data['asn']['type'];
		 $records['threat'] = $ip_data['threat']['is_threat'];
		 $records['server'] = $server;
		 $records = $database->escape($records);
		 $in = $database->insert('players',$records);
//print_r($records);
		 if ($in === true ){
			 	 $done++;
			 }
			else {
				echo 'Failed on '.$data['tst'].PHP_EOL;
				echo 'Query stored in mysql_fail.txt'.PHP_EOL;
				file_put_contents('mysql_fail.txt',$in.PHP_EOL,FILE_APPEND);
							}
			 }
		 //print_r($records); 
		 if ($count ==250) {$max = 1;} // stop mysql crying
	}

	
}
}

if (empty($done)) {
	echo 'No new players'.PHP_EOL;
}
else {
echo 'Inserted '.$done.' records'.PHP_EOL;
}
echo 'done'.PHP_EOL;
//print_r($records);
?>
