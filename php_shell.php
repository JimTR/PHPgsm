#!/usr/bin/php -d memory_limit=2048M
<?php
echo shell_exec('whoami');
//print_r($argv);
echo shell_exec('echo $PATH');
//print_r($_SERVER);
//$test = file_get_contents('php_shell.php');
//file_put_contents('phpshell.php',$test);
//echo $_SERVER['SCRIPT_NAME'].PHP_EOL;
//echo $_SERVER['SUDO_COMMAND'].PHP_EOL;
$cmd = 'sudo '.trim(str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SUDO_COMMAND']));
echo $cmd.PHP_EOL;
echo shell_exec($cmd);
