<?php
include 'data/include.php';
include DOC_ROOT.'/functions.php';
include DOC_ROOT.'/includes/class.color.php';
include DOC_ROOT.'/includes/class.table.php';
// 95555
$cc = new Console_Color2();
$tick = $cc->convert("%g✔%n");
$cross = $cc->convert("%r✖%n");
 $table = new Console_Table(
    CONSOLE_TABLE_ALIGN_LEFT,
    array('horizontal' => '', 'vertical' => '', 'intersection' => '')
);
$table->setHeaders(array('Installing PHPgsm',' Stage 1: Dependancy Check'));
system('clear');
echo $cc->convert("%cPHPgsm Installer%n").cr; 
//echo get_boot_time().' '.$tick.cr;
$x32 = trim(shell_exec('dpkg --print-foreign-architectures'));
$table->addRow(array('Module','Version' ,'Status','Usage'));
$software['Mysql']['version'] = getVersion('mysql -V');
$software['Mysql']['use'] = 'only required if the database is local';
$software['Apache']['version'] =  getVersion('apache2 -v');
$software['Apache']['use'] = 'only required if using the web API';
$software['Git']['version'] = getVersion('git --version');
$software['Git']['use'] = 'required to update PHPgsm automatically';
$software['Tempreaper']['version'] = getVersion('tmpreaper',true);
$software['Tempreaper']['use'] = 'used for log pruning';
$software['Steamcmd']['version']  = getVersion('steamcmd',true);
$software['Steamcmd']['use']  = 'required to install & update Steam game servers';
$software['GlibC']['version'] = getVersion('libc-bin',true);
$software['GlibC']['use'] = 'required for steam games';
$software['foreign_architecture']['version'] = $x32;
$software['foreign_architecture']['use'] = 'required by Steamcmd';
$software['webmin']['version'] = getVersion('webmin list-config -c |grep server=M 2>/dev/null');
$software['webmin']['use'] = 'Optional - easy configuration tool for apache, mysql etc';
foreach ($software as $k => $v) {
	if ($v['version'] !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
	$k = str_replace('_',' ',$k);
	$table->addRow(array($k,$v['version'] ,$stat,'',$v['use']));
}
unset($software);
$software['php_mysql'] = getVersion('php-mysql',true);
$software['php_gmp'] = getVersion('php-gmp',true);
$software['php_zip'] = getVersion('php-zip',true);
$software['php_xml'] = getVersion('php-xml',true);
$table->addRow(array($cc->convert("%yPHP Modules%n"),'' ,''));
foreach ($software as $k => $v) {
	if ($v !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
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
