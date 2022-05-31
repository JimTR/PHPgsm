#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cli2.php
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
$shortopts ="d::f:s:v::g::t::i::r:q:l::m:";
$longopts[]="debug::";
$longopts[]="help::";
$longopts[]="quick::";
$longopts[]="colour::";
$longopts[]="server:";
$longopts[]="version::";
$longopts[]="games::";
$longopts[]="details::";
$longopts[] ="module::";
$longopts[]="id::";
$longopts[]="start:";
$longopts[]="restart:";
$longopts[] ="quit:";
$longopts[]="log:";
$longopts[]="loop::";
$options = getopt($shortopts,$longopts);
require_once 'includes/master.inc.php';
include 'functions.php';
define ('options',$options);
if(isset($options['debug'])) {
	define('debug',true);
	error_reporting( -1 );
	unset($options['debug']);
	print_r($options);
}
else {
	define('debug',false);
	error_reporting(0);
}
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
use xPaw\SourceQuery\SourceQuery;
define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
define( 'SQ_ENGINE',      SourceQuery::SOURCE );
define( 'LOG',	'logs/ajax.log');
$version = "2.072";
define ('cr',PHP_EOL);
$time = "1645294445";
$build = "30006-2015855331";
define ('CR',PHP_EOL);
define ('borders',array('horizontal' => '─', 'vertical' => '│', 'intersection' => '┼','left' =>'├','right' => '┤','left_top' => '┌','right_top'=>'┐','left_bottom'=>'└','right_bottom'=>'┘','top_intersection'=>'┬'));
if(is_cli()) {

	$shortopts ="d::f:s:v::g::t::i::r:q:l::m:";
	$longopts[]="debug::";
	$longopts[]="help::";
	$longopts[]="quick::";
	$longopts[]="colour::";
	$longopts[]="server:";
	$longopts[]="version::";
	$longopts[]="games::";
	$longopts[]="details::";
	$longopts[] ="module::";
	$longopts[]="id::";
	$longopts[]="start:";
	$longopts[]="restart:";
	$longopts[] ="quit:";
	$longopts[]="log:";
	$longopts[]="loop::";
	$options = getopt($shortopts,$longopts);
	require_once 'includes/master.inc.php';
    include 'functions.php';
	define ('options',$options);
	if(isset($options['debug'])) {
		define('debug',true);
		error_reporting( -1 );
		unset($options['debug']);
		print_r($options);
	}
	else {
		define('debug',false);
		error_reporting(0);

	}

require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');
$version = "2.072";
	define ('cr',PHP_EOL);
$time = "1645294445";
$build = "30006-2015855331";
	define ('CR',PHP_EOL);
	define ('borders',array('horizontal' => '─', 'vertical' => '│', 'intersection' => '┼','left' =>'├','right' => '┤','left_top' => '┌','right_top'=>'┐','left_bottom'=>'└','right_bottom'=>'┘','top_intersection'=>'┬'));

	if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	if (debug) {
		echo 'debug'.cr;
		error_reporting( -1 );
		if (isset($cmds)) {
			foreach ($cmds as $k => $v) {
				if ($k == 'debug'){continue;}
				print "[$k] => $v".cr;
			}
		}
	}
	else {error_reporting( 0 );}
}
else {
	die ('invalid enviroment');
}
$cc = new Color();
define ('warning', $cc->convert("%YWarning%n"));
define ('error', $cc->convert("%RError%n"));
define ('advice', $cc->convert("%BAdvice%n"));
define ('pass',$cc->convert("%GPass%n"));
define ('fail', $cc->convert("%Y  ✖%n"));
$tick = $cc->convert("%g  ✔%n");
$cross = $cc->convert("%r  ✖%n");
if(isset($options['g']) or isset($options['games'])) {
	$cmds['action']='g';
}
if(isset($options['v']) or isset($options['version'])) {$cmds['action']='v';}
if(isset($options['d']) or isset($options['details'])) {
	$cmds['action']='d';
	if (!empty($options['d'])) {
		$cmds['option'] = $options['d'][1];
	}
}
	if(isset($options['t'])) {$cmds['action']='t';}
	if(isset($options['l']) or isset($options['log']))  {
		$cmds['action']='l';
		if(!empty($options['l'])) {
			$cmds['server'] = $options['l'];
		}
		else if(!empty($options['log'])) {
			$cmds['server'] = $options['log'];
		}
		else {
			$cmds['server'] = 'all';
		}
	}
	if(isset($options['i']) or isset($options['id'])) {
		if($options['i'] =='g') {
			$cmds['action'] = 'ig';
		}
		else {
			$cmds['action']='li';
		}
	}
	if(isset($options['server'])) {$cmds['server'] = $options['server'];} 
	if(isset($options['s']) or isset($options['start'])) {
		$cmds['action'] = 's';
		if(!empty($options['s'])) {
			$cmds['server'] = $options['s'];
		}
		if(!empty($options['start'])) {
			$cmds['server'] = $options['start'];
		}
		}
	if(isset($options['r']) or isset($options['restart'])) {
		$cmds['action'] = 'r';
		if(!empty($options['r'])) {
			$cmds['server'] = $options['r'];
		}
		if(!empty($options['restart'])) {
			$cmds['server'] = $options['restart'];
		}
		}	
	if(isset($options['q']) or isset($options['quit'])) {
		$cmds['action'] = 'q';
		if(!empty($options['q'])) {
			$cmds['server'] = $options['q'];
		}
		if(!empty($options['quit'])) {
			$cmds['server'] = $options['quit'];
		}
		}
	if(isset($options['loop'])) {$cmds['loop'] = '';}
	$banner = "cli v $version-$build Noideer Software ©".date('Y').cr;
	if ($cmds['action'] <> 'v'){
		echo $cc->convert("%y$banner%n");
	}
	if (empty($cmds['action'])) {help();}
	//print_r($cmds);
	//die();
