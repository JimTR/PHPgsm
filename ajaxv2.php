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
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	$build = "44658-2257669275";
	$version = 2.07;
error_reporting (0);
$update_done= array();
$ip = $_SERVER['SERVER_ADDR']; // get calling IP
$sql = 'select * from base_servers where base_servers.ip ="'.$_SERVER['REMOTE_ADDR'].'"'; // do we know this ip ? mybb sets this at login
//echo $sql.'<br>';
$valid = $database->num_rows($sql); // get result if the ip can use the data the return value >0
//echo $ip." Valid = $valid <br>".cr;
if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	$logline  = date("d-m-Y H:i:s").' localhost accessed ajax with '.print_r($cmds,true).cr;
	//file_put_contents(LOG,$logline,FILE_APPEND);
	if ($cmds['debug'] == 'true') {
		error_reporting( -1 );
		echo 'Ajax v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	    foreach ($cmds as $k => $v) {
			if ($k == 'debug'){continue;}
			print "[$k] => $v".cr;
		}
		if (empty($cmds['action'])) {exit;}
	}
	else {error_reporting( 0 );}
	
}
else {
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
			echo get_boot_time().cr;
			exit;
			
		case "check_services" :
		if ($cmds['debug'] == true){
			print_r(check_services($cmds));
		}
		else {
			echo json_encode(check_services($cmds));
			
		}
			exit;
			
		case "console":
					$console_data['log'] = readlog($cmds);
					$console_data = array_merge($console_data,viewserver($cmds));
					if ($cmds['debug'] == true){
						print_r($console_data);
					}
					else {
						echo json_encode($console_data);
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
				
		case "readlog":
			if($cmds['debug'] =='true' ) {
				echo print_r(readlog($cmds),true).cr;
			}
			else {
				echo json_encode(readlog($cmds));
			}
				exit;
				
		case "scanlog":
				if (isset($cmds['email'])) {
					echo 'email set'.cr;
				}	
					echo scanlog($cmds);
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
				echo 'Ajax v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
				exit;
						
			case "viewserver":
					if($cmds['debug'] =='true' ) {
						echo print_r(viewserver($cmds),true).cr;
						}
					else {
							echo json_encode(viewserver($cmds));
						}
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
		 //echo "sql = $sql".cr;
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
				        
       switch ($server_data['binary_file']) {
		case 'srcds_run':
			$cmd = 'ps -C '.$server_data['binary_file'].' -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg';
			break;
		default:
				$cmd = 'ps -C '.$server_data['binary_file'].' -o pid,%cpu,%mem,cmd |grep '.$server_data['binary_file'];
				//echo "cmd = $cmd".cr;
			break;
		}
               
                $new = trim(shell_exec($cmd));
                // temp log
				$logline =date("d/m/Y h:i:sa").' => Filtered Output '.$cmds['filter'].cr;
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
					$temp =  trim(file_get_contents($server_data['url'].':'.$server_data['bport'].'/ajax.php?action=top&filter='.$pid.'&key='.md5( ip2long($ip)))); // works on remote ?
					$temp = array_values(array_filter(explode(' ',$temp)));
					$du = shell_exec('du -s '.$server_data['location']); // get size of game
					$x = strpos(trim($du),'/');
					$size = trim(substr($du,0,$x-1));
					//$server_data['count'] =  count($temp);
					$server_data['mem'] = count ($temp);
					$server_data['cpu'] = $temp[count($temp)-4];
					$server_data['size'] = formatBytes(floatval($size)*1024,2);
					$server_data['beta'] = 'running';
					echo print_r($server_data,true).cr;
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
						
														$error = date("d/m/Y h:i:sa").' ('.$server_data['host'].':'.$server_data['port'].') '.$Exception;
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
				// alter this bit	
				// use screen to test 	
				exec('ps -C screen -o pid,cmd |sed 1,1d',$tmp,$val); // this gets running only needs rework may 2021
				//$tmp = explode(PHP_EOL,$t);
				$i=0;
				
				if(empty($tmp)) {
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
															
										if (array_find($server['host_name'].'-console.log',$tmp) >= 0) {
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
												
												$pid = get_pid($server['host'].':'.$server['port']); 
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
	$sql = 'select * from server1 where host_name = "'.trim($exe).'"';
	$server = $database->get_row($sql); // pull results
	if (empty($server['host'])) {
		$return = 'invalid server'; // don't know this server
	   	return $return;
	}
	if ($server['host'] <> $localIP) {
		return 'This Server is not hosted here '.$localip.'/'.$server['host']; // we know this one but it's elsewhere
	}
	// valid so do it
	switch ($server['binary_file']) {
		case 'srcds_run':
			$cmd = 'ps -C '.$server['binary_file'].' -o pid,%cpu,%mem,cmd |grep '.$exe.'.cfg';
			break;
		default:
				$cmd = 'ps -C '.$server['binary_file'].' -o pid,%cpu,%mem,cmd |grep '.$server['binary_file'];
			break;
		}
		//echo $cmd.cr;
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
			switch ($server['binary_file']) {
		case 'srcds_run':
			$cmd = 'screen -X -S '.$server['host_name'] .' quit';
			break;
		default:
				$cmd = 'screen -XS '.$server['host_name'] .' quit';
			break;
		}
			
			exec($cmd);
			$return = 'Stopping Server '.$server['host_name'];
			$update['running'] = 0;
			$update['starttime'] = '';
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			break;
			
		case 'r':
			if (!$is_running) {
				$return = $exe.' is not running, starting instead';
				// should we just start or quit ??
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
				//$return = 'Starting Server '.$server['host_name'];
				break;
			}
			
			$cmd = 'screen -XS '.$server['host_name'] .' quit';
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
				//echo ' ready to do command '.$cmds['cmd'].cr;
	
				foreach ($output as $line) {
					$return .= $line.cr;
				}
				return $return;
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

function check_services($cmds) {
	// run service check
	
	exec('/usr/sbin/service --status-all 2>/dev/null',$services,$retVal);
	//echo "return $retVal<br>";
	//print_r ($services);
	foreach ($services as $key=>$service) {
		
			if (strpos($service,' + ')) {
				$service = str_replace('[ + ]','',$service);
				$id = trim($service);
				if(isset($cmds['running']) and $cmds['running'] == 'true'){
					$return[$id] = '✔ ';
				}
				elseif (!isset($cmds['running'])) {
							$return[$id] = '✔ ';
						}
			
		}
		elseif (strpos($service,'?')) {
			echo 'not sure'.cr;
		}
		else {
			$service = str_replace('[ - ]','',$service);
			$id = trim($service);
			if(isset($cmds['running']) and $cmds['running'] == 'false'){
					$return[$id] = '✖';
			}
			elseif (!isset($cmds['running'])) {
				$return[$id] = '✖';
			}
		}
		//echo $key.' '.$service.cr;
	}
	//print_r($demo);
	return $return;
}

function viewserver($cmds) {
	// replace php file viewplayers.php
		$database = new db();
		$Query = new SourceQuery( );
		$emoji = new Emoji;
		$sql = 'select * from server1 where host_name like "'.$cmds['id'].'"';
		$server =$database->get_row($sql);
		
try 
	{
$Query->Connect( $server['host'], $server['port'], SQ_TIMEOUT, SQ_ENGINE );
$info = $Query->GetInfo();
$players = $Query->GetPlayers( ) ;
$rules = $Query->GetRules( );
	}
	catch( Exception $e )
					{
						$Exception = $e;
						if (strpos($Exception,'Failed to read any data from socket')) {
							$Exception = 'Failed to read any data from socket (Function viewplayers)';
						}
						
						  $error = date("d/m/Y h:i:sa").' ('.$cmds['ip'].':'.$cmds['port'].') '.$Exception;
						  //sprintf("[%14.14s]",$str2)
						  $mask = "%17.17s %-30.30s \n";
						 file_put_contents('logs/xpaw.log',$error.CR,FILE_APPEND);
					}
$Query->Disconnect( );
//we now have data
jump:
$sql = 'select * from players where BINARY name="';
if (count($players)) {
	// we have players
	orderBy($players,'Frags','d'); // score order
	foreach ($players as $k=>$v) {
		//loop  add flag & country $v being the player array 
		// don't update player here let scanlog do it
		$players[$k]['Name'] =$emoji->Encode($v['Name']);
		$player_data = $database->get_results($sql.$database->escape($players[$k]['Name']).'"');
		if (!empty($player_data)) {
			// here we go
			//echo 'Result '.print_r($player_data,true).cr;
			$player_data= reset($player_data);
			$players[$k]['Name'] = Emoji::Decode($players[$k]['Name']);
			$players[$k]['flag'] = 'src ="https://ipdata.co/flags/'.trim(strtolower($player_data['country_code'])).'.png"'; // windows don't do emoji flags use image 
			$players[$k]['country'] = $player_data['country'];
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
}
$return['info'] = $info;
$return['info']['real_players'] = $info['Players']-$info['Bots'];
$return['players'] = $players;
$return['rules'] = $rules;
return $return;
}

function readlog($cmds) {
	if (!isset($cmds['rows'])) {
		$cmds['rows']= 10;
	}
	//convert readlog to ajax function
	$ip = file_get_contents('https://api.ipify.org');// get ip
	if (empty($ip)) { $ip = file_get_contents('http://ipecho.net/plain');} 
	$database = new db();
	$sql = 'select * from server1 where host_name like "'.$cmds['id'].'"';
	$server =$database->get_row($sql);
	//echo $server['location'].cr;
	$filename = $server['location'].'/log/console/'.$cmds['id'].'-console.log';
	if ($ip <> $server['host']) {
		$url = $server['url'].':'.$server['bport'].'/ajaxv2.php?action=get_file&file='.$filename;
		if (isset($cmds['debug'])) {echo '[url] => '.$url.cr;}
		$log_contents = file_get_contents($url);
	}
	else {
		$log_contents = file_get_contents($filename);
	}
	$log_contents = array_reverse(explode(cr,trim($log_contents)));
	//print_r($log_contents);
	foreach($log_contents as $k=>$v) {
		if ($k == $cmds['rows']) {break;}
		$v = preg_replace('/<.*?>/', '', $v); //user number ?
		$v = preg_replace('@\(.*?\)@','',$v); // bracket content
		$v = preg_replace('/Console<0><Console><Console>/','Console',$v);
		$v = preg_replace('/<[U:1:[0-9]+]>/', ' ', $v);
		$v = preg_replace('/</',' ',$v);
		$v = preg_replace('/>/',' ',$v);
		$date ='L '. date("m/d/Y");
		
		if (is_cli()) {
			$pattern = ' /L (\w+)\/(\d+)\/(\d+)/i'; 
			$replacement = '${2}/$1/$3';  
			$v = preg_replace($pattern, $replacement, $v,-1,$count);  
			$replacement = '${1}:$2:$3';
			$pattern = '/(\d+):(\d+):(\d+)/';
			$v = preg_replace($pattern, $replacement, $v,-1,$count);
		}
		else { 
			// this code block needs to read a replacement array for str_replace rather than hard coding them !
			$pattern = ' /L (\w+)\/(\d+)\/(\d+)/i'; 
			$replacement = '<span style="color:yellow;"><b>${2}/$1/$3</b></span>';  
			//display the result returned by preg_replace  
			$v = preg_replace($pattern, $replacement, $v,-1,$count);  
			$replacement = '<span style="color:yellow;"><b>${1}:$2:$3</b></span>';
			$pattern = '/(\d+):(\d+):(\d+)/';
			$v = preg_replace($pattern, $replacement, $v,-1,$count);
			$v = str_replace(' say ',' <span style="color:magenta;"><b> say </b></span>',$v);
			$v = str_replace(' killed ',' <span style="color:red;"><b> killed </b></span>',$v);
			$v = str_replace(' Console ',' <span style="color:#328ba8;"><b> Console </b></span>',$v);
			$v = str_replace('committed suicide',' <span style="color:red;"><b> committed suicide </b></span>',$v);
			$v = str_replace('This command can only be used in-game.','<span style="color:red;">This command can only be used in-game.</span>',$v);
			$v = str_replace('Server logging enabled',' <span style="color:green;"><b>Server logging enabled</b></span>	',$v);
			$v = str_replace('disconnected (reason "Kicked from server")','<span style="color:#ffbf00;"><b>disconnected (reason "Kicked from server")</b></span>',$v);
			$v = str_replace('disconnected',' <span style="color:#ffbf00;"><b>dissconnected</b></span> ',$v);
			$v = str_replace('Writing ','<span style="color:green"><b>Writing </b></span>',$v);
			$v = str_replace('Unknown command','<span style="color:red"><b>Unknown command </b></span>',$v);
			$v = str_replace('"','',$v); // knock out "
		}
		$return[] = $v;
	}
	return array_reverse($return);	// send the data back in the right order !
}

function scanlog($cmds) {
	// scanlog as a function
	error_reporting(E_ALL); // set errors on for dev
	global $settings,$database,$update_done;
	$ip = file_get_contents('https://api.ipify.org');// get ip
	if (empty($ip)) { $ip = file_get_contents('http://ipecho.net/plain');}
	$localip = "ip = $ip";
	 
	if (empty( $settings['ip_key'] )) {
		echo 'Fatal Error - api key missing'.cr;
		exit(7);
	} 
	else {
		$key = $settings['ip_key'] ;
	}
	if(empty($cmds['server'])) {
		return 'function not ran correctly';
	}
	$asql = 'select * from players where steam_id64="'; // sql stub for user updates
	
	$display='';
	if ($cmds['server'] == 'all') {
	
		$allsql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
		$game_results = $database->get_results($allsql);
		$display='';
	
	foreach ($game_results as $run) {
		//bulid path done this way so we can get the file back from a remote server
		$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log'; //used for screen log
		//$path = $run['url'].':'.$run['bport'].'/ajaxv2.php?action=lsof&filter='.$run['host_name'].'&loc='.$run['location'].'/'.$run['game'].'&return=content'; //used for steam log
		$tmp = file_get_contents($path);
		//echo $run['host_name'].' '.$path.cr; // debug code
				
		if (!empty($tmp)) {
			$tmp = array_reverse(explode(cr,trim($tmp)));
			$current_records = count($tmp) ;
			
				if (file_exists($run['host_name'].'-md5.log')) {
					$logold = explode(cr,trim(file_get_contents($run['host_name'].'-md5.log')));
					$lastrecord = intval(trim($logold[1]));
				
				if ($current_records == $lastrecord) {
				// this allows for up or down movement rather than >
					if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
						echo 'getting '.$run['host_name'].'-md5.log - ';
						echo "current records = $current_records no change since last run".cr;
					}
					if(!isset($cmds['fullscan'])) {
						continue;
					}
				}
			
			if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
				echo print_r($logold,true).cr;
				//echo ' Maths = '.intval($current_records)-intval($lastrecord).cr;
				//echo 'file changed records => '.intval($current_records)-$lastrecord.'/'.$current_records.cr;
			}
			
		unset($return);
		$logpos = md5($tmp[0]); // got log pos
		file_put_contents($run['host_name'].'-md5.log',$logpos.cr.count($tmp));
		file_put_contents('/tmp/'.$run['host_name'].'-md5.log',$logpos.cr.count($tmp));
		foreach ($tmp as $logline){
			if(isset($logold[0])){
				if (md5(trim($logline)) == $logold[0] && !isset($cmds['fullscan'])) {
					//echo 'found line '.$logline.cr;
					break;
				}
			}
			$return[] = $logline; 
		}
		if (!empty($return)) {
			//run scan function
			$display .= do_log($run['host_name'],$return);
			if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
				echo print_r(array_reverse($return),true).cr;
				echo "display = $display".cr;
			}
			unset ($return);
		}
			
	}
	if (!file_exists($run['host_name'].'-md5.log')) {
		//create files
		$logpos = md5($tmp[0]); // got log pos
		//file_put_contents($run['host_name'].'-md5.log',$logpos.cr.count($tmp));
		file_put_contents('/tmp/'.$run['host_name'].'-md5.log',$logpos.cr.count($tmp));
	}
	}
	else {
	 // echo $run['host_name'].' the file is empty'.cr; //debug code 
  }
}
}
else {
	// do default or supplied file

	$allsql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.host_name="'.$cmds['server'].'"';
		//echo $allsql.cr;
	$run = $database->get_row($allsql);
	if(empty($run)) {
		echo 'Invalid server id '.$cmds['server'].' correct & try again'.cr;
		exit(2);
	}
	if ($ip == $run['host']) {
		echo ' this is local '.cr; 
		$local = true;
			if(isset($cmds['file'])) {
				if (!file_exists($cmds['file'])) {
					return 'could not open '.$cmds['file'].cr;
					exit (1);
				}
			}
	}
	else {
		if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
			echo 'remote set'.cr;
		}
		$local = false;
	}
		
	if ($local === true) {
		if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
			echo 'use local file system'.cr;
		}
		if (empty($cmds['file'])) { 
		$path = $run['location'].'/log/console/'.$run['host_name'].'-console.log';
		}
		else {
		//next check
		$path = $cmds['file'];
		}
	}
	else {
		// assume run remote
		if(empty($cmds['file'])) {
			if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
				echo 'no file & server remote'.cr;
			}
			$path = $run['url'].':'.$run['bport'].'/ajax.php?action=get_file&file='.$run['location'].'/log/console/'.$run['host_name'].'-console.log';
		}
		else {
			if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
				echo 'file & server remote'.cr;
			}
			$path = $run['url'].':'.$run['bport'].'/ajax.php?action=get_file&file='.$cmds['file'];
		}
	}
		
		
		$tmp = file_get_contents($path);
		$tmp = array_reverse(explode(cr,trim($tmp)));
		$current_records = count($tmp) ;
				
		if(!empty($tmp)) {
			if (isset($cmds['debug']) && $cmds['debug'] == 'true') {
				echo "current records = $current_records".cr;
				echo print_r(array_reverse($tmp),true).cr;
			}
			$display .= do_log($run['host_name'],$tmp);
		}
		
	}
	
	return $display; //.' - done'.cr;
}

