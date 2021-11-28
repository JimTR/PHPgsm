#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cron_scan.php
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
 * 
 */
define('cr',PHP_EOL);
if (!defined('DOC_ROOT')) {
    	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
    }
require (DOC_ROOT.'/includes/master.inc.php');
require DOC_ROOT.'/includes/class.emoji.php';
require DOC_ROOT.'/includes/class.steamid.php';
$version = 1.01;
define("VERSION",$version);
	$build = "16551-1149737173";
    $shortopts ="i:s:v::";
	$longopts[]="debug::";
	$longopts[]="help::";
	$longopts[]="quick::";
	$longopts[]="remote::";
	$longopts[]="silent::";
	$longopts[]="force-modify::";
	$longopts[]="no-email::";
	$options = getopt($shortopts,$longopts);
	define ('options',$options);
	if(isset($options['debug'])) {
		define('debug',true);
		error_reporting( -1 );
		unset($options['debug']);
	}
	else {
		define('debug',false);
		error_reporting(0);

	}
	if (isset($options['quick'])) 
	{
		define('quick',true);
		if(debug) {
			echo 'quick scan enabled'.cr;
		}
	}
	else {
		define('quick',false);
		
	}
	if(isset($options['silent'])) {
		define('silent',true);
		
	}
	else {
		define('silent',false);
	}
	if(isset($options['force-modify'])){
		define('modify',true);
	}
	else {
		define('modify',false);
	}
	if(debug) {
		echo 'current options '.cr.print_r($options,true).cr;
		echo '$argv is '.cr.print_r ($argv,true).cr;
	}
	$prog = basename($argv[0]);
	if(isset(options['v'])){
			echo "$prog v$version-$build © NoIdeer Software ".date('Y').cr;
		exit;
	}

if (empty( $settings['ip_key'] )) {
	echo 'Fatal Error - api key missing'.cr;
	exit(7);
}
$key = $settings['ip_key'] ;
if (is_cli() == false){
echo 'wrong enviroment';
exit;
}
if(empty($options['s'])) {
	echo "$prog v$version - $build © NoIdeer Software ".date('Y').cr;
	if (!isset(options['help'])) {
		echo 'Please supply a Server to scan'.cr;
	}
	echo 'Examples :- '.cr."\t".$prog.' -s<server id>'.cr;
	echo "\t$prog -s<server id> -i<file to import> do not use -i with the all server option it is used for importing data and \e[4mnot\e[0m scanning".cr;
	echo "\t$prog -s<all> this will scan all servers using the default log(s), slow but thorough ".cr;
	//echo "\t--quick scans the current steam log rather than the full log faster but not so thorough, works with all other options".cr;
	echo "\t--debug logs technical details to the console, works with all other options".cr;
	echo "\t--force-modify updates user information even if their IP address has not changed".cr;
	echo "\t--help this information".cr;
	exit(0);
}
$update_done= array();
$file =options['s'];
if ($file == 'all') {
	    if(isset($options['i'] )) {
			die( 'Error  -i can not be set if -s is set to all'.cr);
		}
		$game_sql = 'SELECT * FROM `server1` where  running="1" order by host_name ASC';
		$game_results = $database->get_results($game_sql);
		$display='';
	
	
	foreach ($game_results as $run) {
		//bulid path
		$server_key = md5( ip2long($run['base_ip'])) ;
		if (quick) {
			$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=lsof&filter='.$run['host_name'].'&loc='.$run['location'].'/'.$run['game'].'&return=content'; //used for steam log
			
		}
		else {
			$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log'; //used for screen log
					}
		$tmp = geturl($path);
		if(debug) {
			echo $run['host_name'].' '.$path.cr; // debug code
		}
				
		if (!empty($tmp)) {
			//echo $tmp.cr; //debug code
		     $display .= scan_log($run['host_name'],$tmp);
	}
	
	}
	
	//echo 'display = '.strlen($display).cr;
	if(empty($display)) {
		
			if (silent == false) {
				echo 'silent = false'.cr;
				echo 'no output'.cr;
			}
	}
	if(debug) {
		echo $display;
	}
	else {
		
		if(!empty(trim($display))) {
			if(isset($options['no-email'])) {
				echo $display;
			}
			else{
			$a1 = explode("@", $settings['adminemail']);
            $domain = $a1[1];
			$full_date = date($settings['date_format'].' - '.$settings['time_format']);
			$to = $settings['adminemail'];
			$subject = "PHPgsm User Scan at $full_date";
			$txt = $display;
			$headers = "From: PHPgsm <phpgsm@$domain>" . cr;
			mail($to,$subject,$txt,$headers);
		}
		}
	}
}
else {
	// do supplied file
	if(isset(options['i'])) {
	if (!file_exists(options['i'])) {
		echo 'could not open '.options['i'].cr;
		exit (1);
	}
}
	$allsql = 'SELECT * FROM `server1` where host_name="'.options['s'].'"';
		//echo $allsql.cr;
	$run = $database->get_row($allsql);
	if(empty($run)) {
		echo 'Invalid server id '.options['s'].' correct & try again'.cr;
		exit(2);
	}
	//$server_key = md5( ip2long($run['ipaddr'])) ;
	//$path = $argv[1];
	//print_r($run);
	if (!isset(options['i'])) {
		if (quick) {
			$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=lsof&filter='.$run['host_name'].'&loc='.$run['location'].'/'.$run['game'].'&return=content'; //used for steam log
			if (debug) {
				echo $path.cr;
			}
		}
		else {
			$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log';
		}
	}
	else {
		// assume run local 
		$path = options['i'];
	}
		
		
		$tmp = geturl($path);
		echo scan_log(options['s'],$tmp);
}

