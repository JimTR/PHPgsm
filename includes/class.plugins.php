<?php
/****************************************\
 * plugin wrapper class
 * functions load the plugin file into the code
 * find the run function called
 * the framework needs to supply the run file
 * the class needs to workout the location the plugin needs to run
 * plugin class version 2
 */
class plugin {
	
	//start load function
	function load($plugin)
	{
		$can_run = false;
		
				if(file_exists(DOC_ROOT."/includes/plugins/".$plugin.".php")) // test if the plugin is there
				{
					$can_run = $this->check_file($plugin);
					
					if ($can_run)
					{
						
						return true;
					}
					else
					{
					return false;
					}
				}
				
	}
	// end load function
	function get_plugins($runfile, $position ="",$function="")
		{
			if ($function ==="") {$function = 'run';}
		global $database;
		
		$runfile = preg_replace('/\.php$/', '', $runfile);
		/* this will load the list of plugins and runs the required plugin function
		 * runfile is be the actual php file running i.e /forums/category.php
		 * this means we get just the plugins for the actual main file.
		 * the function will return an array 
		 * AREA will give the group to which the plugin is running
		 * require_once DOC_ROOT."inc/plugins/".$plugin.".php";
		 * do I need to check the location here or on load !
		*/  
		
		$sql = "select * from plugins where name = '".$runfile."'" ; // this will check all plugins that can run
		$plugins = $database->get_results($sql);
		
		foreach ($plugins as $plugin)
				{
				$can_do = $this->load ($plugin['plugin']); 			
		if ($plugin['enabled']) {			

		$run_func = "{$plugin['plugin']}_".$function."_".$runfile;
		//echo $run_func.'<br>';

				if ($can_do){
						//echo 'running ' .$run_func.'<br>';
						$run_func(); //run the function
				     }
			}
		else 
		{
			
			$run_func = "{$plugin['plugin']}_disabled";
			//echo $run_func;
			if ($can_do){
						//echo '<br>running ' .$run_func.'<br>';
						$run_func(); //run the function
						//echo '<br>ran function';
				     }
			}
	}
}
	function check_file($plugin)
	{
		/*
		 *  this is where we check if the version is ok
		 * return true if ok
		 * return false otherwise
		 */
		 global $settings;
		 require_once DOC_ROOT."/includes/plugins/".$plugin.".php";
		 $info_func = "{$plugin}_info";
		 $b = $info_func();
		 $no_star = strrpos ($b['run_on'] , '*' );
		 if ($no_star)
		 {
			$version = substr($b['run_on'] , 0 , $no_star);
			//echo '<br> no star = '. $no_star. '& version = '.$version. ' running = '.$settings['version'];
		}
		else {echo 'exact version';}
		          
		
		 if (strpos($settings['version'], $version ) === 0 )
		 {//echo 'this is good';
			 return true;
			 } 
		 else {
				//echo 'no exact match';
				return false;
			}
		 
		 
	 }
	 
	 function admin_plugins()
	 {
		 // run into admin
	 } 
}	
?>
