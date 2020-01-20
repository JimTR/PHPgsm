<?php
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff
include("functions.php");
define ("CR", "</br>");
$disk_info = get_disk_info();
$user_info = get_user_info($disk_info);
$mem = get_mem_info();
//die ('loaded');
$sql = 'select * from base_servers';
if (is_cli()) {
//$x=check_sudo(get_current_user());

//include  'cli.php';
}
else {
	
	$database = new db(); // connect to database
	$template = new Template; // load template class
	$cpu_info = get_cpu_info();
	$software = get_software_info();
	$os = lsb();
	$disk_info = get_disk_info();
	$user_info = get_user_info($disk_info);
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
	$server = shell_exec('curl '.$subpage['host'].'/ajax.php?action=all');
	$server= explode('\n',$server);
	
	$subpage['cpu'] = $server[0];
	$subpage['software'] = $server[1];
	$subpage['disk'] = $server[2];
	$subpage['mem'] = $server[3];
	$subpage['user'] = $server[4];
	$template->replace_vars($subpage);
	$page1.= $template->get_template(); 
	}
	//echo 'end loop<br>';
	//$sql = 'SELECT `servers.*`, `base_servers.url` FROM `servers` join `base_serves` on `servers.host` = `base_servers.ip` WHERE `status` = "l"';
	$sql = 'SELECT * FROM `servers` WHERE `status` = "l"';
	$res = $database->get_results($sql);
	//$server = shell_exec('curl '.$res['url'].'/ajax.php?action=all');
	//$server= explode('\n',$server);
	$servers= array();
	foreach ($res as $data) {
		// servers
		
		$template->load('html/game_server.html'); // load blank template
		$action ='dt';
		$server = $data['location'].'/'.$data['host_name'];
		$x1 = explode(PHP_EOL, exec_lgsm($server,$action));
		//print_r( refactor_array($x1));
		$servers = refactor_array($x1);
		//print_r($servers);
		//die();
		/*
		 * Array ( 
		 * [0] => 
		 * [1] => Distro Details 
		 * [2] => 
		 * [3] => Distro: Ubuntu 18.04.3 LTS 
		 * [4] => Arch: x86_64 
		 * [5] => Kernel: 4.15.0-65-generic 
		 * [6] => Hostname: mail.ickleh.uk 
		 * [7] => Uptime: 83d, 0h, 2m 
		 * [8] => tmux: tmux 2.6 
		 * [9] => glibc: 2.27 
		 * [10] => 
		 * [11] => Server Resource 
		 * [12] => 
		 * [13] => CPU 
		 * [14] => Model: QEMU Virtual CPU version (cpu64-rhel6) 
		 * [15] => Cores: 4 
		 * [16] => Frequency: 2599.996MHz 
		 * [17] => Avg Load: 0.15, 0.41, 0.52 
		 * [18] => 
		 * [19] => Memory 
		 * [20] => Mem: total used free cached available 
		 * [21] => Physical: 3.0GB 2.2GB 542MB 553MB 542MB 
		 * [22] => Swap: 1.5GB 617MB 885MB 
		 * [23] => 
		 * [24] => Storage 
		 * [25] => Filesystem: /dev/vda3 
		 * [26] => Total: 68G 
		 * [27] => Used: 19G 
		 * [28] => Available: 47G 
		 * [29] => 
		 * [30] => Network 
		 * [31] => Interface: eth0 
		 * [32] => IP: 46.32.237.232 
		 * [33] => 
		 * [34] => Game Server Resource Usage 
		 * [35] => 
		 * [36] => CPU Used: 0% 
		 * [37] => Mem Used: 0% 0MB 
		 * [38] => 
		 * [39] => Storage 
		 * [40] => Total: 5.6G 
		 * [41] => Serverfiles: 2.8G 
		 * [42] => Backups: 1.6G 
		 * [43] => 
		 * [44] => Fistful of Frags Server Details 
		 * [45] => 
		 * [46] => Server name: Jim's Frags Server 
		 * [47] => Server IP: 46.32.237.232:27015 
		 * [48] => Server password: NOT SET 
		 * [49] => RCON password: admin0gb7apbA 
		 * [50] => Maxplayers: 20 
		 * [51] => Default map: fof_robertlee 
		 * [52] => Master server: true 
		 * [53] => Status: OFFLINE [54] => 
		 * [55] => fofserver Script Details 
		 * [56] => 
		 * [57] => Script name: fofserver 
		 * [58] => LinuxGSM version: v19.12.5
		 * [59] => glibc required: 2.15 
		 * [60] => Discord alert: off 
		 * [61] => Slack alert: off 
		 * [62] => Email alert: off 
		 * [63] => Pushbullet alert: off 
		 * [64] => IFTTT alert: off 
		 * [65] => Mailgun (email) alert: off 
		 * [66] => Pushover alert: off 
		 * [67] => Telegram alert: off 
		 * [68] => Update on start: off 
		 * [69] => User: nod 
		 * [70] => Location: /home/nod/public_html/fof 
		 * [71] => Config file: /home/nod/public_html/fof/serverfiles/fof/cfg/fofserver.cfg 
		 * [72] => 
		 * [73] => Backups 
		 * [74] => 
		 * [75] => No. of backups: 1 
		 * [76] => Latest backup: 
		 * [77] => date: Mon Dec 2 01:40:06 GMT 2019 (39 days ago) 
		 * [78] => file: /home/nod/public_html/fof/lgsm/backup/fofserver-2019-12-02-013651.tar.gz 
		 * [79] => size: 1.6G
		 * [80] => 
		 * [81] => Command-line Parameters 
		 * [82] =>
		 * [83] => ./srcds_run -game fof -strictportbind -ip 46.32.237.232 -port 27015 +clientport 27005 +tv_port 27020 +map fof_robertlee +servercfgfile fofserver.cfg -maxplayers 20
		 * [84] =>
		 * [85] => Ports 
		 * [86] => 
		 * [87] => Change ports by editing the parameters in: 
		 * [88] => /home/nod/public_html/fof/lgsm/config-lgsm/fofserver 
		 * [89] => 
		 * [90] => Useful port diagnostic command: 
		 * [91] => netstat -atunp | grep srcds_linux 
		 * [92] => 
		 * [93] => DESCRIPTION DIRECTION PORT PROTOCOL 
		 * [94] => > Game/RCON INBOUND 27015 tcp/udp 
		 * [95] => > SourceTV INBOUND 27020 udp 
		 * [96] => < Client OUTBOUND 27005 udp 
		 * [97] => 
		 * [98] => Status: OFFLINE 
		 * [99] => 
		 * [100] =>
		 * [101] => command_details.sh exiting with code: 0 [102] => )
		 */ 
		 if (isset($servers['Game world'])) {
			 $servers['Default map'] = $servers['Game world'];
			 }
		 $cmd = array_search_partial($x1,'Command-line Parameters' )+2;
		 $servers['cmd'] = $x1[$cmd];
		 $gameport = array_search_partial($x1,'DESCRIPTION' )+1;
		 $sourceport = $gameport+1;
		 $clientport = $sourceport+1;		 
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
		 $subpage['Maxplayers'] = $servers['Maxplayers'];
		 $subpage['Server password'] = $servers['Server password'];
		 $subpage['RCON password'] = $servers['RCON password'];
		 $subpage['Default map'] = $servers['Default map'];
		 $subpage['Location'] = $servers['Location'];
		 $subpage['Config file'] = $servers['Config file'];
		 $subpage['user'] = display_user($user_info);
		 $template->replace_vars($subpage);
		 $page2.= $template->get_template(); 
		
		unset($x1);
		
	} 
	//echo $page['tabs'];
	//die();
	
	//$template = new Template;
	$page['header'] = $template->load('html/header.html'); //load header
	$page['body'] = $template->load('html/body.html'); //load body
	$page['logo'] = $template->load('html/logo.html'); //logo
	$page['sidebar'] = $template->load('html/sidebar.html'); // menu
	$page['cpu'] = display_cpu($cpu_info);
	$page['about'] = display_version();
	$page['user'] = display_user($user_info);
	$page['mem'] = display_mem($mem,true);
	$page['rgames'] = display_games();
	$page['software'] = display_software($os,$software);
	$page['disk'] = display_disk($disk_info);
	$page['user'] = display_user($user_info);
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
