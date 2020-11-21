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
 */
 
 require_once 'includes/master.inc.php';
 include 'functions.php';
 define ("CR","<br>");
 if (!empty($_POST)) {
	 $cmds = $_POST;
 }
 else {
	 $cmds = $_GET;
 }
 $cmds = change_value_case($cmds,CASE_LOWER);
  
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
	case "put_file" :
			//need api key !!
			//echo file_put_contents($cmds['file']);
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
			print_r($user_info);
			echo display_user($user_info);
			exit;
	case "all":
			// get all back
			$cpu_info=get_cpu_info();
			$data = display_cpu($cpu_info).'\n';
			$software = get_software_info();
			$os = lsb();
			$data .= display_software($os,$software).'\n';
			$disk_info = get_disk_info();
			$data.= display_disk($disk_info).'\n';
			$mem_info = get_mem_info();
			$data .= display_mem($mem_info,True).'\n';
			$user_info = get_user_info($disk_info);
			$data .= display_user($user_info);
			echo $data;
			exit;
	case "version":
			echo 'Ajax version 1.4';
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
			chdir($detail['location'].'/serverfiles');
			$logFile = $detail['location'].'/log/console/'.$detail['host_name'].'-console.log' ;
			$savedLogfile = $detail['location'].'/log/console/'.$detail['host_name'].'-'.date("d-m-Y").'-console.log' ;
			rename($logFile, $savedLogfile);	
			//$cmd = 'screen -L -Logfile '.$detail['Location'].'/log/console/'.$detail['host_name'].'-console.log -dmS '.$detail['host_name'].' bash -c "'.$detail['startcmd'].'^M"'; //start server
			//$cmd = 'screen -L -Logfile '.$detail['location'].'/log/console/'.$detail['host_name'].'-console.log -dmS '.$detail['host_name'];
			$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$detail['host_name'];
			exec($cmd); // open session
			$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "cd '.$detail['location'].'/serverfiles^M"';
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
			    chdir($detail['location'].'/serverfiles');
				$cmd = 'screen -L -Logfile '.$logFile.' -dmS '.$detail['host_name']; 
				exec($cmd); // open session
				$cmd = 'screen -S '.$detail['host_name'].' -p 0  -X stuff "cd '.$detail['location'].'/serverfiles^M"';
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
			//echo $disp;
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
	$db = new db();
	$t =trim(shell_exec('ps -C srcds_linux -o pid,cmd |sed 1,1d'));
    $tmp = explode(PHP_EOL,$t);
	$i=0;
	if(strlen($t) === 0) {
                
                $ip = file_get_contents("http://ipecho.net/plain"); // get ip
                $sql = 'select  servers.location,count(*) as total from servers where servers.host like "'.substr($ip,0,strlen($ip)-1).'%"';
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
		$result = reset($db->get_results($sql)); // get data back
		$sql = 'select  count(*) as total from servers where servers.host like "'.substr($tmp_array[$i][6],0,strlen($tmp_array[$i][6])-1).'%"'; // query 2 count the game servers
		$server_count= reset($db->get_results($sql)); // get data back
		$count = count($top); // how many records  ?
		$mem += $top[$count-3]; // memory %
		$cpu += $top[$count-4]; // cpu % 
		$du = shell_exec('du -s '.$result['location']); // get size of game
		list($size, $location) = explode(" ", $du); // drop to variables
		$result['mem'] = $top[$count-3];
		$result['cpu'] = $top[$count-4];
		$result['size'] = formatBytes($size*1024,2);
		$return[$result['host_name']] = $result;
		$du = shell_exec('du -s '.dirname($result['location']));
		list ($tsize,$location) = explode(" ",$du);
		$i++;
	}	
	}
	// add computed items 
	$return['general']['live_servers'] = $i;
	$return['general']['total_servers'] = $server_count['total'];
	$return['general']['mem'] = round($mem,2,PHP_ROUND_HALF_UP);
	$return['general']['cpu'] = round($cpu,2,PHP_ROUND_HALF_UP);
	$return['general']['total_size'] = formatBytes($tsize*1024,2);
	return $return;
}
?>