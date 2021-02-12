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
if (!defined('DOC_ROOT')) {
    	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
    }

 define('cr',PHP_EOL);
 define('plus','%2B');
 define('space','%20');  

require_once DOC_ROOT.'/includes/master.inc.php';
include  DOC_ROOT.'/functions.php';
require  DOC_ROOT.'/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
		$Query = new SourceQuery( ); 
$sql = 'select * from servers where running=1';
$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname,base_servers.ip as ipaddr, base_servers.base_ip,base_servers.password FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.running="1" order by servers.host_name';
$games = $database->get_results($sql);

foreach ($games as $game) {
		if (ping($game['host'], $game['port'], 1)) {
		$Query->Connect( $game['host'], $game['port'], 1, SQ_ENGINE );
		$info = $Query->GetInfo();
		$Query->Disconnect( );
		if ($info['Players'] == 0 ) {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$restart[] = $game;
		}

		elseif ($info['Bots'] == $info['Players']) {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$restart[] = $game;
		}
		else  {
			$game['restart'] = $game['url'].':'.$game['bport'].'/ajax.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=';
			$check[] = $game; 
		}
	}
	else { continue;}
}
	echo 'Restarting '.count($restart).'/'.count($games).' server(s)'.cr;
	foreach ($restart as $game) {
			echo file_get_contents($game['restart'].'q').cr; // stop server
			$steamcmd = shell_exec('which steamcmd');
			//chdir(dirname($steamcmd)); // move to install dir
			//print_r($game);
			$exe = DOC_ROOT.'/utils/scanlog.php '.$game['host_name'].' '.$game['location'].'/logs/console/'.$game['host_name'].'-console.log';
			$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exes&cmd='.$exe;
			echo 'will do '.$cmd.cr;
			// check updates
			// scan log
			sleep(1);
			echo file_get_contents($game['restart'].'s').cr; // start server
			}
	
	
	
	if (isset($check)) { 
		echo 'Defered '.count($check).'/'.count($games).' server(s)'.cr;
	foreach ($check as $restart) {
		echo  $restart['server_name'].cr;
	}
}

?>
