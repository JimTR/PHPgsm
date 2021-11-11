<?php
$cmds = $argv;
include 'console_colour.php';
//print_r($console);
//die();
$output =readlog($cmds);
foreach ($output as $show) {
echo $show;
}
function readlog($cmds,$file='') {
global $console;
$n = $cmds[1];
//print_r($cmds);
$count=0;
$a = file('/home/nod/games/nmrih/log/console/nmrihserver-console.log');
//$lines_to_process = count($a);


if (empty($n)) {
      $n = count($a)-2;
}
$arr = array_slice($a, -$n);
//print_r($arr);
$stats = false;
foreach ($arr as  $v) {
        if($v[0] == '#') {continue;}
	$v = strip_tags($v);     
        //$v = preg_replace('/<bot>/',' ',$v);
        $v = str_ireplace('<bot>', "",$v);
        $v = str_ireplace('<spectator>', "",$v);
        $v = str_ireplace('<Unassigned>',"",$v);
        $v = rtrim($v);
        preg_match('/\d+\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/', $v, $result);
        if (isset($result[0])){$ips = $result[0];} else{$ips = '';}
         unset($result);
        preg_match('/<([U:\d+:[0-9]+])>/', $v, $result);
         if(isset($result[1])){$steam_id = $result[1];} else {$steam_id='';}
        unset($result);
        preg_match('/(\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/', $v, $result);
        if(isset($result[0])){$date_time = $result[0];} else{$date_time = '';}
        unset($result);
        $date_time = preg_replace('/(\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/', '<span style="color:yellow">$0</span>', $date_time);
        $v = preg_replace('/L (\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/','',$v);
        //die($v); 
        preg_match('/"([^<]*)<\d/', $v, $result);
        if(isset($result[1])){$user = $result[1]; } else {$user='';}
        if($user == '11'){die($v);}   
        $v = str_replace($user,' ',$v);
        $v = preg_replace('/<\d+>/', ' ', $v);
        $v= preg_replace('/<([U:\d+:[0-9]+])>/', ' ', $v);
        $v= preg_replace('/"/', '', $v);
        $v = preg_replace('/<>/', '', $v);
        $date ='L '. date("m/d/Y");
        $pattern = ' /L (\w+)\/(\d+)\/(\d+)/i';  
        $replacement = '<span style="color:yellow;"><b>${2}/$1/$3</b></span>'; //display the result returned by preg_replace  
        $v = preg_replace($pattern, $replacement, $v,-1,$counts);
        $v = preg_replace('/Console<0><Console><Console>/','Console',$v);
        //preg_replace('/(Unknown command)(.*)/', '$0 --> <span style="color:red;">$1</span>$2', $v); 
        //$v = preg_replace('/Unknown command/',$console['Unknown command'],$v); 
       // $v = str_replace('"','',$v); // remove quotes
        $replacement = '<span style="color:yellow;"><b>${1}:$2:$3</b></span>';
        $pattern = '/(\d+):(\d+):(\d+):/';
        //$v = preg_replace($pattern, $replacement, $v,-1);
        //if (empty($count)) {continue;} // clears non dated rows
         $v = preg_replace('/\((notoriety.*)\)/', '($1)', $v); 
        //$v = preg_replace('@\(.*?\)@','',$v); // bracket content
        $v = preg_replace('/\((attacker.*)\) \((victim.*)\)/', '', $v);
        //if (strpos($v,'========= Stats For') == true) {$stats = true;}
        //if (strpos($v,'HANGE LEVEL:') == true) {$stats = false;}
        if ($stats) {
           echo "$v<br>";
           if(!isset($headers)){
              $headers = preg_replace('/_/', ' ', $v);
              //print_r($headers);
              if (strpos($headers,'========= Stats For') == true) {unset($headers);}
              if (isset($headers)) {
                      $headers = ltrim($headers, ':'); ;
                      $headers = explode(' ', trim($headers));
                      print_r($headers);
                     
                  }    
              //echo "headers $headers set from $v<br>"; 
          } 
        } 
        if (strpos($v,' filter list:') == true) {
				$data = explode(':',$v);
                                //echo $v.PHP_EOL;
                                $type = trim(substr($data[0],0,2));
                                //echo $type.PHP_EOL;
                                $count=0;
                                if($count == 0){
                                   if($type =='IP') {
                                   	$v = "<table style='width:20%;'><tr><td style='width:50%;text-align:center;'>IP Address</td><td>Ban Period</td></tr>";
                                        //$v = 'why br ?';
                                   }
                                  else {
                                     $v = "<table style='width:20%;'><tr><td style='width:50%;text-align:center;'>User ID</td><td>Ban Period</td></tr>";
				}     
                                 } 
				//$v = '';//trim($data[1]);
                                //print_r($data);
				$count = intval($data[1]);
                                //echo "\$count = $count";
			           //$v=$count;
				}
			if (strpos($v,': permanent') or strpos($v,'.000 min')) {
				// do ban lines
                                if($type == "IP") {
                                //$count=0;
				$data = explode(':',$v);
                                $data[1] = str_replace('.000','',$data[1]);
                                //echo "we start with $v".PHP_EOL;
                                $uobject = array_filter(explode(' ',$data[0]));
                                //echo 'new $uobject '.print_r($uobject,true).PHP_EOL;
                                if(!isset($uobject[1])) {
                                   $uobject[1] = '';
                                }
                                //echo 'count of $uobject = '.count($uobject).PHP_EOL;
                                //if($uobject[0] == $count) {
                                   //echo 'we hit the full amount'.PHP_EOL;
                                  // $v.='</table>';
				 //}
                                   $data[0] = trim(str_replace($uobject[0],'',$data[0]));
                                  if(isset($uobject[2])) {
					$uobject[1].=trim($uobject[2]);
                                        unset($uobject[2]);
                                   }
                                  if(isset($uobject[3])) {
                                        $uobject[1].=trim($uobject[3]);
                                        unset($uobject[3]);
                                   }
                                  if(isset($uobject[4])) {
                                        $uobject[1].= trim($uobject[4]);
                                        unset($uobject[4]);
                                   }
                                  if(isset($uobject[5])) {
                                        $uobject[1].= trim($uobject[5]);
                                        unset($uobject[5]);
                                   }

				$v = '<tr><td style="text-align:right;padding-right:11%;">'.trim($uobject[1]).'</td><td>'.trim($data[1]).'</td></tr>' ;
			}
                                if($uobject[0] == $count) {
                                   //echo 'we hit the full amount'.PHP_EOL;
                                   $count = 0;
                                   $v.='</table>';
                                 }
                      
                         elseif($type == 'ID') {
                               $data = explode(' ',$v);
                               $v = $data[3].' - '.$data[1];
                               $v = '<tr><td style="text-align:right;padding-right:11%;">'.trim($data[1]).'</td><td>'.trim($data[3]).'</td></tr>' ;
                                //$v="count = $count ".print_r($data,true);
                                 if($data[0] == $count) {
                                   //echo 'we hit the full amount'.PHP_EOL;
                                   $count = 0;
                                   $v.='</table>';
                                 }


                          }
                         }
                          //$count=0;
			if (strpos($v,'Addip:')) {
                           $v = trim(str_replace('"','',str_replace(')','',$v)));
                           $data = explode(' ',$v);
                           //print_r($data);
                           $ip = trim(str_replace(')','',end($data)));
                            //echo "ip = $ip".'<br>';
                           $v =$data[1].' '.$data[6]." $ip ".' '.$data[3].' '.$data[7].'  '.$data[4].' '.$data[5].' '.$data[9];
                           //$v = print_r($data,true); 
                           //addip
			}
                        if(strpos($v,'Banid:')) {
                           //$v = trim(str_replace('"','',str_replace(')','',$v)));
                          //$v = trim(str_replace('<','',str_replace('>','',$v)));
                          $data = explode(' ',$v);
                          $data[2] =' ID ';
                          $data[3] =" $steam_id "; 
                          //$data[0]= 'Banid'.$data[0];
                          //$data[1] = trim(str_replace('<','',str_replace('>','',$data[1])));
                          //$data[1] = trim(str_replace('"','',str_replace(')','',$data[1])));
                          //$data[1] = str_replace(': ',': ID ',$steam_id);
                          $v= $data[1].$data[2].$data[3].$data[4].' '.$data[6].' '.$data[5].' '.$data[7].' '.$data[8];
                          //$v = print_r($data,true);
                          //banid list
                        }
         if(!empty($v)) {
         	$v =  preg_replace('/(.*) killed (.*) with ?(.*)? (.*)/', '$1 killed$2 with <span id="alf" style="color:yellow;">$3</span> <span id="kev" style="color:red;">$4</span>', $v);
        //if(!empty($date_time)){
         foreach ($console as $k => $val) {
               $v = str_replace($k,$val,$v);
              //preg_replace('/(Unknown command)(.*)/', '$0 --> <span style="color:red;">$1</span>$2', $input_lines);

	}
         $v = preg_replace('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\:\d{1,5}/', '$1 <span style="color:'.$console['user_ip'].';">$0</span>', $v);
         $v = preg_replace('/sv_[^=]*/', '$1<span style="color:cyan;">$0</span>', $v);
         $v = preg_replace('/tv_[^=]*/', '$1<span id="bug" style="color:cyan;">$0</span>', $v); 
         $v = preg_replace('/(\w+\/){1,3}\w+\.\w+/', '<span style="color:Violet;">$0</span>', $v);
        //}
       //else {
        //    $v = '<span style="color:cyan;">'.$v.'</span>';
       // } 
       //$v = preg_replace('/ killid with (.*)/', ' with <span id="last-hit" style="color:yellow;">$1</span> ', $v);
       //$v = preg_replace('/(.*) killed(.*) with (.*)/', '$1 killed$2 with <span style="color:yellow;">$3</span>', $v);
       if (!strpos($v,'</tr>')){
         $return[] = "$date_time $user $v<br>"; // build line here
       //} this is the correct one
        //if(!empty($ips)) {
         // $return[] = "$date_time $user $ips $steam_id $v<br>";
        //} 
    	}
        else {
          $return[] = $v;
          }
	}
}
return $return;
}
