<?php
/*
 * ajax.php
 * 
 * Copyright 2019 Jim Richardson <jim@noideersoftware.co.uk>
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
 * required for ajax requests from html version 
 */
 
 require 'includes/cli_master.inc.php';
 include 'functions.php';
 define ("CR","<br>");
 if(isset($_GET['action'])) {
//echo $_GET['action'].CR;
switch ($_GET['action']) {
	case "boottime" :
			echo get_boot_time();
			exit;
	case "load" :
			$cpu_info=get_cpu_info();
			echo $cpu_info['load'];
			exit;
	case "rgames" :
		  echo  display_games();
		  exit;	
	case "hardware" :
			$cpu_info = get_cpu_info();
			echo display_cpu($cpu_info);
			exit;
	case "software" :
			$software = get_software_info();
			$os = lsb();
			echo display_software($os,$software);
			exit;
	case "disk":
			$disk_info = get_disk_info();
			echo display_disk($disk_info);
			exit;
	case "memory":
			$mem_info = get_mem_info();
			echo display_mem($mem_info,True);
			exit;
	case "user":
			$disk_info = get_disk_info();
			$user_info = get_user_info($disk_info);
			echo display_user($user_info);
			exit;
	case "all":
			// get all back
			$cpu_info=get_cpu_info();
			$data = display_cpu($cpu_info).'\n';
			$software = get_software_info();
			$os = lsb();
			$data .= display_software($os,$software).'\n';
			$disk_info = get_disk_info();
			$data.= display_disk($disk_info).'\n';
			$mem_info = get_mem_info();
			$data .= display_mem($mem_info,True).'\n';
			$user_info = get_user_info($disk_info);
			$data .= display_user($user_info);
			echo $data;
	case "exelgsm":
			if(isset($_GET['path'])) {
			 $server = $_GET['path'];
			 $cmd = $_GET['cmd'];
			 $exe = $_GET['exe'];
			 //echo 'you requested '.$server.'<br>';
			 $output = exe_lgsm($server,$cmd,$exe);
			 //print_r($_GET);
			 echo $output;
			 exit;
		 }													  		
}
}
else {
	echo "you cocked up";
}
function exe_lgsm($server,$action,$exe)
  {
	  /* this will run lgsm functions
	   * Requires $server to workout which game to exec
	   * Requires $action to do whatever with the server , in shorthand notation
	   * returns the lgsm display as a string
	   * note lgsm c/console  & h/help will not be supported via this function
	   * will be used in ajax.php 
	   */
	   switch($action) 
	   {
		   // choose action
		   case "dt" :
			$command = $server.' '.$action;	
			echo $command.'<br>';			
			break 1;
		  case "sp" :
			//$command = 'tmux kill-session -t '.$handle;
			exit; 
		   default:
		   $disp = shell_exec($command);
		   return $disp;
	   }
	   $disp = shell_exec($command);
	   //echo 'disp ?<br>';
	   //echo $disp;
	   return $disp;
	   
  }
?>
