<?php
/*
 * install.php
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
 include '../includes/master.inc.php';
 include DOC_ROOT.'/functions.php';
 define ('cr',PHP_EOL);
 define ('VERSION',2.01);
 echo 'PHPgsm '.VERSION.cr;
 echo 'Welcome to PHPgsm Game Installer '.VERSION.cr;
 echo 'Checking available disk space';
 $diskinfo = get_disk_info();
 if(isset($diskinfo['home_free'])) {$space = ' ('.$diskinfo['home_free'];}
 else { $space = ' ('.$diskinfo['boot_free'];}
 echo $space.' free)'.cr;
 echo 'Checking user capabilities';
 $user = get_user_info($diskinfo);
 //print_r($user);
 if($user['level'] == 1) {$user_level = ' Privledge OK';}
 else { $user_level =' user privledge to low, get an administrator to run this script.'; echo $user_level.cr;exit;}
 echo $user_level.cr;
  $steamcmd = trim(shell_exec('which steamcmd'));
 if (empty($steamcmd)) {
	 echo 'steamcmd not found in the user path, is it installed ?'.cr;
	 echo 'either install steamcmd or add steamcmd to your path & re-run install'.cr;
	 exit;
 }
 if ($user['level'] <1) {
	 echo 'This Installer needs to be run as root or a user with the admin priv'.cr;
	 die(1);
 }
 
 $n='no';
 $answer = ask_question('Please Enter The Steam Id of the Game Server to install or C for non steam ',NULL,$n); 

 if (is_numeric($answer)) {
	 rerun:
	 echo 'Please wait checking steam for server ID '.$answer;//.cr;
	 exec('./check_r.php '.$answer,$output,$ret_val);
	 $x=0;
	 //echo $ret_val.cr;
	 switch ($ret_val) {
		 case 7:
		 //echo $output[1].cr;
		 echo 'No Information found for dedicated server ID '.trim($answer).' are you sure the id is correct ?'.cr;
		 echo 'Tip: run \''.$argv[0].'\' action=show . This will list steam dedicated servers that we know work with PHPgsm, installing a server not on this list could still work.'.cr;
		  
		 exit;
		 case 134:
		 echo $output[1].cr;
		 $rerun = ask_question('Retry (y/n) ','y','n');
		 if ($rerun) {$ret_val=0; $output = array(); goto rerun;} 
		 exit;
	 }
	 //echo $ret_val.cr;
	 //print_r($output);
	 foreach ($output as $line) {
		 if ($x == 0) {$x=1;continue;}
		 echo $line.cr;
		 
	 }
	 $name = str_replace('Found ','',$output[1]);
	 $name = str_replace('(released)',' (y/n)  ',$name);
	  $answer = ask_question('Do you want to install '.trim($name).' ? ','y','n');
	  echo $answer.cr; 
 }
?>