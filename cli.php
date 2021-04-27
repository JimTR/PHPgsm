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
require_once 'includes/master.inc.php';
require_once 'includes/class.table.php';
require_once 'includes/class.color.php';
include 'functions.php';
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');
	define( 'VERSION', 2.02);
	define ('cr',PHP_EOL);
	define ('CR',PHP_EOL);
	error_reporting (0);
	if(is_cli()) {
	$valid = 1; // we trust the console
	$sec = true;
	$cmds =convert_to_argv($argv,"",true);
	
	if ($cmds['debug'] == 'true') {
		error_reporting( -1 );
		echo 'Ajax v'.VERSION.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	    foreach ($cmds as $k => $v) {
			if ($k == 'debug'){continue;}
			print "[$k] => $v".cr;
		}
		if (empty($cmds['action'])) {help();}
	}
	else {error_reporting( 0 );}
	
}
else {
	die ('invalid enviroment');
}
switch ($cmds['action']) {
	
	case 'v' :
	case 'version':	
		echo 'Cli interface v'.VERSION.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	exit;
	case 'd':
	case 'details':
		details($cmds);
	break;
	case 'games':
	case 'g':
		games($cmds);
	break;
	case 'p':
	case 'port':
		
	break;
	case 's':
	case 'start':
	echo 'start'.cr;
	break;
	case 'q':
	case 'quit':
	case 'stop':
	echo 'quit'.cr;
	break;
	default:
	help();
	echo 'do not get '.$cmds['action'].cr;
}

function help() {
	$cc = new Console_Color2();
	$PHP = $cc->convert("%cPHP%n");
	$gsm = $cc->convert("%rgsm%n");
	$option = $cc->convert("%cOption%n");
	$use = $cc->convert("%c\t\tUse%n");
	$table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
$table->addRow(array('','',''));
$table->addRow(array($PHP.$gsm.' Help',''));
$table->addRow(array($option,$use));
$table->addRow(array('v or version','show CLI version & exit'));
$table->addRow(array('s or start ','starts a game server requires a server id to be set'));
$table->addRow(array('q, quit or stop ','stops a game server requires a server id to be set'));
$table->addRow(array('r, or restart ','restarts a game server requires a server id to be set'));
$table->addRow(array('d, or details ','shows details about the running system (takes options see example page)'));
$table->addRow(array('g, or games ','shows details on running game servers (takes options see example page)'));
$table->addRow(array('ig, or igames ','Installs a game from Steam (takes options see example page)'));
$table->addRow(array('is, or iserver ','Installs a server from an installed game (takes options see example page)'));
$table->addRow(array('u, or users ','shows user details (takes options see example page)'));
	 system('clear');
	echo 'Cli interface v'.VERSION.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
	 echo $table->getTable();
	 echo cr;
	 echo 'cli will only work on this machine, if you have remotes either use cli on that machine or the web api.'.cr;
	 $answer = ask_question('enter E for examples or q to quit  ',null,null);
	 echo $answer.cr.cr;
	 exit;
 }
 
 function details($data) {
	 // read server details
	 system('clear');
	 $cc = new Console_Color2();
	 $sw = $cc->convert("%W   Modules%n");
	 $sa = $cc->convert("%W    Server%n");
	 $ha = $cc->convert("%W    Hardware%n");
	 $ma = $cc->convert("%W    Memory%n");
	 $da = $cc->convert("%W     Boot Disk%n");
	 $da1 = $cc->convert("%W     Data Disk%n");
	 if($data['option'] =='h' || $data['option'] =='a') {
		 //
		  $table = new Console_Table(
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
		  $table = new Console_Table(
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
		  $table = new Console_Table(
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
	 $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
    );
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
	 		system('clear');
	 		$Query = new SourceQuery( );
	 		$cc = new Console_Color2();
	 			  $table = new Console_Table(
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
		 $Query->Connect( $gdata['host'], $gdata['port'], 1,  SourceQuery::SOURCE  );
	$players = $Query->GetPlayers( ) ;
	$info = $Query->GetInfo();
	$rules = $Query->GetRules( );
	$Query->Disconnect( );
	if ($info['Players'] >0) {
		$p1 = trim($info['Players']);
		$info['Players'] = $cc->convert("%B$p1%n");
		}
		else {
			$p1 = trim($info['Players']);
		$info['Players'] = $cc->convert("%Y$p1%n");
		}
	$playersd =$info['Players'].'/'.$info['MaxPlayers'];
	$host = $cc->convert("%y".$info['HostName']."%n");
	$table->addRow(array('',$host,date('g:ia \o\n l jS F Y \(e\)',"\t".$gdata['starttime'])."\t",$playersd,"\t".$info["Map"].""));
	//printf($headmask,"\e[38;5;82m".$info['HostName'],"\e[97m started at",date('g:ia \o\n l jS F Y \(e\)', $data['starttime']),"Players Online ".$playersd." Map - ".$info["Map"]);
	}
	echo $table->getTable();
    exit;
 }
?>
