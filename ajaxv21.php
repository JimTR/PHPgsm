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
 *  unlike previous versions of ajax v21 will exec modules rather than the code being written into the file
 *  reasons are:-
 *  1. increased securtiy early versions have a security hole !
 *  2. code will be easier to maintain as you can edit the exec and NOT break any other ajax calls
 *  3. will work fully with the command line as well as HTTP requests
 *  4. better logging 
 */
require_once 'includes/master.inc.php';
include 'functions.php';
require DOC_ROOT. '/xpaw/SourceQuery/bootstrap.php'; // load xpaw
	use xPaw\SourceQuery\SourceQuery;
	define( 'SQ_TIMEOUT',     $settings['SQ_TIMEOUT'] );
	define( 'SQ_ENGINE',      SourceQuery::SOURCE );
	define( 'LOG',	'logs/ajaxv21.log');
	define ('CR',PHP_EOL);
	define ('borders',array('horizontal' => '─', 'vertical' => '│', 'intersection' => '┼','left' =>'├','right' => '┤','left_top' => '┌','right_top'=>'┐','left_bottom'=>'└','right_bottom'=>'┘','top_intersection'=>'┬'));
	define ('no_borders',array('horizontal' => '', 'vertical' => '', 'intersection' => '','left' =>'','right' => '','left_top' => '','right_top'=>'','left_bottom'=>'','right_bottom'=>'','top_intersection'=>''));
	define ('IN_PHPGSM','');
$build = "13119-1577542034";
	$version = "2.101";
