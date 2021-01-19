<?php
/*
 * rules.php
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
 * fetch rules  
 */
//header('Access-Control-Allow-Origin: *');
 define('cr','<br>');
require __DIR__ . '/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery;
$x = strpos($_GET['host'],':');
$sport = substr($_GET['host'],$x+1);
$ip = substr($_GET['host'],0,$x);
	define( 'SQ_SERVER_ADDR', $ip );
	define( 'SQ_SERVER_PORT', $sport );
	define( 'SQ_TIMEOUT',     1 );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
$Query = new SourceQuery( );
$Query->Connect( SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE );
$rules = $Query->GetRules( );
$Query->Disconnect( );
//print_r($rules);
//echo '';
foreach ($rules as $k=>$v) {
	echo '<tr><td style="word-wrap:break-word;">'.$k.'</td><td style="text-align:left;padding-left:3%;">'.$v.'</td></tr>';
}
//echo '</table>';
?>
