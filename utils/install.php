<?php
/*
 * install.php
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
 * 
 */
$run_path = dirname($_SERVER['PHP_SELF'],2); // these guys should be in the dir above
echo $run_path.PHP_EOL;
if ($run_path == '.') { $run_path = '..';} // opps perhaps not
// test the file is in PHPgsm location 

 include $run_path.'/includes/master.inc.php';
 include DOC_ROOT.'/functions.php';
 define ('cr',PHP_EOL);
 define ('VERSION',2.01);
 define ('quit','ctl+c to quit ');
 define ('green_tick',"\e[1m\e[31m ✔ \e[0m");
 
 $cmds =convert_to_argv($argv,"",true);
 $steam_i = false;
 system('clear');
 $quit ='(ctl+c to quit) '; 
  echo 'Welcome to PHPgsm Game Installer '.VERSION.cr;
  if (isset($cmds)){
  if ($cmds['action'] == 'show') { 
	  list_games();
	  $rerun = ask_question('Run install ? (y/n) ','y','n');
	  $steam_i = true;
	  if (!$rerun) { exit;}
  }
}
 echo 'Checking available disk space';
 $diskinfo = get_disk_info();
 if(isset($diskinfo['home_free'])) {$space = ' ('.$diskinfo['home_free'];}
 else { $space = ' ('.$diskinfo['boot_free'];}
 echo $space.' free)'.cr;
 echo 'Checking user capabilities';
 $user = get_user_info($diskinfo);
 //print_r($user);
 if($user['level'] == 1 || root()) {$user_level = ', Privilege OK';}
 else { $user_level =', user privilege to low, get an administrator to run this script.'; echo $user_level.cr;exit;}
 echo $user_level.cr;
  $steamcmd = trim(shell_exec('which steamcmd'));
 if (empty($steamcmd)) {
	 echo 'steamcmd not found in the user path, is it installed ?'.cr;
	 echo 'either install steamcmd or add steamcmd to your path & re-run install'.cr;
	 exit;
 }
 
 $n='no';
 if ($steam_i) {$question = 'Please Enter The Steam Id of the Game Server to install ';}
 else { $question = 'Please Enter The Steam Id of the Game Server to install or C for non steam or ';}
 $answer = trim(ask_question($question.quit,NULL,$n)); 

 if (is_numeric($answer)) {
	 rerun:
	 echo 'Please wait checking steam for server ID '.$answer;//.cr;
	 exec($run_path.'/utils/check_r.php '.$answer,$output,$ret_val);
	 $x=0;
	 echo $ret_val.cr;
	 switch ($ret_val) {
		 case 7:
		 //echo $output[1].cr;
		 echo 'No Information found for dedicated server ID '.trim($answer).' are you sure the id is correct ?'.cr;
		 echo 'Tip: run \''.$argv[0].' action=show\' . This will list steam dedicated servers that we know work with PHPgsm, installing a server not on this list could still work.'.cr;
		  
		 exit;
		 case 134:
		 echo $output[1].cr;
		 $rerun = ask_question('Retry (y/n) ','y','n');
		 if ($rerun) {$ret_val=0; $output = array(); goto rerun;} 
		 exit;
	 }
	 //echo $ret_val.cr;
	 //print_r($output);
	 //stage 1
	 foreach ($output as $line) {
		 
		 if ($x < 2) {
			echo $line.cr;
			$x++;
	 }
	 }
	 $installing['app_id'] = trim($answer);
	 $name = str_replace('Found ','',$output[1]);
	 $server = trim(str_replace('(released)',' ',$name));
	 $name= $server.' (y/n)';
	 $installing['name'] = trim($server);
	 echo cr;
    $answer = ask_question('Do you want to install '.trim($name).' ? ','y','n');
	//echo $answer.cr;
	if ($answer) {
		$installing = stage_1($installing);
		$installing = stage_2($installing);
		$installing = stage_3($installing);
		$installing = stage_4($installing);
		$installing = stage_5($installing);
		//$installing = stage_6($installing);
		echo print_r($installing,true).cr;
		exit;

    }
}
 
 else {
	 echo 'custom mode'.cr;
	 echo 'currently this function is not enabled'.cr;
 }
 
 function list_games() {
	 // show what we know
	 echo 'Games List'.cr;
	 global $database;
	 $sql ="select * from game_servers where is_steam = true";
	 $list = $database->get_results($sql);
	 $max = '';
$maxlen = 0;

foreach ($list as $temp ) {
    $len = strlen($temp['game_name']);
//echo $t.cr;
    if ($len > $maxlen) {
        $maxlen = $len;
        //$max = $elm;
    }
}

	 //print_r($list);
	 $headmask = "%".$maxlen.".".$maxlen."s %14.14s \n";
	 $mask = "%".$maxlen.".".$maxlen."s %12.12s  \n";
	 printf($headmask,'Server Name','    Server ID');
	 foreach ($list as $game) {
		 
		 //echo $game['game_name'].'  '.$game['server_id'].cr;
		 printf($mask,$game['game_name'],$game['server_id']);

	 }
	 echo 'use one of the above server ID\'s '.cr;
 }
 
 function stage_1($data) {
	 // add stage 1 game branch
	 global $output;
	 system('clear');
		redobranch:
			echo 'Installing '.$data['name'].' Stage 1: Choose Branch'.cr.cr;
		$x=0;
			 foreach ($output as $line) {
				if ($x < 3) {
					$x++;
					continue;
					}
					echo $line.cr;
				}
				echo cr.'DO NOT choose a passworded branch, unless you have a valid key & password'.cr;
		$answer = ask_question('Choose a branch from the list above, press Enter for default or '.quit,NULL,'n',true);
		$answer=trim($answer); 
		if ($answer =='') {
			$branch = 5;
		}
		else {
			$branch = array_search_partial($output, $answer).cr;
		}
		
		if ($branch < 4) {
			system('clear');
			echo 'Could not find a branch called \''.$answer.'\' retry '.cr; 
			goto redobranch;
			}
		$branch=trim($branch);
		$tmp = explode('     ',trim($output[$branch]));
		$tmp = tidy_array($tmp);
		$data['branch'] = $tmp[0];
		if(isset($tmp[3])) {$data['password'] = true;}
		else {$data['password'] = false;}
		return $data;
}
 
 function stage_2($data) {
	 // add stage 2 location
	 system('clear');
	 echo 'Installing '.$data['name'].' Stage 2: choose location'.cr.cr;
	  $maxlen = strlen($data['branch']);
	  $lmask = "%20.20s %-".$maxlen.".".$maxlen."s  %4.4s\n";
	  printf($lmask,'Branch Selected',$data['branch'],'✔');
		echo 'adding a location that does not start with a \'/\' will create a location below the current location'.cr.cr;
		$path = ask_question('Enter the path to install '.$data['name'].' enter for current directory or '.quit.' ',NULL,NULL,true);
		$path = trim(str_replace('~/',exec('echo ~').'/',$path));
		if(empty($path)) {
									$data['path'] = getcwd();
								}
		else {
			//check for a /
			if ($path[0] <> '/') {
				//do something
				$path = getcwd().'/'.$path;
			}
			$data['path'] = $path;
		}
		if (file_exists($path)) {
				$answer = ask_question(cr.'The location '.$data['path'].' exists !'.cr.cr."Do you want to install in ".$data['path'].' ? (Y/n) or '.quit,NULL,NULL,true);
				if ($answer || $answer == true) {
					return $data;
					} 
			} else {
				$answer = ask_question(cr.$data['name'].' will be installed to '.trim($data['path']).cr.cr.' press enter to continue or '.quit,NULL,NULL,true);
				return $data;
		}
 }
 
 function stage_3($data) {
	 // part 3
	 
	 top:
	 system('clear');
	 //print_r($installing);
	 echo 'Installing '.$data['name'].' Stage 3: User & Password'.cr.cr;
	 // use printf
	 $maxlen = strlen($data['path']);
	 $lmask = "%20.20s %-".$maxlen.".".$maxlen."s  %4.4s\n";
	 $rmask = "%20.20s %".$maxlen.".".$maxlen."s  %4.4s\n";
	 printf($lmask,'Branch Selected',$data['branch'],'✔');
	 printf($rmask, 'Location Selected',$data['path'],'✔');
	 
	 if (isset($steam_user)) {
		 printf($lmask, 'User Selected',$data['steam_user'],'✔');
	 }
	
	 else {
		$steam_user = trim(ask_question(cr.'Enter Steam User Name, enter for anonymous or '.quit,NULL,NULL,true));
		if (empty(trim($steam_user))) {$steam_user = 'anonymous';}
		$data['steam_user'] = $steam_user;
		
		goto top;}
 
		if ($data['steam_user'] != 'anonymous') {
		redopassword:	
		$steam_password = trim(ask_question('Enter Steam password for User Name, '.$data['steam_user'].' or '.quit,NULL,NULL,false,true));
		echo cr;
		$steam_password2 = trim(ask_question('Re enter Steam password for User Name, '.$data['steam_user'].' or '.quit,NULL,NULL,false,true));
		echo cr;
		if ($steam_password === $steam_password2) {
			echo 'Password set'.cr;
			$data['steam_password'] = $steam_password;
		}
		else {
			echo 'Password checks do not match - try again'.cr;
			goto redopassword;
		}
		
	}
	return $data;
}

