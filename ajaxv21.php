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
	$build = "46611-3909981191";
	$version = 2.10;
print_r($argv);
	$cmds =startup();
        if($cmds['action'] == 'version'){
           echo 'Ajax v'.$version.' '.$build.' Copyright Noideer Software '.$settings['start_year'].' - '.date('Y').cr;
        }
echo 'running';
	function startup() {
		// get supplied options
                global $argv; 
                $output ='';
		if (is_cli()) {
                        echo 'in cli'.CR;
			$shortopts ="a:s:";
			$longopts[] = "debug::";
			$longopts[] = "action:";
			$longopts[] = "server:";
			$cmds = getopt($shortopts,$longopts);
			// running from the command line
			$cmds['valid'] = 1; // we trust the console
			$method = 'cli';
			define ('cr',PHP_EOL);
                        $cmds = convert_to_argv($argv,"",true);
                         
                        //die('cli');
		}
//echo 'cli done<br>';
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
                        //echo $output;
                }
            return $cmds; 
	}
?>
