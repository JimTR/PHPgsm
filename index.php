<?php
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff

//include("functions.php");
define ("CR", "</br>");

$sql = 'select * from base_servers where extraip="0" and enabled="1"';
	
	$database = new db(); // connect to database
	
	$template = new Template; // load template class
	$res = $database->get_results($sql); // pull results
	foreach ($res as $data) {
		// get friendly name
		$fname = $data['fname'];
	}
	$template->load('html/base_server_detail.html');
	$template->replace('fname',$fname); //add id's
	$bs1=$template->get_template();
	//exit;
	$servers = array(); // set array
	if (empty($Auth->id)) {
		
		$template->load('html/login.html');
		$template->replace('servername', $_SERVER['SERVER_NAME']);
		$template->publish();
		exit;
	}
	
foreach ($res as $data) {
	
	
	$template->load('html/base_server.html'); // load blank template
	$template->replace('bs1',$bs1);
	$subpage['server_title'] = $data['name'].' ('.$data['ip'].')';
	$template->replace_vars($subpage);
	$page1.= $template->get_template();
	//use xml
	//add the data array for base server 
	//this does allow remote locations
	// as long as you have the remote software installed
		
	}
	
		
	//Game server(s) 
	$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1"';
	$res = $database->get_results($sql);
	
	foreach ($res as $data) {
		// loop servers
		
		$template->load('html/game_server.html'); // load blank template
		$url = $data['url'].':'.$data['bport'];
		 $ch = curl_init();
	     curl_setopt($ch, CURLOPT_URL, $url.'/ajax.php?action=user');
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 $user = curl_exec($ch);
		 curl_close($ch);
		 $key = $data['host_name'];
		 $subpage['key'] = $key;
		 $subpage['user'] = $user;
		 $subpage['url'] = $url;
		 // xml finish
		 $template->replace_vars($subpage);
		 $page2.= $template->get_template(); 
        		
}
//die();
  //echo $page2.CR;
  //echo 'all data loaded'.CR;
	//$template = new Template;
	$sql = 'select * from game_servers';
	$opts = $database->get_results($sql);
	//print_r($opts);
	$test = array();
	$test['options']='<select id ="gs" class="form-control m-bot15" style="width:fit-content;float:left;">
                  <option value="">Choose Server</option>';
	foreach ($opts as $options) {
		// get servers back
		//echo $options['game_name'].' '.$options['default_path'].CR;
		$test['options'] = $test['options']. '<option value="'.$options['default_path'].'">'.$options['game_name'].'</option>';
		//echo $test;
	}
	$test['options']= $test['options'].'</select>';
	
	//echo 'options loaded'.CR;
	//echo $test['options'];
	//die;
	$page['header'] = $template->load('html/header.html'); //load header
	//echo '<br>header loaded<br>';
	$page['body'] = $template->load('html/body.html'); //load body
	//echo 'body loaded<br>';
	$page['logo'] = $template->load('html/logo.html'); //logo
	//echo 'logo loaded<br>';
	$page['sidebar'] = $template->load('html/sidebar.html'); // menu
	//echo 'sidebar loaded<br>';
	$page['about'] = 'rewrite';
	//echo 'about loaded<br>';
	$page['tabs'] = $page1;
	$page['games'] = $page2;
	$page['install'] = $template->load('html/install.html');
	//$page['options']= "";
	$template->load('html/index.html', COMMENT); // load page
	$template->replace_vars($page);	
	$template->replace_vars($test);
	//echo '<br>about to publish'; 
	// lang goes here
	$template->publish();
	
	
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
