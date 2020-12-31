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
 *  new
 * steamcmd +login anonymous +force_install_dir /home/nod/games/gmod/serverfiles +app_update 4020  +quit example cmd line
 * this does check update status and does the update perhaps this is the way to go ?
 */
include 'includes/cli_master.inc.php';
include 'functions.php';
define ("cr",PHP_EOL);
$database = new db();
//$host= gethostname();
//$ip = gethostbyname($host);
$ip = file_get_contents("http://ipecho.net/plain");
echo 'Starting Check For '.$ip.cr;
$steamcmd = shell_exec('which steamcmd');
echo 'found steamcmd at '.$steamcmd.cr;
list($ip1, $ip2, $ip3, $ip4) = explode(".", $ip);
$ip = $ip1.'.'.$ip2.'.'.$ip3; // get all ip's attached to this server
$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"  and servers.server_id >=0 and host like "'.$ip.'%"' ;
//echo $sql.cr;
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
			    echo 'local '.print_r ($local,true).cr;
			}
		}
		else {
			$local['appid'] = $data['server_id'];
			$local['buildid'] = $data['buildid'];
			$local['update'] = $data['server_update'];
			
		}
			    if (!in_array($local['appid'],$processed)) {
					//print_r($processed);
					//echo cr.$local['appid'].cr;
					
					 $remote = check_branch($local['appid'],$steamcmd);
					
					$cmd = $steamcmd.' +app_info_update 1 +app_info_print "'.$local['appid'].'"  +quit';
					echo $cmd;
					//$result = shell_exec($cmd);
					
					//$remote = test_remote($result); // check to be removed 
			
			// need to set branch !
			if (isset($remote['public']['buildid'])) {
				// slow up db hits 
				$processed[] = $local['appid']; // done this app
				$man_check = local_update($data,$local); // check if manual update has been done
				if($man_check['buildid'] <> $local['buildid']) {
					$local['buildid'] = $man_check['buildid'];
					$data['buildid']=0;
					echo 'Correcting Build'.cr;
					 echo 'Locally installed version '.$man_check['buildid'].cr;
				}
			    $update['server_id'] = $local['appid'];;
				$update['buildid'] = $local['buildid'];
				$update['rbuildid'] = $remote['public']['buildid']; 
				$update['rserver_update']= $remote['public']['update'];
				$update['server_update']= $man_check['update'];
				//echo 'app id '.$local['update'].cr;
			    $where['server_id'] = $local['appid']; // update all servers with that app with the current build 
			    //if ($data['rbuildid'] <> $remote['buildid']) {
					// just update if there is an updated build
					
					$database->update('servers',$update,$where);
				//}
			    echo cr.'Details for App Id '.$local['appid'].' ('.$data['host_name'].')'.cr;
			    echo cr.'Branch Detail'.cr;
				echo print_r($remote,true).cr;
				$mask = "%11.11s %14.14s %40s %8s \n";
				$headmask = "%11.11s %14.14s %25s %25s \n";
				printf($headmask,'Branch','    Build ID','Release Date','Password');
				foreach($remote as $branch=>$rdata) {
					//loop it through
					if (!isset($rdata['buildid'])){continue;}
					if (isset($rdata['pwdrequired'])) {
						$pwd ='yes';
					}	
					else {
							$pwd='no';
						}
						
						printf($mask,$branch, $rdata['buildid'],date('l jS F Y \a\t g:ia',$rdata['timeupdated']),$pwd );
				}
			    echo cr.'Local Build id '.$local['buildid'].cr;
			    echo 'Remote Build id '.$remote['public']['buildid'].cr;
                echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$man_check['update']).cr;
               
                if ($local['buildid'] <> $remote['public']['buildid']) {
					echo 'Update Required'.cr;
					if ($settings['update'] = 1) {
				    echo 'Auto Update Set'.cr;
				    $cmd = $steamcmd.' +login anonymous +force_install_dir '.$data['location'].'/serverfiles +app_update '.$data['server_id'].' +quit';
				    echo $cmd.cr;
				    $update = shell_exec($cmd);
				    // this appears to work so update the database ? or wait for the next run ?
				    echo $update.cr;
					} 
				}
			}
			
		}
	}
		function local_update($build,$local) {
			
			//
			$acf_loc = $build['location'].'/serverfiles/steamapps';
			$find = 'appmanifest_';
		    $files = glob($acf_loc."/*" . $find . "*");
			$acf_file = file_get_contents($files[0]);
			$local_data =  local_build($acf_file);
			return $local_data;
		}	
		
		function check_branch($appid,$steamcmd) {
/*
 * Written 28-12-2020
 * function to check and return steamcmd branches
 * part of cron_u
 * $appid is the server/game code to  check
 */ 	
 
$cmd = '/usr/games/steamcmd +app_info_update 1 +app_info_print '.$appid.' +quit |  sed \'1,/branches/d\'';
//echo $cmd.' ('.$steamcmd.')'.cr;
//exit;
$data= shell_exec($cmd);
$data = str_replace('{','',$data);
$data = str_replace('}','',$data);
$data= trim($data);
$arry = explode(cr,$data);
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
