<?php
   
    if (!defined('DOC_ROOT')) {
    	define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
    }
    
    require DOC_ROOT . '/includes/functions.inc.php';  // spl_autoload_register() is contained in this file
    require DOC_ROOT . '/includes/class.dbquick.php'; // DB quick class may replace dbobject.php... and has done 
    require DOC_ROOT . '/includes/class.mobile_detect.php'; // device type class
    require DOC_ROOT. '/includes/config.php'; // get config
    include DOC_ROOT. '/includes/settings.php'; // get settings 
$build = "2187-1168086988";
$version = "2.00";
	$time_format = "h:i:s A";  // force time display
	$tz = $settings['server_tz']; // set a default time zone
   	date_default_timezone_set($tz); // and set it 
    define( 'DB_HOST', $config['database']['hostname'] ); // set database host
	define( 'DB_USER', $config['database']['username'] ); // set database user
	define( 'DB_PASS', $config['database']['password'] ); // set database password
	define( 'DB_NAME', $config['database']['database'] ); // set database name
	define( 'SEND_ERRORS_TO', $config['database']['errors'] ); //set email notification email address
	define( 'DISPLAY_DEBUG', $config['database']['display_error'] ); //display db errors?
	define( 'DB_COMMA',  '`'); // back tick 
	define( 'TIME_NOW', time()); //time stamp
    define( 'FORMAT_TIME',  date($time_format)); // format the time
    define( 'GIG',1073741824);
     if ($settings['send_cors'] ==1) {
		 header("Access-Control-Allow-Origin: *");
	}
    if ($settings['year'] === "1")
   {
	// set data to roman numerals
       define ("COPY_YEAR", romanNumerals(date("Y"))); 
       define ("START_YEAR",romanNumerals($settings['start_year']));
   }
    else {
	 define ("COPY_YEAR", date("Y")); 
         define ("START_YEAR",$settings['start_year']);
      }

    const SALT = 'insert some random text here';
    $database = new db();
    $detect = new Mobile_Detect;
    $isMobile = $detect->isMobile();
    $isTablet = $detect->isTablet();
    
    // Fix magic quotes
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
?>
