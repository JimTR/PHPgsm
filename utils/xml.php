<?php
/*
 * xmlv2.php
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
 * xml.php v2
 *  
 */
include '../includes/master.inc.php';
include '../functions.php';
define ('cr',PHP_EOL);
define ('version',2.01);
	$build = "11328-1544574938";
//run from cli
//error_reporting (0);
if(is_cli()) {
	Header('Content-type: text/xml');
	die(print_r($argv));	
	$type= $argv;
	$cmds =convert_to_argv($type,"",true);
	if (isset($cmds['debug'])) {
		error_reporting( -1 );
	}
	else {error_reporting( 0 );}
}
else{
	error_reporting( 0 );
	if (!empty($_POST)) {
		$cmds =convert_to_argv($_POST,"",true);
	}
	else {
		$cmds =convert_to_argv($_GET,"",true);
	}
}
// run from cli end
print_r($cmds);
if ($cmds['action'] == 'v') {
echo 'version hit';
echo version.cr;
exit;
}
Header('Content-type: text/xml');
if (empty($cmds['type'])) {$cmds['type']='all';} // just return everything
$xml = new SimpleXMLElement('<Servers/>'); // start xml
require '../xpaw/SourceQuery/bootstrap.php'; // load xpaw
use xPaw\SourceQuery\SourceQuery;
$gameq  = new SourceQuery( );
define( 'SQ_TIMEOUT',     1 );
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
//
$sql  = 'SELECT * FROM `base_servers` WHERE `enabled`= 1 and extraip = 0';
$bases = $database->get_results($sql);
foreach ($bases as $base) {
	$key =md5( ip2long($base['ip']));
	$address = $base['url'].':'.$base['port'];
       // echo "address = $address".cr;
	$tmp = geturl($address.'/ajaxv2.php?action=game_detail&data=true'.'&key='.$key);
	$game_detail = json_decode(stripslashes($tmp),true);
	$info[$base['fname']] = $game_detail;
	}
//print_r($info);
//die();
// replace ajax2
foreach ($info as $k => $test) {
	 foreach ($test as $k1 =>$game){
		if ($k1 == 'general' ) {
			continue;
		}
				
				if ($game['running']) {
				try
					{
					$gameq->Connect( $game['host'], $game['port'], SQ_TIMEOUT, SQ_ENGINE );
					$info1 = $gameq->GetInfo();
					}
				catch( Exception $e )
					{
						$Exception = $e;
						if (strpos($Exception,'Failed to read any data from socket')) {
							$Exception = 'Failed to read any data from socket Module (xmlv2)';
						}
						
						$error = date("d/m/Y h:i:sa").' ('.$game['host'].':'.$game['port'].') '.$Exception;
						  //sprintf("[%14.14s]",$str2)
						  $mask = "%17.17s %-30.30s \n";
						file_put_contents('logs/xpaw.log',$error.cr,FILE_APPEND);
					}
					$ptot[$k]['slots'] += $info1['MaxPlayers'];
					$info[$k][$k1]  = array_merge($info[$k][$k1] ,$info1);
					$info[$k][$k1]['online'] = 'Online';
					if ($info1['Players'] > 0) {
						$ptot[$k]['players'] += $info1['Players'];
						$info[$k][$k1] ['players']  = $gameq->GetPlayers( ) ;
					}
					$gameq->Disconnect( );
				}
				
			else {
					$info[$k][$k1]	['Bots'] =0;
					$info[$k][$k1]['Players'] = 0;
					$info[$k][$k1]['Secure'] = 0;
					$info[$k][$k1]['HostName'] = $info[$k][$k1]['server_name'];
					$info[$k][$k1]['online'] = 'Offline'; 
		}
		if ($info[$k][$k1]['buildid'] <> $info[$k][$k1]['rbuildid']) {
				$info[$k][$k1]['update'] = true;
		}
		else {
			$info[$k][$k1]['update'] = false;
		}
	}
}
// end replace