function stage_4($data) {
	// stage 4
	 top:
	 system('clear');
	 echo 'Installing '.$data['name'].' Stage 4: Review Installation'.cr.cr;
	 echo 'Review the information, if everything is correct press enter to install '.$data['name'].cr.cr;	
	 // use printf
	 $maxlen = strlen($data['path']);
	 $lmask = "%20.20s %-".$maxlen.".".$maxlen."s  %4.4s\n";
	 $rmask = "%20.20s %".$maxlen.".".$maxlen."s  %4.4s\n";
	 printf($lmask,'Branch',$data['branch'],'✔');
	 printf($rmask, 'Location',$data['path'],'✔');
	 printf($lmask, 'User',$data['steam_user'],'✔');
	 if(empty($data['steam_password'])) {
		 printf($lmask, 'Password','Not Required','✔');
	 }
	 else {
		 printf($lmask, 'Password','Set','✔');
	 }
			$steam_user = trim(ask_question(cr.'Press enter to continue or '.quit,NULL,NULL,true));
	  	  return $data;
}

function stage_5($data)  {
	// do steamcmd
	top:
	system('clear');
	 echo 'Installing '.$data['name'].' Stage 5: Installation'.cr.cr;
	 echo 'This process may take some time, the installer may appear to hang with the prompt \'waiting for steamcmd\''.cr;
	 echo 'this normally indicates either steamcmd is updating itself or steamcmd is having a problem connecting to steam\'s servers'.cr.cr;
	 $cmd = 'screen -L -Logfile install.log -dmS install';
	 exec ($cmd,$screen,$retval);
	 $cmd ='steamcmd +login '.$data['steam_user'].' +force_install_dir '.$data['path'].' +app_update '.$data['app_id'].' +quit';
         $scmd = 'screen -S install -p 0  -X stuff "'.$cmd.'^M"';
         exec ($scmd); // got steamcmd running
         sleep (1); // wait for ps
	
     $ps = shell_exec('ps -el | grep steamcmd');
	 $psa = tidy_array(explode(' ',$ps)); // ps data including the pid
      if (isset($psa[3])) {
         $pid = $psa[3];
         $oldline= '';
	     echo 'Waiting for steamcmd to start'.cr;
         while (file_exists( "/proc/$pid" )){
			$file = "install.log";
			$fdata = file($file);
			if (isset($fdata[count($fdata)-1])) {
				$line = $fdata[count($fdata)-1];
				if (strpos($line,cr)) {
					if ($line  != $oldline) { 
						$tmp = str_replace('(','',trim($line));
						$tmp = str_replace(')','',$tmp);
						$steamlog = tidy_array(explode(' ',$tmp));
						if (isset($steamlog[3])) {  
						$downloading = $steamlog[3].' '. $data['name'];
						$dl = strlen($downloading); // server length
						$mask = "%".$dl.".".$dl."s %25.25s %-40s \n";
						$current =  floatval($steamlog[6]);
						$percent = $steamlog[5].'%';
						$current = formatBytes($current,2);
						$total =  formatBytes(floatval($steamlog[8]),2);
						printf($mask,$downloading,"$current out of $total","$steamlog[4] $percent");
						$oldline =$line;
					    }
					}
				}
			}
		 }
	 }
	 
    $cmd = 'screen -X -S install -p 0 -X stuff "exit^M"';
    //$lsof = trim(shell_exec('lsof -e /run/user/1000/gvfs install.log'));
    $lsof = trim(shell_exec('lsof  install.log'));
    exec($cmd); //clear up the install terminal
	 while ($lsof) {
		 $lsof = trim(shell_exec('lsof install.log'));
		 }
	$log =explode(PHP_EOL,file_get_contents('install.log'));
	$line= trim($oldline);
	$unread = false;
	 foreach ($log as $a) {
		if ($unread ) {
			$p1 = strpos($a, 'Success!');
				if ($p1 !== false) {
					echo $a.cr;
					echo 'yippee'.cr;
					$data['success'] = true;
					unlink('install.log');
					break;
					}
					
      
		}
        if (trim($a) == $line) {
                //echo 'found'.cr;
                $unread = true;
                }
}
return $data; 
}

 function stage_6 ($data) {
	 // configure
	 top:
	 system('clear');
	 echo 'Installing '.$data['name'].' Stage 6: Configure Server'.cr.cr;
	 echo "Let's configure your server for use".cr;
	 $host = ask_question('Your Server Name ',NULL,NULL,true);
	 if (trim($host) =='') {
		 echo 'not a good idea to have a blank host name !'.cr.'Let\'s try again';
		 sleep (3);
		 goto top;
	 }
	 // host name set 
	 $config['host']  = trim($host);
	 $rcon_password = ask_question('Enter a password for RCON or leave blank for a generated password ',NULL,NULL,true);
	 if (empty(trim($rcon_password))) {
		 $config['rcon'] = randomPassword();
	 }
	 else {
		 $config['rcon'] = $rcon_password;
	 }
	  
	 print_r($config);	 
 }
 
 
 function tidy_array($array) {
	 foreach ($array as $k => $v) {
		 //loop
		 if(trim($v) == '') {unset($array[$k]);}
		 $array[$k] = trim($v); 
	 }
	 $return = array_values($array);
	 return $return;
 }

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}		 
?>
