<?php
include 'data/include.php';
include DOC_ROOT.'/functions.php';
include DOC_ROOT.'/includes/class.color.php';
include DOC_ROOT.'/includes/class.table.php';
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
$table->addRow(array('Module','Version' ,'Status'));
$software['Mysql'] = getVersion('mysql -V');
$software['Apache'] =  getVersion('apache2 -v');
$software['Git'] = getVersion('git --version');
$software['Tempreaper'] = getVersion('tmpreaper',true);
$software['Steamcmd']  = getVersion('steamcmd',true);
$software['GlibC'] = getVersion('libc-bin',true);
foreach ($software as $k => $v) {
	if ($v !='Not Installed'){ $stat= $tick;} else{$stat = $cross;}
	$table->addRow(array($k,$v ,$stat));
}
unset($software);
$software['php_mysql'] = getVersion('php-mysql',true);
$software['php_gmp'] = getVersion('php-gmp',true);
$software['php_zip'] = getVersion('php-zip',true);
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
print_r($software);
ask_question('press a key',null,null,true);
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
