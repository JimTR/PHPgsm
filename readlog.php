<?php
/*
 * send log file name
 */
// header('Access-Control-Allow-Origin: *');
$file = $_GET['path'];
$result = array();
clearstatcache(true, $file);
$data['time']    = filemtime($file);
$data['content'] = $_GET['time'] < $data['time']
    ? getLastLines($file,$_GET['lines'])
    : false;

foreach ($data['content'] as $k => $v ) {
	$x = strpos($v,'" connected, address "');
	if ($x >0 ) {
		echo 'new  ';
		preg_match('/U:[0-9]:\d+/', $v, $t); // get steam id
		$id = trim($t[0]);
		file_put_contents('dbug.txt','new '.$id.PHP_EOL);
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
    //if (!$count) {continue;}
	$v = preg_replace('/"/','',$v);
	$v = preg_replace('/<[0-9]+>/', ' ', $v);
	//$v = preg_replace('/<[^0-9]+>/',' ',$v); //remove
	$v = trim($v);
	//$v = preg_replace('~^(\S+)\s+(\S+)$~', '<b>$1</b><i>$2</i>', $v);
	$tuni = strpos($v,'team Unassigned');
	$v = str_replace('Unassigned','',$v);
	$v = str_replace('#SDK_Team_','',$v);
	$v = str_replace(' say ',' <span style="color:magenta;"><b> say </b></span>',$v);
	$v = str_replace(' killed ',' <span style="color:red;"><b> killed </b></span>',$v);
	$v = str_replace(' Console ',' <span style="color:#328ba8;"><b> Console </b></span>',$v);
	$v = str_replace('committed suicide',' <span style="color:red;"><b> committed suicide </b></span>',$v);
	$v =str_replace('This command can only be used in-game.','<span style="color:red;">This command can only be used in-game.</span>',$v);
	$v = str_replace('Server logging enabled',' <span style="color:green;"><b>Server logging enabled</b></span>	',$v);
	$v = str_replace('Writing ','<span style="color:green"><b>Writing </b></span>',$v);
	//$v = str_replace('Writing cfg/banned_user.cfg.','<span style="color:red;"></span><b>Writing cfg/banned_user.cfg.</b></span>',$v);
	$v = str_replace('fof_cripplecreek','<span style="color:#0d1f54;"><b>fof_cripplecreek</b></span>',$v); 
	$v = str_replace('validated','<span style="color:green;"><b>validated</b></span>',$v); 
	//$v = str_replace('Console','',$v,2);
	if ($tuni >0 ) {
		$v = str_replace('team ','team Unassigned',$v);
	}
	$v = str_replace('"','',$v);
	$at = preg_replace('/-/','',$at);
	$at = strtotime($at);
	if  (date("d-m-Y H:i:s",$v) === '01-01-1970 01:00:00') {
		$at = date("d/m/Y - H:i:s");
		//continue;
		$v = $at.': '.$v;
		} 
	else {
		$j = preg_replace ('/(\d{2})/(\d{2})/(\d{4})/',"$3/$1/$2",$v);
		//echo '$j = '.$j;
	}
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
?>
