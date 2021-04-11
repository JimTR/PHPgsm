#!/usr/bin/php
<?php
define ('cr',PHP_EOL);
define ('br','<br>');
define('DOC_ROOT', dirname(__FILE__,2));
include 'includes/functions.php';
include 'includes/class.color.php';
include 'includes/class.table.php';
$cc = new Console_Color2();
define('version',1.01);
if (!isset($argv[1])) {
	echo cr;
	echo $cc->convert("%rCommand Option Missing%n").cr;
	echo cr;
	echo "\t".$argv[0]." ".$cc->convert("%r-m%n")." - install a master server".cr;
	echo "\t".$argv[0]." ".$cc->convert("%r-s%n")." - install a slave server".cr;
	echo "\t".$argv[0]." ".$cc->convert("%r-h%n")." - display help".cr;
	echo "\t".$argv[0]." ".$cc->convert("%r-v%n")." - installer version".cr;
	echo cr;
	exit;
}
    if (strtolower($argv[1] == '-v')) {
		echo 'Install - '.version.cr;
		exit;
	}
	if (strtolower($argv[1] == '-h')) {
		echo 'help'.cr;
		exit;
	}
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
    

$tick = $cc->convert("%g  ✔%n");
$cross = $cc->convert("%r  ✖%n");
$req = $cc->convert("%gRequired%n");
$rreq = $cc->convert("%rRequired%n");
$opt = $cc->convert("%yOptional%n");
$ropt = $cc->convert("%rOptional%n");
 $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
$table->setHeaders(array('Installing PHPgsm',' Stage 1: Dependency Check','','',cr));

system('clear');
echo $cc->convert("%cPHPgsm Installer%n").cr; 
//echo get_boot_time().' '.$tick.cr;
$x32 = trim(shell_exec('dpkg --print-foreign-architectures'));
if (empty($x32)) {
	$x32 = 'Not Enabled';
}
$table->addRow(array($cc->convert("%yLinux Modules%n"),'' ,'',''));
$table->addRow(array($cc->convert("%cModule%n"),$cc->convert("%c\t\tVersion%n"),$cc->convert("%c\t\tStatus%n"),'',$cc->convert("%c\t\t\t\tUsage%n")));
$headmask = "%13.13s";
$screen = dpkg('screen');
$loc =dpkg('mlocate');
$git = dpkg('git'); 
$tmpr = dpkg('tmpreaper');
$steam = dpkg('steamcmd:i386');
$glib = dpkg('libc-bin');
$webmin = dpkg('webmin');
$st = dpkg('mysql-server');
$tmux = dpkg('tmux');
if(isset($steam[2])){
	$software['Steamcmd']['version']  = sprintf($headmask,$steam[2]);
	$software['Steamcmd']['use']  = $req.' - '.$steam[4].' install & update Steam dedicated game servers';
}

else {
	$software['Steamcmd']['version']  = sprintf($headmask,$steam[1]);
	$software['Steamcmd']['use']  = $rreq.' -  Command-line interface for Valve\'s Steam install & update Steam dedicated game servers';
}
if (isset($glib[2])){
	$software['GlibC']['version'] = sprintf($headmask,$glib[2]);
	$software['GlibC']['use'] = $req.' - '.$glib[4].' for Steam dedicated game servers';
}
else {
	$software['GlibC']['version'] = sprintf($headmask,$glib[1]);
	$software['GlibC']['use'] = $rreq.' -  for Steam dedicated game servers';
}	
$software['Foreign_Architecture']['version'] = sprintf($headmask,$x32);
$software['Foreign_Architecture']['use'] = $req.' - Steamcmd requires 32bit architecture';
if (isset($screen[2])){
	$software['Screen']['version'] = sprintf($headmask,$screen[2]);
	$software['Screen']['use'] = $req.' - '.$screen[4].' for Steam dedicated game servers';
}
else {
	$software['Screen']['version'] = sprintf($headmask,$screen[1]);
	$software['Screen']['use'] = $rreq.' -  for Steam dedicated game servers';
}	
if (!isset($st[2])) {
	$st = dpkg('mysql-common');
}
if (isset($st[2])) {
	
	$software['Mysql']['version'] = sprintf($headmask,$st[2]);
	$software['Mysql']['use'] = $opt.' - '.$st[4];
}
else {
	$software['Mysql']['version'] = sprintf($headmask,$st[1]);
	$software['Mysql']['use'] = $ropt.' - for use if the PHPgsm database is installed locally';
}
$apache = dpkg('apache2');
if (isset($apache[2])) {
$software['Apache']['version'] =  sprintf($headmask,$apache[2]);
$software['Apache']['use'] = $opt.' - '.$apache[4].', only required if using the web API on this machine (master server) ';
}
else {
	$software['Apache']['version'] = sprintf($headmask,$apache[1]);
	$software['Apache']['use'] = $ropt.' -  only required if using the web API on this machine ';
}
if (isset($git[2])) {
	$software['Git']['version'] = sprintf($headmask,$git[2]);
	$software['Git']['use'] = $opt.' - '.$git[4].' required to update PHPgsm automatically';
}
else {
		$software['Git']['version'] = sprintf($headmask,$git[1]);
		$software['Git']['use'] = $ropt.' -  use :- to update PHPgsm automatically';
	}
