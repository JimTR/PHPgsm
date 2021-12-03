<?php
/*
 * readlog.php
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
 *  Requires PHP > 7.4
 *
 * utility for console app
 */
 //header('Access-Control-Allow-Origin: *');
 
$version = "2.01";
$build = "5169-2686274782";
	require_once 'includes/master.inc.php';
 define ('cr', PHP_EOL);
 define ('br','<br/>');
 if(is_cli()) {
	 //die('running from command'.cr);
	 $file= $argv[1];
	 $id = $argv[2];
	 $_GET['lines'] = $argv[3];
	 $filename = $file.'/log/console/'.$id.'-console.log';
 }
 if(!isset($file)) {
$file = $_GET['path'];
$filename = $file.'/log/console/'.$_GET['id'].'-console.log';
}
$result = array();
clearstatcache(true, $filename);
$data['time']    = filemtime($file);
$data['content'] = $_GET['time'] < $data['time']
    ? getLastLines($filename,$_GET['lines'])
    : false;
print_r($data['content']);
foreach ($data['content'] as $k => $v ) {
	$x = strpos($v,'" connected, address "');
	if ($x >0 ) {
		//echo 'new  ';
		preg_match('/U:[0-9]:\d+/', $v, $t); // get steam id
		$id = trim($t[0]);
		file_put_contents('logs/dbug.txt','new '.$id.PHP_EOL);
	}
	$v = preg_replace('/<.*?>/', '', $v); //user number ?
	$v = preg_replace('@\(.*?\)@','',$v); // bracket content
	$v = preg_replace('/Console<0><Console><Console>/','Console',$v);
	$v = preg_replace('/<[U:1:[0-9]+]>/', ' ', $v);
	$v = preg_replace('/</',' ',$v);
	$v = preg_replace('/>/',' ',$v);
	//$v = preg_replace('/L .. /', '', $v);
	$date ='L '. date("m/d/Y");
    $pattern = ' /L (\w+)\/(\d+)\/(\d+)/i';  
    $replacement = '<span style="color:yellow;"><b>${2}/$1/$3</b></span>';  
    //display the result returned by preg_replace  
    $v = preg_replace($pattern, $replacement, $v,-1,$count);  
    $replacement = '<span style="color:yellow;"><b>${1}:$2:$3</b></span>';
    $pattern = '/(\d+):(\d+):(\d+)/';
    $v = preg_replace($pattern, $replacement, $v,-1,$count);
    //if (empty($count)) {continue;} // clears non dated rows
	$v = preg_replace('/"/','',$v);
	$v = preg_replace('/<[0-9]+>/', ' ', $v);
	$v = trim($v);
	// run replace line here 
	$tuni = strpos($v,'team Unassigned');
	$v = str_replace('Unassigned','',$v);
	$v = str_replace('#SDK_Team_','',$v);
	$v = str_replace(' say ',' <span style="color:magenta;"><b> say </b></span>',$v);
	$v = str_replace(' killed ',' <span style="color:red;"><b> killed </b></span>',$v);
	$v = str_replace(' Console ',' <span style="color:#328ba8;"><b> Console </b></span>',$v);
	$v = str_replace('committed suicide',' <span style="color:red;"><b> committed suicide </b></span>',$v);
	$v =str_replace('This command can only be used in-game.','<span style="color:red;">This command can only be used in-game.</span>',$v);
	$v = str_replace('Server logging enabled',' <span style="color:green;"><b>Server logging enabled</b></span>	',$v);
	$v = str_replace('disconnected (reason "Kicked from server")','<span style="color:#ffbf00;"><b>disconnected (reason "Kicked from server")</b></span>',$v);
	$v = str_replace('disconnected',' <span style="color:#ffbf00;"><b>dissconnected</b></span> ',$v);
	$v = str_replace('Writing ','<span style="color:green"><b>Writing </b></span>',$v);
	
	//$v = str_replace('Writing cfg/banned_user.cfg.','<span style="color:red;"></span><b>Writing cfg/banned_user.cfg.</b></span>',$v);
	$v = str_replace('fof_cripplecreek','<span style="color:#0d1f54;"><b>fof_cripplecreek</b></span>',$v); 
	$v = str_replace('validated','<span style="color:green;"><b>validated</b></span>',$v); 
	//$v = str_replace('Console','',$v,2);
	if ($tuni >0 ) {
		$v = str_replace('team ','team Unassigned',$v);
	}
	$v = str_replace('"','',$v);
	
	echo $v.PHP_EOL;
}
//print_r($data['content']);
function getLastLines($path, $totalLines) {
  $lines = array();

  $fp = fopen($path, 'r');
  fseek($fp, -1, SEEK_END);
  $pos = ftell($fp);
  $lastLine = "";

  // Loop backword until we have our lines or we reach the start
  while($pos > 0 && count($lines) < $totalLines) {

    $C = fgetc($fp);
    if($C == "\n") {
      // skip empty lines
      if(trim($lastLine) != "") {
        $lines[] = $lastLine;
      }
      $lastLine = '';
    } else {
      $lastLine = $C.$lastLine;
    }
    fseek($fp, $pos--);
  }

  $lines = array_reverse($lines);

  return $lines;
}
/*
 * 
 * name: replace_line
 * @param 
 * $data  = line to adjust
 * $game_id = game this log belongs to , allows different formats per game id
 * @return
 *  formatted line
 * 
 */
function replace_line($data,$game_id) {
	
	return $line;
}
?>
