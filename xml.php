<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include 'includes/master.inc.php';
include 'functions.php';

if (!empty($_POST)) {
	 $cmds = $_POST;
 }
 else {
	 $cmds = $_GET;
 }

if(isset($cmds)){$cmds = change_value_case($cmds,CASE_LOWER);}
if (empty($cmds['type'])) {$cmds['type']='all';}
require_once('GameQ/Autoloader.php'); //load GameQ
$GameQ = new \GameQ\GameQ();
$database = new db(); 
$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport, base_servers.fname FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"';
$res = $database->get_results($sql);
$sql = 'select base_servers.*, software.* from base_servers left join software on base_servers.ip = software.ip where extraip="0" and enabled="1"';
$base_servers = $database->get_results($sql); 
$Gq = array();
$xml = new SimpleXMLElement('<Servers/>');

if ($cmds['type'] == 'games' || $cmds['type'] == 'all') {
foreach ($res as $getgames) {
	// get game data
	     $key = $getgames['host_name'];
		 $Gq[$key]['id'] = $getgames['host_name'];
	     $Gq[$key]['host'] = $getgames['host'].':'.$getgames['port'];
	     $Gq[$key]['type'] = $getgames['type'];
} 
 
          $GameQ->addServers($Gq);
          $results = $GameQ->process();


$xmlserver="game_server";
foreach ($res as $data) {
	if ($data['buildid'] < $data['rbuildid']) {
		// needs update
		$update = 'Requires Update to version '.$data['rbuildid'];
		$updatei = 1;
	}  
	else {
		$update = 'Up To Date';
		$updatei = 0;
	}
	$now = new Datetime();
	 
	$date = new DateTime();
	$date->setTimestamp($data['starttime']);
	
	$interval = $now->diff($date);

    $rt = $interval->format('%a days %h hours %I mins %S Seconds');
	if (empty($results[$data['host_name']]['gq_online'])) {
		$online = 'Offline';
	}
	else {
		$online = 'Online';
		//$update = xmlResponse($data['app_id'],$results[$data['host_name']]['version']); // add version ctl
		
	}
	//ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep <cfgfile>
	$tmp = explode(' ',trim(shell_exec('ps -C srcds_linux -o pid,%cpu,%mem,cmd |grep '.$data['host_name'])));
	$pid = $tmp[0];
	$tmp = array_values(array_filter(explode(' ',trim(shell_exec('top -b -n 1 -p '.$pid.' | sed 1,7d')))));
	$count =  count($tmp);
	
     //print_r($tmp);
     //die();
	$track = $xml->addChild($xmlserver);
	$track->addChild('uid',$data['uid']);
    $track->addChild('name',$data['host_name']);
    $track->addChild(fname,$data['fname']);
    $track->addChild('app_id',$data['app_id']);
    $track->addChild('game_port',$data['port']);
    $track->addChild('source_port',$data['source_port']);
    $track->addChild('client_port',$data['client_port']);
    $track->addChild('server_pass',$data['server_password']);
    $track->addChild('rcon_pass',$data['rcon_password']);
    $track->addChild('rt',$rt );
    $track->addChild('secure',$results[$data['host_name']]['secure']);
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
    $track->addChild('joinlink',$results[$data['host_name']]['gq_joinlink']);
    $track->addChild('players',$results[$data['host_name']]['gq_numplayers']);
    $track->addChild('maxplayers',$data['max_players']);
    $track->addChild('bots', $results[$data['host_name']]['num_bots']);
    $track->addChild('update_msg',$update);
    $track->addChild('uds',$updatei);
    $track->addChild('version',$data['buildid'].' (last updated '.date('l jS F Y \a\t g:ia',$data['server_update']).')');
    $track->addChild('cpu',trim($tmp[$count-4]));
    $track->addChild('mem',trim($tmp[$count-3]));
    $track->addChild('count',$count);
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
 unset ($update);
 unset ($count); 
}
XML_print($xml);
die();
}

