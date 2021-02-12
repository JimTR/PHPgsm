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
	exit;
} 
if (!is_numeric($argv[1])) {
	echo 'Server Id must be numeric'.cr;
	exit;
}
echo 'Checking Server id '.$argv[1].cr;
$appid = $argv[1];
$t = check_branch($appid);
echo 'Branch Detail'.cr;
//echo print_r($t,true).cr;
$mask = "%11.11s %14.14s %40s %8s \n";
$headmask = "%11.11s %14.14s %25s %25s \n";
printf($headmask,'Branch','    Build ID','Release Date','Password');
foreach($t as $branch=>$data) {
	//loop it through
	if (!isset($data['buildid'])){continue;}
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
$cmd = 'steamcmd +app_info_update 1 +app_info_print '.$appid.' +quit |  sed \'1,/branches/d\'';
$data= shell_exec($cmd);
$data = str_replace('{','',$data);
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

?>
