#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cli.php v3
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
	$options = getopt($shortopts,$longopts);
	require_once 'inc/master.inc.php';
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

require DOC_ROOT. '/inc/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');

$build = "30925-1478332564";
$version = "3.01";
$time = "1639295486";
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	define ('borders',array('horizontal' => '─', 'vertical' => '│', 'intersection' => '┼','left' =>'├','right' => '┤','left_top' => '┌','right_top'=>'┐','left_bottom'=>'└','right_bottom'=>'┘','top_intersection'=>'┬'));

	if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	
	if (debug) {
		echo 'debug'.cr;
		error_reporting( -1 );
		//echo 'Cli interface v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
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
	define ('error', $cc->convert("%RError%n"));
	define ('advice', $cc->convert("%BAdvice%n"));
	define ('pass',$cc->convert("%GPass%n"));
	define ('fail', $cc->convert("%Y  ✖%n"));
	define ('update',$cc->convert("%CUpdated%n"));
	$tick = $cc->convert("%g  ✔%n");
    $cross = $cc->convert("%r  ✖%n");
    $update = $cc->convert("%C >%n");
   
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
	if(isset($options['i']) or isset($options['id'])) 
	{
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
	//if(isset($options['q'])) {$cmds['action'] = 'q';}
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
	
	//echo $cmds['server'].cr;
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
			//echo print_r($hosts,true).cr;
			foreach ($hosts as $host) {
				//echo $host['host_name'].' '.$host['location'].' '.$host['fname'].' ('.$host['host'].')'.cr;
				if($host['running'] == 1) {
					//running
					$running = $tick;
				}
				else {
					//not running
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
	    $t = $cc->convert("%cRelease Date%n");
	    echo $cc->convert("%BFile Integrity Checker%n").cr;;
		$table->setHeaders(array($option,$use,$notes,$v,$t));
		//echo 'doing all php files'.cr;
			foreach (glob("*.php") as $filename) {
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version'],$check['time']));
				
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
		foreach (glob("inc/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version'],$check['time']));
		}
		foreach (glob("modules/*.php") as $filename) {
		
				$check = check_file($filename);
				$table->addRow(array($check['file_name'],$check['symbol'],$check['reason'],$check['full_version'],$check['time']));
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
	//$table->addRow(array('','',''));
	$table->setHeaders(array($option,$use));
	$table->addRow(array('-s or --start <server id>','starts a game server, use -i to find <server id>'));
	$table->addRow(array('-q, or --quit <server id>','stops a game server'));
	$table->addRow(array('-r, or --restart <server id>','restarts a game server'));
	$table->addRow(array('-d','shows details about the running system (takes sub options)'));
	$table->addRow(array('-g, or --games ','shows details on running game servers (takes sub options)'));
	//$table->addRow(array('ig, or igames ','Installs a game from Steam (takes sub options see example page)'));
	//$table->addRow(array('is, or iserver ','Installs a server from an installed game (takes sub options see example page)'));
	//$table->addRow(array('u, or user ','shows user details (takes sub options see example page)'));
	$table->addRow(array('-l, or --log  <server id>','processes server logs, if server id is set to all scans all servers'));
	$table->addRow(array('-i, or --id ','Lists valid server Id\'s that cli can use.'));
	$table->addRow(array('-v or --version','show version & exit'));
	 //system('clear');
	//echo 'Cli interface v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	 
	 	 //echo $table1->getTable();
	 $option = $cc->convert("%cSub Options%n");
	 $use = $cc->convert("%cNotes%n");
	 $table->addSeparator();
	 $table->addRow(array($option,$use));
	 $table->addSeparator();
	 $table->addRow(array('-dm(option) ','option can be h,d,s,m e.g '.basename($argv[0]).' -dms' ));
	 $table->addRow(array('-f or --file <file>','no clue on what this can do noideer at all' ));
	 //$table->setAlign(0, CONSOLE_TABLE_ALIGN_CENTER);
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
	 		global $database;
	 		$Query = new SourceQuery( );
	 		$cc = new Color();
	 		$table = new Table(CONSOLE_TABLE_ALIGN_CENTER,borders,4,null,true,CONSOLE_TABLE_ALIGN_CENTER);
	//$database = new db(); // connect to database
	$sql = 'select * from servers where enabled ="1" and running="1" order by servers.host_name'; //select all enabled & running recorded servers
    $res = $database->get_results($sql); // pull results
    //echo print_r($res,true).cr;
    //^[[0;34mblue^[[0m
    $table->setheaders(array($cc->convert("%cServer%n"), $cc->convert("%cStarted%n"),$cc->convert("%cPlayers Online%n"),$cc->convert("%cCurrent Map%n")));
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
		$info['MaxPlayers'] = $cc->convert("%b".$info['MaxPlayers']."%n");
		if ($info['MaxPlayers'] <10){
			$info['MaxPlayers'] = $info['MaxPlayers'].' ';
		}
	$playersd =$info['Players'].'/'.$info['MaxPlayers'];
	$host = $cc->convert("%y".$info['HostName']."%n")	;
	$start_date =date('g:ia \o\n l jS F Y \(e\)',$gdata['starttime']);
	$table->addRow(array($host,$start_date,$playersd,$info["Map"]));
	//printf($headmask,"\e[38;5;82m".$info['HostName'],"\e[97m started at",date('g:ia \o\n l jS F Y \(e\)', $data['starttime']),"Players Online ".$playersd." Map - ".$info["Map"]);
	}
	$table->setAlign(0, CONSOLE_TABLE_ALIGN_RIGHT);
	$table->setAlign(3, CONSOLE_TABLE_ALIGN_RIGHT);
	//$table->setAlign(2, CONSOLE_TABLE_ALIGN_RIGHT);
	echo $table->getTable();
    exit;
 }
 
 function check_file($file_name) {
	  // test file
	global $tick,$cross,$update;
	$cc = new Color; 
	$return=array();
	if(is_file($file_name) == false){
		echo error.' Could not find '.$file_name.cr;
		$return['reason'] = ' Could not find ';
		$return['symbol'] = $cross;
		$return['status'] = false;
		return $return;
	}
	
	$file = file_get_contents($file_name); // got the file
	$fsize = filesize($file_name); // not sure with this
	$nf = explode(cr,$file);// turn file to array
	$matches = array_values(preg_grep('/\$build = "\d+-\d+"/', $nf));
	if (!empty($matches)){$b_match =$matches[0];} else{ $b_match = '';} 
	$matches = array_values(preg_grep('/\$version = "\d+.\d+"/', $nf));
	if (!empty($matches)){$v_match =$matches[0];} else{ $v_match = '';} 
	$matches = array_values(preg_grep('/\$version = "\d+.\d+.\d+"/', $nf));
	if (!empty($matches)){$v1_match =$matches[0];} else{ $v1_match = '';} 
	$matches = array_values(preg_grep('/\$time = "\d+"/', $nf));
	if (!empty($matches)){$t_match =$matches[0];} else{ $t_match = '';}
	$nf = remove_item($nf,$b_match); // build info
	$nf = remove_item($nf,$v_match); // duplet
	$nf = remove_item($nf,$v1_match); //triplet
	$nf = remove_item($nf,$t_match); // time string
	$length = strlen(implode(cr,$nf)); // string length
	$crc = crc32(implode(cr,$nf)); // crc the remaining
	if (!empty($t_match)) {
		$t = trim(str_replace('$time = "','',$t_match));
		$t = trim(str_replace('";','',$t));
		$time = date("d-m-Y H:i:s",$t);
	}
	else {$time='0';}
	if (!empty($v_match)) {
	//print_r($v);
	$version = trim(str_replace('$version = "','',$v_match));
	$version = trim(str_replace('";','',$version));
	}
	if (!empty($v1_match)) {
	//print_r($v);
	$version = trim(str_replace('$version = "','',$v1_match));
	$version = trim(str_replace('";','',$version));

	}
	if ($b_match=='' and empty($version)) {
		$version = '';
	//echo error.' unable to check '.$file_name.' file structure is incorrect'.$cross.cr;
	$return['file_name'] = $cc->convert("%R $file_name%n");
	$return['reason'] = error.$cc->convert("%R The file structure is incorrect%n");
	$return['symbol'] = $cross;
	$return['status'] = false;
	$return['fsize'] = $length;
	$return['build'] ='';
	$return['full_version'] = $version;
	//$return['time'] = 0;
	return $return;
	}
	$build = str_replace('$build = "','',$b_match);
	$build = str_replace('";','',$build);
	$b_detail = explode('-',$build);
    
	if (!empty($version) and $b_match == '' ) {
	$return['file_name'] = $cc->convert("%W $file_name%n");
	$return['reason'] = pass.", " .$cc->convert("%Wuser configured file%n");
	$return['symbol'] = $tick;
	$return['status'] = true;
	$return['fsize'] = $length;
	$return['build'] ='';
	$return['full_version'] = $cc->convert("%W$version-$fsize-$crc%n");
	$return['time'] = date ("d-m-Y H:i:s", filectime($file_name));
	return $return;
	}	
	if ($b_detail[0] == $length and $crc == $b_detail[1]) {
		$remote_file = check_remote_file($file_name); // see if there's an update
		
		if (empty($remote_file['time'])) { 
			$return['reason'] = error.$cc->convert("%R file not found in ".settings['branch']."%n");
			$return['symbol'] = $cross;
			$file_name= $cc->convert("%R ".$file_name."%n");
			$d_version = $cc->convert("%R$version-$fsize-$crc"."%n");
			$time  = $cc->convert("%C$time%n");
			}
		elseif ($remote_file['time'] == $t) {
			 $return['reason'] = pass .$cc->convert("%C, File is up to date%n"); 
			 $return['symbol'] = trim($tick); 
			 $file_name = $cc->convert("%C ".$file_name."%n");
			 $d_version = $cc->convert("%C$version-$length-$crc"."%n");
			 $time  = $cc->convert("%C$time%n");
			 }
		elseif ($remote_file['time'] < $t) 
		{
			 $return['reason'] = warning.",".$cc->convert("%Ylocal file is newer than source.%n");
			 $return['symbol'] = fail; $file_name = $cc->convert("%Y ".$file_name."%n");
			 $d_version = $cc->convert("%Y$version-$length-$crc"."%n");
			 $time  = $cc->convert("%Y$time%n");
			 			 		 }
		elseif ($remote_file['time'] > $t) 
		{ 
			$return['reason'] = update." ".$cc->convert("%c".date("d-m-Y H:i:s",$remote_file['time'])."%n");
			$return['symbol'] = " ".$update;
			$file_name = $cc->convert("%C ".$file_name."%n");
			$d_version = $cc->convert("%R$version-$length-$crc"."%n");
			$time  = $cc->convert("%C$time%n");
			}
		
		$return['file_name'] = $file_name;
		$return['status'] = 1;
		$return['fsize'] = $fsize;
		$return['build'] = $crc;
		$return['version'] = $version;
		$return['full_version'] = $d_version;
		$return['time'] = $time;
		return $return;
	}
	else {
		//echo $file_name.' has an error !, it\'s not as we coded it  '.cr;
		//echo 'have you editied the file ? If so you need to re install a correct copy.'.cr;
		$return['file_name'] = $cc->convert("%Y $file_name%n");
		$return['reason'] = warning.$cc->convert("%Y File has altered%n");
		$return['symbol'] = fail;
		$return['status'] = 2;
		$return['fsize'] = $fsize;
		$return['build'] = $crc;
		$return['version'] = $version;
		$return['full_version'] = $cc->convert("%Y$version-$length-$crc%n");
		$return['time'] = $time;
		return $return;
	}
}

function remove_item($array,$value) {
	// remove item from array
	$remove = array_search_partial($array,$value);
	if(!$remove == false ) {
		unset($array[$remove]);
	}
	return $array;
}
function arrayInsert($array, $position, $insertArray)
{
    $ret = [];

    if ($position == count($array)) {
        $ret = $array + $insertArray;
    }
    else {
        $i = 0;
        foreach ($array as $key => $value) {
            if ($position == $i++) {
                $ret += $insertArray;
            }

            $ret[] = $value;
        }
    }

    return $ret;
}

function check_remote_file($file_name) {
	$file ="https://raw.githubusercontent.com/JimTR/PHPgsm/".settings['branch']."/$file_name"; // need to have this as a branch setting
	//echo "file = $file".cr;
	$raw = geturl($file);
	$nf = explode(cr,$raw);// turn file to array
	$matches = array_values(preg_grep('/\$build = "\d+-\d+"/', $nf));
	if (!empty($matches)){$b_match =$matches[0];} else{ $b_match = '';} 
	$matches = array_values(preg_grep('/\$version = "\d+.\d+"/', $nf));
	if (!empty($matches)){$v_match =$matches[0];} else{ $v_match = '';} 
	$matches = array_values(preg_grep('/\$version = "\d+.\d+.\d+"/', $nf));
	if (!empty($matches)){$v1_match =$matches[0];} else{ $v1_match = '';} 
	$matches = array_values(preg_grep('/\$time = "\d+"/', $nf));
	if (!empty($matches)){$t_match =$matches[0];} else{ $t_match = '';}
	$time = trim(str_replace('$time = "','',$t_match));
	$time = trim(str_replace('";','',$time));
	$version = trim(str_replace('$version = "','',$v_match));
	$version = trim(str_replace('";','',$version));
	$build = str_replace('$build = "','',$b_match);
	$build = str_replace('";','',$build);
	$return['build'] = $build;
	$return['time'] = $time;
	$return['version'] = $version;
	return $return;
}
?>