/* ----- LOCAL FUNCTIONS  -----*/


function scan_log($server,$data) {
	
	// scan log file
	$new_users = 0;
	$done= 0;
	$update_users = 0;
	$update_server = false;
	$return ='';
	global $database, $key, $settings;
	$update_req = 'Your server needs to be restarted in order to receive the latest update.';
	$user_sql = 'select * from players where steam_id64="'; // sql stub for user updates
	$log_lines = explode(cr,$data);
	if ( strpos($data,$update_req)) {
			// server needs an update & restart
			$update_server = true;
		}
	if(debug) {
		echo 'Rows to process '.count($log_lines)." on $server".cr; //debug code
	}
	foreach ($log_lines as $log_line) {
		// loop lines, in here check for server needs a restart
		$bot = strpos($log_line,' connected, address "none');
		if($bot) {continue;} //ignore bot lines
		$x = strpos($log_line,' connected, address ');
		if ($x >0) {
		// save output
		$log_line=trim($log_line);
		$tmp = user_data($log_line);
		$users[] = $tmp;
		
	}
	
}
	if (isset($users)) {
		if(debug){
			echo "$server Found the following:-".cr. print_r($users,true).cr;
		}
		$pc = count($users);
	} //debug code
	else {
		if (!silent){
			$return = "\t No Logons in selected log for $server since last server start".cr;
		}
		$pc = 0;
		if ($update_server == true) {
			$s = update_server($server);
		return $s;
	}
		return $return;
	}
	
		foreach ($users as $user) {
			// process user
			$modifed = false; // not modified yet do we need this ?
			$added = false; //not added yet do we need this ?
			$user_output = ''; //clear output 
			$user_name = trim($user['name']); //user name
			$user_search = $user['id2'].'"'; //database search term
			$ip = ip2long($user['ip']); //converted IP address
			$sql = $user_sql.$user_search; // full query
			$user_result = $database->get_row($sql); // get user info
				if(!empty($user_result)) {
					// now process user
					$user_output = "\t$user_name (".$user_result['country'].") ";
					unset($user_result['id']); // take out id
					unset($user_result['steam_id']);// dont save native steam_id
					$where['steam_id64'] = $user['id2']; // database update clause
					$last_logon = strtotime($user['time']); // latest login time
					//print_r($user_result);
					// start to check log data against user data
					if ($last_logon <=  $user_result['last_log_on']) {
						continue; // re reading the same as before let's move on
					}
					else {
						if (empty($return)) {$return = "Processing server $server".cr;}
						$update_users++; // how many updated this run 
						// new login
						//$return .= "new login from $user_name on $server ";
						$user_result['last_log_on'] = $last_logon; // update time
						$user_result['log_ons'] ++; // add the logon
						$user_output.= ' new logon  at '.date($settings['date_format'],$lastlogon).'  '.date($settings['time_format'],$lastlogon).' (total '.$user_result['log_ons'].')'; //screen display
						if ($ip <> $user_result['ip'] or modify) {
							// we have a changed IP or forcing an update
							$ip_data = get_ip_detail($user['ip']); // get new ip information
							$user_result['continent'] = $ip_data['continent_name'];
							$user_result['country_code'] = $ip_data['country_code'];
							$user_result['country'] = $ip_data['country_name'];
							$user_result['region'] = $ip_data['region'];
							$user_result['city'] = $ip_data['city'];
							$user_result['flag'] = Emoji::Encode($ip_data['emoji_flag']);
							$user_result['time_zone'] = $ip_data['time_zone']['name'];
							if (isset($ip_data['asn'])) {
								$user_result['type'] = $ip_data['asn']['type'];
							}
							else {
								$user_result['type'] ='n/a';
							}
							$user_result['threat'] = $ip_data['threat']['is_threat'];
							$user_output.= ' IP Changed from '.long2ip($user_result['ip']).' to '.long2ip($ip); //screen display
						}
						if (trim($user_name) <> trim($user_result['name'])) {
							$user_output.= ' User name change from '.$user_result['name'].' to '.$user_name; // changed user name
							$result['name'] = $user_name;
						}
						if(strpos($user_result['server'],$server) === false) {
							$user_output.= ' played a new server'; // playing a new server
							$result['server'].=$server.'*';
						}
						$user_output.=cr;
						$return .= $user_output; //screen output
						$user_result = $database->escape($user_result); // escape the changes ready to insert into the database
						$update = $database->update('players',$user_result,$where); // udate the database
						if ($update === false) {
							echo cr.'Database Update failed with'.cr;
							print_r($result);
							//$update_users--;
						}
						else {
							// database updated do second update
							$sql = 'call update_logins ('.$user_result['steam_id64'].',"'.$server.'",'.$user_result['last_log_on'].')';  //add more to this ?
							$database->query($sql);
						}
					}
				}
			else {
				if (empty($return)) {$return = "Processing server $server".cr;}
				$new_users++; // how many added this run
				// here we add a new user
				$last_logon = strtotime($user['time']); // get first login time
				$ip_data = get_ip_detail($user['ip']); // get the ip data
				$insert['ip'] = $ip; // save long ip
				$insert['steam_id64'] = $user['id2'];
				$insert['name'] = $user_name;
				$insert['first_log_on'] = $last_logon;
				$insert['log_ons'] = 1;
				$insert['last_log_on'] = $last_logon;
				$insert['continent'] = $ip_data['continent_name'];
				$insert['country_code'] = $ip_data['country_code'];
				$insert['country'] = $ip_data['country_name'];
				$insert['region'] = $ip_data['region'];
				$insert['city'] = $ip_data['city'];
				$insert['flag'] = Emoji::Encode($ip_data['emoji_flag']);
				$insert['time_zone'] = $ip_data['time_zone']['name'];
				if (isset($ip_data['asn']['type'])) {$insert['type'] = $ip_data['asn']['type'];}
				else {$insert['type'] = 'N/A';}
				$insert['threat'] = $ip_data['threat']['is_threat'];
				$insert['server'] = $server.'*';
				$insert = $database->escape($insert); // escape data
				$update = $database->insert('players',$insert); // add to database
				if ($update === true ){
					$return .="\t".$user_name.' ('.$insert['country'].') New User Record added at '.date($settings['date_format'],$last_logon).' '.date($settings['time_format'],$last_logon).cr;
					$sql = 'call update_logins ('.$insert['steam_id64'].',"'.$server.'",'.$insert['last_log_on'].')';
					$database->query($sql);
				}
			 
			else {
				echo 'Database Insertion failed with'.cr;
				print_r($insert);		 
			}

		}	
	}
	$mask = "%15.15s %4.4s \n";
	if ($update_users >0 || $new_users >0 ) {
		$return .= sprintf($mask,'Modified Users',$update_users );
		$return .= sprintf($mask,'New Users',$new_users );
	} 
	if ($update_server == true) {
		$return .= cr.'Warning '.$server.' needs updating & restarting'.cr;
		$return .= update_server($server);
	}
	if(!empty($return)) {
		$return .= "Processed $server".cr;
	}
	else {
		if (!silent) {
			return "\t No new logons on $server since last scan".cr;
		}
	}
	if (debug) {
		$return .= "counts modified = $update_users new = $new_users".cr;
	}
	return $return;
}