switch ($cmds['action']) {
	case 'v' :
	case 'version':	
		echo $banner;
	exit;
	case 'd':
	case 'details':
		details($cmds);
	break;
	case 'games':
	case 'g':
		games($cmds);
	break;
	case 'l':
	case 'log':
		if(!isset($cmds['server'])) {
			echo 'no Server ID supplied'.cr;
			exit;
			}
		if ($cmds['server'] == 'all' || empty($cmds['server'])) {
			echo $cc->convert("%bLog scan%n").cr;
			$exe = urlencode ('cron/cron_scan.php -sall --no-email');
			$cmd = $settings['url'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
			//echo $cmd.cr;
			$content = geturl($cmd);
			if(empty(trim($content))) {
				echo cr.'Log(s) up to date'.cr;
			}
			else {
				echo 'Checking '.$cmds['server'].cr;
				echo "\r$content";
			}
			break;
		}
		$sql = "select * from server1 where host_name like '".trim($cmds['server'])."'";
		$server = $database->get_row($sql);
		if (empty($server)) {
			echo 'invalid Server ID '.$cmds['server'].cr;
			break;
		} 
		$exe = urlencode ('./scanlog.php  -s'.$server['host_name']);
		$cmd = $server['url'].':'.$server['bport'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
		echo 'Full log scan for '.$cmds['server'].cr;
		$content = geturl($cmd);
		if(empty(trim($content))) {
			echo cr.'Log up to date'.cr;
		}
		else {
			echo "\r$content";
		}
		break;
	case 'li':
	case 'list':
		echo $cc->convert("%BList Server ID's%n").cr;
		$table = new table(CONSOLE_TABLE_ALIGN_CENTER,borders,2,null,true,CONSOLE_TABLE_ALIGN_CENTER);
		$table->setHeaders(array($cc->convert("%cGame Server ID%n"),$cc->convert("%cServer ID%n"),$cc->convert("%cHost%n"),$cc->convert("%cLocation%n"),$cc->convert("%cOnline%n")));
		$table->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);
		$table->setAlign(1, CONSOLE_TABLE_ALIGN_RIGHT);
		$table->setAlign(2, CONSOLE_TABLE_ALIGN_RIGHT);
		$table->setAlign(3, CONSOLE_TABLE_ALIGN_RIGHT);
		$sql = "select * from server1 where enabled = 1 order by host_name";
		$hosts = $database->get_results($sql);
		foreach ($hosts as $host) {
			if($host['running'] == 1) {
				$running = $tick;
			}
			else {
				$running = $cross;
			}
			$table->addRow(array($host['server_id'],$host['host_name'],$host['fname'].' ('.$host['host'].')',$host['location'],$running));
		}
		echo $table->getTable();
		break;
	case 'ig':
	case 'igames':
		if (file_exists(DOC_ROOT.'/utils/install.php')) {
			include DOC_ROOT.'/utils/install.php';
		}
		else {
			echo 'installer module missing !'.cr;
		}
		break;
	case 'is':
	case 'iserver':
		if (empty($cmds['path']) || !isset($cmds['path'])) {
			echo error.' invalid install location, correct and retry'.cr;
			break;
		} 
		if (empty($cmds['game']) || !isset($cmds['game'])) {
			echo error.' missing game ID, correct and retry'.cr;
			break;
		} 
		if (empty($cmds['ip']) || !isset($cmds['ip'])) {
			$ip = geturl('https://api.ipify.org');// get ip
			if (empty($ip)) { $ip = geturl('http://ipecho.net/plain');}
		}
		exec('utils/check_r.php '.$cmds['game'],$output,$ret);
		$cds = strpos($output[1] ,'No Data for Server ID');
		if ($cds === false) {
			$game_name = str_replace('Found','',$output[1]);
			$game_name = trim(str_replace('(released)','',$game_name));
		}
		else {
			 echo error.' '.$output[1].cr;
			break;
		}
		$sql = "select * from base_servers where base_ip like '".$ip."'";
		$servers = $database->get_results($sql);
		if(count($servers) == 0) {
			echo warning." no base server record for ($ip), run $argv[0] with the ib switch".cr;
		}
		else {
			$fname = $servers[0]['fname'];
			if($servers[0]['enabled'] == 0) {
				echo warning." Base Server $fname ($ip) is not enabled".cr;
			}
			$server = $servers[0];
			$sql = "select * from game_servers where server_id like '".$cmds['game']."' and installed_on like '".$server['fname']."'";
			$servers = $database->get_results($sql);
			if (count($servers) == 0) {
				$game = $database->get_row("select game_name from game_servers where server_id =".$cmds['game']);
				echo advice.' There is no installation of game server \''.$game_name.'\' on base server '.$fname.', install the game first'.cr;
			}
			else {
				if (is_dir($cmds['path'])) {
					echo warning.' location exists, this operation will overwrite '.$cmds['path'].' ';
					$answer = ask_question('continue y/n ','y','n');
				}
				print_r($servers);
			}
		}
		break; 
	case 'p':
	case 'port':
		
	break;
	case 'r':
	case 'restart':
	if(!isset($cmds['server'])) {
		echo 'no Server ID supplied'.cr;
		exit;
		}
		$sql = "select * from server1 where host_name like '".trim($cmds['server'])."'";
		$server = $database->get_row($sql);
		if (empty($server)) {
			echo 'invalid Server ID '.$cmds['server'].cr;
			break;
		} 
		$cmd = $server['url'].':'.$server['bport'].'/ajaxv2.php?action=exescreen&server='.$server['host_name'].'&key='.md5($server['host']).'&cmd=r';
		echo geturl($cmd).cr;
		break;
	case 's':
	case 'start':
	if(!isset($cmds['server'])) {
		echo 'no Server ID supplied'.cr;
		exit;
		}
		$sql = "select * from server1 where host_name like '".trim($cmds['server'])."'";
		$server = $database->get_row($sql);
		if (empty($server)) {
			echo warning.' invalid Server ID '.$cmds['server'].cr;
			break;
		} 
		$cmd = $server['url'].':'.$server['bport'].'/ajaxv2.php?action=exescreen&server='.$server['host_name'].'&key='.md5($server['host']).'&cmd=s';
		echo geturl($cmd).cr;
	break;
	case 't':
	case'test':
		$table = new table(CONSOLE_TABLE_ALIGN_LEFT,borders,3,null,true,CONSOLE_TABLE_ALIGN_CENTER);
		$option = $cc->convert("%cFile%n");
		$space = chr(032);
	    $use = $cc->convert("%cStatus%n");
	    $notes = $cc->convert("%cResult%n");
	    $v = $cc->convert("%cVersion%n");
	    echo $cc->convert("%BFile Integrity Checker%n").cr;;
		$table->setHeaders(array($option,$use,$notes,$v));
		//echo 'doing all php files'.cr;
			foreach (glob("*.php") as $filename) {
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
				
		}
		foreach (glob("cron/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
		}
		foreach (glob("install/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
		}
		foreach (glob("utils/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
		}
		foreach (glob("includes/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
		}
		foreach (glob("modules/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version']));
		}
		echo $table->getTable();
		break;
			
	case 'q': 
	case 'quit':
	case 'stop':
	if(!isset($cmds['server'])) {
		echo 'no Server ID supplied'.cr;
		exit;
	}
	
		$sql = "select * from server1 where host_name like '".trim($cmds['server'])."'";
		$server = $database->get_row($sql);
		if (empty($server)) {
			echo error.' invalid Server ID '.$cmds['server'].cr;
			break;
		} 
		$cmd = $server['url'].':'.$server['bport'].'/ajaxv2.php?action=exescreen&server='.$server['host_name'].'&key='.md5($server['host']).'&cmd=q';
		echo file_get_contents($cmd).cr;
	break;
	default:
	help();
	echo 'do not get '.$cmds['action'].cr;
}

function help() {
	global $settings,$cmds,$argv,$version,$build;
	$cc = new Color();
	$PHP = $cc->convert("%cPHP%n");
	$gsm = $cc->convert("%rgsm%n");
	$option = $cc->convert("%cOption%n");
	$use = $cc->convert("%cUse%n");
	echo $PHP.$gsm.' Help'.cr;
	echo 'Usage : - '.basename($argv[0]).$cc->convert("%G <option>%n").$cc->convert("%y <sub_options>%n").cr;
	$table = new Table(CONSOLE_TABLE_ALIGN_LEFT,borders,1,null,true,CONSOLE_TABLE_ALIGN_CENTER);
	$table->setHeaders(array($option,$use));
	$table->addRow(array('-s or --start <server id>','starts a game server, use -i to find <server id>'));
	$table->addRow(array('-q, or --quit <server id>','stops a game server'));
	$table->addRow(array('-r, or --restart <server id>','restarts a game server'));
	$table->addRow(array('-d','shows details about the running system (takes sub options)'));
	$table->addRow(array('-g, or --games ','shows details on running game servers (takes sub options)'));
	$table->addRow(array('-l, or --log  <server id>','processes server logs, if server id is set to all scans all servers'));
	$table->addRow(array('-i, or --id ','Lists valid server Id\'s that cli can use.'));
	$table->addRow(array('-v or --version','show version & exit'));
	$option = $cc->convert("%cSub Options%n");
	$use = $cc->convert("%cNotes%n");
	$table->addSeparator();
	$table->addRow(array($option,$use));
	$table->addSeparator();
	$table->addRow(array('-dm(option) ','option can be h,d,s,m e.g '.basename($argv[0]).' -dms' ));
	$table->addRow(array('-f or --file <file>','no clue on what this can do noideer at all' ));
	echo $table->getTable();
	echo cr;
	echo 'cli will only install games or servers on this machine, if you have remotes either use cli on that machine or the web api.'.cr;
	echo 'however, you can control remotely installed servers'.cr;
	exit;
}
 
 function details($data) {
	 // read server details
	 //system('clear');
	 //printr($data,true);
	 if (empty($data['option']) || !isset($data['option'])) {
		 $data['option'] = 'a';
	 }
	 $cc = new Color();
	 $sw = $cc->convert("%WModules%n");
	 $sa = $cc->convert("%WServer%n");
	 $ha = $cc->convert("%WHardware%n");
	 $ma = $cc->convert("%WMemory%n");
	 $da = $cc->convert("%WBoot Disk%n");
	 $da1 = $cc->convert("%WData Disk%n");
	 $da2 = $cc->convert("%WRoot Disk%n");
	 if($data['option'] =='h' || $data['option'] =='a') {
		 //
		  $table = new Table(CONSOLE_TABLE_ALIGN_LEFT,'',4,null,true);
		$cpu_info = get_cpu_info();
		 echo $cc->convert("%BHardware Information%n").cr;
		 $table->addRow(array($ha,''));
		 $table->addRow(array($cc->convert("%y\tUptime%n"),$cpu_info['boot_time']));
		 $table->addRow(array($cc->convert("%y\tCpu Model%n"),$cpu_info['model_name']));
		 $table->addRow(array($cc->convert("%y\tCpu Processors%n"),$cpu_info['processors']));
		 $table->addRow(array($cc->convert("%y\tCpu Cores%n"),$cpu_info['cpu_cores']));
		 $table->addRow(array($cc->convert("%y\tCpu Speed%n"),$cpu_info['cpu_MHz'].' MHz'));
		 $table->addRow(array($cc->convert("%y\tCpu Cache%n"),$cpu_info['cache_size']));
		 $table->addRow(array($cc->convert("%y\tCpu Load%n"),$cpu_info['load']));
		 $table->addRow(array($cc->convert("%y\tIP Address(es) %n"),$cpu_info['ips']));
		 $table->addRow(array($cc->convert("%y\tProcesses%n"),$cpu_info['process']));
		 $table->addRow(array($cc->convert("%y\tReboot Required%n"),$cpu_info['reboot']));
		 echo $table->getTable();
		 echo cr;
	 }
	  if($data['option'] =='m' || $data['option'] =='a') {
		 //
		  $table = new table(CONSOLE_TABLE_ALIGN_LEFT,'',4,null,true,CONSOLE_TABLE_ALIGN_CENTER);
		$mem_info = get_mem_info();
		echo $cc->convert("%BMemory Information%n").cr;
		//$table->addRow(array($ma,''));
		$table->setheaders(array($ma,$cc->convert("%BTotal"),"Free","Cached",$cc->convert("Active%n")));
		$table->addRow(array($cc->convert("%y      Real%n"),$mem_info['MemTotal'],$mem_info['MemFree'],$mem_info['Cached'],$mem_info['Active']));
		$table->addRow(array($cc->convert("%y      Swap%n"),$mem_info['SwapTotal'], $mem_info['SwapFree'],$mem_info['SwapCached']));
		$table->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);
		 $table->setAlign(1, CONSOLE_TABLE_ALIGN_RIGHT);
		 $table->setAlign(2, CONSOLE_TABLE_ALIGN_RIGHT);
		 $table->setAlign(3, CONSOLE_TABLE_ALIGN_RIGHT);
		 $table->setAlign(4, CONSOLE_TABLE_ALIGN_RIGHT);
		echo $table->getTable();
		echo cr;
	}
	  if($data['option'] =='d' || $data['option'] =='a') {
		 //
		  $table = new Table(CONSOLE_TABLE_ALIGN_LEFT,'',4,null,true);
		$disk_info = get_disk_info();
		echo $cc->convert("%BDisk Information%n").cr;
		$table->addRow(array($da,''));
		//$table->addRow(array('',$cc->convert("%BTotal"),"\t\t Free","  Cached",$cc->convert(" Active%n")));
		$table->addRow(array(trim($cc->convert("%y\tFile System%n")),$disk_info['boot_filesystem']));
		$table->addRow(array($cc->convert("%y\tMount Point%n"),$disk_info['boot_mount']));
		$table->addRow(array($cc->convert("%y\tDisk Size%n"),$disk_info['boot_size']));
		$table->addRow(array($cc->convert("%y\tDisk Used%n"),$disk_info['boot_used'].' ('.$disk_info['boot_pc'].')'));
		$table->addRow(array($cc->convert("%y\tDisk Free%n"),$disk_info['boot_free']));
		if(isset($disk_info['home_filesystem'])) {
			$table->addRow(array($da1,''));
			$table->addRow(array(trim($cc->convert("%y\tFile System%n")),$disk_info['home_filesystem']));
			$table->addRow(array($cc->convert("%y\tMount Point%n"),$disk_info['home_mount']));
			$table->addRow(array($cc->convert("%y\tDisk Size%n"),$disk_info['home_size']));
			$table->addRow(array($cc->convert("%y\tDisk Used%n"),$disk_info['home_used'].' ('.$disk_info['home_pc'].')'));
			$table->addRow(array($cc->convert("%y\tDisk Free%n"),$disk_info['home_free']));
		}
		if(isset($disk_info['root_filesystem'])) {
			$table->addRow(array($da1,''));
			$table->addRow(array(trim($cc->convert("%y\tFile System%n")),$disk_info['root_filesystem']));
			$table->addRow(array($cc->convert("%y\tMount Point%n"),$disk_info['root_mount']));
			$table->addRow(array($cc->convert("%y\tDisk Size%n"),$disk_info['root_size']));
			$table->addRow(array($cc->convert("%y\tDisk Used%n"),$disk_info['root_used'].' ('.$disk_info['root_pc'].')'));
			$table->addRow(array($cc->convert("%y\tDisk Free%n"),$disk_info['root_free']));
		}
		echo $table->getTable();
		echo cr;
	}
	 if($data['option'] =='s' || $data['option'] =='a') {
		$table = new Table(CONSOLE_TABLE_ALIGN_LEFT,"",4,null,true);
		$database= new db();
		$software = get_software_info($database);
		echo $cc->convert("%BSoftware Information%n").cr;
		$table->addRow(array($sa,''));
		$table->addRow(array($cc->convert("%y\tServer OS%n"),$software['os']));
		$table->addRow(array($cc->convert("%y\tKernel Version%n"),$software['k_ver']));
		$table->addRow(array($cc->convert("%y\tHost%n"),$software['host']));
		$table->addRow(array($sw,''));
		$table->addRow(array($cc->convert("%y\tPHP Version%n"),$software['php']));
		$table->addRow(array($cc->convert("%y\tScreen Version%n"),$software['screen']));
		$table->addRow(array($cc->convert("%y\tGlibC Version%n"),$software['glibc']));
		$table->addRow(array($cc->convert("%y\tMySql Version%n"),$software['mysql']));
		$table->addRow(array($cc->convert("%y\tApache Version%n"),$software['apache']));
		$table->addRow(array($cc->convert("%y\tCurl Version%n"),$software['curl']));
		$table->addRow(array($cc->convert("%y\tNginX Version%n"),$software['nginx']));
		$table->addRow(array($cc->convert("%y\tQuota Version%n"),$software['quotav']));
		$table->addRow(array($cc->convert("%y\tPostfix Version%n"),$software['postfix']));
		$table->addRow(array($cc->convert("%y\tLitespeed Version%n"),$software['litespeed']));
		$table->addRow(array($cc->convert("%y\tGit Version%n"),$software['git']));
		$table->addRow(array($cc->convert("%y\tTmux Version%n"),$software['tmux']));
		echo $table->getTable();
	}
    exit;
 }
 
 function games($cmds) {
	 // review games
<<<<<<< HEAD
=======
	//system('clear');
>>>>>>> 2c4b273e674149be49f6387cdc2c68683633ff39
	$Query = new SourceQuery( );
	$cc = new Color();
	$table = new Table(CONSOLE_TABLE_ALIGN_CENTER,borders,4,null,true,CONSOLE_TABLE_ALIGN_CENTER);
	$database = new db(); // connect to database
	$sql = 'select * from servers where enabled ="1" and running="1" order by servers.host_name'; //select all enabled & running recorded servers
   	$res = $database->get_results($sql); // pull results
    if(isset($cmds['loop'])) {
		stream_set_blocking(STDIN, 1);
		$table = new Table(CONSOLE_TABLE_ALIGN_CENTER,'',4,null,true,CONSOLE_TABLE_ALIGN_CENTER);
		system("clear"); //start from a known good point
		echo "\033[?25l"; // turn cursor off
	}
    $table->setheaders(array($cc->convert("%cServer%n"), $cc->convert("%cStarted%n"),$cc->convert("%cPlayers Online%n"),$cc->convert("%cCurrent Map%n")));
    echo $cc->convert("%BGame Server Information%n").cr;
    foreach ($res as $gdata) {
		 try {	 
			$Query->Connect( $gdata['host'], $gdata['port'], 1,  SourceQuery::SOURCE  );
			$players = $Query->GetPlayers( ) ;
			$info = $Query->GetInfo();
			$rules = $Query->GetRules( );
		}
		catch( Exception $e ) {
			$Exception = $e;
			if (strpos($Exception,'Failed to read any data from socket')) {
				$Exception = 'Failed to read any data from socket (module viewplayers)';
			}
			$error = date("d/m/Y h:i:sa").' ('.$gdata['host'].':'.$gdata['port'].') '.$Exception;
			$Query->Disconnect( );
			continue;
		}
		$Query->Disconnect( );
		if ($info['Players'] >0) {
			$p1 = trim($info['Players'])-trim($info['Bots']);
			$info['Players'] = $cc->convert("%Y$p1%n");
		}
		else {
			$p1 = trim($info['Players']);
			$info['Players'] = $cc->convert("%B$p1%n");
		}
		$info['MaxPlayers'] = $cc->convert("%b".$info['MaxPlayers']."%n");
		if ($info['MaxPlayers'] <10){
			$info['MaxPlayers'] = $info['MaxPlayers'].' ';
		}
		$playersd =$info['Players'].'/'.$info['MaxPlayers'];
		$host = $cc->convert("%y".$info['HostName']."%n")	;
		$start_date = time2str($gdata['starttime']); 
		$table->addRow(array($host,$start_date,$playersd,$info["Map"]));
	}
	$table->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);
	$table->setAlign(3, CONSOLE_TABLE_ALIGN_RIGHT);
	echo "\0337"; // set cusor position
	echo $table->getTable();
	if(isset($cmds['loop'])) {
<<<<<<< HEAD
		while (false == ($line = CheckSTDIN())){
			sleep (2);
			echo "\0338"; //return to saved cursor
=======
		while (FALSE == ($line = CheckSTDIN())){
			sleep (2);
			echo "\0338";
>>>>>>> 2c4b273e674149be49f6387cdc2c68683633ff39
			$res = $database->get_results($sql);
			$table = new Table(CONSOLE_TABLE_ALIGN_CENTER,'',4,null,true,CONSOLE_TABLE_ALIGN_CENTER);
			$table->setheaders(array($cc->convert("%cServer%n"), $cc->convert("%cStarted%n"),$cc->convert("%cPlayers Online%n"),$cc->convert("%cCurrent Map%n")."\033[0K"));
			foreach ($res as $gdata) {
				try {	 
					$Query->Connect( $gdata['host'], $gdata['port'], 1,  SourceQuery::SOURCE  );
					$players = $Query->GetPlayers( ) ;
					$info = $Query->GetInfo();
					$rules = $Query->GetRules( );
				}
				catch( Exception $e ) {
					$Exception = $e;
					if (strpos($Exception,'Failed to read any data from socket')) {
						$Exception = 'Failed to read any data from socket (module viewplayers)';
					}
					$error = date("d/m/Y h:i:sa").' ('.$gdata['host'].':'.$gdata['port'].') '.$Exception;
					$Query->Disconnect( );
					continue;
				}
				$Query->Disconnect( );
				if ($info['Players'] >0) {
					$p1 = trim($info['Players'])-trim($info['Bots']);
					$info['Players'] = $cc->convert("%Y$p1%n");
				}
				else {
					$p1 = trim($info['Players']);
					$info['Players'] = $cc->convert("%B$p1%n");
				}	
				$info['MaxPlayers'] = $cc->convert("%b".$info['MaxPlayers']."%n");
				if ($info['MaxPlayers'] <10){
					$info['MaxPlayers'] = $info['MaxPlayers'].' ';
				}
				$playersd =$info['Players'].'/'.$info['MaxPlayers'];
				$host = $cc->convert("%y".$info['HostName']."%n")	;
				$start_date = time2str($gdata['starttime']); 
				$table->addRow(array($host,$start_date,$playersd,$info["Map"]."\033[0K"));
			}
			$table->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);
			$table->setAlign(3, CONSOLE_TABLE_ALIGN_RIGHT);
			echo $table->getTable();
			echo "\033[0J";
			$x++;
		}
		
		echo 'Thanks for watching ! '."\xf0\x9f\x98\x80\x0a\x00";
		echo "\033[?25h";
	}
    
 }
  function dostuff() {
	  echo <<<EOL

         (__)
         (oo)
  /-------\/ Moooooo
 / |     ||
*  ||----||
   ^^    ^^
   
EOL;
echo "\xf0\x9f\x92\xa9\x0a\x00";
  }
 
 function check_file($file_name) {
	  // test file
	global $tick,$cross;
	$cc = new Color; 
	$return=array();
	if(is_file($file_name) == false){
		echo error.' Could not find '.$file_name.cr;
		$return['reason'] = ' Could not find ';
		$return['symbol'] = $cross;
		$return['status'] = false;
		return $return;
	}
	
	$file = file_get_contents($file_name);
	$fsize = filesize($file_name)+1;
	$nf = explode(cr,$file);
	$matches = array_values(preg_grep('/\$build = "\d+-\d+"/', $nf));
	$v = array_values(preg_grep('/\$version = "\d+.\d+"/', $nf));
	$v1 = array_values(preg_grep('/\$version = "\d+.\d+.\d+"/', $nf));
	if (!empty($v)) {
	//print_r($v);
	$version = trim(str_replace('$version = "','',$v[0]));
	$version = trim(str_replace('";','',$version));
	//echo $version.cr;
	//print_r($matches);
	}
	if (!empty($v1)) {
	//print_r($v);
	$version = trim(str_replace('$version = "','',$v1[0]));
	$version = trim(str_replace('";','',$version));
	//echo $version.cr;
	//print_r($matches);
	}
	if (empty($matches) and empty($version)) {
	//echo error.' unable to check '.$file_name.' file structure is incorrect'.$cross.cr;
	$return['file_name'] = $file_name;
	$return['reason'] = error." Unable To Check ! The file structure is incorrect";
	$return['symbol'] = $cross;
	$return['status'] = false;
	$return['fsize'] = $fsize;
	$return['build'] ='';
	$return['full_version'] = $version;
	return $return;
	}
	
	//echo 'file '.$file_name.' - '.$matches[0].cr;
	$oldbp = strpos($file,'$build');
	$eol = strpos($file,';',$oldbp);
	$build = substr($file,$oldbp,$eol-$oldbp);
	$tmp = substr_replace($file,'',$oldbp,$eol-$oldbp);
	$ns = crc32($tmp);
    $length= strlen($tmp);
    //echo 'file '.$file_name.' - '.$tmp.' '.$ns.cr;
	//$build = trim($matches[0]);
	$build = str_replace('$build = "','',$build);
	$build = str_replace('"','',$build);
	$b_detail = explode('-',$build);
	//echo print_r($b_detail,true).cr;
	//echo "\$fsize = $fsize \$ns = $ns strlen = $length".cr;
	if (!empty($version) and empty($matches)) {
	$return['file_name'] = $file_name;
	$return['reason'] = pass.", user configured file";
	$return['symbol'] = $tick;
	$return['status'] = true;
	$return['fsize'] = $fsize;
	$return['build'] ='';
	$return['full_version'] = "$version-$fsize-$ns";
	return $return;
	}	
	if ($b_detail[0] == $length and $ns == $b_detail[1]) {
		
		//echo advice.' '.$file_name.$tick.cr;
		$return['file_name'] = $file_name;
		$return['reason'] = pass .", File is up to date";
		$return['symbol'] = trim($tick);
		$return['status'] = 1;
		$return['fsize'] = $fsize;
		$return['build'] = $ns;
		$return['version'] = $version;
		$return['full_version'] = "$version-$fsize-$ns";
		return $return;
	}
	else {
		//echo $file_name.' has an error !, it\'s not as we coded it  '.cr;
		//echo 'have you editied the file ? If so you need to re install a correct copy.'.cr;
		$return['file_name'] = $file_name;
		$return['reason'] = warning." File has altered !";
		$return['symbol'] = fail;
		$return['status'] = 2;
		$return['fsize'] = $fsize;
		$return['build'] = $ns;
		$return['version'] = $version;
		$return['full_version'] = "$version-$fsize-$ns";
		return $return;
	}
}

function CheckSTDIN() {
<<<<<<< HEAD
	$read = array(STDIN);
	$wrte = NULL;
	$expt = NULL;
=======
    $read = array(STDIN);
    $wrte = NULL;
    $expt = NULL;
>>>>>>> 2c4b273e674149be49f6387cdc2c68683633ff39
    $a = stream_select($read, $wrte, $expt, 0);
    if ($a && in_array(STDIN, $read)) {
        // you can read from STDIN now, it'll only be available if there is anything in STDIN
        // you can return the value or pass it to global or class variable
        return fread(STDIN, 1); // set your desired string length
    } else return false;
}

?>
