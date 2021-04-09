<?php
include 'data/include.php';
include DOC_ROOT.'/functions.php';
include DOC_ROOT.'/includes/class.color.php';
include DOC_ROOT.'/includes/class.table.php';
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
$table->addRow(array('Module','   Version' ,'Status','Usage'));


$git = dpkg('git'); 
$tmpr = dpkg('tmpreaper');
$steam = dpkg('steamcmd:i386');
$glib = dpkg('libc-bin');
$st = dpkg('mysql-server');
if (!isset($st[2])) {
	$st = dpkg('mysql-common');
}
if (isset($st[2])) {
	$software['Mysql']['version'] = $st[2];
	$software['Mysql']['use'] = 'Optional - '.$st[4];
}
else {
	$software['Mysql']['version'] = $st[1];
	$software['Mysql']['use'] = 'Optional - for use if the PHPgsm database is installed locally';
}
$apache = dpkg('apache2');
if (isset($apache[2])) {
$software['Apache']['version'] =  $apache[2];
$software['Apache']['use'] = 'Optional - '.$apache[4].', only required if using the web API on this machine ';
}
else {
	$software['Apache']['version'] = $apache[1];
	$software['Apache']['use'] = 'Optional -  only required if using the web API on this machine ';
}
if (isset($git[2])) {
	$software['Git']['version'] = $git[2];
	$software['Git']['use'] = 'Optional -  '.$git[4].' required to update PHPgsm automatically';
}
else {
		$software['Git']['version'] = $git[1];
		$software['Git']['use'] = 'Optional -  use :- to update PHPgsm automatically';
	}
if (isset($tmpr[2])) {	
	$software['Tmpreaper']['version'] = $tmpr[2];
	$software['Tmpreaper']['use'] = 'Optional - '.$tmpr[4].' used for log pruning';
}
else {
	$software['Tmpreaper']['version'] = $tmpr[1];
	$software['Tmpreaper']['use'] = 'Optional -  used for log pruning';
}	
if(isset($steam[2])){
	$software['Steamcmd']['version']  = $steam[2];
	$software['Steamcmd']['use']  = 'Required - '.$steam[4].' install & update Steam dedicated game servers';
}

else {
	$software['Steamcmd']['version']  = $steam[1];
	$software['Steamcmd']['use']  = 'Required -  use :- install & update Steam dedicated game servers';
}
if (isset($glib[2])){
	$software['GlibC']['version'] = $glib[2];
	$software['GlibC']['use'] = 'Required - '.$glib[4].' for Steam dedicated game servers';
}
else {
	$software['GlibC']['version'] = $glib[1];
	$software['GlibC']['use'] = 'Required -  for Steam dedicated game servers';
}	
$software['foreign_architecture']['version'] = $x32;
$software['foreign_architecture']['use'] = 'Required  - Steamcmd';
$software['webmin']['version'] = getVersion('webmin -v');
$software['webmin']['use'] = 'Optional - easy configuration tool for apache, mysql etc';
$software['locate']['version'] = getVersion('locate -V');
$software['locate']['use'] = 'Optional - fast file finder';
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
/*if ($apache !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
$table->addRow(array('Apache',$apache ,$stat));
if ($mysql !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
$table->addRow(array('Mysql',$mysql ,$stat));
$treap = getVersion('tmpreaper',true);
$php_mysql = getVersion('php-mysql',true);
$php_gmp = getVersion('php-gmp',true);

if ($php_mysql !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
$table->addRow(array('PHP Mysql module',$php_mysql ,$stat));
if ($php_gmp !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
$table->addRow(array('PHP gmp module',$php_gmp ,$stat));
if ($treap !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
$table->addRow(array('Tmpreaper',$treap ,$stat)); */

echo $table->getTable();
print_r($software);
ask_question('press a key',null,null,true);
echo cr;
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
