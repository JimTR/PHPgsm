<?php
echo 'checking'.PHP_EOL;
$find = 'appmanifest_';
$files = glob($_GET['p']."/*" . $find . "*");
$acf = file_get_contents($files[0]);
$local=local_build($acf);
echo 'Local Build id '.$local['buildid'].PHP_EOL;
echo 'Last Local Update '.date('l jS F Y \a\t g:ia',$local['update']).PHP_EOL;
$cmd = 'steamcmd  +app_info_update 1 +app_info_print "'.$local['appid'].'"  +quit';
//echo $cmd.PHP_EOL; 
$result = shell_exec($cmd);
//echo $result;
$remote = test_remote($result);
//print_r($test);
echo 'Remote Build id '.$remote['buildid'].PHP_EOL;
echo 'Last Remote Update '. date('l jS F Y \a\t g:ia',$remote['update']).PHP_EOL; 
//$j['public']['timeupdated']);
if ($local['buildid'] < $remote['buildid']) {
	echo 'update required'.PHP_EOL;
}
else {
	echo 'Server Uptodate'.PHP_EOL;
}

function local_build($data) {
$string = trim(preg_replace('/\t/', '', $data));
$string = trim(preg_replace('/""/', ',', $string));
$string = trim(preg_replace('/"/', '', $string));
$string = trim(preg_replace('/{/', '', $string));
$string = trim(preg_replace('/}/', '', $string));
$ta = explode(PHP_EOL,$string);
$ta = array_filter($ta);
$j = refactor_local($ta);
$return['appid'] = $j['AppState']['appid'];
$return['buildid'] = $j['AppState']['buildid'];
$return['update'] = $j['AppState']['LastUpdated'];
return $return;
}

function refactor_local($array) {
	// refactor array with keys
	global $keyset;
	foreach ($array as &$value) {
			//read data
			if(empty($value)) { 
			//echo 'empty'.PHP_EOL;
			}
		else {
			// make array
			//if ($keyset = 1) {echo 'keyset'.PHP_EOL;}
			 $i = strpos($value,",",0);
			 if ($i == 0) {
			 $key1 = trim($value);
			 $nos[$key1] =array();
			 $keyset=1;
			 continue;
		 }
		   else {
			   //echo 'hit else'.PHP_EOL;
			   $i = strpos($value,",",0);
			if ($i > 0 )
			{
            $key = trim(substr($value,0,$i));
            if (isset($key1)) {
		    $nos[$key1][$key] = trim(substr($value,$i+1));
		}
		else {
			$nos[$key] = trim(substr($value,$i+1));
		}
		}
		   }
		}	
			
		
		}
		return $nos;
//print_r($nos);
}
function test_remote($file) {
$string = trim(preg_replace('/\t/', '', $file));
$string = trim(preg_replace('/""/', ',', $string));
$string = trim(preg_replace('/"/', '', $string));
$string = trim(preg_replace('/{/', '', $string));
$string = trim(preg_replace('/}/', '', $string));
$ta = explode(PHP_EOL,$string);
$j = refactor_remote($ta);
$return['buildid'] = $j['public']['buildid'];
$return['update'] = $j['public']['timeupdated'];
return $return;
}
function refactor_remote($array) {
	// refactor array with keys
	foreach ($array as &$value) {
			//read data
			if(empty($value)) { 
			//echo 'empty'.PHP_EOL;
			}
		else {
			// make array
			 $i = strpos($value,",",0);
			 if ($i == 0) {
			 $key1 = trim($value);
			 $nos[$key1] =array();
		 }
		}	
			$i = strpos($value,",",0);
			if ($i > 0 )
			{
            $key = trim(substr($value,0,$i));
            if (isset($key1)) {
		    $nos[$key1][$key] = trim(substr($value,$i+1));
		}
		else {
			$nos[$key] = trim(substr($value,$i+1));
		}
		}
		
		}
		return $nos;
//print_r($nos);
}
?>