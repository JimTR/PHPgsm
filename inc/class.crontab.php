<?php
/*
 * class.crontab.php
 * 
 * Copyright 2020 Jim Richardson <jim@noideersoftware.co.uk>
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
$version = "2.00";

$build = "3162-3439087272";

 class Crontab {
    
    // In this class, array instead of string would be the standard input / output format.
    
    // Legacy way to add a job:
    // $output = shell_exec('(crontab -l; echo "'.$job.'") | crontab -');
    
    static private function stringToArray($jobs = '') {
        $array = explode("\n", trim($jobs)); // trim() gets rid of the last \r\n
          foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    static private function arrayToString($jobs = array()) {
        $string = implode(PHP_EOL, $jobs);
        return $string;
    }
    
    static public function getJobs() {
        $output = shell_exec('crontab -l');
        return self::stringToArray($output);
    }
    
    static public function saveJobs($jobs = array()) {
        $output = shell_exec('echo "'.self::arrayToString($jobs).'" | crontab -');
        
        
        return $output;	
    }
    
    static public function doesJobExist($job = '') {
        $jobs = self::getJobs();
        if (in_array($job, $jobs)) {
			return true;
        } else {
            return false;
        }
    }
    
    static public function addJob($job = '') {
        if (self::doesJobExist($job)) {
            return false;
        } else {
			//echo 'adding job '.$job.'<br>';
            $jobs = self::getJobs();
            $jobs[] = $job;
            self::saveJobs($jobs);
            return self::getJobs();
        }
    }
    
    static public function removeJob($job = '') {
		
       if (self::doesJobExist($job)) {
		    echo 'found job<br>';
            $jobs = self::getJobs();
            unset($jobs[array_search($job, $jobs)]);
            self::saveJobs($jobs);
            return self::getJobs();
        } else {
			
            return false;
        }
    }
    
    static public function updateJob($job = '') {
			if (self::doesJobExist($job)) {
		    //echo 'update found job<br>';
		    $jobs = self::getJobs();
		    //$update = array_map($job, $jobs);
		    //$update = preg_grep('/^'.$job.'\s.*/', $jobs);
		    $update = array_search($job,$jobs,true); // get
		    //echo 'should be at '.$update.'<br>'; 
		} else {
			//echo 'no good<br>';
			return false;
		}
		
    }
}
?>
