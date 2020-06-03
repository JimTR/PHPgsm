#!/usr/bin/php -d memory_limit=2048M
<?php
// cronu
include 'includes/cli_master.inc.php';
include 'functions.php';
$database = new db();
$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"  and servers.server_id >=0';
	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
$acf_loc = $data['location'].'/serverfiles/steamapps';
		$find = 'appmanifest_';
		$files = glob($acf_loc."/*" . $find . "*");
		if (!empty($files)){
			$acf_file = file_get_contents($files[0]);
			//echo $acf_file.'<br>';
			    $local =  local_build($acf_file);
			    $cmd = '/usr/games/steamcmd  +app_info_update 1 +app_info_print "'.$local['appid'].'"  +quit';
			//echo $cmd.PHP_EOL; 
				$result = shell_exec($cmd);
			//echo $result;
			$remote = test_remote($result);
			//print_r($remote);
			//echo PHP_EOL;
			        $update['server_id'] = $local['appid'];;
				$update['buildid'] = $local['buildid'];
				$update['rbuildid'] = $remote['buildid']; 
				$update['rserver_update']= $remote['update'];
				$update['server_update']= $local['update'];
			    $where['host_name'] = $data['host_name']; 
			    $database->update('servers',$update,$where);
			echo 'Details for App Id '.$local['appid'].PHP_EOL;
			echo 'Local Build id '.$local['buildid'].PHP_EOL;
			echo 'Remote Build id '.$remote['buildid'].PHP_EOL;
                        echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$local['update']).PHP_EOL;
			//echo '<br>';
			}
			//else {echo $data['location'].'/serverfiles/steamapps'.PHP_EOL;}
			}

?>
