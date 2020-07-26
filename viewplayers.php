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
 * 
 */


$servers = [
    
    'id'      => $_GET['id'],
    'type'    => $_GET['type'],
    'host'    => $_GET['host']
    
];
//46.32.237.232:27016
require ('includes/master.inc.php');
require_once('GameQ/Autoloader.php');
require 'includes/Emoji.php';
$sql = 'select * from players where name="';
$GameQ = new \GameQ\GameQ();
//echo "new gameq<br>";
$GameQ->addServer($servers);
//echo "added server<br>";
$results = $GameQ->process();
//echo "good to here<br>";
//print_r($results);
$key = $servers['id'];
$disp .='<div>Current Map : &nbsp;<span>'.$results[$key]['gq_mapname'].'</span>&nbsp;&nbsp; Players Online&nbsp;'.$results[$key]['gq_numplayers'].'/'.$results[$key]['gq_maxplayers'].' </div>';
$players = 	$results[$key]['gq_numplayers'];
if ($players >0) {
					// we have players
					// add sub template
					$disp .= '<table><tr><td style="width:60%;">Name</td><td style="width:20%;">Score</td><td>Time Online</td></tr>'; // start table
					$player_list = $results[$key]['players']; // get the player array
					orderBy($player_list,'gq_score');
					foreach ($player_list as $k=>$v) {
						//loop through player array
						//$playerN = substr($player_list[$k]['gq_name'],0,20); // chop to 20 chrs
						$playerN = $player_list[$k]['gq_name'];
						$result = $database->get_results($sql.$playerN.'"');
						//$playerN = iconv("UTF-8", "ISO-8859-1//IGNORE", $playerN); //remove high asci
						$playerN = str_pad($playerN,25); //pad to 25 chrs
						switch (true) {
							// format score
							case  ($player_list[$k]['gq_score']<0) :
								// minus
								$pscore = '&nbsp;&nbsp;&nbsp;'.$player_list[$k]['gq_score']; //format score
								break;
							case  ($player_list[$k]['gq_score']<10) :
								//
								$pscore = '&nbsp;&nbsp;&nbsp;&nbsp;'.$player_list[$k]['gq_score']; //format score
								break;
								case  ($player_list[$k]['gq_score']<100) :
								//
								$pscore = '&nbsp;&nbsp;'.$player_list[$k]['gq_score']; //format score
								break;
							case  ($player_list[$k]['gq_score']<1000)	:
								//
								$pscore = $player_list[$k]['gq_score']; //format score
								break;
						}
						// format display here
						// add sub template
							if (!empty($result)) {
										
							$result= reset($result);
							$result['ip']=long2ip ($result['ip']);
							//echo json_decode('"'.$result['flag'].'"');
							$result['flag'] = Emoji::Decode($result['flag']);
							//echo trim($result['flag']).'      '.$playerN.' - '.$pscore.' - '.gmdate("H:i:s", $player_list[$k]['gq_time']).PHP_EOL;
							$disp .='<tr><td><i style="color:green;">'.$result['flag'].'&nbsp'.$playerN.'</i></td><td>'.$pscore.'</td><td style="padding-left:1%;">'.gmdate("H:i:s", $player_list[$k]['gq_time']).'</td></tr>';
							//print_r($result);
						}
						else {
									$disp .='<tr><td><i style="color:green;">'.$playerN.'</i></td><td>'.$pscore.'</td><td style="padding-left:1%;">'.gmdate("H:i:s", $player_list[$k]['gq_time']).'</td></tr>';
						}
						//$disp .='<tr><td><i style="color:green;">'.$playerN.'</i></td><td>'.$pscore.'</td><td style="padding-left:1%;">'.gmdate("H:i:s", $player_list[$k]['gq_time']).'</td></tr>';
						
					}
					// end of players for each
					$disp .='</table><br>';
				}
//echo $players.'<br>';
echo $disp;

		function orderBy(&$data, $field)
  {
    $code = "return strnatcmp(\$b['$field'], \$a['$field']);";
    usort($data, create_function('$a,$b', $code));
  }
?>
