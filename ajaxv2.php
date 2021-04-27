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
 * ajax v2 to go with xml v2 & mybb.js
 *  requires PHP  >= 7.4
 */
require_once 'includes/master.inc.php';
include 'functions.php';
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');
	define( 'VERSION', 2.03);
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	
error_reporting (0);
$ip = $_SERVER['SERVER_ADDR']; // get calling IP
$sql = 'select * from base_servers where base_servers.ip ="'.$_SERVER['REMOTE_ADDR'].'"'; // do we know this ip ? mybb sets this at login
$valid = $database->num_rows($sql); // get result if the ip can use the data the return value >0

if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	$logline  = date("d-m-Y H:i:s").' localhost accessed ajax with '.print_r($cmds,true).cr;
	//file_put_contents(LOG,$logline,FILE_APPEND);
	if ($cmds['debug'] == 'true') {
		error_reporting( -1 );
		echo 'Ajax v'.VERSION.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	    foreach ($cmds as $k => $v) {
			if ($k == 'debug'){continue;}
			print "[$k] => $v".cr;
		}
		if (empty($cmds['action'])) {exit;}
	}
	else {error_reporting( 0 );}
	
}
else {
	//define ('CR',"<br>");
	//define ('cr',"<br>");
	error_reporting( 0 );
	if (!empty($_POST)) {
		$cmds = convert_to_argv($_POST,"",true);
	}
	else {
		if (isset($cmds)) {
			$cmds = array_merge($cmds,convert_to_argv($_GET,"",true));
		}
		else {
			$cmds = convert_to_argv($_GET,"",true);
		}
	}
}

if(!$valid) { 
	die( 'invalid request '.$ip.cr );
}

