<?php
@ob_end_clean();
require 'includes/master.inc.php'; // do login and stuff
include("functions.php");
define ("CR", "</br>");
$disk_info = get_disk_info();
$user_info = get_user_info($disk_info);
$mem = get_mem_info();
//die ('loaded');

if (is_cli()) {
//$x=check_sudo(get_current_user());

include  'cli.php';
}
else {
	//$page = new template;
	
	$cpu_info = get_cpu_info();
	$template = new Template;
	$page['header'] = $template->load('html/header.html'); //load header
	$page['body'] = $template->load('html/body.html'); //load body
	$page['logo'] = $template->load('html/logo.html'); //logo
	$page['sidebar'] = $template->load('html/sidebar.html'); // menu
	$page['cpu'] = display_cpu($cpu_info);
	$page['about'] = display_version();
	$page['user'] = display_user($user_info);
	$page['mem'] = display_mem($mem,true);
	$page['rgames'] = display_games();
	//echo $page['header'];
	//print_r($page); 
	$template->load('html/index.html', COMMENT); // load page
	$template->replace_vars($page);	 
	$template->publish();
	//exit;
	
	$cpu_info = get_cpu_info();
    $os = lsb();
    $software = get_software_info();

           // print_r($page);		
//include 'html.php';
}
 
//echo "Currently Running Servers for ".get_current_user().CR;
 
//display_disk($disk_info);

//echo '<div style = "float:right;width:20%;color:blue;clear:left;">';
//display_version();
//echo '</div>';
//display_software($os,$software);
//print_r($os);
echo "<p>";
//print_r($software);
//echo strpos($tmux,'server')."\r\n";
//echo PHP_OS." (".lsb().")".CR;
$filename = 'index.php';
//print_r(posix_getpwuid(fileowner($filename)));
//echo '</p>'.$os['PRETTY_NAME'].CR;
?>
