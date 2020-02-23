<?php
$servers = [
    
    'id'      => $_GET['id'],
    'type'    => $_GET['type'],
    'host'    => $_GET['host']
    
];
//46.32.237.232:27016
require_once('GameQ/Autoloader.php');
$GameQ = new \GameQ\GameQ();
//echo "new gameq<br>";
$GameQ->addServer($servers);
//echo "added server<br>";
$results = $GameQ->process();
//echo "good to here<br>";
//print_r($results);
$key = $servers['id'];
$disp .='<div>Current Map : &nbsp;<span>'.$results[$key]["gq_mapname"].'</span>&nbsp;&nbsp; Players Online&nbsp;'.$results[$key]['gq_numplayers'].'/'.$results[$key]['gq_maxplayers'].' </div>';
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
						$disp .='<tr><td><i style="color:green;">'.$playerN.'</i></td><td>'.$pscore.'</td><td style="padding-left:1%;">'.gmdate("H:i:s", $player_list[$k]['gq_time']).'</td></tr>';
						
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
