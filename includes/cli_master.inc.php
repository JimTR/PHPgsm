<?PHP
    error_reporting(E_ERROR | E_PARSE);
      // Determine our absolute document root
    if (!defined('DOC_ROOT')) {
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));
}
    
    require DOC_ROOT . '/includes/functions.inc.php';  // spl_autoload_register() is contained in this file
    require DOC_ROOT . '/includes/class.dbquick.php'; // DB quick class may replace dbobject.php... and has done 
    require DOC_ROOT. '/includes/config.php'; // get config
	require DOC_ROOT. '/includes/settings.php';// get settings 
		$site->config = &$config; // load the config
	$site->settings = &$settings; // load settings
	$time_format = "h:i:s A"; // default time settings should get from Auth
	$tz = $site->settings['server_tz'];
    date_default_timezone_set($tz); //need to pull this from config    
    define( 'DB_HOST', $site->config['database']['hostname'] ); // set database host
	define( 'DB_USER', $site->config['database']['username'] ); // set database user
	define( 'DB_PASS', $site->config['database']['password'] ); // set database password
	define( 'DB_NAME', $site->config['database']['database'] ); // set database name
	//define( 'SEND_ERRORS_TO', $site->config['database']['errors'] ); //set email notification email address do we need this with cli
	define( 'DISPLAY_DEBUG', $site->config['database']['display_error'] ); //display db errors?
	define( 'DB_COMMA',  '`'); // sql comma thingy 
	define('TIME_NOW', time()); //time stamp
    define('FORMAT_TIME',  date($time_format)); // this should be the user time format
    define('GIG',1073741824);
    const SALT = 'insert some random text here';
    $database =  new db(); // connect to database
    if ($database->link->connect_errno >0 ) {
    // if database connection fails terminate with error message
    die(FORMAT_TIME.' - Failed To Connect To Database '.DB_NAME.CR.'check settings in config.php'.CR);
}
    
      
    
   


    
