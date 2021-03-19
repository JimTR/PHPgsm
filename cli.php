#!/usr/bin/php -d memory_limit=2048M
<?php
//error_reporting( 0 );
error_reporting ( E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
define ("CR","\r\n");
//echo '??';
require 'includes/master.inc.php'; 
require __DIR__ . '/xpaw/SourceQuery/bootstrap.php';
use xPaw\SourceQuery\SourceQuery ;
$Query = new SourceQuery( );
ini_set('error_reporting', E_ALL);
if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {   
	//echo "called directly"; 
	include ("functions.php");
	}

if(root()) {
	echo "This script Can not be run by Root User".CR;
	exit;
}

if (isset($argc)) {
	for ($i = 1; $i < $argc; $i++) {
		//echo "Argument #" . $i . " - " . $argv[$i] . "\n";
	}
}
else {
	echo "<head><title>PHPgsm Error</title></head>";
	echo "<h1>This can only be ran from the command line</h1>";
	exit;
}
s1:

if (isset($argv[1])) {
switch (strtolower($argv[1]))
{
	case "help":
	case "h":
	    echo "d or debug displays script info".CR;
	    echo "\t Secondary Commands".CR;
	    echo "\ts Show Software info only".CR;
	    echo "\tc Show Hardware info only".CR;
	    echo "\td Show Disk info only".CR;
	    echo "\tm Show Memory info only".CR;
	    echo "g or games show game info only".CR; 
	    echo "h or help  this help screen".CR;
	    echo "v or version shows software version".CR;
	    echo "i or install you will be asked what server you want to install".CR;
		//echo "help required".CR;
		exit;
		break;
	case "v":
	case "version":
		system('clear');
		display_version();
		exit;
	case "g":
	case "games":
	if(is_cli()) {
			system('clear');
	//$database = new db(); // connect to database
	$sql = 'select * from servers where enabled ="1" and running="1" order by servers.host_name'; //select all enabled & running recorded servers
    $res = $database->get_results($sql); // pull results
    
  echo CR."\e[1m\e[34m Game Server Information\e[0m".CR;
  echo "\t\e[1m\e[31mRunning Servers\e[97m".CR;
foreach ($res as $data) {
			
    $Query->Connect( $data['host'], $data['port'], 1,  SourceQuery::SOURCE  );
	$players = $Query->GetPlayers( ) ;
	$info = $Query->GetInfo();
	$rules = $Query->GetRules( );
	$Query->Disconnect( );
	$playersd =$info['Players'].'/'.$info['MaxPlayers'];
	$headmask = "%-40.40s %13.13s %25s %25s  \n";
    printf($headmask,"\e[38;5;82m".$info['HostName'],"\e[97m started at",date('g:ia \o\n l jS F Y \(e\)', $data['starttime']),"Players Online ".$playersd." Map - ".$info["Map"]);
		if ($info['Players'] >0 ) {
		// players
		//print_r($players);
		//echo "\t\t\t\e[1m \e[34m Player\t\t        Score\t        Online For\e[97m".CR;
		$headmask = "%50s %30.30s %23s  \n";
		printf($headmask,"\e[1m \e[34m Player",'Score',"Online For\e[97m");
		orderBy($players,'Frags',"d"); // order by score
		foreach ($players as $k=>$v) {
						//echo $k.' '.$v.cr;
					//$playerN = substr($players[$k]['Name'],0,20); // chop to 20 chrs
					setlocale(LC_CTYPE, 'en_AU.utf8');
					$playerN = trim($players[$k]['Name']);
//iconv("UTF8", "CP1251//TRANSLIT//IGNORE", $text);	
					$playerN = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $playerN); //remove high asci
					$playerN = str_pad($playerN,25,' ' ,STR_PAD_LEFT); //pad to 25 chrs
		
	/*	if ($players[$k]['Frags'] <10) {
			// switch statement !! rather than if's
			$pscore ="  ".$players[$k]['Frags']; //format score
		}
		elseif ($players[$k]['Frags'] <100)  {
			$pscore = " ".$players[$k]['Frags']; //format score
		}
		else {
			$pscore = $players[$k]['Frags']; //format score
		} */
		//echo  "\t\t\t".$playerN."\t ".$pscore."\t\t ". $players[$k]['TimeF'].CR;
		$headmask = "%-20s %-25s %15s %' 8s %17s  \n";
		printf($headmask,' ',$playerN,' ',$players[$k]['Frags'], $players[$k]['TimeF']);
		
	}
	//echo CR;
	}
	//echo CR;
	}
	
}
else {
			display_games();
		}
			exit;
	case "debug":
	case "d":
	system('clear');	
	if (isset($argv[2])) {
		switch  (strtolower($argv[2])) 
		{
		case "s":
		case "software";
			$software = get_software_info($database);
			$os = lsb();
			display_software($os,$software);
			exit;
		case "c":
		case "cpu":
			$cpu_info = get_cpu_info();
			display_cpu($cpu_info);
			exit;
		case "d":
		case "disk":
			$disk_info = get_disk_info();
			display_disk($disk_info);
			exit;
		case "m":
		case "memory":
			$mem_info = get_mem_info();
			display_mem($mem_info,True);
			exit;
		case "u":
		case "user":
			$disk_info = get_disk_info();
			$user_info = get_user_info($disk_info);
			display_user($user_info);
			exit;
	}
}
	
	echo 'Please Wait ';
	$mem_info = get_mem_info();
	echo'.';
	$software = get_software_info($database);
	echo'.';
	$disk_info = get_disk_info();
	echo '.';
	$user_info = get_user_info($disk_info);
	echo '.';
    $cpu_info = get_cpu_info();
    $os = lsb();
    echo '.'.CR;
    
	echo CR." \r\n\e[1m \e[34mServer Information\e[0m".CR;
    display_cpu($cpu_info);
	display_mem($mem_info,True);
	display_disk($disk_info);
	display_software($os,$software);
	display_user($user_info);
	//display_games();
	exit;
	default:
			echo $argv[1]." is an invalid command. Use one from the list below\r\n";
			$argv[1]="h";
			goto s1;
}			
}
else {$test= "help screen";
	$argv[1]="h";
	goto s1;
echo  $test.CR;}

?>
