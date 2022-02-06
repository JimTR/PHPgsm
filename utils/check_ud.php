#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * ud1.php
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
define ('cr',PHP_EOL);
$version = "2.00";
$build = "4440-4116720586";
if (!isset($argv[1])) {
	echo 'supply a server ID !'.cr;
	help();
	exit(4);
} 
if (!is_numeric($argv[1])) {
	echo 'Server Id must be numeric'.cr;
	help();
	exit(5);
}
if(!isset($argv[2])) {
	echo 'supply sever location'.cr;
	help();
	exit(6);
}
if(!isset($argv[3])) {
	$localbranch = 'public';
}
else {
	$localbranch = $argv[3];
} 

echo 'Checking Server id '.$argv[1].cr;
$file = $argv[2].'/steamapps/appmanifest_'.$argv[1].'.acf';
if (file_exists($file)) {
$local = check_local($file);
}
else {
	 echo 'Data for server id '.$argv[1].' is not present in the location '.$argv[2].cr;
	 help();
	 exit(20);
 }
//echo print_r($local,true).cr;
$remote = check_branch($argv[1]);
//echo print_r($remote,true).cr;
//$key = array_search(40489, array_column($userdb, 'uid'));
if ($remote[$localbranch]['buildid'] <> $local['buildid']) {
	$last = date('l jS F Y \a\t g:ia',$local['LastUpdated']);
	echo $local['name'].' needs update to '.$remote[$localbranch]['buildid'].' from '.$local['buildid'].' last update '.$last.cr;
	echo 'Branch Detail'.cr;
//echo print_r($t,true).cr;
$mask = "%11.11s %14.14s %40s %8s \n";
$headmask = "%11.11s %14.14s %25s %25s \n";
printf($headmask,'Branch','    Build ID','Release Date','Password');
foreach($remote as $branch=>$data) {
	//loop it through
	if (!isset($data['buildid'])){continue;}
	if (isset($data['pwdrequired'])) {
		$pwd ='yes';
	}	
	else { $pwd='no';}
	printf($mask,$branch, $data['buildid'],date('l jS F Y \a\t g:ia',$data['timeupdated']),$pwd );

}
exit(-1);
}
else {
	echo $local['name'].'('.$argv[1].') is up to date ('.$remote[$localbranch]['buildid'].')'.cr;
	exit ();
}
function check_local($file) {
 exec('cat '.$file.' |sed \'3,17!d\'',$data,$retVal);
//$arry = explode(cr,trim($data));
foreach ($data as $key=>$value) {
	// clear blanks
	if(empty(trim($value))) 
	{ 
		unset ($arry[$key]);
		continue;
		}
	else {
		$value =substr(trim($value),1);
		$z = strpos($value,'"');
		$nz = substr($value,0,$z);
		$value =trim(str_replace($nz.'"','',$value));
        $value=trim(str_replace('"','',$value));
        $arry[$key]=$value;
        $return[$nz]=$value;
	}	
	}
	return $return;
}

function check_branch($appid) {
/*
 * Written 28-12-2020
 * function to check and return steamcmd branches
 * part of cron_u
 * $appid is the server/game code to  check
 */
$steamcmd = trim(shell_exec('which steamcmd')); 	
$cmd = "$steamcmd +app_info_update 1 +app_info_print $appid +quit".' |  sed \'1,/branches/d\'';
$data= shell_exec($cmd);
$data = str_replace('{','',$data);
$data = str_replace('}','',$data);
$data= trim($data);
$arry = explode(cr,trim($data));
$c =count($arry);
if( $c == 1){
	echo 'No Data for Server ID '.$appid;
	echo ' is this server installed ?'.cr;
	exit;
}
foreach ($arry as $key=>$value) {
	// clear blanks
	if(empty(trim($value))) 
	{ 
		unset ($arry[$key]);
		continue;
		}
	else {
		$arry[$key] = trim($arry[$key]);
	}	
	if (preg_match("/\t/", $arry[$key])) {
    
    // setting
   $value= substr(trim($value),1);
   $z = strpos($value,'"');
   $nz = substr($value,0,$z);
   $value =trim(str_replace($nz.'"','',$value));
   $value=trim(str_replace('"','',$value));
   $return[$branch][$nz]= trim($value);
}
else
{
    // heading
     $y= trim(preg_replace('/\t+/', '', $value));
     $branch = str_replace('"','',$y);
     
}
}
return $return;

}
function help() {
	global $argv;
	echo 'Usage '.$argv[0].' <serverID> <location> <branch>'.cr;
	echo cr.'example : ';
	echo $argv[0].' 4020 '.getcwd().' beta'.cr;
	echo 'branch is optional, if ommitted the public branch is used to test against'.cr;
}
?>
