<?php
function get_boot_time() {
    $tmp = explode(' ', file_get_contents('/proc/uptime'));
   
//Combined
$days = floor($tmp[0]/86400);
$hours = floor($tmp[0] / 3600);
$mins = floor(($tmp[0] - ($hours*3600)) / 60);
$secs = floor($tmp[0] % 60);
if($days>0){
          //echo $days;exit;
          $hours = $hours - ($days * 24);
          $hrs = str_pad($hours,2,' ',STR_PAD_LEFT);
          
          if ($days >1){
          $return_days = " Days ";
	  }
	  elseif ($days = 1) {
		  $return_days = " Day ";
	  }
	  //hours
     }
     else {
      $return_days="";
      //$hrs = str_pad($hours,2,'0',STR_PAD_LEFT);
      $days="";
     
     }

     //$mins = str_pad($mins,2,'0',STR_PAD_LEFT);
     
     $sec = str_pad($secs,2,'0',STR_PAD_LEFT);
      $hrs = $hours;
     if ($hours > 1) {
		 $return_hours = " hours ";
		 }
	elseif ($hours === 1) {
		$return_hours = " hour ";
		//$hrs ="";
	}	 
	else {
		$return_hours ="";
		$hrs ="";
		//echo '0 hours'.CR;
	}	 
     if ($mins > 1) {
		 $return_mins = " mins ";
		 }
	elseif ($mins = 1) {
		$return_mins = " mins ";
		//$hrs ="";
	}	 
	else {
		$return_mins =" mins";
		$mins ="00";
	}	      
return  $days.$return_days.$hrs.$return_hours.$mins.$return_mins.$sec." seconds";

    //return ;
}
function is_cli()
{
    if ( defined('STDIN') )
    {
        return true;
    }

    if ( php_sapi_name() === 'cli' )
    {
        return true;
    }

    if ( array_key_exists('SHELL', $_ENV) ) {
        return true;
    }

    if ( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) 
    {
        return true;
    } 

    if ( !array_key_exists('REQUEST_METHOD', $_SERVER) )
    {
        return true;
    }

    return false;
}
function root() {
	/*
	 * checks for root user not sudo user
	 * see check_sudo for priv user
	 */ 
 if (posix_getuid() === 0){
	 // root user
        return true;
   } 
   else {
       // non root user use check_sudo !
         return false;
}
}
function check_sudo($user)
{

$user=trim($user);
$j= shell_exec('getent group sudo | cut -d: -f4');
$yes= strpos($j, $user);
if ($yes ===0 or $yes>1) {
return true;
}
else {   
return false;
}
}
function lsb() {
$os = trim(shell_exec ("cat /etc/os-release"));
$os = str_replace('"',"",$os);
//echo $os.CR;
$os = explode(PHP_EOL,$os);
//print_r ($os);
foreach ($os as &$value) {
			//read data
			$i = strpos($value,"=",0);
            $key = trim(substr($value,0,$i));
		    $nos[$key] = trim(substr($value,$i+1));
		}
//print_r($nos);
return $nos ;
}
function get_disks(){
    if(php_uname('s')=='Windows NT'){
        // windows
        $disks=`fsutil fsinfo drives`;
        $disks=str_word_count($disks,1);
        if($disks[0]!='Drives')return '';
        unset($disks[0]);
        foreach($disks as $key=>$disk)$disks[$key]=$disk.':\\';
        return $disks;
    }
    
    else{
        // unix
        $data=`mount`;
        $data=explode(' ',$data);
        $disks=array();
        foreach($data as $token)if(substr($token,0,5)=='/dev/')$disks[]=$token;
        return $disks;
    }
}
function getSymbolByQuantity($bytes) {
    $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $exp = floor(log($bytes)/log(1024));
    echo $exp."\r\n";
    //exit;
     $space =$symbol[$exp]+($bytes/pow(1024, floor($exp)));
    return $space;
}
function dataSize($Bytes)
{
$Type=array("", "K", "M", "G", "T");
$counter=0;
while($Bytes>=1024)
{
$Bytes/=1024;
$counter++;
}
$Bytes= round($Bytes,2);
return("".$Bytes." ".$Type[$counter]."B ");
}
function getSql()
{
	ob_start(); 
phpinfo(INFO_MODULES); 
$info = ob_get_contents(); 
ob_end_clean(); 
$info = stristr($info, 'Client API version'); 
preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match); 
$gd = $match[0]; 
//echo 'MySQL:  '.$gd.' <br />'; 
return $gd ;
}
function get_mem_info() {
	// return info from proc/meminfo
	$free = file('/proc/meminfo');
	
	foreach ($free as &$value) {
		//echo $value.CR;
		 $i = strpos($value,":",0);
         $key = substr($value,0,$i);
		 preg_match_all('!\d+!', $value, $matches);
		 //echo dataSize(implode(' ', $matches[0])*1024).CR;
		
		 $temp = trim(implode(' ', $matches[0])*1024);

		 $mem_info[$key] = formatBytes($temp);
		 
	}
	$maxlen = max(array_map('strlen', $mem_info));
	//echo "max len ".$maxlen.CR;
	$maxlen = 14;
	foreach ($mem_info as $key=>&$value){
		//check len
		 $len = strlen($value);
		 if ($len < $maxlen) {
			 //pad
			 $pad = $maxlen-$len;
             //echo "pad by ".$pad.CR;
             $mem_info[$key] = str_pad ( $value , $pad ," ", STR_PAD_LEFT);		
	}
}
foreach ($mem_info as $key=>&$value){
		//check len
		 $len = strlen($value);
		 if ($len = $maxlen) {
			 //pad
			 //$pad = $maxlen-$len;
             //echo " ".$len.CR;
             $mem_info[$key] = str_pad ( $value , $pad ," ", STR_PAD_LEFT);		
	}
}
	return $mem_info;
		
}

