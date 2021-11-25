<?php
/*
 * log_prune.php
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
 * Log Prune 
 * runs from the utils directory 
 * cli program written for the cron section 
 */
require '../includes/master.inc.php';
include '../functions.php';
define ('cr',PHP_EOL);
define ('CR',PHP_EOL);
	$build = "1666-3400310146";
if (is_cli()) {
	echo 'log prune live'.cr;
}
else {
	
	echo 'Wrong Enviroment';
	exit;
}
$reaper = shell_exec('which tmpreaper');
if (empty($reaper)) {
	$user =shell_exec('whoami');
	echo 'tmpreaper is not installed. ';
	if (check_sudo($user)) {
		$response = ask_question('Do you want to install ? (y/n) ','y','n');
		if ($response === true) {
				$password = ask_question('Enter Sudo Password ','','',true,true);
				//echo cr.$password.cr;
				$d = shell_exec('echo '. $password.' | sudo -S apt -y install tmpreaper');
				echo $d.cr;
		}
	}
	else{
		echo cr.'Ask an administrator to install tmpreaper & try again'.cr;
	}
} 

?>
