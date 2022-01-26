<?php
//echo 'functions 1.04';
	$version = "2.042";
	$build = "39171-3654919592";	
if (isset($argv)) {
		$runfile = basename($argv[0]);
			if (isset($argv[1])  and $runfile == 'functions.php') {
				echo "Functions v$version".PHP_EOL;
				echo 'Build '.$build.PHP_EOL;
				exit;
			}
		}
function get_boot_time() {
    $tmp = explode(' ', file_get_contents('/proc/uptime'));
   
//Combined
$value = intval($tmp[0]);
$days = floor($value/86400);
$hours = floor($value / 3600);
$mins = floor(($value - ($hours*3600)) / 60);
$secs = floor($value % 60);
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
        print_r($data);
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
         $valuef=floatval(str_replace ( $key.':' , '' , $value))*1024;
         $value=floatval(str_replace ( $key.':' , '' , $value));
		     		
		 $mem_info[$key] = formatBytes($valuef,2);
		 $mem_info[$key.'_raw'] = trim($value);
		 
	}

	$maxlen = max(array_map('strlen', $mem_info));
	
	$maxlen = 14;
	foreach ($mem_info as $key=>&$value){
		//check len
		 $len = strlen($value);
		 if ($len < $maxlen) {
			 //pad
			 $pad = $maxlen-$len;
             //echo "pad by ".$pad.CR;
             $mem_info[$key] = $value;		
	}
}

	return $mem_info;
		
}
function get_cpu_info() {
	//get cpu info & return as array
	global $settings;
	$cpu = file('/proc/cpuinfo');
	exec('lscpu',$tmp,$ret);
	foreach ($tmp as $k => $v) {
		$tmpline = explode(':',$v);
		$key = str_replace(' ','_',$tmpline[0]);
		$key = str_replace(['(',')'],'',$key);
		$cpu2[trim(strtolower($key))] = trim($tmpline[1]);
	}
	//print_r($cpu2); 	
		foreach ($cpu as &$value) {
			//read data
			$i = strpos($value,":",0);
			//$value = str_replace(' ','_',$value);
			//echo $value.PHP_EOL;
            $key = trim(substr($value,0,$i));
            $key  = str_replace(' ','_',$key);
		 if (strlen($key) === 0) {
			 // only take the first processor
			 break;
		 }
		  $cpu_info[$key] = trim(substr($value,$i+1));
		}
		$cpu_info['processors'] = trim(shell_exec(" grep -c ^processor /proc/cpuinfo")); // count processors
		$load = sys_getloadavg();
		$cpu_info['load_1_min'] = number_format($load[0],2);
		$cpu_info['load_10_min'] = number_format($load[1],2);
		$cpu_info['load_15_min'] = number_format($load[2],2);
		$cpu_info['load_1_min_pc'] = ($load[0]*100)/$cpu2['cpus'];
		$cpu_info['load_10_min_pc'] = ($load[1]*100)/$cpu2['cpus'];
		$cpu_info['load_15_min_pc'] = ($load[2]*100)/$cpu2['cpus'];
		$cpu_info['load'] = number_format($load[0],2)." (1 min)  ".number_format($load[1],2)." (10 Mins)  ".number_format($load[2],2)." (15 Mins)";
		$cpu_info['boot_time'] = get_boot_time();
		$local = shell_exec('hostname -I');
		$local = str_replace(' ', ', ',trim($local));
		$all_ip =explode(',',$local);
		if (isset($settings['router_ip']) and $settings['router_ip'] == true) {
			// get outer ip
			//echo 'hit this'.cr;
			//print_r($settings);
			$public_ip = geturl('ifconfig.me');
		}
		//interfaces ! netstat -i  |sed 1,2d
		// ip addr | grep "^ *inet " checks virtual adaptors
		$cpu_info['local_ip'] = $all_ip[0];
		$cpu_info['ips'] = $local;
		if(isset($public_ip)){
			$cpu_info['ips']="$public_ip, ".$cpu_info['ips'];
		}
		$cpu_info['process'] = trim(shell_exec("/bin/ps -e | wc -l"));
		if (is_file('/var/run/reboot-required') === true) {
			$cpu_info['reboot'] ='Yes';
		}
		else {
			$cpu_info['reboot'] ='No';
		}
		$cpu_info = array_merge($cpu_info,$cpu2);
		//print_r($cpu_info);
		unset($cpu_info['processor']);
		$split_cpu = explode(' ',$cpu_info['model_name']);
		$vx='';
		$key = array_search('CPU', $split_cpu);
		for ($x = 0; $x < $key; $x++) {
			//echo "The number is: $x".cr;
			$vx .=$split_cpu[$x].' ';
		}
		
		$cpu_info['model_name'] = trim($vx);
		$cpu_info['cpu_MHz'] = number_format($cpu_info['cpu_MHz'],2,'.','');
		return $cpu_info;
}
function get_user_info () {
	$Disk_info = get_disk_info();
	if(!defined('cr') ){
		define('cr',PHP_EOL);
	}
	file_put_contents("testFile", "test");
    $user_id = fileowner("testFile");
    unlink("testFile");
    $user = posix_getpwuid($user_id);
	$user['level'] =check_sudo($user['name']);
	$groupid = $user['gid'];
	$groupinfo = posix_getgrgid($groupid);
	//$user['group'] = $groupinfo;
	if (!empty($groupinfo['members'])) {
		$user['members'] = $groupinfo['members'];
	}
	$gecos = explode(',',$user['gecos']); // split data
	unset($user['gecos']);
	foreach ($gecos as $k => $v) {
		switch ($k) {
			case 0:
				$user['real_name'] = $v;
				break;
			case 1:
				$user['room_number'] = $v;
				break;
			case 2:
				$user['work_phone'] = $v;	
				break;
			case 3:
				$user['home_phone'] = $v;
				break;
			case 4:
				$user['other'] = $v;
			}
		}
    //die(print_r($user));
	exec("quota 2> /dev/null",$quota,$ret);
	if (isset($quota[3])){
		// user has quota
		$tmp = trim($quota[3]);
		$tmp = array_values(array_filter(explode(" ",$tmp),'strlen'));
		$tmp[] = $tmp[1]-$tmp[0]; 
		$user['quota_used'] = dataSize($tmp[0]*1024);
        $user['quota_used_raw'] = $tmp[0];
        $user['quota'] = dataSize($tmp[1]*1024);
        $user['quota_raw'] =$tmp[1]; 
        $user['quota_free'] = dataSize($tmp[6]*1024);
        $user['quota_free_raw'] = $tmp[6];        
       
	}
	else {
			if(isset($Disk_info['home_free'])){
				$free =floatval( $Disk_info['home_free']);
				$user['quota'] = $Disk_info['root_size'];
				$user['quota_used'] = $Disk_info['root_used']; 	
				$user['quota_free'] = $Disk_info['root_free'];
				$user['disk_locations'] = 2;
			}
			else {
				$user['quota'] = $Disk_info['boot_size'];
				$user['quota_free'] = $Disk_info['boot_free'];
				$user['disk_locations'] = 1;
			}
	}
	//print_r($user);
	return $user;    
	
}


