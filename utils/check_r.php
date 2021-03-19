#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * check_r.php
 * 
 * Copyright 2020 Jim Richardson <jim@noideersoftware.co.uk>
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
 * example steamcmd +app_info_update 1 +app_info_print 295230 +quit |  sed '1,/branches/d' 
 */
define ('cr',PHP_EOL);
if (!isset($argv[1])) {
	echo 'supply a server ID !'.cr;
	exit(1);
} 
if (!is_numeric($argv[1])) {
	echo 'Server Id must be numeric'.cr;
	exit(2);
}
echo 'Checking Server id '.$argv[1].cr;
$appid = $argv[1];
$cmd = 'steamcmd +app_info_update 1 +app_info_print '.$appid.' +quit ';
$output = shell_exec($cmd);
$fail = strpos($output,'No app info for AppID '.$appid);
if ($fail >0) {
			echo 'No Data for Server ID '.$appid;
			echo ' is this server ID valid ?'.cr;
			exit;
		}
$branches = get_block($output,'"branches');
$t = check_branch($appid);
$common = get_block($output,'"common','}');
$common = array_block($common);
$extended = get_block($output,'"extended','}');
$extended = array_block($extended);
	
//die();
echo 'Found '.$common['name'].'('.$common['ReleaseState'].')'.cr;
if (isset($common['oslist'])) {
	echo 'Runs on '.$common['oslist'].cr;
}
else {
	//print_r($extended);
}
echo 'Branch Detail'.cr;
//echo print_r($t,true).cr;
$max = '';
$maxlen = 0;

foreach ($t as $elm =>$data) {
    $len = strlen($elm);

    if ($len > $maxlen) {
        $maxlen = $len;
        $max = $elm;
    }
}

$maxlen ++;
$mask = "%".$maxlen.".".$maxlen."s %14.14s %40s %8s \n";
$headmask = "%".$maxlen.".".$maxlen."s %14.14s %25s %25s \n";
printf($headmask,'Branch','    Build ID','Release Date','Password');
foreach($t as $branch=>$data) {
	//loop it through
	if (!isset($data['buildid'])){continue;}
	if (!isset($data['timeupdated'])) { $data['timeupdated'] = 0;}
	if (isset($data['pwdrequired'])) {
		$pwd ='yes';
	}	
	else { $pwd='';}
	printf($mask,$branch, $data['buildid'],date('l jS F Y \a\t g:ia',$data['timeupdated']),$pwd );

}

function check_branch($appid) {
/*
 * Written 28-12-2020
 * function to check and return steamcmd branches
 * part of cron_u
 * $appid is the server/game code to  check
 */ 	
global $branches;
$data = str_replace('{','',$branches);
$data = str_replace('}','',$data);
$data= trim($data);
$arry = explode(cr,trim($data));
$c =count($arry);
if( $c == 1){
	echo 'No Data for Server ID '.$appid;
	echo ' is this server ID valid ?'.cr;
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

function get_block($data,$keyword,$toend = "") {
	// strip blocks from output
	$x = strpos($data,$keyword);
	if (!$toend =='') {
		
		$y = strpos($data,$toend,$x);
		return substr($data,$x,$y-$x);
	} 
	else {
		return substr($data,$x);
	}
}

function array_block($data) {
	// turn block to array
	$data = str_replace('{','',$data);
	$data = str_replace('}','',$data);
	$data= trim($data);
	$arry = explode(cr,trim($data));
	$c =count($arry);
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
   $return[$nz]= trim($value);
}
else
{
    // heading
     $y= trim(preg_replace('/\t+/', '', $value));
     $branch = str_replace('"','',$y);
     
}
}
	//print_r($return);
	return $return;
}

  

?>
