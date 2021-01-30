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
require __DIR__ . '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
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
	echo 'Ajax v2'.cr;
	print_r($cmds);
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
					
			case "game_detail" :
					echo json_encode(game_detail());
					if($cmds['debug'] =='true' ) {print_r(game_detail());}
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
	define( 'SQ_TIMEOUT',     1 );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	global $cmds; // get options 
	$db = new db();
	$mem =0;
	$cpu = 0;
	$r=1;
	if(isset($cmds['filter'])) {
		$ip = file_get_contents("http://ipecho.net/plain"); // get ip
		 if (empty($ip)) { $ip = shell_exec('curl http://ipecho.net/plain');} 
		 $sql = 'select servers.* , base_servers.port as bport, base_servers.base_ip, base_servers.url from servers left join base_servers on servers.host = base_servers.ip where servers.host_name = "'.$cmds['filter'].'"';
		 //echo $sql.'<br>';		 
		 $server_data = $db->get_results($sql);
		  $server_data=reset($server_data);
		  if (empty($server_data['base_ip'])) {         
                if ($ip <> trim($server_data['host'])) {
					echo 'wrong call guv !<br>';
					exit;
				}
			}
			              
                $cmd = 'ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg';
               
                $new = trim(shell_exec($cmd));
                // temp log
				$logline =date("d/m/Y h:i:sa").'looking at '.$new.cr;
				file_put_contents('ajax.log',$logline,FILE_APPEND);
                if (empty($new)) {
					$du = shell_exec('du -s '.$server_data['location']); // get size of game
					$du = str_replace('<br>','',$du);
					$x = strpos(trim($du),'/');
					$size = trim(substr($du,0,$x-1));
					$server_data['cpu'] = '0';
					$server_data['size'] = formatBytes(floatval($size)*1024,2);
					$server_data['mem'] = '0';
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
					$server_data['count'] =  count($temp);
					$server_data['mem'] = $temp[$server_data['count']-3];
					$server_data['cpu'] = $temp[$server_data['count']-4];
					$server_data['size'] = formatBytes(floatval($size)*1024,2);
					}
		$return[$server_data['host_name']] = $server_data;
		return $return;
	}
// no filter start
	else{
			$ip = file_get_contents("http://ipecho.net/plain"); // get ip
			if (empty($ip)) { $ip = shell_exec('curl http://ipecho.net/plain');}
				$checkip = substr($ip,0,strlen($ip)-1); 		
				$t =trim(shell_exec('ps -C srcds_linux -o pid,cmd |sed 1,1d')); // this gets running only 
				$tmp = explode(PHP_EOL,$t);
				$i=0;
				if(strlen($t) === 0) {
						// nothing running
						$sql =  'SET sql_mode = \'\'';
						$a= $db->query( 'SET sql_mode = \'\''); 
						$sql ='select  servers.location,count(*) as total from servers where servers.host like "'.$checkip.'%"';
						echo $sql;
						$server_count = reset($db->get_results($sql));
						$du = shell_exec('du -s '.dirname($server_count['location']));
						list ($tsize,$location) = explode(" ",$du);
				}
			else{
						// here we have the runners in $tmp array
						$sql = 'select servers.* , base_servers.port as bport, base_servers.base_ip, base_servers.url from servers left join base_servers on servers.host = base_servers.ip  where servers.host like "'.$checkip.'%" and servers.enabled=1'; // get them all
						$servers = $db->get_results($sql);
						$server_count = $db->num_rows($sql)+1;
						
						foreach ($servers as $server) {
																
										if (array_find($server['host_name'].'.cfg',$tmp) >= 0) {
												// running server add live data
												if ($game['running']) {
													try
														{
															$gameq->Connect( $server['host'], $server['port'], SQ_TIMEOUT, SQ_ENGINE );
															$info1 = $gameq->GetInfo();
														}
													catch( Exception $e )
														{
															$Exception = $e;
															if (strpos($Exception,'Failed to read any data from socket')) {
																$Exception = 'Failed to read any data from socket Module (Ajax - Game Detail)';
														}
						
														$error = date("d/m/Y h:i:sa").' ('.$sever['host'].':'.$server['port'].') '.$Exception;
														//sprintf("[%14.14s]",$str2)
														$mask = "%17.17s %-30.30s \n";
														file_put_contents('logs/xpaw.log',$error.cr,FILE_APPEND);
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
													$return[$server['host_name']] = $server;
													$i++;
										}
										else {
												$du = trim(shell_exec('du -s '.$server['location'])); // get size of game
												$size = str_replace($server['location'],'',$du);
												$server['mem'] = 0;
												$server['cpu'] = 0;
												$server['size'] = formatBytes(floatval($size)*1024,2);
												$return[$server['host_name']] = $server;
										} 
						}	
						$du = shell_exec('du -s '.dirname($server['location']));
						$tsize = str_replace(dirname($server['location']),'',$du);
			}
	// add computed items 
				$return['general']['live_servers'] = $i;
				$return['general']['total_servers'] = $server_count;
				$return['general']['mem'] = round($mem,2,PHP_ROUND_HALF_UP);
				$return['general']['cpu'] = round($cpu,2,PHP_ROUND_HALF_UP);
				$return['general']['total_size'] = formatBytes(floatval($tsize)*1024,2);
				$return['general']['total_size_raw'] = floatval($tsize);
				$logline =date("d/m/Y h:i:sa").' looking at '.print_r($return['dabserver'],true).PHP_EOL;
				return $return;
		}
}			
?>
