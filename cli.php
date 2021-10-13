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
	$shortopts ="d::f:s:v::g::t::li::r:q:l:";
	$longopts[]="debug::";
	$longopts[]="help::";
	$longopts[]="quick::";
	$longopts[]="colour::";
	$longopts[]="server:";
	$longopts[]="version::";
	$longopts[]="games::";
	$longopts[]="details::";
	$longopts[]="list::";
	$longopts[]="start:";
	$longopts[]="restart:";
	$longopts[] ="quit:";
	$longopts[]="log:";
	$options = getopt($shortopts,$longopts);
	
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
require_once 'includes/master.inc.php';

include 'functions.php';

require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');
	$version = 2.05;
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	
	$build = "23371-3855903511";
	
	
	if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	
	if (debug) {
		echo 'debug'.cr;
		error_reporting( -1 );
		echo 'Cli interface v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
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
//system('clear');
$cc = new Color();
	define ('warning', $cc->convert("%YWarning%n"));
	define ('error', $cc->convert("%RError  %n"));
	define ('advice', $cc->convert("%BAdvice%n"));
	define ('pass',$cc->convert("%GPassing%n"));
	define ('fail', $cc->convert("%Y  ✖%n"));
	$tick = $cc->convert("%g  ✔%n");
    $cross = $cc->convert("%r  ✖%n");
    //print_r($options);
    if(isset($options['g']) or isset($options['games'])) {$cmds['action']='g';}
	if(isset($options['v']) or isset($options['version'])) {$cmds['action']='v';}
	if(isset($options['d']) or isset($options['details'])) {$cmds['action']='d';}
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
	if(isset($options['l']) and isset($options['i']) or isset($options['list'])) {$cmds['action']='li';}
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
	//if(isset($options['q'])) {$cmds['action'] = 'q';}
	
	if (empty($cmds['action'])) {help();}
switch ($cmds['action']) {
	
	case 'v' :
	case 'version':	
		echo 'Cli interface v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
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
		if ($cmds['server'] == 'all') {
			echo 'Quick log scan';
			$exe = urlencode ('./scanlog.php -sall');
			$cmd = $settings['url'].'/ajaxv2.php?action=exe&cmd='.$exe.'&debug=true';
			//echo $cmd.cr;
			$content = file_get_contents($cmd);
			if(empty(trim($content))) {
			echo cr.'Log(s) up to date'.cr;
		}
		else {
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
		echo 'Full log scan for '.$cmds['server'];
		$content = file_get_contents($cmd);
		if(empty(trim($content))) {
			echo cr.'Log up to date'.cr;
		}
		else {
			echo "\r$content";
		}
		break;
	case 'li':
	case 'list':
			echo 'list server id\'s'.cr;
			$sql = "select host_name from server1 where enabled = 1 order by host_name";
			$hosts = $database->get_results($sql);
			//echo print_r($hosts,true).cr;
			foreach ($hosts as $host) {
				echo $host['host_name'].cr;
			}
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
				 $ip = file_get_contents('https://api.ipify.org');// get ip
				if (empty($ip)) { $ip = file_get_contents('http://ipecho.net/plain');}
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
			//echo $ip.cr;
			$sql = "select * from base_servers where base_ip like '".$ip."'";
			$servers = $database->get_results($sql);
			if(count($servers) == 0) {
				echo warning." no base server record for ($ip), run $argv[0] with the ib switch".cr;
			}
			else {
				// we have a valid base server do we have any installs ?
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
		echo file_get_contents($cmd).cr;
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
		echo file_get_contents($cmd).cr;
	break;
	case 't':
	case'test':
		$table = new Table(CONSOLE_TABLE_ALIGN_LEFT, array('horizontal' => '', 'vertical' => '', 'intersection' => ''));
		$option = $cc->convert("%cFile%n");
	    $use = $cc->convert("%c\t   Status%n");
	    $notes = $cc->convert("%c\t\tResult%n");
	    echo cr;
	    $table->setHeaders( array ($option,$use,$notes));
		//$table->addRow(array('','',''));
		//$table->addRow(array($option,$use,$notes,''));
		echo $cc->convert("%BFile Integrity Checker%n").cr;
		
		//echo 'doing all php files'.cr;
			foreach (glob("*.php") as $filename) {
		
				$check = check_file($filename);
				if ($check['status']) {
					$table->addRow(array($filename,$check['symbol'],pass.' build '.$check['fsize'].'-'.$check['build']));
				}
				else {
					$table->addRow(array($filename,$check['symbol'],$check['reason']));
				}
		}
		foreach (glob("install/*.php") as $filename) {
		
				$check = check_file($filename);
				if ($check['status']) {
					$table->addRow(array($filename,$check['symbol'],pass.' build '.$check['fsize'].'-'.$check['build']));
				}
				else {
					$table->addRow(array($filename,$check['symbol'],$check['reason']));
				}
		}
		foreach (glob("utils/*.php") as $filename) {
		
				$check = check_file($filename);
				if ($check['status']) {
					$table->addRow(array($filename,$check['symbol'],pass.' build '.$check['fsize'].'-'.$check['build']));
				}
				else {
					$table->addRow(array($filename,$check['symbol'],$check['reason']));
				}
		}
		foreach (glob("includes/*.php") as $filename) {
		
				$check = check_file($filename);
				if ($check['status']) {
					$table->addRow(array($filename,$check['symbol'],pass.' build '.$check['fsize'].'-'.$check['build']));
				}
				else {
					$table->addRow(array($filename,$check['symbol'],$check['reason']));
				}
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
	$use = $cc->convert("%c\t\tUse%n");
	$table = new Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
$table->addRow(array('','',''));
$table->addRow(array($PHP.$gsm.' Help',''));
$table->addRow(array('Usage : - '.basename($argv[0]).' action=<option> <sub_options>',''));
$table->addRow(array($option,$use));
$table->addRow(array('-v or --version','show CLI version & exit'));
$table->addRow(array('-s or --start <server id>','starts a game server'));
$table->addRow(array('-q, --quit <server id>','stops a game server'));
$table->addRow(array('-r, or --restart <server id>','restarts a game server'));
$table->addRow(array('-d, or --details ','shows details about the running system (takes sub options see example page)'));
$table->addRow(array('-g, or --games ','shows details on running game servers (takes sub options see example page)'));
$table->addRow(array('ig, or igames ','Installs a game from Steam (takes sub options see example page)'));
$table->addRow(array('is, or iserver ','Installs a server from an installed game (takes sub options see example page)'));
$table->addRow(array('u, or user ','shows user details (takes sub options see example page)'));
$table->addRow(array('-l, or --log  <server id>','processes server logs, if server id is set to all scans all (default)'));
$table->addRow(array('-li, or --list ','Lists valid server Id\'s that cli can use.'));
	 //system('clear');
	echo 'Cli interface v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	 echo $table->getTable();
	 echo cr;
	 echo 'cli will only install games or servers on this machine, if you have remotes either use cli on that machine or the web api.'.cr;
	 echo 'however, you can control remotely installed servers'.cr;
	 //$answer = ask_question('enter E for examples or q to quit  ',null,null);
	 echo $answer.cr.cr;
	 exit;
 }
 
 function details($data) {
	 // read server details
	 //system('clear');
	 if (empty($data['option']) || !isset($data['option'])) {
		 $data['option'] = 'a';
	 }
	 $cc = new Color();
	 $sw = $cc->convert("%W   Modules%n");
	 $sa = $cc->convert("%W    Server%n");
	 $ha = $cc->convert("%W    Hardware%n");
	 $ma = $cc->convert("%W    Memory%n");
	 $da = $cc->convert("%W     Boot Disk%n");
	 $da1 = $cc->convert("%W     Data Disk%n");
	 if($data['option'] =='h' || $data['option'] =='a') {
		 //
		  $table = new Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
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
		 $table->addRow(array($cc->convert("%y\tIP Address %n"),$cpu_info['local_ip']));
		 $table->addRow(array($cc->convert("%y\tProcesses%n"),$cpu_info['process']));
		 $table->addRow(array($cc->convert("%y\tReboot Required%n"),$cpu_info['reboot']));
		 echo $table->getTable();
		 echo cr;
	 }
	  if($data['option'] =='m' || $data['option'] =='a') {
		 //
		  $table = new Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
		$mem_info = get_mem_info();
		echo $cc->convert("%BMemory Information%n").cr;
		$table->addRow(array($ma,''));
		$table->addRow(array('',$cc->convert("%BTotal"),"\t\t Free","  Cached",$cc->convert(" Active%n")));
		$table->addRow(array(trim($cc->convert("%y    Mem%n")),"\t\t".$mem_info['MemTotal'],"\t".$mem_info['MemFree']," ".$mem_info['Cached'],"  ".$mem_info['Active']));
		$table->addRow(array($cc->convert("%y   Swap%n"),"\t\t".$mem_info['SwapTotal'], "\t".$mem_info['SwapFree'],$mem_info['SwapCached']));
		echo $table->getTable();
		echo cr;
	}
	  if($data['option'] =='d' || $data['option'] =='a') {
		 //
		  $table = new Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
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
		echo $table->getTable();
		echo cr;
	}
	 if($data['option'] =='s' || $data['option'] =='a') {
	 $table = new Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
    $database= new db();
    $software = get_software_info($database);
    //echo print_r($data,true).cr;
    //echo print_r($software,true).cr;
    echo $cc->convert("%BSoftware Information%n").cr;
    //$table->addRow(array('','',''));
    //$table->addRow(array($cc->convert("%bSoftware Information%n"),'',''));
   
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
 
 function games($data) {
	 // review games
	 		//system('clear');
	 		$Query = new SourceQuery( );
	 		$cc = new Color();
	 			  $table = new Table(
    CONSOLE_TABLE_ALIGN_RIGHT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
	$database = new db(); // connect to database
	$sql = 'select * from servers where enabled ="1" and running="1" order by servers.host_name'; //select all enabled & running recorded servers
    $res = $database->get_results($sql); // pull results
    //echo print_r($res,true).cr;
    //^[[0;34mblue^[[0m
    $table->addRow(array("\t\tServer", "\tStarted"," Online\tCurrent Map"));
    echo $cc->convert("%BGame Server Information%n").cr;
    foreach ($res as $gdata) {
		 //echo print_r($gdata,true).cr;
	try {	 
		$Query->Connect( $gdata['host'], $gdata['port'], 1,  SourceQuery::SOURCE  );
		$players = $Query->GetPlayers( ) ;
		$info = $Query->GetInfo();
		$rules = $Query->GetRules( );
	}
	catch( Exception $e )
					{
						$Exception = $e;
						if (strpos($Exception,'Failed to read any data from socket')) {
							$Exception = 'Failed to read any data from socket (module viewplayers)';
						}
						
						  $error = date("d/m/Y h:i:sa").' ('.$gdata['host'].':'.$gdata['port'].') '.$Exception;
						  //sprintf("[%14.14s]",$str2)
						  //echo $error.cr;
						  $mask = "%17.17s %-30.30s \n";
						 //file_put_contents('logs/xpaw.log',$error.CR,FILE_APPEND);
						 $Query->Disconnect( );
						 continue;
						 
					}
	$Query->Disconnect( );
	//print_r($info);
	if ($info['Players'] >0) {
		$p1 = trim($info['Players']);
		$info['Players'] = $cc->convert("%B$p1%n");
		}
		else {
			$p1 = trim($info['Players']);
		$info['Players'] = $cc->convert("%Y$p1%n");
		}
	$playersd =$info['Players'].'/'.$info['MaxPlayers'];
	//echo $playersd.cr;
	$host = $cc->convert("%y".$info['HostName']."%n");
	$table->addRow(array('',$host,date('g:ia \o\n l jS F Y \(e\)',"\t".$gdata['starttime'])."\t",$playersd,"\t".$info["Map"].""));
	//printf($headmask,"\e[38;5;82m".$info['HostName'],"\e[97m started at",date('g:ia \o\n l jS F Y \(e\)', $data['starttime']),"Players Online ".$playersd." Map - ".$info["Map"]);
	}
	echo $table->getTable();
    exit;
 }
 
 function check_file($file_name) {
	  // test file
	global $tick,$cross;
	if(is_file($file_name) == false){
		echo error.' Could not find '.$file_name.cr;
		$return['reason'] = ' Could not find ';
		$return['symbol'] = $cross;
		$return['status'] = false;
		return $return;
	}
	
	$file = file_get_contents($file_name);
	$fsize = filesize($file_name);
	$nf = explode(cr,$file);
	$matches = array_values(preg_grep('/\$build = "\d+-\d+"/', $nf));
	$v = array_values(preg_grep('/\$version = "\d+.\d+"/', $nf));
	if (empty($matches)) {
	//echo error.' unable to check '.$file_name.' file structure is incorrect'.$cross.cr;
	$return['reason'] = error.' unable to check, the file structure is incorrect';
	$return['symbol'] = $cross;
	$return['status'] = false;
	$return['fsize'] = $fsize;
	$return['build'] ='';
	return $return;
	}
	//echo 'file '.$file_name.' - '.$matches[0];
	$tmp = str_replace($matches[0],'',$file);
    $ns = crc32($tmp);
    //echo 'file '.$file_name.' - '.$matches[0].' '.$ns.cr;
	$build = trim($matches[0]);
	$build = str_replace('$build = "','',$build);
	$build = str_replace('";','',$build);
	$b_detail = explode('-',$build);
	
	if ($b_detail[0] == $fsize and $ns == $b_detail[1]) {
		
		//echo advice.' '.$file_name.$tick.cr;
		$return['reason'] = 'file Passed';
		$return['symbol'] = trim($tick);
		$return['status'] = true;
		$return['fsize'] = $fsize;
		$return['build'] = $ns;
		return $return;
	}
	else {
		//echo $file_name.' has an error !, it\'s not as we coded it  '.cr;
		//echo 'have you editied the file ? If so you need to re install a correct copy.'.cr;
		$return['reason'] = warning.' fails check, file has altered ';
		$return['symbol'] = fail;
		$return['status'] = false;
		$return['fsize'] = $fsize;
		$return['build'] = $ns;
		return $return;
	}
}
?>
