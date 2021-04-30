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
 *  developed using PHP V8.0.3
 * tested to work on PHP V7.4.3
 * 
 */
$run_path = dirname($_SERVER['PHP_SELF'],2); // these guys should be in the dir above
echo $run_path.PHP_EOL;
exec('cat /proc/mounts |grep gvfsd-fuse',$tmp,$rval);
if ($tmp) {
$tmp =explode(' ',$tmp[0]);
$installing['gvfs'] = trim($tmp[1]);
}
clean_up();
if (!defined('DOC_ROOT')) {
	define('DOC_ROOT',dirname(__DIR__));
}
 require_once DOC_ROOT.'/includes/master.inc.php';
 require_once DOC_ROOT.'/functions.php';
 define ('cr',PHP_EOL);
 define('CR',cr);
 define ('VERSION',2.02);
 define ('quit','<ctl-c> to quit ');
  echo 'defines done'.cr;
    require_once DOC_ROOT.'/includes/class.table.php';
    require_once DOC_ROOT.'/includes/class.color.php';
    echo 'tables done'.cr;
$cc = new Console_Color2();
$tick = $cc->convert("%g✔%n");
$cross = $cc->convert("%r✖%n");
 define ('green_tick',$tick);
 define ('red_cross',$cross);
 define ('warning',$cc->convert("%rWarning %n"));
 $cmds =convert_to_argv($argv,"",true);
 $steam_i = false;
 system('clear');
  $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_RIGHT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
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
//$table->addRow(array('','',''));
$table->addRow(array('Mount Point','Free Space' ));
 echo 'Checking available disk space'.cr;
 $diskinfo = get_disk_info();
 if (isset($diskinfo['boot_free'])) {
	 //echo $diskinfo['boot_mount'].' ( '.$diskinfo['boot_free'].' free )'.cr;
	 $table->addRow(array($diskinfo['boot_mount'],$diskinfo['boot_free']));
	 
 }
 if(isset($diskinfo['home_free']))  {
	 //echo $diskinfo['home_mount'].' ( '.$diskinfo['home_free'].' free )'.cr;
	 $table->addRow(array($diskinfo['home_mount'],$diskinfo['home_free']));
	 }
 echo $table->getTable();
 if(!root()) {
 echo 'Checking user capabilities';
 $user = get_user_info($diskinfo);
 //print_r($user);
 if($user['level'] == 1 || root()) {$user_level = ', Privilege OK';}
 else { $user_level =', '.warning.'user privilege level low, the installer will run in safe mode.'; }
 echo $user_level.cr;
 $installing = $user;

}
else {
	//
	echo 'Hi Root, you need to supply a valid user and group for the install,'.cr;
	echo 'However if you are doing an install that you control & your users are symlinked to the install, enter root as the user'.cr.cr;
	$answer = trim(ask_question('enter target user '.quit,NULL,NULL)); 
	$installing['base_user'] = trim($answer);
	
}
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
	 exec(DOC_ROOT.'/utils/check_r.php '.$answer,$output,$ret_val);
	 $installing['disk_size'] = str_replace('Size on disk ','',$output[3]);
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
	 echo $output[1].cr;
	 	 
	 $installing['app_id'] = trim($answer);
	 $name = str_replace('Found ','',$output[1]);
	 $server = trim(str_replace('(released)',' ',$name));
	 $name = $server.' (y/n)';
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
		$installing = stage_6($installing);
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
				if ($x < 4) {
					$x++;
					continue;
					}
					echo $line.cr;
				}
				echo cr.'DO NOT choose a passworded branch, unless you have a valid key & password'.cr;
		$answer = ask_question('Choose a branch from the list above, press Enter for default or '.quit,NULL,null);
		$answer=trim($answer); 
		if ($answer =='') {
			$branch = 6;
		}
		else {
			$branch = array_search_partial($output, $answer).cr;
		}
		
		if ($branch < 5) {
			system('clear');
			echo 'Could not find a branch called \''.$answer.'\' retry '.cr; 
			goto redobranch;
			}
		$branch=trim($branch);
		$tmp = explode('     ',trim($output[$branch]));
		$tmp = tidy_array($tmp);
		$data['branch'] = $tmp[0];
		if(isset($tmp[3])) {
			//$data['password'] = true;
			$data['branch_password'] = ask_question(cr."Enter Branch Password or ".quit,null,null);
			}
		//else {$data['password'] = false;}
		return $data;
}
 
 function stage_2($data) {
	 // add stage 2 location
	 top:
	 $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
//$table->setHeaders(array('Installing ',$data['name'],' Stage 2: choose location'));
$table->addRow(array('','',''));
$table->addRow(array('Branch Selected',$data['branch'] ,green_tick));
	 system('clear');
	 echo 'Installing '.$data['name'].' Stage 2: choose location'.cr;
	 echo $table->getTable();
	$appinstalled = '';
	 
	  $maxlen = strlen($data['branch']);
	  $lmask = "%20.20s %-".$maxlen.".".$maxlen."s  %4.4s\n";
	  //printf($lmask,'Branch Selected',$data['branch'],green_tick);
		echo 'Current Location '.getcwd ( ).cr;
		echo 'adding a location that does not start with a \'/\' will create a location below the current location'.cr.cr;
		$path = ask_question('Enter the path to install '.$data['name'].' enter for current directory or '.quit.' ',NULL,NULL);
		$full_home = exec('echo ~');
		$path = trim(str_replace('~/',$full_home.'/',$path));
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
			
			echo cr.'The location '.$data['path'].' exists !'.cr;
					$ins = check_acf($path);
					if($ins){
						echo cr.'Found '.count($ins).' Games installed at '.$path.cr;
					foreach ($ins as $ins1) {
						if ($data['app_id'] == $ins1['appid']) { $appinstalled = $ins1;} 
						echo $ins1['name'].' '.$ins1['appid'].cr;
					}
				   }   
					if($appinstalled) {
						echo cr.$data['name'].' is already installed at this location'.cr;
						$answer = ask_question( 'Do you want to validate '.$data['name'].' ?  y/n '.quit,'y','n'); 
						if ($answer) {
							echo 'validate'.cr;
							$data['validate'] = true;
							$data['stage'] = 5;
							print_r($data);
							exit;
						}
					}
					else {
						echo cr.'This is not good practice to install dedicated servers to the same location proceed with caution'.cr; 
						echo 'This is not an error condition as some games can be installed to the same location and still work others will break other games installed'.cr;
						echo '2 games that work in the same location are Fistfull of Frags & Counterstrike Source'.cr;
				}
				
				$answer = ask_question(cr."Do you want to install in ".$data['path'].' ? (Y/n) or '.quit,'y','n');
				if ($answer == false) {
					echo 'false'.cr;
					goto top ;
				}
				
				
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
	  $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
//$table->setHeaders(array('Installing ',$data['name'],' Stage 2: choose location'));
$table->addRow(array('','',''));
$table->addRow(array('Branch Selected',$data['branch'] ,green_tick));
$table->addRow(array('Install Location',$data['path'] ,green_tick));
	 system('clear');
	 //print_r($installing);
	 echo 'Installing '.$data['name'].' Stage 3: User & Password'.cr;
	 echo $table->getTable();
	
	 
	 if (isset($steam_user)) {
		 $table->addRow(array('User',$data['steam_user'] ,green_tick));
	 }
	
	 else {
		$steam_user = trim(ask_question(cr.'Enter Steam User Name, enter for anonymous or '.quit,NULL,NULL,false));
		if (empty(trim($steam_user))) {$steam_user = 'anonymous'; $data['steam_password'] = '';}
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
	 	 $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
//$table->setHeaders(array('Installing ',$data['name'],' Stage 2: choose location'));
$table->addRow(array('','',''));
$table->addRow(array('Branch Selected',$data['branch'] ,green_tick));
$table->addRow(array('Install Location',$data['path'] ,green_tick));
$table->addRow(array('User',$data['steam_user'] ,green_tick));
if(empty($data['steam_password'])) {
	$table->addRow(array('Password','Not Required' ,green_tick));
		// printf($lmask, 'Password','Not Required','✔');
	 }
	 else {
		 $table->addRow(array('Password','Set' ,green_tick));
		
	 }
	 system('clear');
	 //print_r($installing);
	 echo 'Installing '.$data['name'].' Stage 3: User & Password'.cr;
	 echo $table->getTable();
	 echo 'Review the information, if everything is correct press enter to install '.$data['name'].cr.cr;	
	 // use printf
	 
			$steam_user = trim(ask_question(cr.'Press enter to continue or '.quit,NULL,NULL,true));
	  	  return $data;
}

function stage_5($data)  {
	// do steamcmd
	top:
	system('clear');
	 echo 'Installing '.$data['name'].' Stage 5: Installation'.cr.cr;
	 echo 'This process may take some time, the installer may appear to hang with the prompt \'waiting for steamcmd to start\''.cr;
	 echo 'this normally indicates either steamcmd is updating itself or steamcmd is having a problem connecting to steam\'s servers'.cr.cr;
	 $cmd = 'screen -L -Logfile install.log -dmS install';
	 exec ($cmd,$screen,$retval);
	 if ($data['branch'] == 'public') {
        $branch = '';
}
else {
        $branch =' -beta '.$data['branch'];
        $data['validate'] = true;
}

	 if(!isset($data['validate'] )) {
			$cmd ='steamcmd +login '.$data['steam_user'].' '.$data['steam_password'].' +force_install_dir '.$data['path'].' +app_update '.$data['app_id'].$branch.' +quit';
		}
	else {
			$cmd ='steamcmd +login '.$data['steam_user'].' '.$data['steam_password'].' +force_install_dir '.$data['path'].' +app_update '.$data['app_id'].$branch.' validate +quit';
		}		
		
         $scmd = 'screen -S install -p 0  -X stuff "'.$cmd.'^M"';
         exec ($scmd); // get steamcmd running
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
    if (isset ($data['gvfs'])) {	$lsofcmd = 'lsof -e '.$data['gvfs'].' install.log';}
    else { $lsofcmd = 'lsof install.log';}
		$lsof = trim(shell_exec($lsofcmd));
   
    exec($cmd); //clear up the install terminal
   
	 while ($lsof) {
		 $lsof = trim(shell_exec($lsofcmd));
		 }
	$log =explode(PHP_EOL,file_get_contents('install.log'));
	$line= trim($oldline);
	$unread = false;
	 foreach ($log as $a) {
		if ($unread ) {
			$p1 = strpos($a, 'Success!');
				if ($p1 !== false) {
					$finish = "\e[38;5;82mSuccess\e[0m,".$data['name']." is fully installed at ".$data['path']."\e[0m";
					//echo $finish.cr;
					//echo 'yippee'.cr;
					$data['success'] = true;
					unlink('install.log');
					break;
					}
					else {
						$tmp = str_replace('(','',trim($a));
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
						}
						else {
							echo "$a blank ?".cr;
						}
					}
      
		}
        if (trim($a) == $line) {
                //echo 'found'.cr;
                $unread = true;
                }
}
$answer = ask_question(cr.$finish.cr.'press <enter> to configure the server or '.quit,NULL,NULL,true);
return $data; 
}

 function stage_6 ($data) {
	 // configure
	 exec('du -hs '.$data['path'],$du,$ret);
	 $x = strpos($du[0],'/');
	 $name = $data['name'];
	 $path = $data['path'];
	 $dir_size = trim(substr($du[0],0,$x-1));
	 top:
	 system('clear');
	 echo 'Installing '.$data['name'].' Stage 6: Configure Server'.cr.cr;
     echo "$name is installed at $path and has used $dir_size of disk space ".green_tick.cr;
     if(isset($data['host'])) { 
		 	 echo "Host Name set to ".$data['host'].' '.green_tick.cr;
		 }
	 echo "Let's configure your server for use".cr;
	 if (!isset($data['host'])) {
	 $host = ask_question('Server Host Name ',NULL,NULL,false);
	 if (trim($host) =='') {
		 echo 'not a good idea to have a blank host name !'.cr.'Let\'s try again';
		 sleep (3);
		 goto top;
	 }
	 
	 // host name set 
	 $data['host']  = trim($host);
	 goto top;
	}
	 $rcon_password = ask_question('Enter a password for RCON or leave blank for a generated password ',NULL,NULL,false,true);
	 if (empty(trim($rcon_password))) {
		 $data['rcon'] = randomPassword();
	 }
	 else {
		 $data['rcon'] = $rcon_password;
	 }
	 echo cr; 
	 echo print_r($data,true).cr;
	 	 
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

function check_acf ($path) {
	// read files
	$r = 0;
	foreach (glob($path."/steamapps/*.acf") as $filename) {
    //echo "$filename size " . filesize($filename) . "\n";
    $tmp = file($filename);
   		foreach ($tmp as $key=>$value) {
        // clear blanks
        if(empty(trim($value))) 
        { 
                unset ($tmp[$key]);
                continue;
                }
        else {
                $value =substr(trim($value),1);
                $z = strpos($value,'"');
                $nz = substr($value,0,$z);
                $value =trim(str_replace($nz.'"','',$value));
        $value=trim(str_replace('"','',$value));
        $tmp[$key]=$value;
        $return[$nz]=$value;
        }       
        }
        //print_r ($return);
        $x[$r]['appid'] = $return['appid'];
        $x[$r]['name'] = $return['name'];
        $x[$r]['sizeondisk'] = $return['SizeOnDisk'];
        $x[$r]['path'] = $filename;
		$r++;
		
}
//print_r ($x);
return $x;
}	 

function clean_up() {
	// did it crash ?
	if(is_file('install.log')) {
		unlink('install.log');
	}
	exec('screen -ls |grep install',$screen,$ret);
	if (isset($screen[0])) {
		// we have an unwanted screen
		$cmd = 'screen -X -S install -p 0 -X stuff "exit^M"';
		exec($cmd);
	}
}
?>