$time = "1643984003";
	$cmds = startup();
	//print_r($argv);
	//echo 'returned $cmds '.cr,printr($cmds).cr;
	if ($cmds['valid'] == false ) {
		die( 'invalid end point');
	}
	if (!isset($cmds['action']) || empty($cmds['action'])){
		die('I don\'t know what you mean'.cr);
	}
	if (!isset($cmds['output']) || empty($cmds['output'])){
		$cmds['output']='json';
	}
	if (isset($cmds['debug'])){ print_r($cmds);}
	switch ($cmds['action']) {
        case 'version' :
           echo "Ajax v$version-$build Copyright Noideer Software ".date('Y').cr;
            break;
         case 'game_detail':
				include 'modules/game_detail.php';
				$content =game_detail();
				//echo 'doing new function'.cr;
				output($content,$cmds['output'],'<game_detail/>','player');
				break;
        case 'help' :
			if(empty($cmds['topic'])) {$cmds['topic'] = null;} 
          die(help($cmds['topic']));
          break;
          
        case 'scanlog':
             //printr($cmds);
             if(!isset($cmds['server'])) {$cmds['server'] = 'all';}
             $exe = './scanlog.php -s'.$cmds['server'];
             if(isset($cmds['silent'])) {$exe.=' --silent';}
			exec($exe,$content,$ret_val);
			output($content,$cmds['output'],'<scan_log/>','output');
			break; 
			
			case 'console':
				if(empty($cmds['server'])) {goto error_default;}
				if(empty($cmds['rows'])){$cmds['rows']= 0;}
				$sql = 'select * from server1 where host_name ="'.$cmds['server'].'"'; // sql to get details
				$server = $database->get_row($sql); // get server details
				if(empty($server['location']))
				 {
					 $cmds['error_reason'] = 'Invalid server';
					 goto error_default;
				 }
				$file = $server['location'].'/log/console/'.$cmds['server'].'-console.log'; // get the correct file name
				//$cmds = array_merge($cmds,$server); //merge all server info into the cmds array 
				define ('server',$server);
				define ('cmds',$cmds);
				include 'modules/console.php'; //load the module
					
			    $output = readlog($cmds,$file); // run the command
			   output($output,$cmds['output'],'<console/>','output'); //output the result				
			   break;
			   
		   default:
				error_default:
				$cmds['error_msg'] = 'invalid endpoint'; // we can add extra messages to this array
				output($cmds,$cmds['output'],'<error/>','output'); //send back error message
          }

	function startup() {
		// get supplied options
                
                $output ='';
                $cmds = array();
               
		if (is_cli()) {
			global $argv; 
			//$method = $argv";
                        define ('cr',PHP_EOL);
			 //echo 'raw argv '.print_r($argv,true);
                $argv = array_map('strtolower',$argv);
                //echo 'lowered argv '.printr($argv,true);
                
                      //  echo 'in cli'.CR;
			$shortopts ="a:A:s:S:d::D::v::V::h::H::t:T:o:O:R:r:";
			$longopts[] = "debug";
			$longopts[] = "DEBUG::";
			$longopts[] = "action:";
			$longopts[] = "server:";
			$longopts[] = "version";
			$longopts[] = "VERSION";
			$longopts[] = "help::";
			$longopts[] = "HELP::";
			$longopts[] = "topic:";
			$longopts[] = "TOPIC:";
			$longopts[] = "silent::";
			$options = getopt($shortopts,$longopts);
			//echo 'options as is  '.printr($options,true).cr;
			
			$options = array_change_key_case($options,CASE_LOWER);
			$options = array_map('strtolower',$options);
			//echo 'case changed '.printr($options);
			// running from the command line
			//echo 'options '.printr($options,true).CR;
			$method = 'cli';
			if (!isset($cmds['action'])) { 
				$cmds = convert_to_argv($argv,"",true);
			}
			if(isset($options['debug'])) {
				$cmds['debug']= true;
				//define('debug',true); // maybe not define cmds later ?
				//print_r($argv);
			}
            if(isset($options['v']) or isset($options['version'])) {$cmds['action'] ='version';}
            if(isset($options['silent'])) {$cmds['silent'] ='--silent';}
            if(isset($options['o'])) {
				$cmds['output'] = $options['o'];
				} 
				else {
					//$cmds['output'] ='text';
					}
				
            if(isset($options['s'])) {
				$cmds['server'] = $options['s'];
			}
			else {
				//$cmds['server'] = 'all';
			}
            if(isset($options['help'])||isset($options['h'])){
				$cmds['action'] ='help';
			}
				if(!empty($options['topic'])) {
					$cmds['topic'] = $options['topic'];
				}
				elseif (!empty($options['t'])) {
					$cmds['topic'] = $options['t'];
				}
				else {
					//$cmds['helpopt'] = null;
				}
			//}
            if(isset($options['a'])) {$cmds['action'] = $options['a'];}
            if(isset($options['r'])) {$cmds['rows'] = $options['r'];} 
            //switch ($options) {
            $cmds['valid'] = true; // we trust the console
            //return $cmds;
            
		}

		else {
			// run via url
                        //echo 'run by url<br>';
                        define ('cr','<br>');
			if(!empty($_POST)) {
				// this is the norm
                               //echo 'in post<br>';
                               // echo print_r($_POST,true).'<br>';
				$cmds =convert_to_argv($_POST,"",true);
				$method = '$_POST';
				//define ('cr','<br>');
			}
			if(!empty($_GET)) {
				// not the best but added
                                 //echo 'in get<br>';
                                 //echo print_r($_GET,true).'<br>';
				if (isset($cmds)) {
					// we have details from $_POST
                                        //echo 'merge get<br>';
					$cmds = array_merge($cmds,convert_to_argv($_GET,"",true));
					$method .='/$_GET';
				}
				else {
					// no $_POST backwards compat
                    $cmds = convert_to_argv($_GET,"",true);
					$method = '$_GET';
					
				}
			}
			
			 if (isset($_SERVER['HTTP_PHPGSM_AUTH']) and $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
                        $cmds['valid'] = true;
                        //echo 'auth on'.cr;
                        }
                else {
					$cmds['valid'] = false;
				}        
		} 
		
		//$output .= "method = $method".cr;
          //       foreach ($cmds as $k => $v) {
            //         $output .= "[$k]=>$v".cr;
              //   }
                // $output .= "$method finished".cr;
		        //echo printr($cmds,true);
                //if(isset($_SERVER)) {
                 //$output .= printr($_SERVER,true);
                //}
                //echo print_r($_SERVER,true);
                
              if(isset($cmds['debug']) and $cmds['valid'] ==1) {
                  //echo $output;
                 
			}
			 //die('x1');
			 //printr($cmds);
            return $cmds; 
	}
	
	function help($option=null) {
		// display help
		if (is_cli()) {
			//echo "option = $option".cr;
			global $version,$build,$settings;
			$table = new Table(CONSOLE_TABLE_ALIGN_LEFT, borders, 2, null,true,CONSOLE_TABLE_ALIGN_CENTER);
			$year = $settings['start_year'];
			$date = date('Y');
			$cc = new Color();
			$option1 = $cc->convert("%cOption%n");
			$use = $cc->convert("%cUse%n");
			$notes = $cc->convert("%cNotes%n");
			echo $cc->convert("%MAjax v$version $build Copyright Noideer Software $year - $date%n").cr;
			if (is_null($option) ){ 
			//echo cr;
			$table->setHeaders( array ($option1,$use,$notes));
			$table->addRow(array(' --help' ,'get help','display help on a subject e.g \'--help --topic action\''));
			$table->addRow(array('-a, --action' ,'send action','major option must be set'));
		}
		else {
			switch ($option) {
				case 'action':
				case 'a':
				echo "help for '-a' & '--action'".cr;
				$table->setHeaders( array ($option1,$use,$notes));
				$table->addRow(array(' game_detail' ,'returns JSON array of currently running servers','-s'));
				$table->addRow(array(' game_detail#1' ,'returns xml array of currently running servers','loads of options here, where do I begin ?'));
				$table->addRow(array(' scanlog' ,'scans server logs','-x'));
				break;
				default:
				echo "no help for $option".cr;
				exit;
			}
			
		}
		echo $table->getTable().cr;
	}
	else {
		echo 'no help available'.cr;
	}
	}
	
	function arrayToXML($array, SimpleXMLElement $xml, $child_name)
{
    foreach ($array as $k => $v) {
        if(is_array($v)) {
            (is_int($k)) ? arrayToXML($v, $xml->addChild($child_name), $v) : arrayToXML($v, $xml->addChild(strtolower($k)), $child_name);
        } else {
            (is_int($k)) ? $xml->addChild($child_name, $v) : $xml->addChild(strtolower($k), $v);
        }
    }

    return $xml->asXML();
}