function get_cpu_info() {
	//get cpu info & return as array
	$cpu = file('/proc/cpuinfo');
		
		foreach ($cpu as &$value) {
			//read data
			$i = strpos($value,":",0);
            $key = trim(substr($value,0,$i));
		 if (strlen($key) === 0) {
			 // only take the first processor
			 break;
		 }
		  $cpu_info[$key] = trim(substr($value,$i+1));
		}
		$cpu_info['processors'] = trim(shell_exec(" grep -c ^processor /proc/cpuinfo")); // count processors
		$load = sys_getloadavg();
		$cpu_info['load'] = $load[0]." (1 min)  ".$load[1]." (10 Mins)  ".$load[2]." (15 Mins)";
		$cpu_info['boot_time'] = get_boot_time();
		$cpu_info['local_ip'] = getHostByName(getHostName());
		return $cpu_info;
}

function get_user_info ($Disk_info) {
	// return user info as an array
	//print_r($Disk_info);
	$user['name'] = trim(shell_exec("whoami"));
	$q = shell_exec("quota -vs 2> /dev/null");
	$cmd = "du -hs /home/".trim($user['name'])." 2> /dev/null";
	$du = trim(shell_exec($cmd)); //"du -hs /home/jim 2> /dev/null"
	$du = explode("\t",$du);
	//print_r ($du).CR;
	if(empty($q)) {
		
		//echo "Quota Not installed".CR;
		$user['quota used'] = format_num($du[0]); 
		$user['quota'] = 'Unlimited';
		$user['quota free'] = $Disk_info['home free'];
	}
	else {
		// run quota
		$q = explode("  ",$q);
	    $user['quota'] = dataSize(intval($q[15]) * 1000000);
	    $user['quota used'] = dataSize(intval($q[14]) * 1000000);
	    $user['quota free'] = dataSize(intval($q[15]) * 1000000-intval($q[14]) * 1000000);
	}
	//print_r($user);
	return $user;    
	
}