if ($cmds['type'] == 'base' || $cmds['type'] == 'all') {
//$mem_info = get_mem_info(); //theses need to be the server in question
//$disk_info = get_disk_info();
//$up_time = get_boot_time();
//$cpu_info = get_cpu_info();

$xmlserver = "base_server";
foreach ($base_servers as $data) {
	//$up_time = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=boottime');
	$temp0 = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=hardware&data=true');
	$cpu_info = json_decode($temp0);
	$temp1 = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=software&data=true');
	$software = json_decode($temp1);
	$temp = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=disk&data=true');
	$disk_nfo = json_decode(stripslashes($temp),true);
	$temp = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=memory&data=true');
	$mem_info = json_decode(stripslashes($temp),true);
	//header('Access-Control-Allow-Origin: *');
	//$temp = file_get_contents($data['url'].':'.$data['port'].'/ajax.php?action=game_detail&data=true');
	//$game_detail = json_decode(stripslashes($temp),true);
	$track = $xml->addChild($xmlserver);
    $track->addChild('name',$data['name']);
    $track->addChild('fname',$data['fname']);
    $track->addChild('distro',$software->os);
    $track->addChild('ip', $cpu_info->local_ip);
    $track->addChild('cpu_model', $cpu_info->model_name);
    $track->addChild('cpu_processors', $cpu_info->processors);
    $track->addChild('cpu_cores',$cpu_info->cpu_cores);
    $track->addChild('cpu_speed',$cpu_info->cpu_MHz);
    $track->addChild('cpu_cache',$cpu_info->cache_size);
    $track->addChild('process',$cpu_info->process);
    $track->addChild('reboot',$cpu_info->reboot);
    $track->addChild('kernel',$software->k_ver);
    $track->addChild('php',$software->php);
    $track->addChild('screen',$software->screen);
    $track->addChild('glibc',$software->glibc); 
    $track->addChild('mysql',$software->mysql);
    $track->addChild('apache',$software->apache); 
    $track->addChild('curl',$software->curl);
    $track->addChild('nginx',$software->nginx);  
    $track->addChild('quota',$software->quota);
    $track->addChild('postfix',$software->postfix);
    $track->addChild('uptime',$cpu_info->boot_time); 
    $track->addChild('memTotal',trim($mem_info['MemTotal']));  
    $track->addChild('memfree',trim($mem_info['MemFree']));
    $track->addChild('memcache',trim($mem_info['Cached']));
    $track->addChild('memactive',trim($mem_info['Active']));
    $track->addChild('swaptotal',trim($mem_info['SwapTotal']));
    $track->addChild('swapfree',trim($mem_info['SwapFree']));
    $track->addChild('swapcache',trim($mem_info['SwapCached']));
    $track->addChild('boot_filesystem',$disk_nfo['boot_filesystem']);
    $track->addChild('boot_mount',$disk_nfo['boot_mount']);
    $track->addChild('boot_size',$disk_nfo['boot_size']);
    $track->addChild('boot_used',$disk_nfo['boot_used'] ." (".$disk_nfo['boot_pc'] .")");
    $track->addChild('boot_free',$disk_nfo['boot_free']);
    $track->addChild('load',$cpu_info->load);
    $track->addChild('gamespace',$game_detail['general']['total_size']);
    //$track->addChild('total_cpu',$game_detail['general']['cpu']);
    if (isset($disk_nfo['home_filesystem'])) {
		// diff
		$track->addChild('home_filesystyem',$disk_nfo['home_filesystem']);
		$track->addChild('home_mount',$disk_nfo['home_mount']);
		$track->addChild('home_size',$disk_nfo['home_size']);
		
	}
}
}
//die();
if (!(isset($cmds['action']))){ 
XML_print($xml);
}
else {
	XML_array($xml);
}
function XML_print($xml) {
	Header('Content-type: text/xml');
	//header('Access-Control-Allow-Origin: *');
	print($xml->asXML());
}
function XML_array($xml) {
	$new = simplexml_load_string($xml->asXML());
	$con = json_encode($new);  
	
 echo $con;     
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
    
function xmlResponse($app,$version) {
	   //client version
		$version = str_replace('.','',$version); // remove points
		 if (!is_numeric($version) ) {
			$response['message'] = 'invalid Server version';
			return $response;
		} 
		//echo version
		$url = "https://api.steampowered.com/ISteamApps/UpToDateCheck/v1/?appid=".$app."&version=".$version;
		//file_put_contents('debug.txt', $url.PHP_EOL, FILE_APPEND | LOCK_EX);
		$ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url);
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $response = json_decode(curl_exec($ch),true);
		 curl_close($ch);
		 //$response = json_decode($response,true);
		 $response=($response['response']);
		
		
	 if ($response['success'] === true){
		 //unset($response['success']);
		 unset($response['version_is_listable']);
		if ($response['up_to_date'] === true) {
			//unset($response['up_to_date']);
			unset($response['version_is_listable']);
			$response['message'] = 'Up To Date';
			$response['required_version'] = $version;
			
}
		else {
			$response['up_to_date'] = 0;
			
			$response['message'] = 'Update Required';
			
}
}
return $response;
}
function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!$contents) return array();
        if (!function_exists('xml_parser_create')) {
            // print "'xml_parser_create()' function not found!";
            return array();
        }
        // Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); // http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents) , $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) return; //Hmm...
        // Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array; //Refference
        // Go through the tags.
        $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble
            // This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data); //We could use the array by itself, but this cooler.
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }
            // Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {                                   
                                    if ( $attr == 'ResStatus' ) {
                                        $current[$attr][] = $val;
                                    }
                    if ($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            // See tag status and do the needed.
                        //echo"<br/> Type:".$type;
            if ($type == "open") { //The starting of the tag '<tag>'
                $parent[$level - 1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
                                        //print_r($current[$tag . '_attr']);
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            }
            elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                // See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;
                }
                else { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //If it is not an array...
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            }
            elseif ($type == 'close') { //End of tag '</tag>'
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }
    
    // Let's call the this above function xml2array
    
    // it will work 100% if not ping me @skype: sapan.mohannty
    
?>