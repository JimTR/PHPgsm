<?php
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff
require 'includes/functions_site.php'; //admin functions 
//include("functions.php");
define ("CR", "</br>");

    $sql = 'select * from base_servers where extraip="0" and enabled="1"';
	
	
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
	$sql = 'SELECT servers.* , base_servers.url, base_servers.port as bport FROM `servers` left join `base_servers` on servers.host = base_servers.ip where servers.id <>"" and servers.enabled="1" order by servers.host_name';
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
		 $template->load('html/game_detail.html');
		 $template->replace_vars($subpage);
		 $fp .= $template->get_template();
		 // now load front page template 
         //set all to display:none & use js to turn them on/off	via xml data	
}

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
	
	
	$page['header'] = $template->load('html/header.html'); //load header
	//echo '<br>header loaded<br>';
	$page['body'] = $template->load('html/body.html'); //load body
	//echo 'body loaded<br>';
	$page['logo'] = $template->load('html/logo.html'); //logo
	//echo 'logo loaded<br>';
	$page['sidebar'] = $template->load('html/sidebar.html'); // menu
	//echo 'sidebar loaded<br>';
	$page['about'] = $template->load('html/about.html');
	//echo 'about loaded<br>';
	$page['tabs'] = $page1;
	$page['games'] = $page2;
	$page['install'] = $template->load('html/install.html');
	$page['settings'] = $template->load('html/settings.html');
	$page['version'] = $settings['version'];
	$page['date'] = date("Y");
	//$page['options'] = $fp; //beta ;
	$template->load('html/index.html', COMMENT); // load page
	$template->replace_vars($page);	
	$template->replace_vars($test);
	
	// lang goes here
	$template->publish();
	
	function settings() {
	//get the settings to edit
	global $page,$site, $database;
	$template = new Template;
	$template->load('html/settings.html');
	$sql = 'select * from settings where display = 1 and setting_type = 0 order by s_order asc ';
	$results = $database->get_results($sql);
		$setting_line = new Template; // set up the line html
		foreach ($results as $value){
			// loop the settings only settings stored will be displayed
			$setting_line->load('html/subs/settings_row.html');
			// need a routine to add missing settings
			if ($value['type'] == 1) {
				// here we add the image
				$temp['image'] = '<span><img style="max-height:50px;float:right;margin-top:1%;" src ="'.$site->settings[$value['area']].'"></span>';
			}
			else {$temp['image'] ='';}
			if ($value['type'] == 2)
				{
					$value['value'] = $site->settings[$value['area']];
					if ($value['area'] === 'year') { $tags ="Roman,Standard";}
					else {$tags = "Yes,No";}
					$temp['input'] = yesno_box($value,$tags);
				}
				elseif ($value['type'] == 0 || $value['type'] == 1 ) {
					$value['value'] = $site->settings[$value['area']];
					//text_box($value);
					$temp['input'] = text_box($value);}
				elseif ($value['type'] == 3) { 
					$value['value'] = $site->settings[$value['area']];
					$temp['input'] = select_box($value,"");
					
					}	
			$temp['title'] = $value['title'];
			
			$temp['value']= $site->settings[$value['area']];
			$temp['desc'] = $value['s_desc'];
			
			$setting_line->replace_vars($temp);
			$a .= $setting_line->get_template(); 
		}
		//print_r ($site->settings);
		//echo $a;
		$template->replace( 'settings',$a);
		return $template->get_template();
		
}
?>