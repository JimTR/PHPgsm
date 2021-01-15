<?php
/*
 * ajax.php
 * 
 * Copyright 2019 Jim Richardson <jim@noideersoftware.co.uk>
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
 * required for ajax requests from html version 
 * this file will be impossible to run without intervention from an other local or remote script
 */
 // localhost d41d8cd98f00b204e9800998ecf8427e
 require_once 'includes/master.inc.php';
 include 'functions.php';
 $ip =$_SERVER['SERVER_ADDR'];
 if(is_cli()) {
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	$sec = true;
	$type= $argv;
	$cmds =convert_to_argv($type,"",true);
	$logline  = date("d-m-Y H:i:s").' localhost accessed ajax with '.print_r($cmds,true).PHP_EOL;
	file_put_contents('ajax.log',$logline,FILE_APPEND);
	if (isset($cmds['debug'])) {
		error_reporting( -1 );
	}
	else {error_reporting( 1 );}
	//print_r($cmds);
	//die('Finished'.cr);
	// need to do something here
}
else {
 define ("CR","<br>");
 define ('cr',"<br>");
 error_reporting( -1 );
 if (!empty($_POST)) {
	 $cmds =convert_to_argv($_POST,"",true);
 }
 else {
	 $cmds =convert_to_argv($_GET,"",true);
 }
$logline  = date("d-m-Y H:i:s").' <'.$_SERVER['REMOTE_ADDR'].'>';
//file_put_contents('ajax.log',$logline,FILE_APPEND); // debug code
 // $cmds = change_value_case($cmds,CASE_LOWER);
}
/*
 * beta logging code
 * check to see what we have back in normal use
 */
//print_r($_SERVER); // debug code
 if (isset($_SERVER['REMOTE_ADDR'])) {
 $logline.=' Connected ';
 //$logline .= ' command to execute\,'.$_SERVER['QUERY_STRING'].'\''.PHP_EOL;
}
else {
	$logine = ' No Remote IP connected';
	//file_put_contents('ajax.log',$logline,FILE_APPEND);
	// fail it out
}
 $logline .= 'command to execute '.$cmds['action'].' ';
 if (isset($cmds['key'])) {
	 //$logline .= 'Key Found ';
	 if ($cmds['key'] == md5( ip2long($ip))) {
		 //we check if it's for us
		  $logline .= ' Key Valid'.PHP_EOL;
		  // now check for the next level
	  }
	  else {
		  // fail out
		  $logline .= ' Key Invalid ('.$cmds['key'].') '.md5( ip2long($ip)).' - '.$ip.PHP_EOL;
		  file_put_contents('ajax.log',$logline,FILE_APPEND);
		 // exit;
	  }
 }
 else {
	 $logline .= date("d-m-Y H:i:s").' Key Not Found'.PHP_EOL;
	 exit;
 }
 //line one done
	//file_put_contents('ajax.log',$logline,FILE_APPEND);
