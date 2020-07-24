<?php
/*
 * functions_admin.php
 * 
 * Copyright 2014 Jim Richardson <jim@noideersoftware.co.uk>
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
function getDirectorySize($path) 
{ 
  $totalsize = 0; 
  $totalcount = 0; 
  $dircount = 0; 
  if ($handle = opendir ($path)) 
  { 
    while (false !== ($file = readdir($handle))) 
    { 
      $nextpath = $path . '/' . $file; 
      if ($file != '.' && $file != '..' && !is_link ($nextpath)) 
      { 
        if (is_dir ($nextpath)) 
        { 
          $dircount++; 
          $result = getDirectorySize($nextpath); 
          $totalsize += $result['size']; 
          $totalcount += $result['count']; 
          $dircount += $result['dircount']; 
        } 
        elseif (is_file ($nextpath)) 
        { 
          $totalsize += filesize ($nextpath); 
          $totalcount++; 
        } 
      } 
    } 
  } 
  closedir ($handle); 
  $total['size'] = $totalsize; 
  $total['count'] = $totalcount; 
  $total['dircount'] = $dircount; 
  return $total; 
} 

function sizeFormat($size) 
{ 
    if($size<1024) 
    { 
        return $size." bytes"; 
    } 
    else if($size<(1024*1024)) 
    { 
        $size=round($size/1024,1); 
        return $size." KB"; 
    } 
    else if($size<(1024*1024*1024)) 
    { 
        $size=round($size/(1024*1024),1); 
        return $size." MB"; 
    } 
    else 
    { 
        $size=round($size/(1024*1024*1024),1); 
        return $size." GB"; 
    } 

}  
function getSql()
{
	ob_start(); 
phpinfo(INFO_MODULES); 
$info = ob_get_contents(); 
ob_end_clean(); 
$info = stristr($info, 'Client API version'); 
preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match); 
$gd = $match[0]; 
//echo 'MySQL:  '.$gd.' <br />'; 
return $gd ;
}

function getdbsize()
{
	global $database;
	// return db
	$sqldb = 'SELECT table_schema "Data Base Name", SUM( data_length + index_length ) "Data Base Size ", SUM( data_free ) "Free Space",Engine, Create_time
FROM information_schema.TABLES
WHERE table_schema = "'.DB_NAME.'"
 GROUP BY table_schema';
$dbsize = $database->get_row($sqldb);
//$sql = "SELECT table_schema \"Data Base Name\", SUM( data_length + index_length ) \"Data Base Size \", SUM( data_free ) \"Free Space\",Engine\n"
//    . "FROM information_schema.TABLES\n"
//    . "WHERE table_schema = \"midland\"\n"
//    . " GROUP BY table_schema";

 return $dbsize ;
/*$query = "SHOW TABLE STATUS FROM `".DB_NAME."`";
  if ($result = mysql_query($query)) {
    $tables = 0;
    $rows = 0;
    $size = 0;
    while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
      $rows += $row["Rows"];
      $size += $row["Data_length"];
      $size += $row["Index_length"];
      $tables++;
    }
  }

  $data[0] = $size;
  $data[1] = $tables;
  $data[2] = $rows;
  return $data;
}

$result = get_dbsize($db);
$megabytes = $result[0] / 1024 / 1024;
/* http://www.php.net/manual/en/function.number-format.php */
//$megabytes = number_format(round($megabytes, 3), 2, ',', '.');
}

function yesno_box($record,$label = "yes,no")
{
	//$label ='yes,no';
	if ($record['area'] == 'disable_plugin') {$label = "Yes,No";}
	$titles = explode(',',$label);
	/* create a yes no box
	 * $record contains the database record
	 * $label contains the box text eg yes/on on/off
	 * returns a formatted box
	 */
	
	 if ($record['value'] == 1)
	 {
	 $yesno = '<input id="on" type="radio" name="'.$record['area'].'" value="1" checked>
			<label style="color:#000;" for="on">'.$titles[0].'</label>
			<input id="off" type="radio" name="'.$record['area'].'" value="0">
			<label style="color:#000;" for="off">'.$titles[1].'</label>';}
	else {
			$yesno = '<input id="on" type="radio" name="'.$record['area'].'" value="1">
			<label style="color:#000;" for="on">'.$titles[0].'</label>
			<input id="off" type="radio" name="'.$record['area'].'" value="0" checked>
			<label style="color:#000;" for="off">'.$titles[1].'</label>';}	
	 return $yesno;
 }
 
 function text_box ($record)
 {
	 /* do a text box !
	  * this function just returns a html text box at a length a bit longer than the content 
	  */
	   $length = ceil(strlen($record['value']) /100 * 20 + strlen($record['value']));
	   $text = '<input type="text" class="text" size="'.$length.'" name="'.$record['area'].'" value="'.$record['value'].'">';
	  return $text;
  } 
 
 function select_box ($record,$type)
 {
	 /* do a drop down box
	  * $type should give you an idea on the options perhaps
	  */
	  if ($record['area'] == 'theme_path') {  
	  $type = get_themes();
     }
     elseif ($record['area'] == 'server_tz') {
		 $type =  DateTimeZone::listIdentifiers(DateTimeZone::ALL);
	 }
	  $test ='<select name="'.$record['area'].'">';
	  while (list($key, $val) = each($type))
   {
		if ($val == $record['value']){
		$test.='<option value ="'.$val.'" selected>'.ucfirst($val).'</option>';
		}
		else{
				$test.='<option value ="'.$val.'">'.ucfirst($val).'</option>';
			}
	
   }
	  $test.='</select>';
	  return $test;
  }  
  
  function get_themes()
  {
  //global $site->settings;
   $dir = DOC_ROOT.'/themes';
   $curdir = getcwd();
// Open a known directory, and proceed to read its contents
chdir ($dir);
$dirs = array_filter(glob('*'), 'is_dir');
if(($key = array_search('img', $dirs)) !== false) {
    unset($dirs[$key]);
}
//print_r( $dirs);

chdir($curdir);
//$page['theme'] = '<select>';
return $dirs;
}

function tz_list($tz1)
{
	/* function returns an option list containing valid time zones
	 * but only the option section the rest of the form must be made elsewhere
	 * defaults the time zone to utc if no time zone is supplied
	 */ 
	if (is_null($tz1)){$tz1 = 416;} 
	
    $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    
	foreach ($tzlist as $key => $tzitem)
		{
			if ($key == $tz1) {
				$tz .= '<option selected="selected" value="'.$key.'">'.$tzitem.'</option>';
			}
			else{
				$tz .= '<option value="'.$key.'">'.$tzitem.'</option>';
			}
		}
	  
	  return $tz;
  }
?>
