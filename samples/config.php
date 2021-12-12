<?php
/**
 * Database configuration
 *
 */
$version = "3.00";
$config['database']['type'] = 'mysqli';
$config['database']['database'] = 'database name';
$config['database']['default_table_prefix'] = 'gsm_'; // for shared database
$config['database']['hostname'] = 'hostname'; // this can be any host or localhost
$config['database']['username'] = 'user'; //database user name
$config['database']['password'] = 'pass'; // database user password
$config['database']['encoding'] = 'utf8mb'; // database encoding //  I would leave this as is
$config['database']['errors'] = "email_address"; // send emails to this address if mysql fails or errors
$config['database']['useDBSessions'] =  "0"; // not used
$config['database']['display_error'] = "1"; // will display mysql errors to the screen if set to 1 else silent
?>