//if (validate($cmds)===false) {die();}  
 if(isset($cmds['action'])) {
//header('Access-Control-Allow-Origin: *');
//check_update();

switch (strtolower($cmds['action'])) {
	case "boottime" :
			echo get_boot_time();
			exit;
	case "load" :
			$cpu_info=get_cpu_info();
			echo $cpu_info['load'];
			exit;
	case "get_file" :
			//need api key
			echo file_get_contents($cmds['file']);
			exit;	
	case "ps_file" :
			//need api key !!
			//echo file_put_contents($cmds['file']);
			if (isset($cmds['filter'])) {
				// add the grep filter
				echo shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg');
			}
			else {
				echo shell_exec ('ps -C srcds_linux -o pid,%cpu,%mem,cmd'); 
			}
			exit;
	case "top" :
				if (isset($cmds['filter'])) {
					//do stuff
					echo shell_exec('top -b -n 1 -p '.$cmds['filter'].' | sed 1,7d');
				}
			 	exit;
	case "lsof" :
					// get open file
					echo $cmds['lsof_file'].'<br>';
					
					if (isset($cmds['lsof_file'])) {
						// return the open file,  the interface should format this correctly not ajax's job
						// what ajax needs is the full path to where the file resides
						// note, this will only return an open file 
						// this runs only on the local server, must be called on each server
						$cmd = 'lsof | grep m1 '.$cmds['lsof_file'];
						echo $cmd.'<br>';
						$tmp = shell_exec('lsof | grep -m1 '.$cmds['lsof_file']);
						echo $tmp.'<br>';
						$x = explode(' ',$tmp);
							foreach ($x as $k=>$v) 
								if (empty(trim($v))) {
									unset ($x[$k]);
								}
								else {
									$x[$k]=trim($v);
								}
							}
						$c = count($x); // need this to check file size
						$x = array_values($x); // re-number array
						if ($c == 7 ) {
							// empty file return message
							echo 'file=0';
						}
						else { 
							$c = $c-1;
							}
						// now do stuff return either the path or contents ?
						// sending back the contents will save a call but maybe wrong 
						$filename = $x[$c]; //got file name
						if ($cmds['return'] == 'content') {
							echo file_get_contents($filename);
						}
						else {
							echo $filename;
						}
						
				exit;
					
	case "game_detail" :
			$gd =game_detail();
			//print_r($gd);
			$json = json_encode($gd);
			echo $json;
			exit;				
	case "rgames" :
		  echo  display_games();
		  exit;	
	case "hardware" :
			$cpu_info = get_cpu_info();
			if (isset($cmds['data'])) {
			//print_r ($cpu_info);
			$json = json_encode($cpu_info);
			echo $json;
			//$arr = json_decode($json);
			//print_r($arr);
		}
			else {echo display_cpu($cpu_info);}
			exit;
	case "software" :
			
			$software = get_software_info($database);
			if (isset($cmds['data'])) {
			//print_r ($cpu_info);
			$json = json_encode($software);
			echo $json;
			//$arr = json_decode($json);
			//print_r($arr);
		}
		else {
			$os = lsb();
			echo display_software($os,$software);
		}
			exit;
	case "disk":
			$disk_info = get_disk_info();
			//print_r($disk_info);
			if (isset($cmds['data'])) {
			$json = json_encode($disk_info);
			echo $json;
		}
		else {
			echo display_disk($disk_info);
		}
			exit;
	case "memory":
			$mem_info = get_mem_info();
			if (isset($cmds['data'])) {
			//print_r ($mem_info);
			$json = json_encode($mem_info);
			echo $json;
		}
		else{
			echo display_mem($mem_info,True);
		}
			exit;
	case "user":
			$disk_info = get_disk_info();
			$user_info = get_user_info($disk_info);
			if (isset($cmds['data'])) {
			$json = json_encode($user_info);
			echo $json;
		}
		else {
			echo display_user($user_info);
		}
			exit;
	case "all":
			// get all back
			$cpu_info=get_cpu_info();
			
			$software = get_software_info($database);
			$os = lsb();
			
			$disk_info = get_disk_info();
			
			$mem_info = get_mem_info();
			
			$user_info = get_user_info($disk_info);
			$gd =game_detail();
			
			if (isset($cmds['data'])) {
				// json
				$return['cpu']=$cpu_info;
				$return['software']=$software;
				$return['disk_info']=$disk_info;
				$return['mem_info']=$mem_info;
				$return['user_info']=$user_info;
				$return['game_detail']=$gd;
				$xml = new SimpleXMLElement('<servers/>');
				array_to_xml($return,$xml);
				//print $xml->asXML();
				//array_walk_recursive($return, array ($xml, 'addChild'));
				//header('Content-type: text/xml');
				//print $xml->asXML();
				print_r($return);
			}
			else {
					$data = display_cpu($cpu_info).'\n';
					$data .= display_software($os,$software).'\n';
					$data.= display_disk($disk_info).'\n';
					$data .= display_mem($mem_info,True).'\n';
					$data .= display_user($user_info);
					echo $data;
				}
			exit;
	case "version":
			echo 'Ajax version 1.44';
			exit;
	case "allservers":
			// return servers
	$sql = 'select * from base_servers where extraip="0" and enabled ="1"';			
	$database = new db(); // connect to database
	$template = new Template; // load template class
	$res = $database->get_results($sql); // pull results
	$servers = array(); // set array
	
foreach ($res as $data) {
	
	
	$template->load('html/base_server.html'); // load blank template
	//add the data array for base server 
	//this does allow remote locations
	// as long as you have the remote software installed
	
	$subpage['server_title'] = $data['name'].' ('.$data['ip'].')';
	$subpage['host'] = $data['url'].':'.$data['port'] ;
	$subpage['id'] = 'collapse'.$acc ;
	$subpage['ip'] = $data['ip'];
	// curl the data for the server
		$ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $subpage['host'].'/ajax.php?action=all');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $server = curl_exec($ch);
		 curl_close($ch);
	     $server= explode('\n',$server);
	
	$subpage['cpu'] = $server[0];
	$subpage['software'] = $server[1];
	$subpage['disk'] = $server[2];
	$subpage['mem'] = $server[3];
	$subpage['user'] = $server[4];
	$subpage['ajax'] = '1.0';
	$subpage['version'] = $settings['version'];
	$template->replace_vars($subpage);
	$page1.= $template->get_template(); 
	}
	 echo $page1.'*';
		
	//Game server(s) 
	$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1" and base_servers.enabled="1"';
	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
		// loop servers
		
		$template->load('html/game_server.html'); // load blank template
		$action ='dt';
		$server = $data['location'].'/'.$data['host_name'];
		$url = $data['url'].':'.$data['port'];
		
		 $ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url.'/ajax.php?action=exelgsm&path='.$server.'&cmd=dt');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $x1 = curl_exec($ch);
		 curl_close($ch);
		
		$x1 = explode(PHP_EOL,$x1 );
		$servers = refactor_array($x1);
		$cmd = array_search_partial($x1,'Command-line Parameters' )+2;
		 $servers['cmd'] = $x1[$cmd];
		 // make gameq call
		 //print_r($servers);
		 //echo CR;
		 require_once('GameQ/Autoloader.php'); //load GameQ
		 $key = $data['host_name'];
		 $x2['id'] = $data['host_name'];
	     $x2['host'] = $servers['Server IP'] ;
	     $x2['type'] = $data['type'];
	     //print_r($x2);
	     //echo CR;
	     $ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url.'/ajax.php?action=user');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $user = curl_exec($ch);
		 curl_close($ch);
		 
	     $GameQ = new \GameQ\GameQ();
		//include ("server-info.php");
          $GameQ->addServer($x2);
          $results = $GameQ->process();
          $gameport = array_search_partial($x1,'DESCRIPTION' )+1;
		  $sourceport = $gameport+1;
		  $clientport = $sourceport+1;	
		  $subpage['Current map'] = $results[$key]["gq_mapname"];
		 if (isset($servers['Game world'])) {
			 $servers['Default map'] = $servers['Game world'];
			 }
		 if ($results[$key]['gq_online'] == 1 ){
			  //online
			  $online = 'img/online.png';
		  }
		  else {$online = 'img/offline.png';}
		 
		 $subpage['gameport'] = filter_var($x1[$gameport], FILTER_SANITIZE_NUMBER_INT);
		 $subpage['sourceport'] = filter_var($x1[$sourceport],FILTER_SANITIZE_NUMBER_INT);
		 $subpage['clientport'] = filter_var($x1[$clientport],FILTER_SANITIZE_NUMBER_INT);	
		 $subpage['server_name'] = $servers['Server name'].' ('.$servers['Server IP'].')';
		 $subpage['lgsm'] = substr($servers['LinuxGSM version'],1);
		 $subpage['cmd'] = $servers['cmd'];
		 $subpage['Discord alert'] = $servers['Discord alert'];
		 $subpage['Slack alert'] = $servers['Slack alert'];
		 $subpage['Email alert'] = $servers['Email alert'];
		 $subpage['Update on start'] = $servers['Update on start'];
		 $subpage['Pushbullet alert'] = $servers['Pushbullet alert'];
		 $subpage['IFTTT alert'] = $servers['IFTTT alert'];
		 $subpage['Mailgun (email) alert'] = $servers['Mailgun (email) alert'];
		 $subpage['Pushover alert'] = $servers['Pushover alert'];
		 $subpage['Telegram alert'] = $servers['Telegram alert'];
		 $subpage['players'] = $results[$key]['gq_numplayers'];
		 $subpage['Maxplayers'] = $servers['Maxplayers'];
		 $subpage['Server password'] = $servers['Server password'];
		 $subpage['RCON password'] = $servers['RCON password'];
		 $subpage['Default map'] = $servers['Default map'];
		 $subpage['Location'] = $servers['Location'];
		 $subpage['Config file'] = $servers['Config file'];
		 $subpage['Status'] = $online;
		 $subpage['user'] = $user;
		 $template->replace_vars($subpage);
		 $page2.= $template->get_template(); 
        // echo $page2.CR;
		
		
}	
echo $page2;
	case "exelgsm":
			if(isset($cmds['path'])) {
			 $server = $cmds['path'];
			 $cmd = $cmds['cmd'];
			 $exe = trim($cmds['exe']);
			 //echo 'you requested '.$server.'<br>';
			 
			 //print_r($_GET);
			 $output = exe_lgsm($server,$cmd,$exe);
			 echo $output;
			 exit;}
	 case "exescreen":
	        $alreadyrunning = 0; 
			$cmd = $cmds['cmd'];
			$exe = trim($cmds['exe']);
			$text= trim($cmds['text']);
		    $s= exe_screen('ls',$exe,$text,$status);
		    $x=strpos($s,',');
		
		$server1=substr($s,0,$x);
		if ($server1 <> $exe) {unset($server1);}
			if (!empty($server1)){
			//running
			$alreadyrunning = 1;
		}
		file_put_contents('ajax.log','ready to do exe_screen'.PHP_EOL
,FILE_APPEND);
		echo exe_screen($cmd,$exe,$text,$alreadyrunning);
											  		
}
}
else {
	echo "you cocked up";
}
function exe_lgsm($server,$action,$exe)
  {
	  /* this will run lgsm functions
	   * Requires $server to workout which game to exec
	   * Requires $action to do whatever with the server , in shorthand notation
	   * returns the lgsm display as a string
	   * note lgsm c/console  & h/help will not be supported via this function
	   * will be used in ajax.php 
	   */
	   //echo $server.' '.$action.' '.$exe;
	   switch($action) 
	   {
		   // choose action
		   case "dt" :
			$command = $server.' '.$action;	
			echo $command.'<br>';			
			break 1;
		  case "sp" :
			//$command = 'tmux kill-session -t '.$handle;
			exit;
		  case "st":
				$command = $server.' '.$action;	
				echo $command;
				$disp = shell_exec($command);
				exit;
		   default:
		   		   echo $command;
		   $disp = shell_exec($command);
		   return $disp;
	   }
	   $disp = shell_exec($command);
	   //echo 'disp ?<br>';
	   //echo $disp;
	   return $disp;
	   
  }
  function exe_screen($action,$exe="",$text="",$status="")
  {
	  /* run screen commands
	   * first off get all you need b4 running the action
	   * with start get the command line from lgsm
	  */
	   //echo $server.' action '.$action.' file '.$exe.' status '.$status.'Text '.$text.'<br>';
	   if( $exe <>"") {
	   $database = new db(); // connect to database   
	   $sql = 'select * from servers where host_name = "'.$exe.'"';
	   $x = $database->get_results($sql); // pull results
	   //print_r($x);
	   $detail =$x[0];
	   //print_r($detail);
   }
	  
	   switch($action)
		{
		  case "s":
			//start screen session
			
			if($status === 1) {
				$disp = $exe.' is already running !';
				break;
			}
			chdir($detail['location']);
			$logFile = $detail['location'].'/log/console/'.$detail['host_name'].'-console.log' ;
			$savedLogfile = $detail['location'].'/log/console/'.$detail['host_name'].'-'.date("d-m-Y").'-console.log' ;
			rename($logFile, $savedLogfile);	
			//$cmd = 'screen -L -Logfile '.$detail['Location'].'/log/console/'.$detail['host_name'].'-console.log -dmS '.$detail['host_name'].' bash -c "'.$detail['startcmd'].'^M"'; //start server
			//$cmd = 'screen -L -Logfile '.$detail['location'].'/log/console/'.$detail['host_name'].'-console.log -dmS '.$detail['host_name'];
			$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$detail['host_name'];
			exec($cmd); // open session
			$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "cd '.$detail['location'].'^M"';
			exec($cmd);
			$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "'.$detail['startcmd'].'^M"'; //start server
			exec($cmd); // start game
			$disp = 'Starting Server '.$detail['host_name'];
			$sql = 'update servers set running = 1 where host_name = "'.$exe.'"';
			$update['running'] = 1;
			$update['starttime'] = time();
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			break;
		  case "q":
			// stop screen session
			if($status === 0) {
			  $disp = "start ".$detail['host_name']." before stopping it";
			  break;
				}
			
			$cmd = 'screen -X -S '.$detail['host_name'] .' quit';
			//echo $cmd;
			exec($cmd);
			
			$disp = 'Stopping Server '.$detail['host_name'];
			$update['running'] = 0;
			$update['starttime'] = '';
			$where['host_name'] = $exe; 
			$database->update('servers',$update,$where);
			
			break;
		  case "r":
				$cmd = 'screen -X -S '.$detail['host_name'] .' quit';
				exec($cmd); //kill session
				$logFile = $detail['location'].'/log/console/'.$detail['host_name'].'-console.log' ;
				$savedLogfile = $detail['location'].'/log/console/'.$detail['host_name'].'-'.date("d-m-Y").'-console.log' ;
				rename($logFile, $savedLogfile);
				$update['running'] = 0;
				$update['starttime'] = '';
			    $where['host_name'] = $exe; 
			    $database->update('servers',$update,$where);
			    chdir($detail['location']);
				$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$detail['host_name']; 
				exec($cmd); // open session
				$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "cd '.$detail['location'].'^M"';
			    exec($cmd); //make sure we are in the right place
				$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "'.$detail['startcmd'].'^M"'; 
				exec($cmd); // start game
				$disp = 'Restarting Server '.$detail['host_name'];
				$sql = 'update servers set running = 1 where host_name = "'.$exe.'"';
				$update['running'] = 1;
				$update['starttime'] = time();
				$where['host_name'] = $exe; 
				$database->update('servers',$update,$where);
				break;
		  case "c":
		  	  if($status === 0) {
			  $disp = "start Server before issuing commands";
			  break;
		 	  }
		  $cmd = 'screen -S '.$exe.' -p 0 -X stuff "'.$text.'^M"';
		  exec($cmd);
		  $disp = 'Command Sent';
			// send console command
			break;
		  case "ls":
			// read screen sessions
			//echo "<br>LS<br>";
			$cmd = 'screen -ls '.$exe;
			//echo '<br>'.$cmd;
			$screenList = shell_exec($cmd);
			//echo $screenList;
			$screenList = explode(PHP_EOL,$screenList);
			foreach (array_slice($screenList,1) as $key=>$value) {
				// loop array
				//echo $value.'<br>';
				$value= trim($value);
				$temp = preg_replace('/^[0-9]+./', '', $value);
				//echo $temp.'<br>';
				if (strpos($temp,'/run/screen/S') >1) {
					//echo 'hit end <br>';
					break;
				}
				$x =strpos($temp,'(');
				$server = trim(substr($temp,0,$x));
				//if ($x=0){continue;}
				//echo 'Server = '.$server.'<br>';
				preg_match('/([\/[0-9]+\/+[0-9]+\/+[0-9]+[0-9])/', $temp, $date);
				preg_match('/([\/[0-9]+:+[0-9]+:+[0-9]+[0-9])/', $temp, $time);
				$timestamp = $date[1].' '.$time[1];
				$timestamp = strtotime($timestamp);
				//echo $timestamp.'<br>';
				if (!empty($server)) { 
					if ($server==$exe) {
						$disp .= $server.','.$timestamp."*" ;
				}
				elseif (empty($exe)) {
					$disp .= $server.','.$timestamp."*" ;
				}
					
				//echo $server.' '.$timestamp.'<br>';
			}
			} 
			//print_r($screenList);
			
			break;		
		}
		return $disp;
		
	} 
	
	function refactor_array($array) {
	// refactor array with keys
	foreach ($array as &$value) {
			//read data
			$i = strpos($value,":",0);
            $key = trim(substr($value,0,$i));
		    $nos[$key] = trim(substr($value,$i+1));
		}
		return $nos;
//print_r($nos);
}