function get_ip_detail($ip) {
	// return api data
	global $key;
	
	$cmd =  'https://api.ipdata.co/'.$ip.'?api-key='.$key;
	if (debug) {
		echo "getting ip data with $cmd".cr; 
	}
	$ip_data = json_decode(geturl($cmd), true); //get the result
	if (empty($ip_data)) {
		echo "Couldn't get data for $ip".cr;
		return null;
	}
	 if (empty($ip_data['threat']['is_threat'])) {$ip_data['threat']['is_threat']=0;}
	 //print_r($ip_data); //debug code
	 return $ip_data;
}

function user_data($value) {
	//
	 $vx =preg_split('/"/', $value);
	 $sx =preg_split('/<\d+>/', $value);
	 //preg_split('/: "/', $input_line);
	 $sx2 = preg_split('/: "/', $sx[0]);
	 $sx3 = preg_split('/"/', $sx[1]);
	 $vx = array_filter($vx);
	 $vx[0] = trim(str_replace('L ','',$vx[0]));
	 $vx[0] =  substr($vx[0], 0, -1);
	 $vx[0] = str_replace('-','',$vx[0]);
	 $s =preg_split('/<\d+>/', $vx[1]);
	  if(debug) {
		 echo "value = $value".cr;
		 echo 'count of $vx = '.count($vx).cr;
		echo '$vx is set to'.cr.print_r($vx,true).cr;
		echo '$sx is set to'.cr.print_r($sx,true).cr;
		echo '$sx2 is set to'.cr.print_r($sx2,true).cr;
		echo '$sx3 is set to'.cr.print_r($sx3,true).cr;
		echo '$s is set to'.cr.print_r($s,true).cr;
		}
	 $s[1] = str_replace('<','',$sx3[0]);
	 $s[1] = str_replace('>','',$s[1]);
	 $vx[1] = Emoji::Encode($sx2[1]);
	 $vx[2] = $s[1];
	 $vx[3] = strtok( $sx3[2], ':' );
	 $steam_id = new SteamID( $vx[2] );
	 $vx[] = $steam_id->ConvertToUInt64();
	 $return['time'] = $vx[0];
	 $return['name'] = $vx[1];
	 $return['id'] = $vx[2];
	 $return['id2'] = $steam_id->ConvertToUInt64();
	 $return['ip'] = $vx[3];
	 if(debug) {
		echo 'Returning user data as'.cr.print_r($return,true).cr;
		}
	 return $return;
}	 

