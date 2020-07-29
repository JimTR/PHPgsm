#!/usr/bin/php -d memory_limit=2048M
<?php
/*
 * cron_u.php
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
 * This script checks for updates on steam for installed games, 
 * updates the database ready to update.
 * TODO add code to auto update be aware of steamcmd segmentation errors !
 * 
 */
include 'includes/cli_master.inc.php';
include 'functions.php';
define ("cr","\r\n");
$database = new db();
echo 'Starting'.cr;
$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"  and servers.server_id >=0';
	$res = $database->get_results($sql);
	//print_r($res);
	foreach ($res as $data) {
		if(empty($data['buildid'])) {
        $acf_loc = $data['location'].'/serverfiles/steamapps';
		$find = 'appmanifest_';
		$files = glob($acf_loc."/*" . $find . "*");
		if (!empty($files)){
			
			    $acf_file = file_get_contents($files[0]);
			    $local =  local_build($acf_file);
			    echo 'local'.cr;
			    print_r ($local);
			}
		}
		else {
			$local['app_id'] = $data['appid'];
			$local['buildid'] = $data['buildid'];
			$local['update'] = $data['server_update'];
			print_r($local);
		}
			    if (!in_array($local['appid'],$processed)) {
					//print_r($processed);
					echo cr.$local['appid'].cr;
					$cmd = '/usr/games/steamcmd  +app_info_update 1 +app_info_print "'.$local['appid'].'"  +quit';
					echo $cmd;
					//$result = shell_exec($cmd);
					//$remote = test_remote($result);
			//print_r($result);
			echo 'local'.cr;
			print_r($local);
			echo 'remote'.cr;
			print_r($remote);
			echo PHP_EOL;
			exit;
			if (isset($remote['buildid'])) {
				// slow up db hits 
				$processed[] = $local['appid']; // done this app
			    $update['server_id'] = $local['appid'];;
				$update['buildid'] = $local['buildid'];
				$update['rbuildid'] = $remote['buildid']; 
				$update['rserver_update']= $remote['update'];
				$update['server_update']= $local['update'];
			    $where['server_id'] = $local['appid']; // update all servers with that app with the current build 
			    if ($data['rbuildid'] <> $remote['buildid']) {
					// just update if there is an updated build
					$database->update('servers',$update,$where);
				}
			    echo 'Details for App Id '.$local['appid'].PHP_EOL;
			    echo 'Local Build id '.$local['buildid'].PHP_EOL;
			    echo 'Remote Build id '.$remote['buildid'].PHP_EOL;
                echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$local['update']).PHP_EOL;
			}
		}
			//echo '<br>';
			}
			//else {echo $data['location'].'/serverfiles/steamapps'.PHP_EOL;}
			//}
print_r($processed);
?>