// do what's needed
	switch (strtolower($cmds['action'])) {
		case "all" :
		if($cmds['debug'] =='true' ) {
			echo cr.print_r(all($cmds),true).cr;
				}
		else {
			echo json_encode(all($cmds));
		}
			exit;
			
		case "boottime" :
			echo get_boot_time();
			exit;
			
		case "check_services" :
		if ($cmds['debug'] == true){
			print_r(check_services());
		}
		else {
			echo json_encode(check_services());
			
		}
			exit;
				
		case "exescreen" :
				echo exescreen($cmds);
				if (is_cli()) {
					echo cr;
				}
				exit;
				
		case "exe" :
				echo exe($cmds);
				exit;	
				
		case "get_file" :
			if (isset($cmds['post'])) {
				file_put_contents(LOG,print_r($cmds,true),FILE_APPEND);
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
					
			case "game_detail" :
					
					if($cmds['debug'] =='true' ) {
						echo print_r(game_detail(),true).cr;
						}
					else {
							echo json_encode(utf8ize(game_detail()));
						}
					exit;	
			
			case "version":
				echo 'Ajax v'.VERSION.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr; 
				exit;					
}

function lsof($cmds) {
						
						$tpid = shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg');
						$get_pid = explode(' ',trim($tpid));
						$pid= $get_pid[0];
						$tmp = shell_exec('ls -l /proc/'.$pid.'/fd |grep '.$cmds['loc'].'/logs');
						//echo $tmp.cr; // debug code
						$x = explode(' ',$tmp);
							foreach ($x as $k=>$v) { 
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
							//echo 'get contents of '.$filename.'    '.filesize($filename).cr;
							echo file_get_contents($filename);
						}
						else {
							echo $filename;
							
						}
						if (is_cli()) {echo cr;}
				exit;
			}
			
function game_detail() {
	// get processes
	
	$gameq  = new SourceQuery( );
	
	global $cmds; // get options 
	$db = new db();
	$mem =0;
	$cpu = 0;
	$total_players = 0;
	$total_bots = 0;
	$total_slots = 0;
	$r=1;
	if(isset($cmds['filter'])) {
		$ip = file_get_contents('https://api.ipify.org');// get ip
		 if (empty($ip)) { $ip = file_get_contents('http://ipecho.net/plain');} 
		 $sql = 'select * from server1 where host_name = "'.$cmds['filter'].'"';
		 if ($db->num_rows($sql) >0) {		 
			$server_data = $db->get_results($sql);
			$server_data=reset($server_data);
		  if (empty($server_data['base_ip'])) {         
                if ($ip <> trim($server_data['host'])) {
					echo 'Invalid enviroment '.cr;
					exit;
				}
			}
		}
		else {
			      echo 'Invalid enviroment '.cr;
					exit;
				}
				        
                $cmd = 'ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg';
               
                $new = trim(shell_exec($cmd));
                // temp log
				$logline =date("d/m/Y h:i:sa").' Filtered Output '.$cmds['filter'].cr;
				file_put_contents(LOG,$logline,FILE_APPEND);
                if (empty($new)) {
					$du = shell_exec('du -s '.$server_data['location']); // get size of game
					$du = str_replace('<br>','',$du);
					$x = strpos(trim($du),'/');
					$size = trim(substr($du,0,$x-1));
					$server_data['cpu'] = '0';
					$server_data['size'] = formatBytes(floatval($size)*1024,2);
					$server_data['mem'] = '0';
					$server_data['beta'] = 'not running';
				}
               
                $tmp = explode(' ',$new);
				if (!empty($tmp[0])) {
					$pid = $tmp[0];
					$count = count($tmp);
					$temp =  trim(file_get_contents($server_data['url'].':'.$server_data['bport'].'/ajax.php?action=top&filter='.$pid.'&key='.md5( ip2long($ip))));
					$temp = array_values(array_filter(explode(' ',$temp)));
					$du = shell_exec('du -s '.$server_data['location']); // get size of game
					$x = strpos(trim($du),'/');
					$size = trim(substr($du,0,$x-1));
					//$server_data['count'] =  count($temp);
					$server_data['mem'] = count ($temp);
					$server_data['cpu'] = $temp[count($temp)-4];
					$server_data['size'] = formatBytes(floatval($size)*1024,2);
					$server_data['beta'] = 'running';
					$server['online'] = 'Online';
													try
														{
															$gameq->Connect( $server_data['host'], $server_data['port'], SQ_TIMEOUT, SQ_ENGINE );
															$sub_cmd = 'GetInfo';
															$info1 = $gameq->GetInfo();
															
															//echo print_r($info1,true).cr;
															$server_data  = array_merge($server_data ,$info1);
															if ($info1['Players'] > 0) {
																$total_players += $info1['Players'];
																$sub_cmd = 'GetPlayers';
																$server_data['players']  = $gameq->GetPlayers( ) ;
																}
														}
													catch( Exception $e )
														{
															$Exception = $e;
															if (strpos($Exception,'Failed to read any data from socket')) {
																$Exception = 'Failed to read any data from socket Module (Ajax - Game Detail '.$sub_cmd.')';
														}
						
														$error = date("d/m/Y h:i:sa").' ('.$sever_data['host'].':'.$server_data['port'].') '.$Exception;
														//sprintf("[%14.14s]",$str2)
														$mask = "%17.17s %-30.30s \n";
														file_put_contents(LOG,$error.cr,FILE_APPEND);
														}
					}
		$gameq->Disconnect( );			
		$return[$server_data['host_name']] = $server_data;
		return $return;
	}
// no filter start
	else{
			if(isset($cmds['ip'])) {
				$ip = $cmds['ip'];
			}
			else {
					$host= gethostname();
					$ip = gethostbyname($host);
					$ip = file_get_contents('https://api.ipify.org');
					if (empty($ip)) { $ip = file_get_contents('http://ipecho.net/plain');}
				}
				$checkip = substr($ip,0,strlen($ip)-1); 		
				$t =trim(shell_exec('ps -C srcds_linux -o pid,cmd |sed 1,1d')); // this gets running only 
				$tmp = explode(PHP_EOL,$t);
				$i=0;
				if(strlen($t) === 0) {
						// nothing running
						$sql =  'SET sql_mode = \'\'';
						$a= $db->query( 'SET sql_mode = \'\''); 
						$sql ='select  servers.location,count(*) as total from servers where servers.host like "'.$checkip.'%"';
						//echo $sql;
						$server_count = $db->get_row($sql);
						if (empty($server_count['location'])) {
							$cmds['debug']='true';
							$return = 'No Servers found for '.$ip;
							return $return;
						}
						$du = shell_exec('du -s '.dirname($server_count['location']));
						list ($tsize,$location) = explode(" ",$du);
				}
			else{
						// here we have the runners in $tmp array
						$sql = 'select * from server1 where host like "'.$checkip.'%" and enabled=1 order by server_name ASC';
						$servers = $db->get_results($sql);
						$server_count = $db->num_rows($sql);
						
						foreach ($servers as $server) {
															
										if (array_find($server['host_name'].'.cfg',$tmp) >= 0) {
											$total_slots  += $server['max_players'];	
												// running server add live data 
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
												$rec = array_find($server['host_name'].'.cfg',$tmp);
												$server1 = str_replace('./srcds_linux','',$tmp[$rec]); // we don't need this throw it
												$server1 = str_replace(' -insecure','',$server1); // we don't need this throw it
												$server1= trim($server1); // get rid of spaces & CR's 
												$tmp_array[$i] = explode(' ',$server1); // arrayify
												// temp log
												$pid = $tmp_array[$i][0]; // git process id
												$cmd = 'top -b -n 1 -p '.$pid.' | sed 1,7d'; // use top to query the process
												$top = array_values(array_filter(explode(' ',trim(shell_exec($cmd))))); // arrayify
												$count = count($top); // how many records  ?
												$mem += $top[$count-3]; // memory %
												$cpu += $top[$count-4]; // cpu %
												$du = trim(shell_exec('du -s '.$server['location'])); // get size of game
												$size = str_replace($server['location'],'',$du);
												$server['mem'] = $top[$count-3];
												$server['cpu'] = $top[$count-4];
												$server['size'] = formatBytes(floatval($size)*1024,2);
													if (empty($server['host_name'])) {
															$logline =date("d/m/Y h:i:sa").' No Host_name !! '.$server1.PHP_EOL;
															file_put_contents('logs/ajax.log',$logline,FILE_APPEND);
															continue;
													}
													
													$return[$server['fname']][$server['host_name']] = $server;
													$i++;
										}
										else {
												$du = trim(shell_exec('du -s '.$server['location'])); // get size of game
												$size = str_replace($server['location'],'',$du);
												$server['mem'] = 0;
												$server['cpu'] = 0;
												$server['online'] = 'Offline';
												$server['size'] = formatBytes(floatval($size)*1024,2);
												$return[$server['fname']][$server['host_name']] = $server;
										} 
						}	
						$du = shell_exec('du -s '.dirname($server['location']));
						$tsize = str_replace(dirname($server['location']),'',$du);
			}
	// add computed items
				$return['general']['server_id'] = $server['fname'];
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
}		
function all($cmds) {
	//get the lot
			global $database;
			$return=get_cpu_info();
			$return = array_merge($return,get_software_info($database));
			$return = array_merge($return,get_disk_info());
			$return = array_merge($return,get_mem_info());
			$return = array_merge($return,get_user_info($return));
			//if(isset($cmds['servers'])) {
				$tmp = game_detail();
				$add = $tmp['general'];
				$x = floatval($add['total_size'])/1000; // get size
				$return['quota_pc'] =  number_format( $x* (100/floatval($return['quota'])) ,2);
				$return = array_merge($return,$add);
				//}
			return $return;
		}	
		
		
		
function exescreen ($cmds) {
	// start & stop etc
	global $database;
	$exe =$cmds['server'];
	$localIP = getHostByName(getHostName()); // carefull if the hostname is set to 127.0.0.1
	$sql = 'select * from servers where host_name = "'.trim($exe).'"';
	$server = $database->get_row($sql); // pull results
	if (empty($server['host'])) {
		$return = 'invalid server'; // don't know this server
	   	return $return;
	}
	if ($server['host'] <> $localIP) {
		return 'This Server is not hosted here'; // we know this one but it's elsewhere
	}
	// valid so do it
	
	$cmd = 'ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$exe.'.cfg';
	$is_running = shell_exec ($cmd); // are we running ?
	switch ($cmds['cmd']) {
		case 's' :
			if ($is_running) {
				$return = $exe.' is already running';
				break;
			}
			
			chdir($server['location']);
			$logFile = $server['location'].'/log/console/'.$server['host_name'].'-console.log' ;
			$savedLogfile = $server['location'].'/log/console/'.$server['host_name'].'-'.date("d-m-Y").'-console.log' ;
			rename($logFile, $savedLogfile); // log rotate
			$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$server['host_name'];
			exec($cmd); // open session
			$cmd = 'screen -S '.$server['host_name'].' -p 0  -X stuff "'.$server['startcmd'].'^M"'; //start server
			exec($cmd);
			$sql = 'update servers set running = 1 where host_name = "'.$exe.'"';
			$update['running'] = 1;
			$update['starttime'] = time();
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			$return = 'Starting Server '.$server['host_name'];
			break;
			
		case 'q':
			if (!$is_running) {
				$return = $exe.' is not running';
				break;
			}
			
			$cmd = 'screen -X -S '.$server['host_name'] .' quit';
			exec($cmd);
			$return = 'Stopping Server '.$server['host_name'];
			$update['running'] = 0;
			$update['starttime'] = '';
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			break;
			
		case 'r':
			if (!$is_running) {
				$return = $exe.' is not running';
				// should we just start or quit ??
				break;
			}
			
			$cmd = 'screen -X -S '.$server['host_name'] .' quit';
			exec($cmd); // stop the server 
			chdir($server['location']);
			$logFile = $server['location'].'/log/console/'.$server['host_name'].'-console.log' ;
			$savedLogfile = $server['location'].'/log/console/'.$server['host_name'].'-'.date("d-m-Y").'-console.log' ;
			rename($logFile, $savedLogfile); // log rotate
			sleep(1); //slow the process up
			$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$server['host_name'];
			exec($cmd); // open session
			$cmd = 'screen -S '.$server['host_name'].' -p 0  -X stuff "'.$server['startcmd'].'^M"'; 
			exec($cmd); // restart the server
			$sql = 'update servers set running = 1 where host_name = "'.$exe.'"';
			$update['running'] = 1;
			$update['starttime'] = time();
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			$return = 'Restarting Server '.$server['host_name'];
			break;
			
		case 'c':
			if (!$is_running) {
				$return ='start '.$exe.' before sending commands';
				// should we just start or quit ??
				break;
			}
			
			$cmd = 'screen -S '.$exe.' -p 0 -X stuff "'.trim($cmds['text']).'^M"';
			$return = $cmd;
			exec($cmd);
		  	$return = 'Command Sent'; // send console command
			break;
			
		case 'u':
			// do this to catch if the server is running or not
				if($is_running) {
					$return = 'Stop server before an update';
					break;
				}
				chdir ($server['install_dir']);
				//$steamcmd =shell_exec('which steamcmd');
				//$cmds['cmd'] = $steamcmd.' +login anonymous +force_install_dir '.$server['install_dir'].'/'.$server['game'].' +app_update '.$server['server_id'].' +quit';
				exe($cmds);
				break;
				
		}
		
	return $return;	
}
		
function exe($cmds) {
	// run a command this array needs to be in a settings file
	
	$allowed = array('scanlog.php','cron_u.php','cron_r.php'.'check_ud.php','steamcmd','tmpreaper');
	foreach ($allowed as $find) {
       if (strpos($cmds['cmd'], $find) !== FALSE ) { 
        //echo $cmds['cmd']." Match found".cr; 
        $can_do = true;
		}
	}
	if(empty($can_do)) {
		return 61912;
	}
	
	if($can_do == true) {
	/* 
	 * Exit codes
	 * 0 = ran correctly
	 * 127 = file not found
	 * 139 = segmentation
	 */ 
	
		exec($cmds['cmd'],$output,$retval);
	
			if (isset($cmds['debug'])) {
				echo ' ready to do command '.$cmds['cmd'].cr;
	
				foreach ($output as $line) {
					$return .= $line.cr;
				}
				//$return ; //.= $retval.cr; // put the return value in the array
		}
		else {
		// test if no debug needs to return somthing on success
			foreach ($output as $line) {
				//$return .= $line.cr;
				if(strpos($line,'! App ')) {
					$return = $line.cr;
				}
				//$return ; //.= $retval.cr;
				}
	
	return $return;
}
 
		return false; // just in case anything slips through
	}
}

function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    } else if (is_object($mixed)) {
        $a = (array)$mixed; // from object to array
        return utf8ize($a);
    }
    return $mixed;
}

function check_services() {
	// run service check
	exec('/usr/sbin/service --status-all',$services,$retVal);
	//echo "return $retVal<br>";
	//print_r ($services);
	foreach ($services as $key=>$service) {
		
			if (strpos($service,' + ')) {
			$service = str_replace('[ + ]','',$service);
			$id = trim($service);
			$return[$id] = '✔ ';
		}
		elseif (strpos($service,' ? ')) {
			echo 'not sure'.cr;
		}
		else {
			$service = str_replace('[ - ]','',$service);
			$id = trim($service);
			$return[$id] = '✖';
		}
		//echo $key.' '.$service.cr;
	}
	//print_r($demo);
	return $return;
}
?>
