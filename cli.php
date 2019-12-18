#!/usr/bin/php -d memory_limit=2048M
<?php
define ("CR","\r\n");
global $argv;
error_reporting(E_ERROR | E_PARSE);
if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {   
	//echo "called directly"; 
	include ("functions.php");
	}

if(root()) {
	echo "This script Can not be run by Root User".CR;
	//exit;
}

if (isset($argc)) {
	for ($i = 1; $i < $argc; $i++) {
		//echo "Argument #" . $i . " - " . $argv[$i] . "\n";
	}
}
else {
	echo "argc and argv disabled\n";
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
		//ask_question ("Enter Database Name: ","database","",false);
		exit;
	case "g":
	case "games":
			system('clear');
			display_games();
			exit;
	case "debug":
	case "d":
	system('clear');	
	if (isset($argv[2])) {
		switch  (strtolower($argv[2])) 
		{
		case "s":
		case "software";
			$software = get_software_info();
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
	echo '.';
    $mem_info = get_mem_info();
	echo '.';
	$software = get_software_info();
	echo '.';
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
	display_games();
	
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