function get_pid($task) {
	// return pid
	global $cmds;
	if (isset($cmds['debug']) ){
		echo "task = $task".cr;
	}
	exec ('ss -plt |grep '.$task,$detail,$ret);
	//print_r($detail);
	$a = explode('  ',$detail[0]);
	$b = explode(',',trim(end($a)));
	preg_match('!\d+!', $b[1], $matches);
	if (isset($cmds['debug'] )) {
		//echo print_r($a,true).cr;
		//echo 'used ss -plt |grep '.$task.cr;
		echo $matches[0].cr;
	}
	return $matches[0];
}

function output ($content, $type,$node,$sub_node) {
	global $cmds;
	//print_r($cmds);
	//echo "<pre>node = $node sub_node = $sub_node</pre>".cr;
	//die();
	// do output
	switch ($type) {
		case "json":
		     header('Content-Type: application/json');
			if ($node == '<error/>') {
				$error['error'] = $content;
				$content = $error; 
			}
			//$content = convert_from_latin1_to_utf8_recursively($content);
			echo json_encode($content);
			break;
		case "xml":
			header('Content-Type: text/xml; charset=UTF-8');
			//printr($content);
			//echo '<pre>';
			print  arrayToXML($content, new SimpleXMLElement($node), $sub_node);
			//echo '</pre>';
			break;	
		case "text":
				if ($node == '<error/>') {
				$error['error'] = $content;
				$content = $error; 
				}
				printr($content);
				break;
		default:
				header('Content-Type: application/json');
				echo json_encode($content);	
	}
}

function exe($cmds) {
	// run a command this array needs to be in a settings file
	$sudo = false;
	//$allowed = array('scanlog.php','cron_u.php','cron_r.php','check_ud.php','steamcmd','tmpreaper', 'sudo');
	include 'includes/allowed_cmds.php';
	//we could go php 8 here and use str_starts_with
	if (strpos($cmds['cmd'],'sudo ')) {
		// trying to run sudo
		$cmds['cmd'] = str_replace('sudo ','');
		$sudo = true;
	}
	foreach ($allowed as $find) {
       if (strpos($cmds['cmd'], $find) !== FALSE ) { 
        //echo $cmds['cmd']." Match found".cr;
        $run_cmd =  $find; // store what we are going to run  
        $can_do = true;
		}
	}
	if(empty($can_do)) {
		return 61912;
	}
	
	if($can_do == true) {
	/* 
	 * Exit codes
	 * 0 = ran correctly
	 * 127 = file not found
	 * 139 = segmentation
	 */ 
	if ($sudo) { $cmds['cmd'] = 'sudo '.$cmds['cmd'];} // glue sudo back in and be sure the user can run it
		exec($cmds['cmd'],$output,$retval);
	
			if (isset($cmds['debug'])) {
				//echo ' ready to do command '.$cmds['cmd'].cr;
				//print_r($output);
				foreach ($output as $line) {
					$return .= $line.cr;
				}
				return $return;
				//$return ; //.= $retval.cr; // put the return value in the array
		}
		else {
			// put retval as the first element of the array
			$return = array_unshift($output,$retval);
			return $return;
		}
 
		return false; // just in case anything slips through
	}
}

function convert_from_latin1_to_utf8_recursively($dat)
   {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = convert_from_latin1_to_utf8_recursively($d);

         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = convert_from_latin1_to_utf8_recursively($d);

         return $dat;
      } else {
         return $dat;
      }
   }     
	
?>
