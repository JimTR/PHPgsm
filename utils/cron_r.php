#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cron_r.php
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
 * restart servers at a given time
 *  
 */
 define('cr',PHP_EOL); 
require_once '../includes/master.inc.php';
include  '../functions.php';
require  '../xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
		$Query = new SourceQuery( ); 
$sql = 'select * from servers where running=1';
$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr, base_servers.base_ip,base_servers.password FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
$games = $database->get_results($sql);
//print_r($games);
foreach ($games as $game) {
		if (ping($game['host'], $game['port'], 1)) {
			//echo 'ok'.cr;
		
		$Query->Connect( $game['host'], $game['port'], 1, SQ_ENGINE );
		$info = $Query->GetInfo();
		$Query->Disconnect( );
		//echo print_r($info,true).cr;
		if ($info['Players'] == 0 ) {
			//echo $info['HostName']. ' can be restarted  '.cr;
			//echo 'use '.$game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']).cr;
			$game['restart']=  $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']);
			$restart[] = $game;
			 $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']);
			//echo file_get_contents($game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr'])).cr;
		}
		elseif ($info['Bots'] == $info['Players']) {
			//echo $info['HostName']. ' can be restarted [bots] '.cr;
			//echo 'use '.$game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']).cr;
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']);
			$restart[] = $game;
			//echo file_get_contents($game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr'])).cr;
		}
		else  {
			//echo $info['HostName']. ' can not be restarted ('.$info['Players'].' players online)'.cr;
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr']);
			$check[] = $game; 
		}
	}
	else { continue;}
}
	//print_r ($restart);
	echo 'Restarting '.count($restart).'/'.count($games).' server(s)'.cr;
	foreach ($restart as $game) {
		echo 'restarting '.$game['server_name'].' - ';
		//print_r($game);
		echo file_get_contents($game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&cmd=r&exe='.$game['host_name'].'&key='.md5($game['ipaddr'])).cr;
		sleep(1);
		//echo 'restarted '.cr;
	}
	
	
	//print_r($check);
	if (isset($check)) { 
		echo 'Defered '.count($check).'/'.count($games).' server(s)'.cr;
	foreach ($check as $restart) {
		echo  $restart['server_name'].cr;
	}
}

?>
