<?php
include 'data/include.php';
include DOC_ROOT.'/functions.php';
system('clear');
echo 'PHPgsm Installer'.cr; 
echo get_boot_time().cr;
echo getVersion('mysql -V').cr;
$apache =  getVersion('apache2 -v');
$git = getVersion('tmpreaper',true);
$php = getVersion('php-mysql',true);
echo "git = $git".cr;
echo "php mysql = $php".cr;
echo "Apache is $apache".cr;
ask_question('press a key',null,null,true);
if (is_file(DOC_ROOT.'/includes/config.php')) {
	db_config(1);
}
else {
		db_config(0);
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
