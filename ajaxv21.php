<?php
/*
 * ajaxv21.php
 * 
 * Copyright 2021 Jim Richardson <jim@noideersoftware.co.uk>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
require_once 'includes/master.inc.php';
include 'functions.php';
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajax.log');
	define ('CR',PHP_EOL);
	$build = "4657-1166691063";
	$version = 2.101;
	$cmds =startup();
	//echo 'returned $cmds ',print_r($cmds,true).cr;
        if($cmds['action'] == 'version'){
           echo 'Ajax v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
        }
        if($cmds['action'] =='help') {die(help());}
echo 'running'.cr;
	function startup() {
		// get supplied options
                
                $output ='';
               
		if (is_cli()) {
			global $argv; 
			 //echo 'raw argv '.print_r($argv,true);
                $argv = array_map('strtolower',$argv);
               // echo 'lowered argv '.print_r($argv,true);
            //echo 'in cli'.CR;
			$shortopts ="a:A:s:S:d::D::v::V::h::H::";
			$longopts[] = "debug::";
			$longopts[] = "DEBUG::";
			$longopts[] = "action:";
			$longopts[] = "server:";
			$longopts[] = "version";
			$longopts[] = "VERSION";
			$longopts[] = "help::";
			$longopts[] = "HELP::";
			//$result = array_map('strtolower',$myArray);
			$options = getopt($shortopts,$longopts);
			//echo 'options as is  '.print_r($options,true);
			$options = array_change_key_case($options,CASE_LOWER);
			$options = array_map('strtolower',$options);
			//echo 'case changed '.print_r($options,true);
			// running from the command line
			//echo 'options '.print_r($options,true).CR;
			$cmds['valid'] = 1; // we trust the console
			$method = 'cli';
			define ('cr',PHP_EOL);
			if (!isset($argv['action'])) { 
				$cmds = convert_to_argv($argv,"",true);
			}
			if(isset($options['debug'])) {$cmds['debug']= true;}
            if(isset($options['v']) or isset($options['version'])) {$cmds['action'] ='version';}
            if(isset($options['h']) or isset($options['help'])) {$cmds['action'] ='help';}
            if(isset($options['a'])) {$cmds['action'] = $options['a'];} 
            //switch ($options) {
				
           
                        //die('cli');
		}

		//else {
			// run via url
                        //echo 'run by url<br>';
			if(!empty($_POST)) {
				// this is the norm
                               //echo 'in post<br>';
                               // echo print_r($_POST,true).'<br>';
				$cmds =convert_to_argv($_POST,"",true);
				$method = '$_POST';
				define ('cr','<br>');
			}
			if(!empty($_GET)) {
				// not the best but added
                                 echo 'in get<br>';
                                 echo print_r($_GET,true).'<br>';
				if (isset($cmds)) {
					// we have details from $_POST
                                        echo 'merge get<br>';
					$cmds = array_merge($cmds,convert_to_argv($_GET,"",true));
					$method .='/$_GET';
				}
				else {
					// no $_POST backwards compat
                                        echo 'just get<br>'; 
					$cmds = convert_to_argv($_GET,"",true);
					$method = '$_GET';
					define ('cr','<br>');
				}
			}
		//} 
		$output .= "method = $method".cr;
                 foreach ($cmds as $k => $v) {
                     $output .= "[$k]=>$v".cr;
                 }
                 $output .= "$method finished".cr;
		//echo print_r($cmds,true);
                if(isset($_SERVER)) {
                foreach ($_SERVER as $k => $v) {
                     if(!is_array($v)) { 
                     $output .=  "[$k]=>$v".cr;
                      }
                } 
                }
                //echo print_r($_SERVER,true);
               if (isset($_SERVER['HTTP_PHPGSM_AUTH'])) {
                        $cmds['valid'] = 1;
                        //echo 'auth on'.cr;
                        
                }
                //echo $output;
            return $cmds; 
	}
	function help($option=null) {
		// display help
		global $version,$build,$settings;
		$year = $settings['start_year'];
		$date = date('Y');
		$cc = new Color();
		echo $cc->convert("%GAjax v$version $build Copyright Noideer Software $year - $date%n").cr;
		$table = new Table(CONSOLE_TABLE_ALIGN_LEFT, array('horizontal' => '', 'vertical' => '', 'intersection' => ''));
		$option = $cc->convert("%cOption\t\t\t%n");
	    $use = $cc->convert("%cUse\t\t\t%n");
	    $notes = $cc->convert("%c\tNotes%n");
	    echo cr;
	    $table->setHeaders( array ($option,$use,$notes));
		//$table->addRow(array('','',''));
		//$table->addRow(array($option,$use,$notes));
		$table->addRow(array('-h, --help' ,'get help','display help on a subject e.g \'--help action\''));
		$table->addRow(array('-a, --action' ,'send action','major option must be set'));
		$table->setHeaders( array ($option,$use,$notes));
		echo $table->getTable().cr;
	}
?>