//echo print_r($info,true).cr;
//die();
if ($cmds['type'] == 'games' || $cmds['type'] == 'all') {
$xmlserver="game_server"; // set tag
foreach ($info as $k =>$game) {
	//print_r($game);
	
	foreach ($game as $k1=>$record) {
		//echo $k['address'].'=>'.$k1['address'].' ('.strlen($k1).')'.cr;
		if (strlen($k1)== 0) {
			$k1 = 'general';
			}
		//print_r($record);
		if($k1 <> 'general') {
			$now = new Datetime();
			$date = new DateTime();
			$date->setTimestamp(intval($record['starttime']));
			$interval = $now->diff($date);
			$record['uptime'] = $interval->format('%a days %h hours %I mins %S Seconds');
			if ($record['update'] === true){
				$update = 'Requires Update to version '.$data['rbuildid'];
				$updatei = 1;
			}  
			else {
				$update = 'Up To Date';
				$updatei = 0;
			}
			$track = $xml->addChild($xmlserver);
			$track->addChild('fname',$k);
			$track->addChild('host_name',$record['HostName']);
			$track->addChild('name',$record['host_name']);  	
			$track->addChild('uid',$record['uid']);
			$track->addChild('app_id',$record['app_id']);
			$track->addChild('game_port',$record['port']);
			$track->addChild('source_port',$record['source_port']);
			$track->addChild('client_port',$record['client_port']);
			$track->addChild('server_pass',$record['server_password']);
			$track->addChild('rcon_pass',$record['rcon_password']);
			$track->addChild('rt',$record['uptime'] );
			$track->addChild('secure',$record['Secure']);
			$track->addChild('logo',$record['logo']);
			$track->addChild('ip', $record['host'].':'.$record['port']);
			$track->addChild('location', $record['location']);
			$track->addChild('url', $record['url']);
			$track->addChild('engine',$record['type']);
			$track->addChild('enabled',$record['enabled']);
			$track->addChild('startcmd',$record['startcmd']);
			$track->addChild('starttime',date('g:ia \o\n l jS F Y \(T\)', floatval($record['starttime'])));
			$track->addChild('online',$record['online']);
			$track->addChild('defaultmap',$record['default_map']);
			$track->addChild('currentmap',$record['Map']);
			$track->addChild('joinlink','steam://connect/'.$record['host'].':'.$record['port'].'/'); //to do
			$track->addChild('players',$record['Players']);
			$track->addChild('maxplayers',$record['max_players']);
			$track->addChild('bots', $record['Bots']);
			$track->addChild('update_msg',date('l jS F Y \a\t g:ia',floatval($record['server_update'])));
			$track->addChild('uds',$updatei);
			$track->addChild('version',$record['buildid']);
			$track->addChild('cpu',$record['cpu']);
			$track->addChild('mem',trim($record['mem']));
			$track->addChild('size',trim($record['size']));
			$players = $track->addChild('current_players');
			$player_list = $record['players'];
			orderBy($player_list,'Frags','d');
			foreach ($player_list as $pz) {
				$players->addChild('pname', $pz['Name'].'|');
				$players->addChild('pscore', $pz['Frags'].'|');
				$players->addChild('ponline',$pz['TimeF'].'|');
  				}
  			
	}
}
		
		
}
		if ($cmds['type'] == 'games' ) {
			//
			print($xml->asXML());
			exit;
		}
}
//end games
//echo 'start base'.cr;
$xmlserver = "base_server";
foreach ($bases as $data) {
		//
		$key =md5( ip2long($data['ip']));
		$tmp = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=all&data=true&key='.$key);
		$sdata = json_decode($tmp,true);
		$fn = $data['fname'];
		//echo print_r($sdata,true).cr;
		$track = $xml->addChild($xmlserver);
		$track->addChild('name',$sdata['host']);
		$track->addChild('fname',$data['fname']);
		$track->addChild('distro',$sdata['os']);
		$track->addChild('ip', $sdata['ips']);
		$track->addChild('cpu_model', $sdata['model_name']);
		$track->addChild('cpu_processors', $sdata['processors']);
		$track->addChild('cpu_cores',$sdata['cpu_cores']);
		$track->addChild('cpu_speed',$sdata['cpu_MHz']);
		$track->addChild('cpu_cache',$sdata['cache_size']);
		$track->addChild('process',$sdata['process']);
		$track->addChild('reboot',$sdata['reboot']);
		$track->addChild('kernel',$sdata['k_ver']);
		$track->addChild('php',$sdata['php']);
		$track->addChild('screen',$sdata['screen']);
		$track->addChild('glibc',$sdata['glibc']); 
		$track->addChild('mysql',$sdata['mysql']);
		$track->addChild('apache',$sdata['apache']); 
		$track->addChild('curl',$sdata['curl']);
		$track->addChild('nginx',$sdata['nginx']);  
		$track->addChild('quota',$sdata['quotav']);
		$track->addChild('postfix',$sdata['postfix']);
		$track->addChild('uptime',$sdata['boot_time']); 
		$track->addChild('memTotal',trim($sdata['MemTotal']));  
		$track->addChild('memTotal_raw',trim($sdata['MemTotal_raw']));  
		$track->addChild('memfree',trim($sdata['MemFree']));
		$track->addChild('memfree_pc',number_format(($sdata['MemFree_raw'] / $sdata['MemTotal_raw'])*100,2));
		$track->addChild('memcache',trim($sdata['Cached']));
		$track->addChild('memcache_raw',trim($sdata['Cached_raw']));
		$track->addChild('memactive',trim($sdata['Active']));
		$track->addChild('memactive_raw',trim($sdata['Active_raw']));
		$track->addChild('swaptotal',trim($sdata['SwapTotal']));
		$track->addChild('swaptotal_raw',trim($sdata['SwapTotal_raw']));
		$track->addChild('swapfree',trim($sdata['SwapFree'])); 
		$track->addChild('swapfree_raw',trim($sdata['SwapFree_raw']));
		$track->addChild('swapfree_pc',number_format(($sdata['SwapFree_raw'] / $sdata['SwapTotal_raw'])*100,2));
		$track->addChild('swapcache',trim($sdata['SwapCached']));
		$track->addChild('swapcache_raw',trim($sdata['SwapCached_raw']));
		$track->addChild('boot_filesystem',$sdata['boot_filesystem']);
		$track->addChild('boot_mount',$sdata['boot_mount']);
		$track->addChild('boot_size',$sdata['boot_size']);
		$track->addChild('boot_used',$sdata['boot_used'] ." (".$sdata['boot_pc'] .")");
		$track->addChild('boot_free',$sdata['boot_free']);
		$track->addChild('load',$sdata['load']);
		$track->addChild('gamespace',$info[$fn]['general']['total_size']);
		$track->addChild('live_games',$info[$fn]['general']['live_servers']);
		$track->addChild('total_games',$info[$fn]['general']['total_servers']);
		$track->addChild('total_mem',$info[$fn]['general']['mem'].'%');
		$track->addChild('total_cpu',$info[$fn]['general']['cpu'].'%');
		$track->addChild('user_name',$sdata['name']);
		$track->addChild('quota_a',floatval($sdata['quota']));
		if (strpos($info[$fn]['general']['total_size'],'MB') >0) {
			$x = floatval($info[$fn]['general']['total_size'])/1000;
		}
		else {
			$x =  floatval($info[$fn]['general']['total_size']);
		}
		
		$track->addChild('quota_used',floatval($sdata['quota_used']));
		$track->addChild('beta',floatval($info[$fn]['general']['total_size'])/1000);
		$track->addChild('quota_pc',number_format( $x* (100/floatval($sdata['quota'])) ,2));
		$track->addChild('total_size_raw',$info[$fn]['general']['total_size_raw']);
		$track->addChild('quota_free',floatval($sdata['quota_free']));
		$track->addChild('quota_raw',floatval($sdata['quota_raw']));
		$track->addChild('total_players',intval($ptot[$fn]['players']));
		$track->addChild('total_slots',$ptot[$fn]['slots']);
		if ($ptot[$fn]['players']>0){
				$player_pc = number_format(((floatval($ptot[$fn]['players'])) / floatval($ptot[$fn]['slots']))*100,0);
		}
		
				
		$track->addChild('players_pc',$player_pc);
		if (isset($sdata['home_filesystem'])) {
			$track->addChild('home_filesystyem',$sdata['home_filesystem']);
			$track->addChild('home_mount',$sdata['home_mount']);
			$track->addChild('home_size',$sdata['home_size']);
		}
		
		//print_r($sdata);
}

print($xml->asXML());
?>
