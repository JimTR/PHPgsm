<?php
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff
include("functions.php");
define ("CR", "</br>");

$sql = 'select * from base_servers where extraip="0"';
if (is_cli()) {
//$x=check_sudo(get_current_user());

//include  'cli.php';
}
else {
	
	$database = new db(); // connect to database
	$template = new Template; // load template class
	$res = $database->get_results($sql); // pull results
	$servers = array(); // set array
	
foreach ($res as $data) {
	
	
	$template->load('html/base_server.html'); // load blank template
	//add the data array for base server 
	//this does allow remote locations
	// as long as you have the remote software installed
	
	$subpage['server_title'] = $data['name'].' ('.$data['ip'].')';
	$subpage['host'] = $data['url'].':'.$data['port'] ;
	$subpage['id'] = 'collapse'.$acc ;
	$subpage['ip'] = $data['ip'];
	// curl the data for the server
		$ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $subpage['host'].'/ajax.php?action=all');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $server = curl_exec($ch);
		 curl_close($ch);
	     $server= explode('\n',$server);
	
	$subpage['cpu'] = $server[0];
	$subpage['software'] = $server[1];
	$subpage['disk'] = $server[2];
	$subpage['mem'] = $server[3];
	$subpage['user'] = $server[4];
	$template->replace_vars($subpage);
	$page1.= $template->get_template(); 
	}
	
		
	//Game server(s) 
	$sql = 'SELECT servers.* , base_servers.url, base_servers.port FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>""';
	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
		// loop servers
		
		$template->load('html/game_server.html'); // load blank template
		$action ='dt';
		$server = $data['location'].'/'.$data['host_name'];
		$url = $data['url'].':'.$data['port'];
		
		 $ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url.'/ajax.php?action=exelgsm&path='.$server.'&cmd=dt');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $x1 = curl_exec($ch);
		 curl_close($ch);
		
		$x1 = explode(PHP_EOL,$x1 );
		$servers = refactor_array($x1);
		$cmd = array_search_partial($x1,'Command-line Parameters' )+2;
		 $servers['cmd'] = $x1[$cmd];
		 // make gameq call
		 //print_r($servers);
		 //echo CR;
		 require_once('GameQ/Autoloader.php'); //load GameQ
		 $key = $data['host_name'];
		 $x2['id'] = $data['host_name'];
	     $x2['host'] = $servers['Server IP'] ;
	     $x2['type'] = $data['type'];
	     //print_r($x2);
	     //echo CR;
	     $ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url.'/ajax.php?action=user');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $user = curl_exec($ch);
		 curl_close($ch);
		 
	     $GameQ = new \GameQ\GameQ();
		//include ("server-info.php");
          $GameQ->addServer($x2);
          $results = $GameQ->process();
          $gameport = array_search_partial($x1,'DESCRIPTION' )+1;
		  $sourceport = $gameport+1;
		  $clientport = $sourceport+1;	
		  $subpage['Current map'] = $results[$key]["gq_mapname"];
		 if (isset($servers['Game world'])) {
			 $servers['Default map'] = $servers['Game world'];
			 }
		 if ($results[$key]['gq_online'] == 1 ){
			  //online
			  $online = 'img/online.png';
		  }
		  else {$online = 'img/offline.png';}
		 
		 $subpage['gameport'] = filter_var($x1[$gameport], FILTER_SANITIZE_NUMBER_INT);
		 $subpage['sourceport'] = filter_var($x1[$sourceport],FILTER_SANITIZE_NUMBER_INT);
		 $subpage['clientport'] = filter_var($x1[$clientport],FILTER_SANITIZE_NUMBER_INT);	
		 $subpage['server_name'] = $servers['Server name'].' ('.$servers['Server IP'].')';
		 $subpage['lgsm'] = substr($servers['LinuxGSM version'],1);
		 $subpage['cmd'] = $servers['cmd'];
		 $subpage['Discord alert'] = $servers['Discord alert'];
		 $subpage['Slack alert'] = $servers['Slack alert'];
		 $subpage['Email alert'] = $servers['Email alert'];
		 $subpage['Update on start'] = $servers['Update on start'];
		 $subpage['Pushbullet alert'] = $servers['Pushbullet alert'];
		 $subpage['IFTTT alert'] = $servers['IFTTT alert'];
		 $subpage['Mailgun (email) alert'] = $servers['Mailgun (email) alert'];
		 $subpage['Pushover alert'] = $servers['Pushover alert'];
		 $subpage['Telegram alert'] = $servers['Telegram alert'];
		 $subpage['players'] = $results[$key]['gq_numplayers'];
		 $subpage['Maxplayers'] = $servers['Maxplayers'];
		 $subpage['Server password'] = $servers['Server password'];
		 $subpage['RCON password'] = $servers['RCON password'];
		 $subpage['Default map'] = $servers['Default map'];
		 $subpage['Location'] = $servers['Location'];
		 $subpage['Config file'] = $servers['Config file'];
		 $subpage['Status'] = $online;
		 $subpage['user'] = $user;
		 $template->replace_vars($subpage);
		 $page2.= $template->get_template(); 
        // echo $page2.CR;
		
		
}
//die();
	//$template = new Template;
	
	$page['header'] = $template->load('html/header.html'); //load header
	$page['body'] = $template->load('html/body.html'); //load body
	$page['logo'] = $template->load('html/logo.html'); //logo
	$page['sidebar'] = $template->load('html/sidebar.html'); // menu
	$page['about'] = display_version();
	$page['tabs'] = $page1;
	$page['games'] = $page2;
	$template->load('html/index.html', COMMENT); // load page
	$template->replace_vars($page);	 
	$template->publish();
	//print_r($servers);
	}
function array_search_partial($arr, $keyword) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}
function refactor_array($array) {
	// refactor array with keys
	foreach ($array as &$value) {
			//read data
			$i = strpos($value,":",0);
            $key = trim(substr($value,0,$i));
		    $nos[$key] = trim(substr($value,$i+1));
		}
		return $nos;
//print_r($nos);
}
?>
