<?php
include 'data/include.php';
include DOC_ROOT.'/functions.php';
include DOC_ROOT.'/includes/class.color.php';
include DOC_ROOT.'/includes/class.table.php';

define('version','1.0');
    if (strtolower($argv[1] == 'v')) {
		echo 'Install - '.version.cr;
		exit;
	}
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
    
$cc = new Console_Color2();
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
$table->setHeaders(array('Installing PHPgsm',' Stage 1: Dependency Check'));
$table->addRow(array('','' ,'',''));
system('clear');
echo $cc->convert("%cPHPgsm Installer%n").cr; 
//echo get_boot_time().' '.$tick.cr;
$x32 = trim(shell_exec('dpkg --print-foreign-architectures'));
if (empty($x32)) {
	$x32 = 'Not Enabled';
}
$table->addRow(array('Module','   Version' ,'Status','Usage'));
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
	$software['Steamcmd']['version']  = $steam[2];
	$software['Steamcmd']['use']  = $req.' - '.$steam[4].' install & update Steam dedicated game servers';
}

else {
	$software['Steamcmd']['version']  = $steam[1];
	$software['Steamcmd']['use']  = $rreq.' -  use :- install & update Steam dedicated game servers';
}
if (isset($glib[2])){
	$software['GlibC']['version'] = $glib[2];
	$software['GlibC']['use'] = $req.' - '.$glib[4].' for Steam dedicated game servers';
}
else {
	$software['GlibC']['version'] = $glib[1];
	$software['GlibC']['use'] = $rreq.' -  for Steam dedicated game servers';
}	
$software['Foreign_Architecture']['version'] = $x32;
$software['Foreign_Architecture']['use'] = $req.' - Steamcmd requires 32bit architecture';
if (isset($screen[2])){
	$software['Screen']['version'] = $screen[2];
	$software['Screen']['use'] = $req.' - '.$screen[4].' for Steam dedicated game servers';
}
else {
	$software['Screen']['version'] = $screen[1];
	$software['Screen']['use'] = $rreq.' -  for Steam dedicated game servers';
}	
if (!isset($st[2])) {
	$st = dpkg('mysql-common');
}
if (isset($st[2])) {
	$software['Mysql']['version'] = $st[2];
	$software['Mysql']['use'] = $opt.' - '.$st[4];
}
else {
	$software['Mysql']['version'] = $st[1];
	$software['Mysql']['use'] = $ropt.' - for use if the PHPgsm database is installed locally';
}
$apache = dpkg('apache2');
if (isset($apache[2])) {
$software['Apache']['version'] =  $apache[2];
$software['Apache']['use'] = $opt.' - '.$apache[4].', only required if using the web API on this machine ';
}
else {
	$software['Apache']['version'] = $apache[1];
	$software['Apache']['use'] = $ropt.' -  only required if using the web API on this machine ';
}
if (isset($git[2])) {
	$software['Git']['version'] = $git[2];
	$software['Git']['use'] = $opt.' - '.$git[4].' required to update PHPgsm automatically';
}
else {
		$software['Git']['version'] = $git[1];
		$software['Git']['use'] = $ropt.' -  use :- to update PHPgsm automatically';
	}
if (isset($tmpr[2])) {	
	$software['Tmpreaper']['version'] = $tmpr[2];
	$software['Tmpreaper']['use'] = $opt.' - '.$tmpr[4].' used for log pruning';
}
else {
	$software['Tmpreaper']['version'] = $tmpr[1];
	$software['Tmpreaper']['use'] = $ropt.' -  used for log pruning';
}	
if (isset($tmux[2])) {	
	$software['Tmux']['version'] = $tmux[2];
	$software['Tmux']['use'] = $opt.' - '.$tmux[4].' used for LGSM compatability';
}
else {
	$software['Tmux']['version'] = $tmux[1];
	$software['Tmux']['use'] = $ropt.' -  terminal multiplexer used for LGSM compatability';
}	
if(isset($webmin[2])) {
$software['Webmin']['version'] = $webmin[2];
$software['Webmin']['use'] = $opt.' - '.$webmin[4];
}
else {
	$software['Webmin']['version'] = $webmin[1];
	$software['Webmin']['use'] = $ropt.' - web-based administration interface for Unix systems';
}
if(isset($loc[2])){
	$software['Locate']['version'] = $loc[2];
	$software['Locate']['use'] = $opt.' - '.$loc[4];
}
else {
	$software['Locate']['version'] = $loc[1];
	$software['Locate']['use'] = $ropt.' - quickly find files on the filesystem based on their name';
}		
foreach ($software as $k => $v) {
	if ($v['version'] !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_',' ',$k);
	$table->addRow(array($k,$v['version'] ,$stat,'',$v['use']));
}
unset($software);
$software['php'] = phpversion();
$software['php_mysql'] = phpversion('mysqli');
$software['php_gmp'] = phpversion('gmp');
$software['php_zip'] = phpversion('zip');
$software['php_xml'] = phpversion('xml');
$software['php_json'] = phpVersion('json');
$software['php_mbstring'] = phpversion('mbstring');
$software['php_readline'] = phpversion('readline');
$software['php_opcache'] = phpversion('opcache');
$table->addRow(array('','' ,'',''));
$table->addRow(array($cc->convert("%yPHP Modules%n"),'' ,''));
foreach ($software as $k => $v) {
	if ($v !=''){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_','-',$k);
	$table->addRow(array($k,$v ,$stat));
}
unset($software);
$table->addRow(array('','' ,'',''));
$table->addRow(array($cc->convert("%yPHPgsm Modules%n"),'' ,''));
$software['Ajax'] = getVersion('php ../ajaxv2.php action=version');
$software['Scanlog'] = getVersion('../scanlog.php v');
foreach ($software as $k => $v) {
	if ($v !=''){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_','-',$k);
	$table->addRow(array($k,$v ,$stat));
}


echo $table->getTable();

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
