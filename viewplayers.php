<?php
/*
 * viewplayers.php
 * 
 * Copyright 2020 Jim Richardson <jim@noideersoftware.co.uk>
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
 * simple file to retrieve & format player data 
 *  support code for console.php
 * remember js
 */
require ('includes/master.inc.php'); 
require 'includes/class.emoji.php';
require __DIR__ . '/xpaw/SourceQuery/bootstrap.php';
define('CR',PHP_EOL);
use xPaw\SourceQuery\SourceQuery;
$x = strpos($_GET['host'],':');
$sport = substr($_GET['host'],$x+1);
$ip = substr($_GET['host'],0,$x);
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
$Query = new SourceQuery( );
try 
	{
$Query->Connect( $ip, $sport, $settings['SQ_TIMEOUT'], SQ_ENGINE );
$results = $Query->GetPlayers( ) ;
$info = $Query->GetInfo();
$rules = $Query->GetRules( );
	}
	catch( Exception $e )
					{
						$Exception = $e;
						if (strpos($Exception,'Failed to read any data from socket')) {
							$Exception = 'Failed to read any data from socket (module viewplayers)';
						}
						
						  $error = date("d/m/Y h:i:sa").' ('.$ip.':'.$sport.') '.$Exception;
						  //sprintf("[%14.14s]",$str2)
						  $mask = "%17.17s %-30.30s \n";
						 file_put_contents('logs/xpaw.log',$error.CR,FILE_APPEND);
					}
$Query->Disconnect( );

if (isset($Exception)) {
	exit;
}

 //header('Access-Control-Allow-Origin: *');
//46.32.237.232:27016

$browser = get_browser(null, true);
 if (strpos($browser['browser_name_pattern'],'Windows')) {
	 $os ='win';
 } 
 //elseif (!isset($browser)) {
	 // no browsercap
// }
	 
if (empty($_GET['id'])) {
	redirect('/');
}


$sql = 'select * from players where BINARY name="';
$disp .='<div id="plist"  style= "text-align:center;font-size:14px;" class="title" ><span class="c_map">Current Map </span>: &nbsp;<span class="c_map_n">'.$info['Map'].'</span>&nbsp;&nbsp;<span class="pol"> Players Online</span>&nbsp;<span class="numplayers">'.$info['Players'].'</span>/<span class ="maxplayers">'.$info['MaxPlayers'].'</span> </div>';

if ($info['Players'] >0) {
					// we have players
					// add sub template
					// take care of players that have the same name as a bot
					$disp .= '<table style="width:100%;border-collapse: inherit;border-spacing: 0px .4em;padding:6px;"><tr class="country"><td style="width:40%;">Name</td><td style="width:30%;">Country</td><td style="width:10%;">Score</td><td>Time Online</td></tr>'; // start table
					$player_list = $results; // get the player array
					orderBy($player_list,'Frags','d');
					foreach ($player_list as $k=>$v) {
						//loop through player array
						$playerN = $player_list[$k]['Name'];
						$playerN2 = Emoji::Encode($playerN);
						$playerN2 = $database->escape($playerN2);
						//echo $playerN2.'  ';
						$result = $database->get_results($sql.$playerN2.'"');
						$playerN = str_pad($playerN,25); //pad to 25 chrs
						$pscore =  $player_list[$k]['Frags'];

						// format display here
						// add sub template
							if (!empty($result)) {
								$result= reset($result);
							    $result['ip']=long2ip ($result['ip']);
								if ($os==='win') {
									$map = '<img style="width:13%;" src="https://ipdata.co/flags/'.trim(strtolower($result['country_code'])).'.png">';
									echo json_decode('"' . $result['flag'] . '"');
								}
								else {
									$map =  Emoji::Decode($result['flag']);
									$os ='Lin';	 
							}			
							
							//$map = Emoji::Decode($result['flag']); //get flag
							$disp .='<tr class="country"><td><i class="player_n" style="">'.$playerN.'</i></td><td>'.$map.' <span class="country">'.$result['country'].'</span></td><td style="text-align:right;padding-right:5%;">'.$pscore.'</td><td style="text-align:right;padding-right:8%;">'. $player_list[$k]['TimeF'].'</td></tr>';
							//print_r($result);
						}
						else {
									$disp .='<tr class="country"><td><i class="player_n" style="" >'.$playerN.'</i></td><td></td><td style="text-align:right;padding-right:5%;">'.$pscore.'</td><td style="text-align:right;padding-right:8%;">'. $player_list[$k]['TimeF'].'</td></tr>';
						}
						
						
					}
					// end of players for each
					$disp .='</table><br>';
				}

echo $disp;

?>
