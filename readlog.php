<?php
/*
 * send log file name
 */
 
$file = $_GET['path'];
$result = array();
clearstatcache(true, $file);
$data['time']    = filemtime($file);
$data['content'] = $_GET['time'] < $data['time']
    ? getLastLines($file,$_GET['lines'])
    : false;

foreach ($data['content'] as $k => $v ) {
	$v = preg_replace('/Console<0><Console><Console>/','Console',$v);
	//$v = preg_replace('/L .. /', '', $v);
	$date ='L '. date("m/d/Y");
    $pattern = ' /L (\w+)\/(\d+)\/(\d+)/i';  
    $replacement = '<span style="color:blue;">${2}/$1/$3\</span>';  

    //display the result returned by preg_replace  
    $v = preg_replace($pattern, $replacement, $v);  

	$v = preg_replace('/"/','',$v);
	$v = preg_replace('/<[0-9]+>/', ' ', $v);
	$v = preg_replace('/<[U:1:[0-9]+]>/', ' ', $v);
	//$v = preg_replace('/<[^0-9]+>/',' ',$v);
	//$v = preg_replace('/</',' ',$v);
	//$v = preg_replace('/>/',' ',$v);
	$at = substr($v,0,22);
	//$v = preg_replace('/^'.$at.'+:/','',$v);
	$v = trim($v);
	//$v = preg_replace('~^(\S+)\s+(\S+)$~', '<b>$1</b><i>$2</i>', $v);
	$tuni = strpos($v,'team Unassigned');
	$v = str_replace('Unassigned','',$v);
	$v = str_replace('#SDK_Team_','',$v);
	//$v = str_replace('Console','',$v,2);
	if ($tuni >0 ) {
		$v = str_replace('team ','team Unassigned',$v);
	}
	$v = str_replace('"','',$v);
	$at = preg_replace('/-/','',$at);
	$at = strtotime($at);
	if  (date("d-m-Y H:i:s",$at) === '01-01-1970 01:00:00') {
		$at = date("d/m/Y - H:i:s");
		//$v = $at.': '.$v;
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