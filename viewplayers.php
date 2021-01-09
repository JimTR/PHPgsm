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
$results = $Query->GetPlayers( ) ;
$info = $Query->GetInfo();
$Query->Disconnect( );
 //header('Access-Control-Allow-Origin: *');
//46.32.237.232:27016
require ('includes/master.inc.php');
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
//require_once('GameQ/Autoloader.php');
require 'includes/Emoji.php';
$sql = 'select * from players where BINARY name="';
//$GameQ = new \GameQ\GameQ();
//echo "new gameq<br>";
//$GameQ->addServer($servers);
//echo "added server<br>";
//$results = $GameQ->process();
//echo "good to here<br>";
//print_r($info);
//$key = $servers['id'];
$disp .='<div style= "text-align:center;" ><span class="c_map">Current Map </span>: &nbsp;<span class="c_map_n">'.$info['Map'].'</span>&nbsp;&nbsp;<span class="pol"> Players Online</span>&nbsp;<span class="numplayers">'.$info['Players'].'</span>/<span class ="maxplayers">'.$info['MaxPlayers'].'</span> </div>';

if ($info['Players'] >0) {
					// we have players
					// add sub template
					$disp .= '<table style="width:100%;border-collapse: inherit;border-spacing: 0px .4em;"><tr class="country"><td style="width:40%;">Name</td><td style="width:30%;">Country</td><td style="width:10%;">Score</td><td>Time Online</td></tr>'; // start table
					$player_list = $results; // get the player array
					orderBy($player_list,'Frags');
					foreach ($player_list as $k=>$v) {
						//loop through player array
						//$playerN = substr($player_list[$k]['gq_name'],0,20); // chop to 20 chrs
						$playerN = $player_list[$k]['Name'];
						
						
						$playerN2 = Emoji::Encode($playerN);
						$playerN2 = $database->escape($playerN2);
						//echo $playerN2.'  ';
						$result = $database->get_results($sql.$playerN2.'"');
						
						//if (empty($result['name'])) {
							//$result = $database->get_results($sql.$playerN.'"');
						//}
						//$playerN = iconv("UTF-8", "ISO-8859-1//IGNORE", $playerN); //remove high asci
						$playerN = str_pad($playerN,25); //pad to 25 chrs
						switch (true) {
							// format score
							case  ($player_list[$k]['Frags']<0) :
								// minus
								$pscore = '&nbsp;&nbsp;&nbsp;'.$player_list[$k]['Frags']; //format score
								break;
							case  ($player_list[$k]['Frags']<10) :
								//
								$pscore = '&nbsp;&nbsp;&nbsp;&nbsp;'.$player_list[$k]['Frags']; //format score
								break;
								case  ($player_list[$k]['Frags']<100) :
								//
								$pscore = '&nbsp;&nbsp;'.$player_list[$k]['Frags']; //format score
								break;
							case  ($player_list[$k]['Frags']<1000)	:
								//
								$pscore = '&nbsp;&nbsp;'.$player_list[$k]['Frags']; //format score
								break;
							 default:
                                                                $pscore = $player_list[$k]['Frags'];
						}

						// format display here
						// add sub template
							if (!empty($result)) {
								$result= reset($result);
							    $result['ip']=long2ip ($result['ip']);
								if ($os==='win') {
									$map = '<img style="width:13%;" src="https://ipdata.co/flags/'.trim(strtolower($result['country_code'])).'.png">';
								}
								else {
									$map =  Emoji::Decode($result['flag']);
									$os ='Lin';	 
							}			
							
							//$map = Emoji::Decode($result['flag']); //get flag
							$disp .='<tr class="country"><td><i class="player_n" style="">'.$playerN.'</i></td><td>'.$map.' <span class="country">'.$result['country'].'</span></td><td>'.$pscore.'</td><td style="padding-left:1%;">'. $player_list[$k]['TimeF'].'</td></tr>';
							//print_r($result);
						}
						else {
									$disp .='<tr class="country"><td><i class="player_n" style="" >'.$playerN.'</i></td><td></td><td>'.$pscore.'</td><td style="padding-left:1%;">'. $player_list[$k]['TimeF'].'</td></tr>';
						}
						
						
					}
					// end of players for each
					$disp .='</table><br>';
				}

echo $disp;

		function orderBy(&$data, $field)
  {
    $code = "return strnatcmp(\$b['$field'], \$a['$field']);";
    usort($data, create_function('$a,$b', $code));
  }
?>