function getVersion($app, $apt=false) { 
	// check for apt-show-versions
	$dbtype='';
	if ($apt == true) {
		echo "apt=true $app".PHP_EOL;
		$app = 'apt-show-versions  '.$app;
		$soutput = explode(' ',shell_exec($app. '  2> /dev/null')); 
		$mangle = $soutput[1];
		//echo print_r($soutput,true).cr;
		$x= strpos($mangle,':');
		if ($x > 0) {
			$mangle = str_replace('~','',$mangle);
			$mangle = substr($mangle,$x+1);
			$x = strpos($mangle,'+');
			$output = substr($mangle,0,$x);
		}
		else {
			$output = str_replace('~','',$soutput[1]);
			$output = str_replace('-','.',$output);
			//echo "output = $output".cr;
			}
		}
	else {
		if ($app == 'nginx -v') {
				// nginx has stdout bug do this
				 shell_exec($app. '  2> nginx');
				$output = file_get_contents('nginx');
				unlink('nginx');
				}
		else if ($app == 'webmin -v') {
			// webmin
			//echo 'hit webmin'.cr;
			if (is_file('/etc/webmin/miniserv.conf')) {
			$app = 'webmin list-config -c |grep server=M 2>/dev/null' ;
			$output = shell_exec($app);
			}
			else {
				$output='';
			}
			//$output = file_get_contents('nginx');
			//unlink('nginx'); 
		}		
		else if ($app == 'mysql -V') {
			// maria test
			$output = shell_exec($app. '  2> /dev/null'); 
			$x = strpos($output,'MariaDB');
			if ($x) {
				$dbtype = ' (MariaDB)';
			}
			
		}
		 else if ($app == 'tmpreaper') {
             shell_exec('tmpreaper 2> tr.txt');
             $output = file_get_contents('tr.txt');
             unlink('tr.txt');
        }
		
			else{
				$output = shell_exec($app. '  2> /dev/null'); 
			}
		}
	if(!empty($output)) {	 
		preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
		//echo 'try 1 '.print_r($version,true).cr;
		if (empty($version[0])) {
			preg_match('@[0-9]+\.[0-9]+@', $output, $version);
			//echo 'try 2 '.print_r($version,true).cr;
		}
		if (empty($version[0])) {
	   	    preg_match('@[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
			//echo 'try 3 '.print_r($version,true).cr; 
		}
		if (!empty($version[0])) {
		return $version[0].$dbtype; 
		}
		else {
			//echo $app.PHP_EOL;
			//print_r($version);
			return 'Not Installed';
		}
	}
	return 'Not Installed';	
}
function get_software_info($database) {
	/* return software info as array
	 * check for apt-show-versions
	 * $ver = getversion('apt-show-versions -V'); // is apt-show-versions installed ?
	 * if ( $ver == 'Not Installed') { $apt= false;}  else {$apt=true;}
	 */
	 //$ver = getversion('apt-show-versions -V'); // is apt-show-versions installed ?
	  //if ( $ver == 'Not Installed') { $apt= false;}  else {$apt=true;}
	  ///usr/local/lsws/bin/lshttpd -v
	  $hctrl = shell_exec('hostnamectl');
	  $hctrl = explode(PHP_EOL,trim($hctrl));
	  foreach ($hctrl as $temp) {
		  // make new array
		  $x = strpos($temp,':');
		  $key = strtolower(str_replace(' ','_',trim(substr($temp,0,$x))));
		  $value = trim(substr($temp,$x+1));
		  //echo $key.'=>'.$value.CR;
		  $newarr[$key]=$value;
	  }
	  $hctrl =$newarr; 
	 $apt=false;
	 $php_version = explode('.', PHP_VERSION);
	 $php  = $php_version[0].'.'.$php_version[1];
	 $lsb = lsb();
	 $software['k_ver'] = php_uname('r');
	 $software['host'] =php_uname('n');
	 $software['os'] = $hctrl['operating_system'];  
	switch ($apt) {
		case true:
		// this is slower ! but cleaner and allows to show if upgrades are available 
		$software['glibc'] = getVersion('libc-bin',$apt);
		$software['apache'] = getVersion('apache2',$apt);
		$software['php'] = getVersion('php'.$php,$apt);
		$software['mysql'] = getVersion('mysql-server',$apt);
		$software['quotav'] = getVersion('quota',$apt);
		$software['nginx'] = getVersion('nginx-common',$apt);
		$software['screen'] = getVersion('screen',$apt);
		$software['postfix'] = getVersion('postfix',$apt);
		$software['curl'] = getVersion('curl',$apt);
		$software['tmux'] = getVersion('tmux',$apt);
		$software['litespeed'] = getVersion('litespeed',$apt);
		$software['git'] = getVersion('git',$apt);
		$oftware['asv'] = getVersion('apt-show-versions',$apt);
		break;
		default:
		 $software['glibc'] = getVersion('ldd --version',$apt);
		 $software['apache'] = getVersion('/usr/sbin/apache2 -v');
	     $software['php'] = getVersion('php -v');
	     $software['mysql'] = getVersion('mysql -V');
	     $software['quotav'] = getVersion('quota -V');
	     $software['nginx'] = getVersion('nginx -v');
	     $software['screen'] = getVersion('screen -v');
	     $software['postfix'] = getVersion('/usr/sbin/postconf -d mail_version');
	     $software['curl'] = getVersion('curl -V');
	     $software['tmux'] = getVersion('tmux -V');
	     $software['litespeed'] = getVersion('/usr/local/lsws/bin/lshttpd -v');
	     $software['git'] = getVersion('git --version');
	     $software['tmpreaper'] = getVersion('tmpreaper',false);
	     $software['asv'] = getVersion('apt-show-versions -V');
	}
	//print_r($software);	 
	 return $software;
}
function get_disk_info() {
	/* return disk info as array
	//echo 'root stuff ! or no quota !'.cr;
	*/ 
exec('df  /',$root,$ret); //need this for sdd or sep system partition
unset ($root[0]);
$root = array_values($root);
$root = array_values(array_filter(preg_split('/(\s)/', $root[0])));
exec('df  /home',$home,$ret); //got the stuff
unset($home[0]);
$home = array_values($home);
$home = array_values(array_filter(preg_split('/(\s)/', $home[0])));
exec('df  |grep boot',$boot,$ret); //got the stuff
//print_r($boot);
//unset($boot[0]);
//$boot = array_values($boot);
//$home = explode("   ",$home[0]);
$boot = array_values(array_filter(preg_split('/(\s)/', $boot[0])));
if($home[0] == $root[0]) {
	//$home matches $root
	//unset($home);
	}
if($boot == $root) {
	//$boot matches $root;
	//unset($root);
	}

//die();
	if(isset($root)) {
		$disk_info['root_filesystem'] = trim($root[0]);
		$disk_info['root_size'] = dataSize(floatval($root[1]) *1024);
		$disk_info['root_size_raw'] = trim($root[1]);
		$disk_info['root_used'] = dataSize(floatval($root[2])*1024);
		$disk_info['root_used_raw'] = trim($root[2]);
		$disk_info['root_free'] = dataSize(floatval($root[3])*1024);
		$disk_info['root_free_raw'] = trim($root[3]);
		$disk_info['root_pc'] = trim($root[4]);
		$disk_info['root_mount'] = trim($root[5]);
	}
				
	if(isset($home)) {
		//echo 'oh home is set'.cr;
		$disk_info['home_filesystem'] = trim($home[0]);
		$disk_info['home_size'] = dataSize(floatval($home[1])*1024);
		$disk_info['home_size_raw'] = trim($home[1]);
		$disk_info['home_used'] = dataSize(floatval($home[2])*1024);
		$disk_info['home_used_raw'] = trim($home[2]);
		$disk_info['home_free'] = dataSize(floatval($home[3])*1024);
		$disk_info['home_free_raw'] = trim($home[3]);
		$disk_info['home_pc'] = trim($home[4]);
		$disk_info['home_mount'] = trim($home[5]);
	}
	if(isset($boot)) {
		//echo 'oh home is set'.cr;
		$disk_info['boot_filesystem'] = trim($boot[0]);
		$disk_info['boot_size'] = dataSize(floatval($boot[1])*1024);
		$disk_info['boot_size_raw'] = trim($boot[1]);
		$disk_info['boot_used'] = dataSize(floatval($boot[2])*1024);
		$disk_info['boot_used_raw'] = trim($boot[2]);
		$disk_info['boot_free'] = dataSize(floatval($boot[3])*1024);
		$disk_info['boot_free_raw'] = trim($boot[3]);
		$disk_info['boot_pc'] = trim($boot[4]);
		$disk_info['boot_mount'] = trim($boot[5]);
	}
	//print_r($disk_info);	
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

function ask_question ($salute,$positive='',$negative='',$press_enter_key=false,$hidden = false) {
	
	$length = strlen($salute)+1;
	str_pad ($salute , $length , " " , STR_PAD_RIGHT );
	run:

	if ($hidden === true) {
		echo $salute; // display question
		$line = getObscuredText($strMaskChar='*');
	return $line;
	
	}
	
	if ($press_enter_key === true) {
	// use press enter to continue
	echo $salute;
	system('stty -echo');
	$handle = fopen ("php://stdin","r"); //open stdin
	$line = fgets($handle); //record it
	fclose($handle);
	system('stty echo');
	return $line;
}
	
$line = readline($salute);	

if (isset($positive)) {
		if (trim(strtolower($line)) == $positive) {
			return true;
		}
		elseif (isset($negative)) {
			return false;
		}
		sleep(2);
	}

if ($line === PHP_EOL) {
	errors:
	// entered empty string
	echo "You must have a valid response".cr;
	unset($line); // clear input
	goto run; // have another go
}

/*
//if (preg_match('/\s/',trim($line)) ) {
if (ctype_space($line)) {
	echo "ERROR response contains spaces".cr; 
	goto errors;
	}
if ($positive <>NULL){	
	if(trim($line) !== $positive){
	     return false;
	}
} */
return $line;
}

function display_mem($mem_info,$colour) {
	// mem display
	if (is_cli()){
	if ($colour === true) {
		//echo "colour".CR;
		$headmask = "%32.32s %13.13s %13.13s %13.13s  \n";
		
		echo "\t\e[1m\e[31m Memory\e[0m".CR;
		printf($headmask, "\e[1m \e[34m Total",'Free','Cached',"Active\e[97m");
		//echo "\t\t\t\e[1m \e[34m Total\t\t    Free\t   Cached\t   Active\e[97m".CR;
		$headmask = "%40.40s %13.13s %10.10s %10.10s  \n";
		printf($headmask,"\e[38;5;82mMem  \e[97m".$mem_info['MemTotal'],$mem_info['MemFree'],$mem_info['Cached'],$mem_info['Active']);
		//echo "\t\t\e[38;5;82mMem\t\e[97m".$mem_info['MemTotal']."\t". $mem_info['MemFree']."\t".$mem_info['Cached']."\t".$mem_info['Active'].CR;
		$headmask = "%40.40s %13.13s %14.14s   \n";
		printf($headmask,"\e[38;5;82mSwap      \e[97m".$mem_info['SwapTotal'],$mem_info['SwapFree'],$mem_info['SwapCached']."\e[0m",'');
		//echo "\t\t\e[38;5;82mSwap\t\e[97m".$mem_info['SwapTotal']."\t". $mem_info['SwapFree']."\t".$mem_info['SwapCached']."\e[0m".CR.CR;
}
else {
	//bw
	//echo "bw".CR;
		echo "\t Memory".CR;
		echo "\t\t\tTotal\t\t Free\t\t Cached\t\tActive".CR;
		echo "\t\tMem\t".$mem_info['MemTotal']."\t". $mem_info['MemFree']."\t".$mem_info['Cached']."\t".$mem_info['Active'].CR;
		echo "\t\tSwap bw\t".$mem_info['SwapTotal']."\t". $mem_info['SwapFree']."\t".$mem_info['SwapCached'].CR.CR;
}
}
else {
	$disp = '<table style="width:100%;"><td></td><td style="width:22%;">Total</td><td style="width:22%;">Free</td><td style="width:22%;">Cached</td><td style="width:22%;">Active</td>
	<tr><td style="color:red;width:22%;">Memory</td><td style="width:22%;" id="memtotalickleh">'.$mem_info['MemTotal'].'</td><td id="memfreeickleh">'.$mem_info['MemFree'].'</td><td id="memcachedickleh">'.$mem_info['Cached'].'<td id="memactiveickleh">'.$mem_info['Active'].'</td></tr>
	<tr><td style="color:red;">Swap</td><td>'.$mem_info['SwapTotal'].'</td><td>'.$mem_info['SwapFree'].'</td><td>'.$mem_info['SwapCached'].'</td></tr></table>';
	
	return $disp;
}
	
}
function running_games($data) {
	// returns running games
	foreach ($data as &$value) {
			//read data
			$i = strpos($value,",",0);
            $key = trim(substr($value,0,$i));
		 if (strlen($key) === 0) {
			 
			 break ;
		 }
		  $return[$key] = trim(substr($value,$i+1));
		}
		
		return $return;
}
function formatBytes($bytes, $precision = 0) { 
    $units = array('B ', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
     $bytes /= (1 << (10 * $pow)); 
    return round($bytes, $precision) . ' ' . $units[$pow];
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
	//else {
if (is_cli()) {		
	echo "\t\e[1m\e[31mSoftware\e[97m".CR;

	echo "\t\e[1m   Server".CR;
	echo "\t\t\e[38;5;82mServer OS        \e[97m".PHP_OS." (".$os['PRETTY_NAME'].")".CR;
	echo "\t\t\e[38;5;82mKernel Version   \e[97m".php_uname('r').CR;
	echo "\t\t\e[38;5;82mHost Name        \e[97m".php_uname('n').CR;
	echo "\t   Required".CR;
	echo "\t\t\e[38;5;82mPHP Version  \e[97m      " .$software['php'].CR;
	echo "\t\t\e[38;5;82mScreen Version\e[97m     " .trim($software['screen']).CR;
	echo "\t\t\e[38;5;82mGlibc Version\e[97m      " .$software['glibc'].CR;
	echo "\t\t\e[38;5;82mMysql Version\e[97m      " .$software['mysql'].CR;
	echo "\t\t\e[38;5;82mApache Version\e[97m     " .$software['apache'].CR;
	echo "\t\t\e[38;5;82mCurl Version\e[97m       " .$software['curl'].CR;
	echo "\t   Optional".CR;
    echo "\t\t\e[38;5;82mNginx Version\e[97m      " .$software['nginx'].CR;
    echo "\t\t\e[38;5;82mQuota Version\e[97m      " .$software['quotav'].CR;
    echo "\t\t\e[38;5;82mPostFix Version\e[97m    " .$software['postfix'].CR;
    echo "\t\t\e[38;5;82mLitespeed Version\e[97m  " .$software['litespeed'].CR;
    echo "\t\t\e[38;5;82mGit Version      \e[97m  " .$software['git'].CR;
    echo "\t\t\e[38;5;82mTmpreaper Version\e[97m  " .$software['tmpreaper'].CR;
    echo "\t\t\e[38;5;82mApt Checker      \e[97m  " .$software['asv'].CR;
    echo "\t\t\e[38;5;82mTmux Version\e[97m       " .$software['tmux']."\e[0m".CR; //required ?
   
}	
else {
	$disp = '<table><tr><td width="40%"><i style="color:red">Server OS</i></td><td>'.PHP_OS." (".$os['PRETTY_NAME'].")".'</td></tr>';
	$disp .= '<tr><td width="40%"><i style="color:red">Kernel Version</i></td><td>'.php_uname('r').'</td></tr>';
	$disp .= '<tr><td width="40%"><i style="color:red">Host Name</i></td><td>'.php_uname('n').'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:green">Required</i></td><td></td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">PHP Version</i></td><td>'.$software['php'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Screen Version</i></td><td>'.$software['screen'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Glibc Version</i></td><td>'.$software['glibc'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Mysql Version</i></td><td>'.$software['mysql'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Apache Version</i></td><td>'.$software['apache'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Curl Version</i></td><td>'.$software['curl'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:green">Optional</i></td><td></td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Nginx Version</i></td><td>'.$software['nginx'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Quota Version</i></td><td>'.$software['quota'].'</td></tr>';
	$disp .= '<tr><td width="50%"><i style="color:red">Postfix Version</i></td><td>'.$software['postfix'].'</td></tr>';
	
	$disp .='</table>'; 
	return $disp;
}
	//}
}
function display_cpu ($cpu_info) {
	global $argv;
	if (isset($argv[2])) {
		echo CR." \r\n\e[1m \e[34mHardware Information\e[0m".CR;
	}
	else{
   
}
if (is_cli()) {
	 echo "\t\e[1m\e[31mHardware\e[97m".CR;
    echo "\t\t\e[38;5;82mUptime         \t\e[97m".$cpu_info['boot_time'].CR;
    echo "\t\t\e[38;5;82mCpu Model      \t\e[97m".$cpu_info['model_name'].CR;
    echo "\t\t\e[38;5;82mCpu Processors \t\e[97m".$cpu_info['processors'].CR;
    echo "\t\t\e[38;5;82mCpu Cores      \t\e[97m".$cpu_info['cpu_cores'].CR;
    echo "\t\t\e[38;5;82mCpu Speed      \t\e[97m".$cpu_info['cpu_MHz']. " MHz".CR;
    echo "\t\t\e[38;5;82mCpu Cache      \t\e[97m",$cpu_info['cache_size'].CR;
    echo "\t\t\e[38;5;82mCpu Load       \t\e[97m".$cpu_info['load'].CR;
	echo "\t\t\e[38;5;82mIP Address\e[97m     \t".$cpu_info['local_ip'].CR;
	echo "\t\t\e[38;5;82mProcesses\e[97m     \t".$cpu_info['process'].CR;
	echo "\t\t\e[38;5;82mReboot Required\e[97m\t".$cpu_info['reboot']."\e[0m".CR;
}
else {
	$sname='ickleh';
	$disp = '<table style="width:100%;"><tr><td width="20%" style="color:red;">Uptime</td><td width="70%" id="boot'.$sname.'">'.$cpu_info['boot_time'].'</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Model</td><td>'.$cpu_info['model_name'].'</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Processors</td><td>'.$cpu_info['processors'].'</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Cores</td><td>'.$cpu_info['cpu cores'].'</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Speed</td><td>'.$cpu_info['cpu_MHz'].'Mhz</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Load</td><td id="load'.$sname.'">'.$cpu_info['load'].'</td></tr>
	<tr><td style="width:20%;color:red;">Cpu Cache</td><td>'.$cpu_info['cache_size'].'</td></tr>
	<tr><td style="width:20%;color:red;">IP Address</td><td>'.$cpu_info['local_ip'].'</td></tr></table>';
	return $disp;
}
}
function display_disk($disk_info) {
	if (is_cli()) {
	echo CR."\e[1m \e[34m Disk Information\e[0m".CR;
	if (!isset($disk_info['boot_hide'])) {echo "\t\e[1m\e[31m Boot\e[0m".CR;}
	echo "\t\t\e[38;5;82m\e[1mFile System\e[97m     ".$disk_info['boot_filesystem'].CR;
	echo "\t\t\e[38;5;82mMount Point\e[97m     ".$disk_info['boot_mount'].CR;
	echo "\t\t\e[38;5;82mDisk Size\e[97m       ".$disk_info['boot_size'].CR;
	echo "\t\t\e[38;5;82mDisk Used\e[97m       ".$disk_info['boot_used']." (".$disk_info['boot_pc'].")",CR;
	echo "\t\t\e[38;5;82mDisk Free\e[97m       ".$disk_info['boot_free'].CR;
	if (isset($disk_info['home_filesystem'])) {
		echo "\t\e[1m\e[31m Data\e[0m".CR;
		echo "\t\t\e[38;5;82m\e[1mFile System\e[97m     ".$disk_info['home_filesystem'].CR;
	echo "\t\t\e[38;5;82mMount Point\e[97m     ".$disk_info['home_mount'].CR;
	echo "\t\t\e[38;5;82mDisk Size\e[97m       ".$disk_info['home_size'].CR;
	echo "\t\t\e[38;5;82mDisk Used\e[97m       ".$disk_info['home_used']." (".$disk_info['home_pc'].")",CR;
	echo "\t\t\e[38;5;82mDisk Free\e[97m       ".$disk_info['home_free']."\e[0m".CR;
	}
	echo "\e[0m";
}
else {
	// html
	if (!isset($disk_info['boot hide'])) {$disp .= '<i>Boot</i>'.CR;}
	$disp .= '<table style ="width:100%;">';
	$disp .= '<tr><td width="22%"><i style="color:red;">File System</i></td><td>'.$disk_info['boot_filesystem'].'</td></tr>';
	$disp .= '<tr><td><i style="color:red;">Mount Point</i></td><td>'.$disk_info['boot_mount'].'</td></tr>';
	$disp .= '<tr><td><i style="color:red;">Disk Size</i></td><td>'.$disk_info['boot_size'].'</td></tr>';
	$disp .= '<tr><td><i style="color:red;">Disk Used</i></td><td>'.$disk_info['boot_used'].' ('.$disk_info['boot_pc'].')</td></tr>';
	$disp .= '<tr><td><i style="color:red;">Disk Free</i></td><td>'.$disk_info['boot_free'].'</td></tr>';
	if (isset($disk_info['home filesystem'])) {
		// home  file system different to boot file system 
	}
	$disp .= '</table>';
	return $disp;
}
}
function display_user($user_info) {
	if (is_cli()) {
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
	echo "\t\e[38;5;82m\tUsed \e[97m\t\t".$user_info['quota_used'].CR;
	echo "\t\e[38;5;82m\tRemaining\e[97m\t".$user_info['quota_free']."\e[0m".CR;
}	
	else {
		// html
	/*echo '<table style="width:100%;"><tr><td style="width:50%;color:red;">User Name</td><td>'.$user_info['name'].'</td></tr>';
	echo '<tr><td style="width:50%;color:red;">Quota</td><td>'.$user_info['quota'].'</td></tr>';
	echo '<tr><td style="width:50%;color:red;">Quota Used</td><td>'.$user_info['quota used'].'</td></tr>';
	echo '<tr><td style="width:50%;color:red;">Remaining</td><td>'.$user_info['quota free'].'</td></tr>';
	//echo '<tr><td style="width:50%;color:red;">Cpu Speed</td><td>'.$cpu_info['cpu MHz'].'</td></tr>';
	echo '</table>'; */
	if(check_sudo($user_info['name']) or root()) {
	$user_priv = 'Super User';
	}
	else {
		$user_priv = "User";
	}
	$disp = '<table style="width:100%;"><tr><td style="width:24%;color:red;">Name</td><td>'.$user_info['name'].'</td></tr>
	<tr><td style="color:red;">Level</td><td>'.$user_priv.'</td></tr>
	<tr><td style="color:red;">Quota</td><td>'.$user_info['quota'].'</td></tr>
	<tr><td style="color:red;">Used</td><td>'.$user_info['quota_used'].'</td></tr>
	<tr><td style="color:red;">Remaining</td><td>'.$user_info['quota_free'].'</td></tr>
	
	</table>';
	return $disp;
	}
} 	
function display_version() {
	if (is_cli()) {
		echo CR."Software Version 1.0.34.0β".CR;
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
POSSIBILITY OF SUCH DAMAGE.'.CR.CR;}
}

function display_games() {
	 //require __DIR__ . '/xpaw/SourceQuery/bootstrap.php';
	 //use xPaw\SourceQuery\SourceQuery as $jim;
	 global $Query;
	 echo 'here'.CR;
	$database = new db(); // connect to database
	$sql = 'select * from servers where enabled ="1" and running="1" order by servers.host_name'; //select all enabled & running recorded servers
    $res = $database->get_results($sql); // pull results
    $servers = array(); // set GameQ array
  
foreach ($res as $data) {
	
	//add the data array for GameQ
	//this does allow remote locations
	// as long as you have the remote software installed
	$key =$data['host_name'];
	$servers[$key]['id'] = $key;
	$servers[$key]['host'] = $data['host'].':'.$data['port'] ;
	$servers[$key]['type'] = $data['type'];
	define( 'SQ_SERVER_ADDR', $data['host'] );
	define( 'SQ_SERVER_PORT', $data['port'] );
	define( 'SQ_TIMEOUT',     1 );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	
    $Query->Connect( SQ_SERVER_ADDR, SQ_SERVER_PORT, SQ_TIMEOUT, SQ_ENGINE );
	$players = $Query->GetPlayers( ) ;
	$info = $Query->GetInfo();
	$rules = $Query->GetRules( );
	$Query->Disconnect( );
	//print_r($info);
	
	}
	require_once('GameQ/Autoloader.php'); //load GameQ
	if (is_cli()) {
		
		
		} // get local servers	
	
	$tm = get_sessions();
	//echo print_r($tm,true).CR;
	$tm =running_games($tm);
    //print_r($tm);
	if(!empty($tm)) {
		unset($tm['install']); 
		$GameQ = new \GameQ\GameQ();
		//include ("server-info.php");
    $GameQ->addServers($servers);
    $results = $GameQ->process();
    //print_r($results);
    // order servers on players
    //orderBy($results,'gq_numplayers',"d");
    if (!is_cli()) {
		$disp = html_display ($tm,$results);
		return $disp;
	}
    	
		
		foreach( $tm as $key=>$value)
{   
	$players = 	$results[$key]['gq_numplayers'].'/'.$results[$key]['gq_maxplayers'];
	$online  = $results[$key]['gq_online'];
	if (!empty($online)) {
    echo "\t\t\e[38;5;82m".$results[$key]["gq_hostname"]."\e[97m started at ". date('g:ia \o\n l jS F Y \(e\)', $value);
                $update['running'] = 1;
				$update['starttime'] = $value;
			    $where['host_name'] = $key;
			   // echo $key.CR; 
			    $database->update('servers',$update,$where);
	echo "\t\t Players Online ".$players." Map - ".$results[$key]["gq_mapname"];//.CR;
       
    if ($players >0) {
			echo "\t\t\t\e[1m \e[34m Player\t\t        Score\t        Online For\e[97m";//.CR;
			$player_list = $results[$key]['players'];
				orderBy($player_list,'gq_score',"d"); // order by score
				foreach ($player_list as $k=>$v) {
					$playerN = substr($player_list[$k]['gq_name'],0,20); // chop to 20 chrs
					$playerN = iconv("UTF-8", "ISO-8859-1//IGNORE", $playerN); //remove high asci
					$playerN = str_pad($playerN,25); //pad to 25 chrs
		
		if ($player_list[$k]['gq_score'] <10) {
			// switch statement !! rather than if's
			$pscore ="  ".$player_list[$k]['gq_score']; //format score
		}
		elseif ($player_list[$k]['gq_score'] <100)  {
			$pscore = " ".$player_list[$k]['gq_score']; //format score
		}
		else {
			$pscore = $player_list[$k]['gq_score']; //format score
		}
		echo  "\t\t\t".$playerN."\t ".$pscore."\t\t ".gmdate("H:i:s", $player_list[$k]['gq_time']);//.CR;
		
	}
		//echo CR;
			}
}
else {
	if (is_cli()){
	echo "\t\t".$key." is not responding, please recheck the server configuration".CR;
}
else {
	$disp .= '<i style=color:red;>'.$key.'</i> is not responding, please recheck the server configuration'.CR;
}
}
}
	//if(is_cli()) { echo"\e[0m";}
	
    
	
	
}
else {
	
	echo "\t\tNo Game Servers Running !".CR.CR;
}
	exit;
}
/*
 * 
 * name: html_display
 * $
 * @return
 * cure html div bug
 */
  

  function get_sessions() {
	  /* Recover screen & Tmux sessions
	   * Feb 2020
	   * the tmux sessions will be removed as php run via apache can not access them
	   */
	   
	   $sql = 'select * from base_servers  where extraip = 0 ' ;
	   $database = new db(); // connect to database
	   $res = $database->get_results($sql); // pull results
	   $sql = 'select servers.host_name from servers where enabled=1';
	   $valid = $database->get_results($sql);
	   //echo print_r($valid,true).CR;
	   $xm='';
	   $tm='';
	   foreach ($res as $data){
	    
		 $ch = curl_init();
		 // need to add key here & replace curl
		 $ipaddr = md5( ip2long($data['ip']));
	     curl_setopt($ch, CURLOPT_URL, $data['url'].':'.$data['port'].'/ajax.php?action=exescreen&cmd=ls&key='.$ipaddr);
	     
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $xm = curl_exec($ch);
		 
		 $xm = trim($xm);
		 $tm .= $xm;
		 //echo $tm.CR;
		 curl_close($ch);
		 
	 }
	  $tm = explode("*",$tm);
	 
	  foreach ($valid as $server) {
		  
		  $search = $server['host_name'];
		  
		 foreach ($tm as $k=>$v){

 if(stripos($v, $search) !== false){
 $xy[$k]=$v;
 }

	  }
  }
  //print_r($xy);
	 return $xy;
	
   }

function local_build($ldata) {
	
$string = trim(preg_replace('/\t/', '', $ldata));
$string = trim(preg_replace('/""/', ',', $string));
$string = trim(preg_replace('/"/', '', $string));
$string = trim(preg_replace('/{/', '', $string));
$string = trim(preg_replace('/}/', '', $string));

$ta = explode(PHP_EOL,$string);
$ta = array_filter($ta);
$j = refactor_local($ta);
//echo print_r($j,true).PHP_EOL;
$return['appid'] = $j['AppState']['appid'];
$return['buildid'] = $j['AppState']['buildid'];
$return['update'] = $j['AppState']['LastUpdated'];
//echo print_r($return,true).PHP_EOL;
return $return;
}

function refactor_local($array) {
	// refactor array with keys
	global $keyset;
	foreach ($array as &$value) {
			//read data
			if(empty($value)) { 
			//echo 'empty'.PHP_EOL;
			}
		else {
			// make array
			//if ($keyset = 1) {echo 'keyset'.PHP_EOL;}
			 $i = strpos($value,",",0);
			 if ($i == 0) {
			 $key1 = trim($value);
			 $nos[$key1] =array();
			 $keyset=1;
			 continue;
		 }
		   else {
			   //echo 'hit else'.PHP_EOL;
			   $i = strpos($value,",",0);
			if ($i > 0 )
			{
            $key = trim(substr($value,0,$i));
            if (isset($key1)) {
		    $nos[$key1][$key] = trim(substr($value,$i+1));
		}
		else {
			$nos[$key] = trim(substr($value,$i+1));
		}
		}
		   }
		}	
			
		
		}
		return $nos;
//print_r($nos);
}
function test_remote($file) {
	//echo 'starting remote'.PHP_EOL;
$string = trim(preg_replace('/\t/', '', $file));
$string = trim(preg_replace('/""/', ',', $string));
$string = trim(preg_replace('/"/', '', $string));
$string = trim(preg_replace('/{/', '', $string));
$string = trim(preg_replace('/}/', '', $string));
$ta = explode(PHP_EOL,$string);
$j = refactor_remote($ta);
$return['buildid'] = $j['public']['buildid'];
$return['update'] = $j['public']['timeupdated'];
//print_r ($j);
return $return;
}
function refactor_remote($array) {
	// refactor array with keys
	foreach ($array as &$value) {
			//read data
			if(empty($value)) { 
			//echo 'empty'.PHP_EOL;
			}
		else {
			// make array
			 $i = strpos($value,",",0);
			 if ($i == 0) {
			 $key1 = trim($value);
			 $nos[$key1] =array();
		 }
		}	
			$i = strpos($value,",",0);
			if ($i > 0 )
			{
            $key = trim(substr($value,0,$i));
            if (isset($key1)) {
		    $nos[$key1][$key] = trim(substr($value,$i+1));
		}
		else {
			$nos[$key] = trim(substr($value,$i+1));
		}
		}
		
		}
		return $nos;
//print_r($nos);
}
function validate($valid) {
	//this will expand over time
	$ip = $_SERVER['SERVER_ADDR'];
	$key = md5( ip2long($ip));
	//echo $key;
	if ($key == $valid['key']) {
		return true;
	}
	return false;
}

function array_search_partial($arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
    return false;
}

function folderSize($dir)
{
    $size = 0;
    $dir  = rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'{,.}*';
    $list = glob($dir, GLOB_BRACE);
    $list = array_filter($list, function($v){
        return preg_match('%(\\\\|/)\.{1,2}$%im', $v) ? false : true;
    });

    foreach ($list as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }

    return $size;
}
function dpkg($app) {

$cmd = "dpkg-query -l | grep -P '( ".$app." )'";
$cmd = 'dpkg -l |grep "^ii  '.$app.'[[:space:]]"';
//echo $cmd.PHP_EOL;
exec ($cmd,$soft,$v);
if ($v >0) {
	unset($v);
	$cmd = "dpkg-query -l | grep -P '( ".$app." )'";
	//echo $cmd.PHP_EOL;
	exec ($cmd,$soft,$v);
}
if ($v >0){
	$soft1[]=$app;
	$soft1[]= 'Not Installed';
	return $soft1;
} 
$soft1 = explode('  ',trim($soft[0]));
foreach($soft1 as $k => $v) {
if (trim($v)=='') {
unset ($soft1[$k]);
}
else {
$soft1[$k] = trim($v);
}
}
//unset($soft1[0]);
//echo print_r($soft1,true).cr;
$soft1=array_values($soft1);
$ver = str_replace('~','.',$soft1[2]);
preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $ver, $version);
if (empty($version[0])) {
	preg_match('@[0-9]+\.[0-9]+@', $ver, $version);
}
 if (empty($version[0])) {

            preg_match('@[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+@', $ver, $version);
            //echo 'try 3 '.print_r($version,true).cr; 
   }
$soft1[2] = $version[0];
//echo $soft1[4].cr;
if(strpos($soft1[4],'my.cnf') !=FALSE) {
	$soft1[4] = 'MariaDB MySQL database server';
}
//print_r($version);
//echo 'found '.count($soft).PHP_EOL;
//echo PHP_EOL;
return $soft1;
}

/**
 * Helper function to do a partial search for string inside array.
 *
 * @param array  $array   Array of strings.
 * @param string $keyword Keyword to search.
 *
 * @return array
 */
function array_partial_search( $array, $keyword ) {
    $found = [];

    // Loop through each item and check for a match.
    foreach ( $array as $string ) {
        // If found somewhere inside the string, add.
        if ( strpos( $string, $keyword ) !== false ) {
            $found[] = $string;
        }
    }

    return $found;
}

function cursor($cmd,$x=0,$y='') {
$esc = "\033[";

switch ($cmd) {
        case 'off':
        echo "$esc?25l".$y;
        return;
        case 'on':
        echo "$esc?25h".$y;
        return;
        case 'up':
        $up =$esc.$x.'A'.$y;
        echo "$up";
        return;
        case 'down':
        $down = $esc.$x.'B';
        echo $down;
        return;
        case 'left':
        $left = $esc.$x.'D';
        echo $left;
        return;
        case 'right':
        $right = $esc.$x.'C';
        echo $right;
        return;
        case 'home':
        echo $esc."H";
        return;
        case 'deline':
        echo $esc."2K";
        return;
}
}

?>