function get_software_info() {
	// return software info as array
	 $php_version = explode('.', PHP_VERSION);
	 $software['php'] = $php_version[0].'.'.$php_version[1].'.'.intval($php_version[2]);
	 $software['glibc'] = trim(shell_exec("ldd --version | sed -n '1s/.* //p'"));
	 $apache = shell_exec("apache2 -v");
	 $software['apache'] = substr($apache,23,6);
	 $software['tmux'] = trim(shell_exec("tmux -V | awk -F'[ ,]+' '{print $2}'"));
	 $software['mysql'] = trim(shell_exec("mysql -V | awk -F'[ ,]+' '{print $5}'"));//getsql();
	 $nginx = shell_exec("nginx -v 2> nginx.txt");
	 $nginx = file_get_contents("nginx.txt");
	 if(strpos($nginx,"not found")){
		 $software['nginx'] = 'Not Installed';
		 //echo "hit here".CR;
	 }
	 else {
		 $nginx = explode(":",$nginx);
		 $software['nginx'] = trim($nginx[1]);
	 }
	  unlink("nginx.txt");
	  $q = shell_exec("quota -V 2> /dev/null");
	  if(empty($q)) {
		  $software['quota'] = "Not Installed";
	  }
	  else {
		  // get quota version
		  $software['quota'] =  substr($q,24,4);
	  }
	  $postfix = shell_exec("postconf -d mail_version 2> /dev/null");
	  $postfix = explode("=",$postfix);
	  if (!empty($postfix[1])) {
		  $software['postfix'] = trim($postfix[1]);
	  }
	  else {
		  $software['postfix'] = "Not Installed";
	  }
	  $screen = shell_exec(" screen -v  2> /dev/null| awk -F'[  ]+' '{print $3}' " ); 
	  if (!empty($screen)) {
		  $software['screen'] = $screen;
	  }
	  else {
		  $software['screen'] = "Not Installed";
	  }
	 return $software;
}