if (isset($tmpr[2])) {	
	$software['Tmpreaper']['version'] = sprintf($headmask,$tmpr[2]);
	$software['Tmpreaper']['use'] = $opt.' - '.$tmpr[4].' used for log pruning';
}
else {
	$software['Tmpreaper']['version'] = sprintf($headmask,$tmpr[1]);
	$software['Tmpreaper']['use'] = $ropt.' -  used for log pruning';
}	
if (isset($tmux[2])) {	
	$software['Tmux']['version'] = sprintf($headmask,$tmux[2]);
	$software['Tmux']['use'] = $opt.' - '.$tmux[4].' used for LGSM compatability';
}
else {
	$software['Tmux']['version'] = sprintf($headmask,$tmux[1]);
	$software['Tmux']['use'] = $ropt.' -  terminal multiplexer used for LGSM compatability';
}	
if(isset($webmin[2])) {
$software['Webmin']['version'] = sprintf($headmask,$webmin[2]);
$software['Webmin']['use'] = $opt.' - '.$webmin[4];
}
else {
	$err = trim($cc->convert("%r".$webmin[1]."%n"));
	$software['Webmin']['version'] = sprintf($headmask,$webmin[1]); //
	$software['Webmin']['use'] = $ropt.' - web-based administration interface for Unix systems';
}
if(isset($loc[2])){
	$software['Locate']['version'] = sprintf($headmask,$loc[2]);
	$software['Locate']['use'] = $opt.' - '.$loc[4];
}
else {
	$software['Locate']['version'] = sprintf($headmask,$loc[1]);
	$software['Locate']['use'] = $ropt.' - quickly find files on the filesystem based on their name';
}		
foreach ($software as $k => $v) {
	if ($v['version'] !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_',' ',$k);
	$table->addRow(array($k,$v['version'] ,$stat,'',$v['use']));
}
unset($software);
$php_v = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;
$software['php']['version'] = sprintf($headmask,phpversion());
$software['php']['use'] = "$req - server-side, HTML-embedded scripting language";
$pmysql = dpkg('php'.$php_v.'-mysql');
$software['php_mysql']['version'] = sprintf($headmask,$pmysql[2]);
$software['php_mysql']['use'] ="$req - ".$pmysql[4];
$software['php_gmp']['version'] = sprintf($headmask,phpversion('gmp'));
$software['php_gmp']['use'] ="$req - ".'GMP module for PHP - provides advanced math functions';
if (!empty(phpversion('zip'))) {
$software['php_zip']['version'] = sprintf($headmask,phpversion('zip'));
}
else {
	$software['php_zip']['version'] = sprintf($headmask,'Not Installed');
}
$software['php_zip']['use'] ="$opt - ZIP module for PHP - provides archive functions required for later versions of PHPgsm";
$software['php_xml']['version'] = sprintf($headmask,phpversion('xml'));
$software['php_xml']['use'] ="$req - ".'XML module for PHP - provides xml data support';
$software['php_json']['version'] = sprintf($headmask,phpVersion('json')); // virtual pack as of 8.0 let php work it out
$software['php_json']['use'] ="$req - JSON module for PHP";
$software['php_mbstring']['version'] = sprintf($headmask,phpversion('mbstring'));
$software['php_mbstring']['use'] ="$req - MBSTRING module for PHP - provides database functions for multibyte objects";
$software['php_readline']['version'] = sprintf($headmask,phpversion('readline'));
$software['php_readline']['use'] ="$req - READLINE module for PHP ";
$popcache = dpkg('php'.$php_v.'-opcache');
$software['php_opcache']['version'] = sprintf($headmask,$popcache[2]);
$software['php_opcache']['use'] = "$opt - ".$popcache[4];
//print_r($software);
//die();
$table->addRow(array('','' ,'',''));
$table->addRow(array($cc->convert("%yPHP Modules%n"),'' ,'',''));
$table->addRow(array($cc->convert("%cModule%n"),$cc->convert("%c\t\tVersion%n"),$cc->convert("%c\t\tStatus%n"),'',$cc->convert("%c\t\t\t\tUsage%n")));
foreach ($software as $k => $v) {
	if ($v['version'] !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_','-',$k);
	$table->addRow(array($k,$v['version'] ,$stat,'',$v['use']));
}
unset($software);
$table->addRow(array('','' ,'',''));
$table->addRow(array($cc->convert("%yPHPgsm Modules%n"),'' ,''));
$table->addRow(array($cc->convert("%cModule%n"),$cc->convert("%c\t\tVersion%n"),$cc->convert("%c\t\tStatus%n"),'',$cc->convert("%c\t\t\t\tUsage%n")));
$software['Ajax'] = sprintf($headmask,getVersion('php '.DOC_ROOT.'/ajaxv2.php action=version'));
$software['Scanlog'] = sprintf($headmask,getVersion(DOC_ROOT.'/scanlog.php v'));
$software['Functions'] = sprintf($headmask,getVersion('php '.DOC_ROOT.'/functions.php -v'));
foreach ($software as $k => $v) {
	if ($v !=''){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_','-',$k);
	$table->addRow(array($k,$v ,$stat));
}


echo $table->getTable();
echo cr;
$answer = strtoupper(ask_question('press (I)nstall (S)kip (Q)uit  ',null,null));
echo "the answer is $answer".cr;
if (is_file(DOC_ROOT.'/includes/config.php')) {
	//db_config(1);
}
else {
		//db_config(0);
	}
	
	
function db_config($action) {
	if ($action == 1) {
		echo cr.cr;
		ask_question('We have configuration for the database connection continue with reconfigure ? ',null,null,false);
	}
	else {
		echo 'do config thingy'.cr;
		$sqlfile = 'data/structure.sql'; 
	}
}
			
?>
