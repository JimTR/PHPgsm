<?php
//echo 'functions 1.04';
define('fversion',1.04);
$runfile = substr($argv[0], strrpos($argv[0], '/') + 1);
if (isset($argv[1])  and $runfile == 'functions.php') {
	echo 'Functions v'.fversion.PHP_EOL;
	exit;
}
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



function get_cpu_info() {
	//get cpu info & return as array
	$cpu = file('/proc/cpuinfo');
		
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
		$cpu_info['load'] = number_format($load[0],2)." (1 min)  ".number_format($load[1],2)." (10 Mins)  ".number_format($load[2],2)." (15 Mins)";
		$cpu_info['boot_time'] = get_boot_time();
		$local = shell_exec('hostname -I');
		$local = str_replace(' ', ', ',trim($local));
		$all_ip =explode(',',$local);
		//interfaces ! netstat -i  |sed 1,2d
		// ip addr | grep "^ *inet " checks virtual adaptors
		$cpu_info['local_ip'] = $all_ip[0];
		$cpu_info['ips'] = $local;
		$cpu_info['process'] = trim(shell_exec("/bin/ps -e | wc -l"));
		if (is_file('/var/run/reboot-required') === true) {
			$cpu_info['reboot'] ='yes';
		}
		else {
			$cpu_info['reboot'] ='no';
		}
		return $cpu_info;
}


function getVersion($app, $apt=false) { 
	// check for apt-show-versions
	$dbtype='';
	if ($apt == true) {
		//echo 'apt=true'.PHP_EOL;
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
			else{
				$output = shell_exec($app. '  2> /dev/null'); 
			}
		} 
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
function get_disk_info() {
	// return disk info as array
	$disks = shell_exec("lsblk -l");
	$boot = shell_exec("df -h /boot");
	$home = shell_exec("df -h /home");
	$root = shell_exec("df -h /");
	if ($root === $home) {
		//echo 'one disk'.CR;
		//$disk_info['disk'] = $root;
				if(strstr($boot, PHP_EOL)) {
		// test for line break
		//echo "line break".CR;
		$boot = explode(" ",trim(strstr($root, PHP_EOL)));
		//print_r($boot).CR;
		$boot=array_filter($boot);
		$boot = array_slice($boot, 0);
		//print_r($boot).CR;
		$disk_info['boot_filesystem'] = trim($boot[0]);
		$disk_info['boot_size'] = format_num(trim($boot[1]),2);
		$disk_info['boot_used'] = format_num(trim($boot[2]),2);
		$disk_info['boot_free'] = format_num(trim($boot[3]),2);
		$disk_info['boot_pc'] = trim($boot[4]);
		$disk_info['boot_mount'] = trim($boot[5]);
		$disk_info['boot_hide'] = "ok";
		
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
		$disk_info['boot_filesystem'] = trim($boot[0]);
		$disk_info['boot_size'] = format_num($boot[1]);
		$disk_info['boot_used'] = format_num($boot[2]);
		$disk_info['boot_free'] = format_num($boot[3]);
		$disk_info['boot_pc'] = trim($boot[4]);
		$disk_info['boot_mount'] = trim($boot[5]);
	}	
	if(strstr($home, PHP_EOL)) {
		$home1 = explode(" ",trim(strstr($home, PHP_EOL)));
		$home1 = array_filter($home1);
		
		$home1 = array_slice($home1,0);
		//print_r($home1);
		$disk_info['home_filesystem'] = $home1[0];
		$disk_info['home_size'] = format_num($home1[1]);
		$disk_info['home_used'] = format_num($home1[2]);
		$disk_info['home_free'] = format_num($home1[3]);
		$disk_info['home_pc'] = $home1[4];
		$disk_info['home_mount'] = $home1[5];
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
?>