function array_search_partial($arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}
 function change_value_case($array,$case = CASE_LOWER){
        $array =array_change_key_case($array, $case);
        switch ($case) {
			case CASE_LOWER:
				$array = array_map('strtolower', $array);
				break;
			case CASE_UPPER:
				$array = array_map('strtoupper',$array);
				break;	
       }
        return $array;
    }
function check_update()
{
	//update for xml
	$database = new db();
	$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1" and base_servers.enabled="1" and servers.server_id >=0';
	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
		// loop
		
		$acf_loc = $data['location'].'/serverfiles/steamapps';
		$find = 'appmanifest_';
		$files = glob($acf_loc."/*" . $find . "*");
		if (!empty($files)){
			$acf_file = file_get_contents($files[0]);
			//echo $acf_file.'<br>';
			    $local =  local_build($acf_file);
			    $update['server_id'] = $local['appid'];;
				$update['buildid'] = $local['buildid'];
				$update['server_update']= $local['update'];
			    $where['host_name'] = $data['host_name']; 
			    $database->update('servers',$update,$where);
			//echo 'Details for App Id '.$local['appid'];
			//echo 'Local Build id '.$local['buildid'].'<br>';
            //echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$local['update']);
			//echo '<br>';
			}
			//else {echo $data['location'].'/serverfiles/steamapps<br>';}
			}
}
function game_detail() {
	// get processes
	global $cmds; // get options 
	$db = new db();
	$mem =0;
	$cpu = 0;
	$r=1;
	if(isset($cmds['filter'])) {
		
		$ip = file_get_contents("http://ipecho.net/plain"); // get ip
		 if (empty($ip)) { $ip = shell_exec('curl http://ipecho.net/plain');} 
		 $sql = 'select servers.* , base_servers.port as bport, base_servers.base_ip, base_servers.url from servers left join base_servers on servers.host = base_servers.ip where servers.host_name = "'.$cmds['filter'].'"';
		 //echo $sql.'<br>';		 
		 $server_data = $db->get_results($sql);
		  $server_data=reset($server_data);
		  if (empty($server_data['base_ip'])) {         
                if ($ip <> trim($server_data['host'])) {
					echo 'wrong call guv !<br>';
					exit;
					// kill if wrong
				}
			}
			else {
					//echo $sql;
				}
                //$new = trim(file_get_contents($server_data['url'].':'.$server_data['bport'].'/ajax.php?action=ps_file&filter='.$server_data['host_name']));
                $cmd = 'ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$cmds['filter'].'.cfg';
                //echo $cmd.'<br>';
                $new = trim(shell_exec($cmd));
                
                if (empty($new)) {
		// offline
		
		$du = shell_exec('du -s '.$server_data['location']); // get size of game
		list($size, $location) = explode(" ", $du); // drop to variables
		$server_data['cpu'] = '';
	    $server_data['size'] = formatBytes(floatval($size)*1024,2);
		$server_data['mem'] = '';
		
	}
               
                $tmp = explode(' ',$new);
		if (!empty($tmp[0])) {
	
	$pid = $tmp[0];
	$count = count($tmp);
	//echo 'using command '.$server_data['url'].':'.$server_data['bport'].'/ajax.php?action=top&filter='.$pid.'&key='.md5( ip2long($ip)).'<br>';
	$temp =  trim(file_get_contents($server_data['url'].':'.$server_data['bport'].'/ajax.php?action=top&filter='.$pid.'&key='.md5( ip2long($ip))));
        //$temp = trim(file_get_contents('top');
	$temp = array_values(array_filter(explode(' ',$temp)));
	$du = shell_exec('du -s '.$server_data['location']); // get size of game
		
	list($size, $location) = explode(" ", $du); // drop to variables
	$server_data['count'] =  count($temp);
	$server_data['mem'] = $temp[$server_data['count']-3];
	$server_data['cpu'] = $temp[$server_data['count']-4];
	$server_data['size'] = formatBytes(floatval($size)*1024,2);
			
	}
	$return[$server_data['host_name']] = $server_data;
	return $return;
}
	else{	
	$t =trim(shell_exec('ps -C srcds_linux -o pid,cmd |sed 1,1d'));
    $tmp = explode(PHP_EOL,$t);
	$i=0;
	if(strlen($t) === 0) {
                
                $ip = file_get_contents("http://ipecho.net/plain"); // get ip
                if (empty($ip)) { $ip = shell_exec('curl http://ipecho.net/plain');}
                $sql =  'SET sql_mode = \'\'';
                $a= $db->query( 'SET sql_mode = \'\'');  
                $sql ='select  servers.location,count(*) as total from servers where servers.host like "'.substr($ip,0,strlen($ip)-1).'%"';
                $server_count = reset($db->get_results($sql));
                
                $du = shell_exec('du -s '.dirname($server_count['location']));
                list ($tsize,$location) = explode(" ",$du);
        }
        else{

	foreach ($tmp as $server) {
		
		$server = str_replace('./srcds_linux','',$server); // we don't need this throw it
		$server = str_replace(' -insecure','',$server); // we don't need this throw it
		$server= trim($server); // get rid of spaces & CR's 
		$tmp_array[$i] = explode(' ',$server); // arrayify
		$pid = $tmp_array[$i][0]; // git process id
		$cmd = 'top -b -n 1 -p '.$pid.' | sed 1,7d'; // use top to query the process
		$top = array_values(array_filter(explode(' ',trim(shell_exec($cmd))))); // arrayify
		$sql = 'select * from servers where servers.host ="'.$tmp_array[$i][6].'" and servers.port = "'.$tmp_array[$i][8].'"'; //query 1 get the server detail
		//echo $sql.cr;
		$result =$db->get_results($sql); // get data back
		$result=reset($result);
		$sql = 'select  count(*) as total from servers where servers.host like "'.substr($tmp_array[$i][6],0,strlen($tmp_array[$i][6])-1).'%"'; // query 2 count the game servers
		$server_count= $db->get_results($sql); // get data back
		$server_count=reset($server_count);
		$count = count($top); // how many records  ?
		$mem += $top[$count-3]; // memory %
		$cpu += $top[$count-4]; // cpu %
		$du = trim(shell_exec('du -s '.$result['location'])); // get size of game
		$size = str_replace($result['location'],'',$du);
		//list($size, $location) = $du_a; // drop to variables
		$result['mem'] = $top[$count-3];
		$result['cpu'] = $top[$count-4];
		$result['size'] = formatBytes(floatval($size)*1024,2);
		$return[$result['host_name']] = $result;
		$i++;
	}	
	$du = shell_exec('du -s '.dirname($result['location']));
	//list ($tsize,$location) = explode(" ",$du);
	$tsize = str_replace(dirname($result['location']),'',$du);
	}
	// add computed items 
	$return['general']['live_servers'] = $i;
	$return['general']['total_servers'] = $server_count['total'];
	$return['general']['mem'] = round($mem,2,PHP_ROUND_HALF_UP);
	$return['general']['cpu'] = round($cpu,2,PHP_ROUND_HALF_UP);
	$return['general']['total_size'] = formatBytes(floatval($tsize)*1024,2);
	return $return;
}
}
function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}


?>
