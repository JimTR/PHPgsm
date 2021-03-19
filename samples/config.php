<?php
/**
 * Database configuration
 *
 */
$config['database']['type'] = 'mysqli';
$config['database']['database'] = 'gsm';
$config['database']['default_table_prefix'] = 'gsm_'; // for shared database
$config['database']['hostname'] = 'localhost';
$config['database']['username'] = 'user';
$config['database']['password'] = 'password';
$config['database']['encoding'] = 'utf8';
$config['database']['errors'] = "email@address.com";
$config['database']['useDBSessions'] =  "0"; // not used yet
$config['database']['display_error'] = "1"; // will email mysql errors to the above address
?>