function update_server($server){
	// if found stop the server and update
	//Your server needs to be restarted in order to receive the latest update.
	global $database, $update_done,$settings,$prog;
	$s = "Server Update via $prog ".VERSION.cr;
	$sql = 'select * from server1 where host_name="'.$server.'"';
	$steamcmd = '/usr/games/steamcmd'; // needs to be full path for sudo
	$game = $database->get_row($sql);
	$stub =  $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&cmd='; // used to start & stop
	if (in_array($game['install_dir'],$update_done)) {
				$s .= 'Update already done'.cr;
			    //$cmd = $stub.'r';
			    //$s .=  file_get_contents($cmd).cr; 	
				return $s;
			}
	$cmd = $stub.'q';
	$s .= geturl($cmd).cr; // stopped server
	// need to check if this is a root install, if so elevate the privs  TODO update app version number 
	$exe = urlencode("sudo $steamcmd +login anonymous +force_install_dir ".$game['install_dir'].' +app_update '.$game['server_id'].' +quit');
	$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
	$s .=geturl($cmd);
	//echo 'updated server using '.$cmd.cr;
	//$cmd = $stub.'s';
	//$s .= file_get_contents($cmd).cr;
	//need to restart all that stem from this install dir
	$sql = "SELECT * FROM `server1` WHERE `game` like '".$game['game']."' and `install_dir` like '".$game['install_dir']."'";
		$restarts = $database->get_results($sql);
		foreach ($restarts as $restart) {
			// restart them all
			$cmd =  $game['url'].':'.$restart['bport'].'/ajaxv2.php?action=exescreen&server='.$restart['host_name'].'&cmd=r'; // used to restart
			$s .= geturl($cmd).cr;
		}
		
	$update_done[] = $game['install_dir'];
	return $s;
}
?>
