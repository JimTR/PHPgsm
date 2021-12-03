#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cron_s.php
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
 * restart servers if failed
 *  
 */
if (!defined('DOC_ROOT')) {
    	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
    }

 define('cr',PHP_EOL);
    $version = "2.05";
	$build = "2258-1522717829";
require_once DOC_ROOT.'/includes/master.inc.php';
include  DOC_ROOT.'/functions.php';
require  DOC_ROOT.'/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
define( 'LOG',DOC_ROOT.'/logs/cron.log'); 
$done = array();
$Query = new SourceQuery( ); 
$sql = "SELECT * FROM `server1` WHERE running=1 ORDER BY`host_name` ASC";
$games = $database->get_results($sql);
	foreach ($games as $game) {
		
			try{
				$Query->Connect( $game['host'], $game['port'], SQ_TIMEOUT, SQ_ENGINE ); // may need to up SQ_TIMEOUT if on a slow server 
				$info = $Query->GetInfo();
			}
			catch( Exception $e ){
				$restart[] = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&server='.$game['host_name'].'&key='.md5($game['host']).'&cmd=r';
			}
		$Query->Disconnect( );
		}
	
if(isset($restart)) {
	echo 'Restarting '.count($restart).' server(s)'.cr;
	foreach ($restart as $game) {
			//$cmd = $game['url'].':'.$game['bport'].'/ajaxv2.php?action=exescreen&cmd=r&debug=true';
			$result =geturl($game);
			if (!$result == 0) {
				echo $result.cr;
			}
			sleep(1);
		}

}
else {
	//echo 'all good'.cr;
}
?>