function get_ip_detail($ip) {
	// return api data
	global $settings; // get settings
	$key = $settings['ip_key'];  // this has been checked via the calling function and should not be empty
	$cmd =  'https://api.ipdata.co/'.$ip.'?api-key='.$key;
	 $ip_data = json_decode(file_get_contents($cmd), true); //get the result
	 if (empty($ip_data['threat']['is_threat'])) {$ip_data['threat']['is_threat']=0;}
	 return $ip_data;
}

function do_log($server,$data) {
	// cron code
	
	$count = 0;
	$done= 0;
	$update_users = 0;
	$uds = false;
	global $database, $settings;
	$key = $settings['ip_key'];
	$update_req = 'Your server needs to be restarted in order to receive the latest update.';
	$asql = 'select * from players where steam_id64="'; // sql stub for user updates
	$rt = 'Processing server '.$server.cr.cr;
	$log = $data;
    // echo 'Rows to process '.count($log).cr; //debug code
    foreach ($log as $value) {
		// loop lines, in here check for server needs a restart
		if ( strpos($value,$update_req) !==false) {
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
		//echo 'processing '.$username.' '.$ip.' '.$id2.cr; //debug code
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
	//echo 'Rows found '.$pc.cr; //debug code
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
	//echo $asql.$user_search.cr; //debug code
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
			$ut.= ' new logon at '.$user_data['time'].' (total '.$result['log_ons'].')';
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
			$database->query($sql); //needed
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
		$last_logon = strtotime($user_data['time']);
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
		 echo print_r($result,true).cr;		 
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
update:
if ($uds == true) {
	$rt .= cr.'Warning '.$server.' needs updating & restarting'.cr;
	$rt .= update_server($server);
}
$rt .= cr.'Processed '.$server.cr;
//echo $rt;
return $rt;
}
}

function update_server($server){
	// if found stop the server and update
	//Your server needs to be restarted in order to receive the latest update.
	global $database, $update_done,$version,$settings;
	$s = 'Server Update via Scanlog '.$version.cr;
	$sql = 'select * from server1 where host_name="'.$server.'"';
	$steamcmd = trim(shell_exec('which steamcmd'));
	if(empty($steamcmd)) {
		// no steamcmd in the path oops
		$steamcmd = '/usr/games/steamcmd';
	}
	$game = $database->get_row($sql);
	$stub =  $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&cmd='; // used to start & stop
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
	echo 'updated server using '.$cmd.cr;
	//$cmd = $stub.'s';
	//$s .= file_get_contents($cmd).cr;
	//need to restart all that stem from this install dir if running
	$sql = "SELECT * FROM `server1` WHERE `game` like '".$game['game']."' and `install_dir` like '".$game['install_dir']."' and `running` = 1";
		$restarts = $database->get_results($sql);
		foreach ($restarts as $restart) {
			// restart them all
			$cmd =  $game['url'].':'.$restart['bport'].'/ajaxv2.php?action=exescreen&server='.$restart['host_name'].'&cmd=r'; // used to restart
			$s .= file_get_contents($cmd).cr;
		}
		
	$update_done[] = $game['install_dir'];
	return $s;
}

function get_pid($task) {
	// return pid
	global $cmds;
	if ($cmds['debug'] == true ){
		echo "task = $task".cr;
	}
	exec ('ss -plt |grep '.$task,$detail,$ret);
	$a = explode('  ',$detail[0]);
	$b = explode(',',trim(end($a)));
	preg_match('!\d+!', $b[1], $matches);
	if ($cmds['debug'] == true) {
		//echo print_r($a,true).cr;
		//echo 'used ss -plt |grep '.$task.cr;
		echo $matches[0].cr;
	}
	return $matches[0];
}
?>
