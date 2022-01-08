<?php
if (!defined("IN_PHPGSM")) die( "phpgsm didn't include me :-(");
 $build = "9790-1448902081";
 $version = "1.00";
 require 'includes/class.emoji.php'; //add emoji class
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	//define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	//define( 'SQ_ENGINE',      SourceQuery::SOURCE );
//else die( "Unknown file included me");
//print_r($_SERVER);
function game_detail() {
	// get processes
	include 'includes/vdfparser.php';
	$gameq  = new SourceQuery( );
	
	global $cmds; // get options 
	$db = new db();
	$mem =0;
	$cpu = 0;
	$total_players = 0;
	$total_bots = 0;
	$total_slots = 0;
	
	
			if(isset($cmds['ip'])) {
				$ip = $cmds['ip'];
			}
			else {
					$host= gethostname();
					$ip = gethostbyname($host);
					$localIP = trim(shell_exec('hostname -I'));
	                $localIPs = explode(' ',$localIP);
	                //print_r($localIPs);
					//$ip = geturl('https://api.ipify.org');
					if (empty($ip)) { $ip = geturl('http://ipecho.net/plain');}
				}
				//$checkip = substr($ip,0,strlen($ip)-1);
				if(empty($ip)) {
					$ip= trim(shell_exec("hostname -I | awk '{print $1}'"));
				}
				$checkip = $ip; 
				
				// use srcds_linux to test 
				exec('ps -C srcds_linux -o  pid,%cpu,%mem,cmd |sed 1,1d',$game_list,$val); // this gets running only needs rework may 2021 for sure jan 2022
				foreach ($game_list as $game) {
					$tmp = array_values(array_filter(explode (" ",$game)));
					$result = find_string_in_array($tmp,'.cfg');
					$tmp['host'] = pathinfo(implode('; ', $result), PATHINFO_FILENAME); 
					$output[] = $tmp;
				}

				//$tmp = explode(PHP_EOL,$t);
				$i=0;
				$sql ='select  servers.location,count(*) as total,servers.host from servers where servers.fname like "'.$cmds['server'].'"'; //fname not ip
						$server_no = $db->get_row($sql); // total servers
						$server_count = $server_no['total'];
						if ($cmds['debug'] == "true") { echo "\$server_count = $server_count".cr; }
				if(empty($output)) {
						// nothing running
								 
						if ($server_count['host'] <> $ip){
							$return = $cmds['server'].' is not hosted here';
							return $return;
						}    
						
						$du = shell_exec('du -s '.dirname($server_count['location']));
						list ($tsize,$location) = explode(" ",$du);
				}
			else{
				if(isset($cmds['filter'])) {
					//filtered output
					$sql = 'select * from server1 where host_name = "'.$cmds['filter'].'"';
				}
				else{
					$sql = 'select * from server1 where fname like "'.$cmds['server'].'" and enabled=1 order by server_name ASC';
				}
					$servers = $db->get_results($sql);
					
						
						foreach ($servers as $server) {
										//$key = array_search('100', array_column($userdb, 'uid'));
										$run_record = get_key($server['host_name'],$output) ;					
										if (get_key($server['host_name'],$output) >= 0) {
											
											
												// running server add live data 
												$server['vdf_file'] = $server['location'].'/steamapps/appmanifest_'.$server['server_id'].'.acf'; 
												$kv = VDFParse($server['vdf_file']);
												$server['vdf_data'] =$kv['AppState'];
												if ($server['running']) {
													$server['online'] = 'Online';
													try
														{
															$gameq->Connect( $server['host'], $server['port'], SQ_TIMEOUT, SQ_ENGINE );
															$sub_cmd = 'GetInfo';
															$info1 = $gameq->GetInfo();
															$server  = array_merge($server ,$info1);
															if ($info1['Players'] > 0) {
																$sub_cmd = 'GetPlayers';
																$total_players += ($info1['Players']-$info1['Bots']);
																$total_bots += $info1['Bots'];
																$server['players']  = $gameq->GetPlayers( ) ;
																$server['players'] = add_steamid($server['players']);
																}
														}
													catch( Exception $e )
														{
															$Exception = $e;
															if (strpos($Exception,'Failed to read any data from socket')) {
																$Exception = 'Failed to read any data from socket (Game Detail=>'.$sub_cmd.')';
														}
														
														$error = date("d/m/Y h:i:sa").' => '.$server['host_name'].'('.$server['host'].':'.$server['port'].') '.$Exception;
														//sprintf("[%14.14s]",$str2)
														$mask = "%17.17s %-30.30s \n";
														file_put_contents(LOG,$error.cr,FILE_APPEND);
														//$server['Players'] = 0;
														}
													}
												$total_slots  += $server['MaxPlayers'];	
												$pid = $output[$run_record][0];
												$server['mem'] = $output[$run_record][2];
												$server['cpu'] = $output[$run_record][1];
												//$cmd = 'top -b -n 1 -p '.$pid.' | sed 1,7d'; // use top to query the process
												//$top = array_values(array_filter(explode(' ',trim(shell_exec($cmd))))); // arrayify
												//$count = count($top); // how many records  ?
												$mem += $server['mem']; // memory %
												$cpu += $server['cpu']; // cpu %
												$du = trim(shell_exec('du -s '.$server['location'])); // get size of game
												$size = str_replace($server['location'],'',$du);
												$server['size'] = formatBytes(floatval($size)*1024,2);
													if (empty($server['host_name'])) {
															$logline =date("d/m/Y h:i:sa").' No Host_name !! '.$server1.PHP_EOL;
															file_put_contents('logs/ajax.log',$logline,FILE_APPEND);
															continue;
													}
													if (isset($cmds['filter'])) {
														// run a different array
														$return['server'] = $server;
													}
												else {	
													$return[$server['fname']][$server['host_name']] = $server;
												}
													$i++;
										}
										else {
												$du = trim(shell_exec('du -s '.$server['location'])); // get size of game
												$size = str_replace($server['location'],'',$du);
												$server['mem'] = 0;
												$server['cpu'] = 0;
												$server['online'] = 'Offline';
												$server['size'] = formatBytes(floatval($size)*1024,2);
												if (isset($cmds['filter'])) {
													// run a different array
													$return['server'] = $server;
												}
												else {
													$return[$server['fname']][$server['host_name']] = $server;
												}
										} 
						}	
						$du = shell_exec('du -s '.dirname($server['location']));
						$tsize = str_replace(dirname($server['location']),'',$du);
			}
	// add computed items
				$return['general']['server_id'] = $cmds['server'];
				$return['general']['live_servers'] = $i;
				$return['general']['total_players'] = $total_players;
				$return['general']['total_bots'] = $total_bots;
				$return['general']['used_slots'] = $total_players+$total_bots;
				$return['general']['total_slots'] = $total_slots;
				$return['general']['total_servers'] = $server_count;
				$return['general']['total_mem'] = round($mem,2,PHP_ROUND_HALF_UP);
				$return['general']['total_cpu'] = round($cpu,2,PHP_ROUND_HALF_UP);
				$return['general']['total_size'] = formatBytes(floatval($tsize)*1024,2);
				$return['general']['total_size_raw'] = floatval($tsize);
				return $return;
}		

