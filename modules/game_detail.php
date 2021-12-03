<?php
if (!defined("IN_PHPGSM")) die( "phpgsm didn't include me :-(");
 $build = "9790-1448902081";
 $version = "1.00";
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	//define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	//define( 'SQ_ENGINE',      SourceQuery::SOURCE );
//else die( "Unknown file included me");
//print_r($_SERVER);
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
		$ip = geturl('https://api.ipify.org');// get ip
		 if (empty($ip)) { $ip = geturl('http://ipecho.net/plain');} 
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
					//print_r($tmp);
					$pid = $tmp[0];
					$count = count($tmp);
					$temp =  trim(file_get_contents($server_data['url'].':'.$server_data['bport'].'/ajaxv2.php?action=top&filter='.$pid)); // works on remote ?
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
					//$ip = geturl('https://api.ipify.org');
					if (empty($ip)) { $ip = geturl('http://ipecho.net/plain');}
				}
				//$checkip = substr($ip,0,strlen($ip)-1);
				if(empty($ip)) {
					$ip= trim(shell_exec("hostname -I | awk '{print $1}'"));
				}
				$checkip = $ip; 
				//if($cmds['debug']='true') {echo "checkip=$ip";}
				// alter this bit	
				// use screen to test 	
				exec('ps -C screen -o pid,cmd |sed 1,1d',$tmp,$val); // this gets running only needs rework may 2021
				//$tmp = explode(PHP_EOL,$t);
				$i=0;
				
				if(empty($tmp)) {
						// nothing running
						
						//$sql =  'SET sql_mode = \'\'';
						//$a= $db->query( 'SET sql_mode = \'\''); 
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
						//$sql = "SELECT DISTINCT `host_name`,`server_name`,`url`,`bport`,`location`,`host`,`port`,`running` FROM server1 where `running` = 1 order by `host_name`";
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
