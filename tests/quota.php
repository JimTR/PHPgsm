<?php
/*
 * quota.php
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
 define ('cr',PHP_EOL);
$q = shell_exec("quota 2> /dev/null");
//echo $q.cr;
exec("quota 2> /dev/null",$quota,$ret);
echo print_r($quota,true).cr;
if (isset($quota[1])){
$tmp = explode(cr,$q);
$tmp =trim($tmp[2]);
$tmp = explode(' ',$tmp);
foreach ($quota as $k => $v) {

                if (empty(trim($v))) {

                        unset($tmp[$k]);
                }
        }
        $tmp = array_values($tmp);
        // print_r($tmp); // now all renumbered
        $used = dataSize($tmp[1]*1024);
        $total = dataSize($tmp[2]*1024);
        $free = dataSize(($tmp[2]*1024)-($tmp[1]*1024));
        echo 'Used = '.$used.'   Total = '.$total.'  Free =  '.$free.cr;
}
else {
	
echo 'root stuff ! or no quota !'.cr;
exec('df -h /',$df,$ret);
unset ($df[0]);
$df = array_values($df);
exec('df -h |grep sd',$tmps,$ret);
foreach ($tmps as $tmp) {
	$df[]=$tmp;
}

echo print_r($df,true).cr;
 exec('lsblk -l |grep "part /"',$lsblk,$ret);
 echo print_r($lsblk,true).cr;
} 

function dataSize($Bytes)
{
$Type=array("", "K", "M", "G", "T");
$counter=0;
while($Bytes>=1024)
{
$Bytes/=1024;
$counter++;
}
$Bytes= round($Bytes,2);
return("".$Bytes." ".$Type[$counter]."B ");
}
?>

