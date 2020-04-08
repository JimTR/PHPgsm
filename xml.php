<?php
require 'includes/class.dbquick.php';
require 'includes/config.php'; // get config
include 'functions.php';
if (!empty($_POST)) {
	 $cmds = $_POST;
 }
 else {
	 $cmds = $_GET;
 }
//$cmds = strtolower( $cmds );
if(isset($cmds)){$cmds = change_value_case($cmds,CASE_LOWER);}
$mem_info = get_mem_info();
$disk_info = get_disk_info();
$up_time = get_boot_time();
$cpu_info = get_cpu_info();
$site->config = &$config; // load the config
require_once('GameQ/Autoloader.php'); //load GameQ
 define( 'DB_HOST', $site->config['database']['hostname'] ); // set database host
 define( 'DB_USER', $site->config['database']['username'] ); // set database user
 define( 'DB_PASS', $site->config['database']['password'] ); // set database password
 define( 'DB_NAME', $site->config['database']['database'] ); // set database name
$GameQ = new \GameQ\GameQ();
$database = new db(); 
$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"';
$res = $database->get_results($sql); 
$sql = 'select base_servers.*, software.* from base_servers left join software on base_servers.ip = software.ip where extraip="0" and enabled="1"';
$base_servers = $database->get_results($sql); 
$Gq = array();
foreach ($res as $getgames) {
	// get game data
	     $key = $getgames['host_name'];
		 $Gq[$key]['id'] = $getgames['host_name'];
	     $Gq[$key]['host'] = $getgames['host'].':'.$getgames['port'];
	     $Gq[$key]['type'] = $getgames['type'];
} 
          $GameQ->addServers($Gq);
          $results = $GameQ->process();

$xml = new SimpleXMLElement('<Servers/>');
$xmlserver="game_server";
foreach ($res as $data) {
	
	$now = new Datetime();
	$date = date("Y-m-d",$data['starttime']);
	 
	$date = new DateTime();
	$date->setTimestamp($data['starttime']);
	
	$interval = $now->diff($date);

    $rt = $interval->format('%a days %h hours %I mins %S Seconds');
	if (empty($results[$data['host_name']]['gq_online'])) {
		$online = 'Offline';
	}
	else {
		$online = 'Online';
	}
	$track = $xml->addChild($xmlserver);
    $track->addChild('name',$data['host_name']);
    $track->addChild(fname,$data['fname']);
    $track->addChild('app_id',$data['app_id']);
    $track->addChild('game_port',$data['port']);
    $track->addChild('source_port',$data['source_port']);
    $track->addChild('client_port',$data['client_port']);
    $track->addChild('server_pass',$data['server_password']);
    $track->addChild('rcon_pass',$data['rcon_password']);
    $track->addChild('rt',$rt );
    $track->addChild('logo',$data['logo']);
    $track->addChild('ip', $data['host'].':'.$data['port']);
    $track->addChild('location', $data['location']);
    $track->addChild('url', $data['url'].':'.$data['bport']);
    $track->addChild('engine',$data['type']);
    $track->addChild('enabled',$data['enabled']);
    $track->addChild('startcmd',$data['startcmd']);
    $track->addChild('starttime',date('g:ia \o\n l jS F Y \(e\)', $data['starttime']));
    $track->addChild('online',$online);
    $track->addChild('defaultmap',$data['default_map']);
    $track->addChild('currentmap',$results[$data['host_name']]['gq_mapname']);
    $track->addChild('players',$results[$data['host_name']]['gq_numplayers']);
    $track->addChild('maxplayers',$data['max_players']); 
    $players = $track->addChild('current_players');
    $i=0;
    $player_list = $results[$data['host_name']]['players']; // get the player array
    orderBy($player_list,'gq_score','d');
   foreach ($player_list as $pz) {
		$i++;
		$xname='pname';
		$xscore='pscore';
		$xonline='ponline';
    $players->addChild('pname', $pz['name'].'|');
    $players->addChild('pscore', $pz['score'].'|');
    $players->addChild('ponline',gmdate("H:i:s",$pz['time']).'|');
  
}
  $track->addChild('host_name',$results[$data['host_name']]['gq_hostname']);    
}
$xmlserver = "base_server";
foreach ($base_servers as $data) {
	
    $track = $xml->addChild($xmlserver);
    $track->addChild('name',$data['name']);
    $track->addChild('fname',$data['fname']);
    $track->addChild('distro',$data['distro']);
    $track->addChild('ip', $data['ip']);
    $track->addChild('cpu_model', $data['cpu_model']);
    $track->addChild('cpu_processors', $data['cpu_processors']);
    $track->addChild('cpu_cores',$data['cpu_cores']);
    $track->addChild('cpu_speed',$data['cpu_speed']);
    $track->addChild('cpu_cache',$data['cpu_cache']);
    $track->addChild('kernel',$data['kernel']);
    $track->addChild('php',$data['php']);
    $track->addChild('screen',$data['screen']);
    $track->addChild('glibc',$data['glibc']); 
    $track->addChild('mysql',$data['mysql']);
    $track->addChild('apache',$data['apache']); 
    $track->addChild('curl',$data['curl']);
    $track->addChild('nginx',$data['nginx']);  
    $track->addChild('quota',$data['quota']);
    $track->addChild('postfix',$data['postfix']);
    $track->addChild('uptime',$up_time); 
    $track->addChild('memTotal',trim($mem_info['MemTotal']));  
    $track->addChild('memfree',trim($mem_info['MemFree']));
    $track->addChild('memcache',trim($mem_info['Cached']));
    $track->addChild('memactive',trim($mem_info['Active']));
    $track->addChild('swaptotal',trim($mem_info['SwapTotal']));
    $track->addChild('swapfree',trim($mem_info['SwapFree']));
    $track->addChild('swapcache',trim($mem_info['SwapCached']));
    $track->addChild('boot_filesystem',$disk_info['boot filesystem']);
    $track->addChild('boot_mount',$disk_info['boot mount']);
    $track->addChild('boot_size',$disk_info['boot size']);
    $track->addChild('boot_used',$disk_info['boot used']." (".$disk_info['boot %'].")");
    $track->addChild('boot_free',$disk_info['boot free']);
    $track->addChild('load',$cpu_info['load']);
    if (isset($disk_info['home filesystem'])) {
		// diff
		$track->addChild('home_filesystyem',$disk_info['home filesystem']);
		$track->addChild('home_mount',$disk_info['home mount']);
		$track->addChild('home_size',$disk_info['home size']);
		
	}
}
if (!(isset($cmds['action']))){ 
XML_print($xml);
}
else {
	XML_array($xml);
}
function XML_print($xml) {
	Header('Content-type: text/xml');
	header('Access-Control-Allow-Origin: *');
	print($xml->asXML());
}
function XML_array($xml) {
	$new = simplexml_load_string($xml->asXML());
	$con = json_encode($new);  
	$newArr = json_decode($con, true); 
  
print_r($newArr);         
}  
function change_value_case($array,$case = CASE_LOWER){
        $array =array_change_key_case($array, $case);
        switch ($case) {
			case CASE_LOWER:
				$array = array_map('strtolower', $array);
				break;
			case CASE_UPPER:
				$array = array_map('strtoupper',$array);
				break;	
       }
        return $array;
    }

?>