function get_disk_info() {
	// return disk info as array
	$disks = shell_exec("lsblk -l");
	$boot = shell_exec("df -h /boot");
	$home = shell_exec("df -h /home");
	$root = shell_exec("df -h /");
	if ($root === $home) {
		//echo 'one disk'.CR;
		$disk_info['disk'] = $root;
				if(strstr($boot, PHP_EOL)) {
		// test for line break
		//echo "line break".CR;
		$boot = explode(" ",trim(strstr($root, PHP_EOL)));
		//print_r($boot).CR;
		$boot=array_filter($boot);
		$boot = array_slice($boot, 0);
		//print_r($boot).CR;
		$disk_info['boot filesystem'] = $boot[0];
		$disk_info['boot size'] = format_num($boot[1]);
		$disk_info['boot used'] = format_num($boot[2]);
		$disk_info['boot free'] = format_num($boot[3]);
		$disk_info['boot %'] = $boot[4];
		$disk_info['boot mount'] = $boot[5];
		$disk_info['boot hide'] = "ok";
		
	}
}
	else {
		if(strstr($boot, PHP_EOL)) {
		// test for line break
		//echo "line break".CR;
		$boot = explode(" ",trim(strstr($boot, PHP_EOL)));
		$boot=array_filter($boot);
		$boot = array_slice($boot, 0);
		//echo 'new str '.$new_str.CR;
		$disk_info['boot filesystem'] = $boot[0];
		$disk_info['boot size'] = format_num($boot[1]);
		$disk_info['boot used'] = format_num($boot[2]);
		$disk_info['boot free'] = format_num($boot[3]);
		$disk_info['boot %'] = $boot[4];
		$disk_info['boot mount'] = $boot[5];
	}	
	if(strstr($home, PHP_EOL)) {
		$home1 = explode(" ",trim(strstr($home, PHP_EOL)));
		$home1 = array_filter($home1);
		
		$home1 = array_slice($home1,0);
		//print_r($home1);
		$disk_info['home filesystem'] = $home1[0];
		$disk_info['home size'] = format_num($home1[1]);
		$disk_info['home used'] = format_num($home1[2]);
		$disk_info['home free'] = format_num($home1[3]);
		$disk_info['home %'] = $home1[4];
		$disk_info['home mount'] = $home1[5];
	}
		// test for line break
		//$disk_info['boot'] = $boot;
		//$disk_info['root'] = $root;
		//$disk_info['home'] = $home;
	}
	//print_r($disk_info);
	//unset ($disk_info['boot']);
	return $disk_info;
}
function format_num ($string) {
	// format df & du
	$num = array(0,1,2,3,4,5,6,7,8,9);
	$unit = str_replace($num, null, $string);
	$string = intval($string);
	if ($unit =="B") {
		$string = $string." ".$unit;
	}
	else {
		$string = $string." ".$unit.'B';
	}
	return $string;
}
function ask_question ($salute,$positive='yes',$negative='no',$press_any_key=false) {
	//if ($positive = "null") { unset($positive);}
	run:
echo $salute;
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if ($press_any_key === true) {
	return trim($line);
}
if ($line === PHP_EOL) {
	errors:
	echo "You must have a valid response".CR;
	unset($line);
	goto run;
}
//if (preg_match('/\s/',trim($line)) ) {
if (ctype_space($line)) {
	echo "ERROR response contains spaces".CR; 
	goto errors;
	}
if ($positive <>"null"){	
	if(trim($line) !== $positive){
		echo "ABORTING!".CR;
		echo trim($line).CR;
		echo $positive.CR;
        exit;
	}
}

echo CR;
echo CR.trim($line).CR;
echo "Thank you, continuing...".CR;
}
function display_mem($mem_info,$colour) {
	// mem display
	if ($colour === true) {
		//echo "colour".CR;
		echo "\t\e[1m\e[31m Memory\e[0m".CR;
		echo "\t\t\t\e[1m \e[34m Total\t\t    Free\t   Cached\t   Active\e[97m".CR;
		echo "\t\t\e[38;5;82mMem\t\e[97m".$mem_info['MemTotal']."\t". $mem_info['MemFree']."\t".$mem_info['Cached']."\t".$mem_info['Active'].CR;
		echo "\t\t\e[38;5;82mSwap\t\e[97m".$mem_info['SwapTotal']."\t". $mem_info['SwapFree']."\t".$mem_info['SwapCached']."\e[0m".CR.CR;
}
else {
	//bw
	//echo "bw".CR;
		echo "\t Memory".CR;
		echo "\t\t\tTotal\t\t Free\t\t Cached\t\tActive".CR;
		echo "\t\tMem\t".$mem_info['MemTotal']."\t". $mem_info['MemFree']."\t".$mem_info['Cached']."\t".$mem_info['Active'].CR;
		echo "\t\tSwap\t".$mem_info['SwapTotal']."\t". $mem_info['SwapFree']."\t".$mem_info['SwapCached'].CR.CR;
}
}
function running_games($data) {
	// returns running games
	foreach ($data as &$value) {
			//read data
			$i = strpos($value,",",0);
            $key = trim(substr($value,0,$i));
		 if (strlen($key) === 0) {
			 // only take the first processor
			 break ;
		 }
		  $return[$key] = trim(substr($value,$i+1));
		}
		//print_r($return);
		return $return;
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B ', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
     $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision, PHP_ROUND_HALF_UP) . ' ' . $units[$pow];
    // $base = log($size, 1024);
    //$suffixes = array('', 'K', 'M', 'G', 'T');   

    //return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)]; 
} 
function display_software($os,$software) {
	// display software
	global $argv;
	if (isset($argv[2])) {
		echo CR." \r\n\e[1m \e[34mSoftware Information\e[0m".CR;
	}
	else {
	echo "\t\e[1m\e[31mSoftware\e[97m".CR;
}
	echo "\t\e[1m   Server".CR;
	echo "\t\t\e[38;5;82mServer OS        \e[97m".PHP_OS." (".$os['PRETTY_NAME'].")".CR;
	echo "\t\t\e[38;5;82mKernel Version   \e[97m".php_uname('r').CR;
	echo "\t\t\e[38;5;82mHost Name        \e[97m".php_uname('n').CR;
	echo "\t   Required".CR;
	echo "\t\t\e[38;5;82mPHP Version  \e[97m    " .$software['php'].CR;
	echo "\t\t\e[38;5;82mTmux Version     \e[97m".$software['tmux'].CR;
	echo "\t\t\e[38;5;82mGlibc Version\e[97m    " .$software['glibc'].CR;
	 echo "\t\t\e[38;5;82mMysql Version\e[97m    " .$software['mysql'].CR;
	echo "\t   Optional".CR;
    echo "\t\t\e[38;5;82mApache Version\e[97m   " .$software['apache'].CR;
    echo "\t\t\e[38;5;82mNginx Version\e[97m    " .$software['nginx'].CR;
    echo "\t\t\e[38;5;82mQuota Version\e[97m    " .$software['quota'].CR;
    echo "\t\t\e[38;5;82mPostFix Version\e[97m  " .$software['postfix'].CR;
    echo "\t\t\e[38;5;82mScreen Version\e[97m   " .$software['screen']."\e[0m".CR;
	
	}
