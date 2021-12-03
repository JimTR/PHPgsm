<?php
//$cmds = $argv;
include 'console_colour.php';
include 'colours/'.$cmds['game'].'-console_colour.php';
$version = "1.00";
$build = "10374-4026955264";
//print_r($cmds);
function readlog($cmds,$file='') {
global $console, $settings;
$roll_back = $cmds['rows'];
$count=0;
$a = file($file);

if (empty($roll_back)) {
      $roll_back = count($a)-2;
}
$arr = array_slice($a, - $roll_back);
$stats = false;
foreach ($arr as  $v) {
        if($v[0] == '#') {continue;}
        if($v[0] == '*') {continue;}
        
		$v = rtrim($v); 
	    preg_match('/\d+\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+/', $v, $result);
        if (isset($result[0])){$ips = $result[0];} else{$ips = '';}
         unset($result);
        preg_match('/<([U:\d+:[0-9]+])>/', $v, $result);
        //print_r($result);
         if(isset($result[1])){$steam_id = $result[1];} else {$steam_id='';}
        unset($result);
        preg_match('/(\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/', $v, $result);
        if(isset($result[0])){
			$date_time = explode('-', $result[0]);
			// format date time as per settings
			$date_time[0] = date($settings['date_format'],strtotime($date_time[0]));
			//echo "hello ".print_r($date_time,true) ;
			$date_time = implode(' -',$date_time);
			} 
			else{
				$date_time='' ;
				}
        unset($result);
        $date_time = preg_replace('/(\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/', '<span style="color:yellow">$0</span>', $date_time);
        $v = preg_replace('/L (\d+)\/(\d+)\/(\d+) - (\d+):(\d+):(\d+)/','',$v);
        //die($v); 
        preg_match('/"([^<]*)<\d/', $v, $result);
        if(isset($result[1])){$user = $result[1]; } else {$user='';}
        $v = str_replace($user,' ',$v);
        $v = preg_replace('/<\d+>/', ' ', $v);
        $v= preg_replace('/<([U:\d+:[0-9]+])>/', ' ', $v);
        $v= preg_replace('/"/', '', $v);
        $v = preg_replace('/<>/', '', $v);
        $v = strip_tags($v);
        $date ='L '. date("m/d/Y");
        $pattern = ' /L (\w+)\/(\d+)\/(\d+)/i';  
        $replacement = '<span style="color:yellow;"><b>${2}/$1/$3</b></span>'; //display the result returned by preg_replace  
        $v = preg_replace($pattern, $replacement, $v,-1,$counts);
        $v = preg_replace('/Console<0><Console><Console>/','Console',$v);
         $replacement = '<span style="color:yellow;"><b>${1}:$2:$3</b></span>';
        $pattern = '/(\d+):(\d+):(\d+):/';
         $v = preg_replace('/\((notoriety.*)\)/', '($1)', $v); 
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
                                   if($type =='IP') {$v = "<table style='width:30%;'><tr><td style='width:50%;text-align:center;'>IP Address</td><td>Ban Period</td></tr>";}
                                   else {$v = "<table style='width:30%;'><tr><td style='width:50%;text-align:center;'>User ID</td><td>Ban Period</td></tr>";}     
                                 } 
				
				$count = intval($data[1]);
                   
				}
			if (strpos($v,': permanent') or strpos($v,'.000 min')) {
				// do ban lines
                                if($type == "IP") {
                                //$count=0;
				$data = explode(':',$v);
                                $data[1] = str_replace('.000','',$data[1]);
                                //echo "we start with $v".PHP_EOL;
                                $uobject = array_filter(explode(' ',$data[0]));
                                if(!isset($uobject[1])) {$uobject[1] = '';}
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

				$v = '<tr><td style="text-align:right;padding-right:11%;color:cyan;">'.trim($uobject[1]).'</td><td style="color:red">'.trim($data[1]).'</td></tr>' ;
			}
                                if($uobject[0] == $count) {
                                   //echo 'we hit the full amount'.PHP_EOL;
                                   $count = 0;
                                   $v.='</table>';
                                 }
                      
                         elseif($type == 'ID') {
                               $data = explode(' ',$v);
                               //echo print_r($data,true); 
                               $v = $data[3].' - '.$data[1];
                               $v = '<tr><td style="text-align:right;padding-right:11%;color:cyan;">'.trim($data[1]).'</td><td style="color:red">'.trim($data[3]).'</td></tr>' ;
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
                           $data_count = count($data)-1;
                           $ip = trim(str_replace(')','',end($data)));
                            //echo "ip = $ip".'<br>';
                            if($data_count == 13) {
								// add line
								//$v = printr($data);
								
								$v = $data[1].' '.$data[6]." $ip ".$data[3].' '.$data[4].' '.$data[7].' '.intval($data[8]).' '.$data[9].' '.$data[10].' '.$data[11];
							}
							else{
								$v =$data[1].' '.$data[6]." $ip ".' '.$data[3].' '.$data[7].'  '.$data[4].' '.$data[5].' '.$data[9];
							}
                           //$v = print_r($data,true); 
                           //addip
			}
                        if(strpos($v,'Banid:')) {
                          $data = explode(' ',$v);
                          //echo print_r($data,true)."steam_id = $steam_id ".cr;  
                          $data[2] =' ID ';
                          $data[3] =" $steam_id "; 
                          $v = $data[1].$data[2].$data[3].$data[4].' '.$data[6].' '.$data[5].' '.$data[7].' '.$data[8];
                        }
         if(!empty($v)) {
         	$v =  preg_replace('/(.*) killed (.*) with ?(.*)? (.*)/', '$1 killed $2 with <span id="alf" style="color:yellow;">$3</span> <span id="kev" style="color:red;">$4</span>', $v);
        //if(!empty($date_time)){
         foreach ($console as $k => $val) {
               $v = str_replace($k,$val,$v);
            
				}
         $v = preg_replace('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\:\d{1,5}/', '$1 <span style="color:'.$console['user_ip'].';">$0</span>', $v);
         $v = preg_replace('/sv_[^=]*/', '$1<span style="color:cyan;">$0</span>', $v);
         $v = preg_replace('/tv_[^=]*/', '$1<span id="bug" style="color:cyan;">$0</span>', $v); 
         $v = preg_replace('/tf_[^=]*/', '$1<span id="bug" style="color:cyan;">$0</span>', $v);
         $v = preg_replace('/mp_[^=]*/', '$1<span id="bug" style="color:cyan;">$0</span>', $v);  
         $v = preg_replace('/(\w+\/){1,5}\w+\.\w+/', '<span style="color:Violet;">$0</span>', $v);
         if(function_exists('str_starts_with')) {
			if (str_starts_with($v,'Player')){continue;};
			if (str_starts_with($v,'userid')){continue;};
			if (str_starts_with($v,')')){continue;};
			if (str_starts_with($v, 'Client')) {continue;}
			if (str_starts_with($v, 'Dropped')) {continue;}
			if (str_starts_with($v, 'Weapon')) {continue;}
			if (str_starts_with($v, 'name')) {continue;}
			if (str_starts_with($v, 'Ignoring')) {continue;}
		}
		else {
			if(startsWith($v,'Player')){continue;}
			if (startsWith($v, 'Dropped')) {continue;}
		}
        // if(strlen($user) >1) {
			// echo strlen($user).' '.$v.'<br>';
         //if (str_starts_with($v, $user)) {continue;}
	 //}
       if (!strpos($v,'</tr>')){
         if(isset($cmds['colour']) and $cmds['colour'] == false) {
             $date_time = strip_tags($date_time);
		     $v = strip_tags($v,'<br> <table> <tr> <td>');
		} 
		  $v = substr_replace($v, $user, 2, 0); 
         $return[] = "<div>$date_time $v</div>"; // build line here
      
    	}
        else {
           if(isset($cmds['colour']) or $cmds['colour'] == false) {
                $v = strip_tags($v,'<br> <table> <tr> <td>');
           } 

          $return[] = "<div>$v</div>";
          }
	}
}
return $return;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}
