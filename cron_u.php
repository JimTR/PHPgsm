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
 * move this to the utilities folder & make sure it's pathed. ??
 */
include 'includes/master.inc.php';
include 'functions.php';
	$build = "7878-1553517931";
define ("cr",PHP_EOL);
$processed= array();
//define('plus','%2B');
if (!isset($argv)) {
	echo 'Wrong Enviroment';
	exit;
}
$host= gethostname();
$ip = gethostbyname($host);
$ip = file_get_contents('https://api.ipify.org');
if(empty($ip)) { $ip = file_get_contents("http://ipecho.net/plain");}
echo 'Starting Check For '.$ip.cr;
$steamcmd = trim(shell_exec('which steamcmd'));
$install_path = dirname($steamcmd);
if (!empty($steamcmd)) {
echo 'found steamcmd at '.$steamcmd.cr;
}
else {
	echo 'steamcmd not found, add steamcmd location to user path'.cr;
	echo 'terminating'.cr;
	exit;
}
list($ip1, $ip2, $ip3, $ip4) = explode(".", $ip);
$ip = $ip1.'.'.$ip2.'.'.$ip3; // get all ip's attached to this server
$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"  and servers.server_id >=0 and host like "'.$ip.'%" and is_steam=1' ;

	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
		        $acf_loc = $data['location'].'/steamapps/appmanifest_'.$data['server_id'].'.acf';
				$disk_size = trim(shell_exec('du -hs '.$data['location']));
				$ds = substr($disk_size,0,strpos($disk_size,'/'));
	    
			    $local =  check_local($acf_loc);
			    
			    
		
		
			    if (!in_array($local['appid'],$processed)) {
								
					 $remote = check_branch($local['appid'],$steamcmd);
															
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
				$update['rserver_update']= $remote['public']['timeupdated'];
				$update['server_update']= $man_check['update'];
				//echo 'app id '.$local['update'].cr;
			    $where['server_id'] = $local['appid']; // update all servers with that app with the current build 
			    //if ($data['rbuildid'] <> $remote['buildid']) {
					// just update if there is an updated build
					
					$database->update('servers',$update,$where);
				//}
			    echo cr.'Details for '.$local['name'].' ('.$local['appid'].')'.cr;
			    echo 'Installed at '.$install_path.'/'.$data['game'].cr; 
			    echo cr.'Branch Detail'.cr;
				
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
			    echo cr.' Local Build id '.$local['buildid'].cr;
			    echo 'Remote Build id '.$remote['public']['buildid'].cr;
                echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$man_check['update']).cr;
               
                if ($local['buildid'] <> $remote['public']['buildid']) {
					echo 'Update Required'.cr;
					if ($settings['update'] = 1) {
				    echo 'Auto Update Set'.cr;
				    // use $install_path + game
				    $cmd = $steamcmd.' +login anonymous +force_install_dir '.$install_path.'/'.$data['game'].' +app_update '.$data['server_id'].' +quit';
				    $updatetxt = shell_exec($cmd);
				    // this appears to work so update the database ? or wait for the next run ?
				    echo $updatetxt.cr;
				     $update['server_id'] = $local['appid'];;
					 $update['buildid'] = $local['buildid'];
					 $update['rbuildid'] = $remote['public']['buildid']; //fix this line 
					 $update['rserver_update']= $remote['public']['timeupdated'];
					 $update['server_update']= $man_check['update'];
					 $where['server_id'] = $local['appid']; // update all servers with that app with the current build need branch check ?
			         $database->update('servers',$update,$where);
					} 
				}
			}
			
		}
		// update disk_space
		unset($update);
		unset($where);
		$where['host_name'] = $data['host_name'];
		$update['disk_space'] = $ds;
		$database->update('servers',$update,$where);
	}
		function local_update($build,$local) {
			
			//
			$acf_loc = $build['location'].'/steamapps';
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

function check_local($file) {
$data = shell_exec('cat '.$file.' |sed \'3,17!d\'');
$arry = explode(cr,trim($data));
foreach ($arry as $key=>$value) {
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

?>