function display_cpu ($cpu_info) {
	global $argv;
	if (isset($argv[2])) {
		echo CR." \r\n\e[1m \e[34mHardware Information\e[0m".CR;
	}
	else{
    echo "\t\e[1m\e[31mHardware\e[97m".CR;
}
    echo "\t\t\e[38;5;82mUptime         \t\e[97m".$cpu_info['boot_time'].CR;
    echo "\t\t\e[38;5;82mCpu Model      \t\e[97m".$cpu_info['model name'].CR;
    echo "\t\t\e[38;5;82mCpu Processors \t\e[97m".$cpu_info['processors'].CR;
    echo "\t\t\e[38;5;82mCpu Cores      \t\e[97m".$cpu_info['cpu cores'].CR;
    echo "\t\t\e[38;5;82mCpu Speed      \t\e[97m".$cpu_info['cpu MHz']. " MHz".CR;
    echo "\t\t\e[38;5;82mCpu Cache      \t\e[97m",$cpu_info['cache size'].CR;
    echo "\t\t\e[38;5;82mCpu Load       \t\e[97m".$cpu_info['load'].CR;
	echo "\t\t\e[38;5;82mIP Address\e[97m     \t".$cpu_info['local_ip']."\e[0m".CR;
	
}
function display_disk($disk_info) {
	echo CR."\e[1m \e[34m Disk Information\e[0m".CR;
	if (!isset($disk_info['boot hide'])) {echo "\t\e[1m\e[31m Boot\e[0m".CR;}
	echo "\t\t\e[38;5;82m\e[1mFile System\e[97m     ".$disk_info['boot filesystem'].CR;
	echo "\t\t\e[38;5;82mMount Point\e[97m     ".$disk_info['boot mount'].CR;
	echo "\t\t\e[38;5;82mDisk Size\e[97m       ".$disk_info['boot size'].CR;
	echo "\t\t\e[38;5;82mDisk Used\e[97m       ".$disk_info['boot used']." (".$disk_info['boot %'].")",CR;
	echo "\t\t\e[38;5;82mDisk Free\e[97m       ".$disk_info['boot free'].CR;
	if (isset($disk_info['home filesystem'])) {
		echo "\t\e[1m\e[31m Data\e[0m".CR;
		echo "\t\t\e[38;5;82m\e[1mFile System\e[97m     ".$disk_info['home filesystem'].CR;
	echo "\t\t\e[38;5;82mMount Point\e[97m     ".$disk_info['home mount'].CR;
	echo "\t\t\e[38;5;82mDisk Size\e[97m       ".$disk_info['home size'].CR;
	echo "\t\t\e[38;5;82mDisk Used\e[97m       ".$disk_info['home used']." (".$disk_info['home %'].")",CR;
	echo "\t\t\e[38;5;82mDisk Free\e[97m       ".$disk_info['home free']."\e[0m".CR;
	}
	echo "\e[0m";
}
function display_user($user_info) {
	echo " \r\n\e[1m \e[34mUser Information\e[0m".CR;
	echo "\t\e[1m\e[31mDetail\e[97m".CR;
	echo "\t\e[38;5;82m\tUser\t\t\e[97m".$user_info['name'].CR;
	echo "\t\e[38;5;82m\tPriv level\t\e[97m";
	if(check_sudo($user_info['name']) or root()) {
	echo 'Super User'.CR;
	}
	else {
		echo "User".CR;
	}
	echo "\t\e[1m\e[31mQuota\e[97m".CR;
	echo "\t\e[38;5;82m\tQuota\e[97m\t\t".$user_info['quota'].CR;
	echo "\t\e[38;5;82m\tUsed \e[97m\t\t".$user_info['quota used'].CR;
	echo "\t\e[38;5;82m\tRemaining\e[97m\t".$user_info['quota free']."\e[0m".CR;	
} 	
function display_version() {
		echo CR."Software Version 1.0.20.1β".CR;
		echo CR.'Copyright (c) '.date("Y").', NoIdeer Software
All rights reserved.
Redistribution and use in source and binary forms, with or without modification, are permitted provided that
the following conditions are met:
Redistributions of source code must retain the above copyright notice, this list of conditions and the
following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of the NoIdeer Software nor the names of its contributors may be used to endorse or
promote products derived from this software without specific prior written permission.
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.'.CR.CR;
}
function display_games() {
	require_once('GameQ/Autoloader.php'); //load gameq
	$tm = shell_exec("tmux ls -F#{session_name},#{session_created}  2> /dev/null");	
	$tm =running_games(explode("\n",$tm));
	//print_r($tm);
	echo CR."\e[1m\e[34m Game Server Information\e[0m".CR;
	if(!empty($tm)) {
		$GameQ = new \GameQ\GameQ();
		include ("server-info.php");
    $GameQ->addServers($servers);
    $results = $GameQ->process();
    
    	
		echo "\t\e[1m\e[31mRunning Servers\e[97m".CR;
		foreach( $tm as $key=>$value)
{   

	$players = 	$results[$key]['gq_numplayers'].'/'.$results[$key]['gq_maxplayers'];
	$online  = $results[$key]['gq_online'];
	if (!empty($online)) {
    echo "\t\t\e[38;5;82m".$results[$key]["gq_hostname"]."\e[97m started at ". date('g:ia \o\n l jS F Y \(e\)', $value);
    echo " Players Online ".$players.CR;
    if ($players >0) {
		echo "\t\t\t\e[1m \e[34m Player\t\t    Score\t   Cached\t   Active\e[97m".CR;
		//echo "list Players".CR;
		$player_list = $results[$key]['players'];
		foreach ($player_list as $k=>$v) 
		{
		//print_r ($results[$key]['players']);
		echo  "\t\t\t".$player_list[$k]['gq_name']." \t\t".$player_list[$k]['gq_score']."\t\t".gmdate("H:i:s", $player_list[$k]['gq_time']).CR;
		
	}
		//echo "done".CR;
	}
}
else {
	echo "\t\t".$key." is not responding, please recheck the server configuration".CR;
}
}
	echo"\e[0m";
	
}
else {
	
	echo "\t\tNo Game Servers Running !".CR.CR;
}

	exit;
}

?>