function add_steamid($players) {
	if (count($players)) {
	// we have players
	$emoji = new Emoji;
	$database = new db();
	orderBy($players,'Frags','d'); // score order
	$sql = 'select * from players where BINARY name="';
	foreach ($players as $k=>$v) {
		//loop  add flag & country $v being the player array 
		// don't update player here let scanlog do it
		$players[$k]['Name']=$emoji->Encode($v['Name']);
		$player_data = $database->get_results($sql.$database->escape($players[$k]['Name']).'"'); // player info from db
		if (!empty($player_data)) {
			// here we go
			//echo 'Result '.print_r($player_data,true).cr;
			$player_data= reset($player_data);
			$players[$k]['Name'] = $emoji->Decode($players[$k]['Name']);
			$players[$k]['flag'] = 'src ="https://ipdata.co/flags/'.trim(strtolower($player_data['country_code'])).'.png"'; // windows don't do emoji flags use image 
			$players[$k]['country'] = $player_data['country'];
			$players[$k]['steam_id'] = $player_data['steam_id64']; // user steam_id
			$players[$k]['ip'] = long2ip($player_data['ip']); // recorded ip address
		}
		else {
			// no current flag or country
			// add a default image for the flag
			// random country
			if(empty(trim($players[$k]['Name']))) { $players[$k]['Name'] = 'Spectator';}
			$players[$k]['flag'] = 'src ="/img/'.'unknown.png"'; // windows don't do emoji flags use image
			$players[$k]['country'] = 'unknown';
			
		}
	}
	return $players;
}
return  false;
}

function find_string_in_array ($arr, $string) {

    return array_filter($arr, function($value) use ($string) {
        return strpos($value, $string) !== false;
    });

}

function get_key ($search,$array) {
	$found = array_filter($array,function($v,$k) use ($search){
  return $v['host'] == $search;
},ARRAY_FILTER_USE_BOTH); // With latest PHP third parameter is optional.. Available Values:- ARRAY_FILTER_USE_BOTH OR ARRAY_FILTER_USE_KEY  
$keys =  array_keys($found); 
	if (count($keys) == 1) {
		return $keys[0];
	}
	else {
		return false;
	}
}