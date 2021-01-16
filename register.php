<?php
/*
 * Register script
 * reworked 28-7-15
 *  
 */
 define('DOC_ROOT', realpath(dirname(__FILE__)));
 require DOC_ROOT.'/includes/master.inc.php'; // do login or not
  if ($settings['register'] == 0 ) {
	 //switched off
	 redirect("/");
 }
 $template = new Template;	
 if($Auth->loggedIn()) 
           {
			   // logged in already go back from whence you came
			   echo 'looks like we are still logged in';
			   redirect("/");
			    }
//if ($_POST['r']) { redirect("index.php");} //indicates an automated login			   
	 if(!empty($_POST['username']))
	{
		
		$ip = getip();
		$_POST['ip'] = $ip;
		//print_r($_POST);
		//$ips = $database->num_rows("select * from users where ip = '".$ip."'");
		
		
		$invalidwords = array ("select","delete","insert","update","'");
		$word = substr_count_array( $_POST['username'], $invalidwords);
		$word = $word + substr_count_array( $_POST['password'], $invalidwords);
		//die ("Word = ".$word);
		if ($word >0) {
					$Error ="Invalid Username or Password";
					goto render;
				}
		$checku['username'] = $_POST['username'];
		if (strlen ($_POST['password']) <= $site->settings['pwdlen']) {
			$Error = "Invalid Password, passwords must be longer than ".$site->settings['pwdlen']." chrs and not contain your username";
			goto render;
		}
		$pwdcheck = stripos ($_POST['password'] , $_POST['username']);
		if ($pwdcheck !== false)
		{
			$Error .= "Invalid Password, passwords must be longer than ".$site->settings['pwdlen']." chrs and not contain your username";
			goto render;
		}
		$checke['email'] = $_POST['email'];
		//else {$Error = "test this out"; goto render;} 
		
				
		if ($database->exists("users","username",$checku))
		{
			$Error .="We're sorry, you have entered a username that is in use.";
			//die ($Error);
			goto render;
			}
			
	
		
		if (rpHash($_POST['defaultReal']) <> $_POST['defaultRealHash']) {	
			
			$Error .= " You Supplied an incorrect captcha";
			
		
			goto render; 
			
		}
			
			//if ($Error){goto render;}		
		//$newuser = array();
		
		$file='log.txt';
			   if(! empty($_SERVER['REMOTE_ADDR']) ){
		
		$newuser['nid'] = getnid();
        //$password = md5($_POST['password'].SALT);
		$newuser['username']= $_POST['username'];
		$newuser['password'] = md5($_POST['password'].SALT);
		$newuser['ip'] = $ip;
		$database->insert ("users",$newuser);
			//echo 'error set <br>';
			//print_r($newuser);
		//die();
		}
			 if($Auth->login($_POST['username'], $_POST['password']))
        {
			
            redirect("index.php");
        }
        else
        {
			
            $Error = "We're sorry, you have entered an incorrect username or password. Please try again.";
           
	   }
			die();
		}  
   elseif(!empty($_POST)) {$Error = "No Username supplied";} 
render:	
if ($Error <> "")
	{
		$Error = '<div style="width:98%;margin:1%;color:yellow;font-weight:bold;text-align:center;">'.$Error.'</div>';
		//echo $Error;
		
	}
$template->load('html/register.html');
$template->replace('servername', $_SERVER['SERVER_NAME']);
$template->replace_vars($page);
$template->replace("password",$_POST['password']);
$template->replace("result"," register");
$template->replace("title", "Register");
$template->replace ("path", $site->settings['url']);
$template->replace("error",$Error );
$template->replace("vari",DOC_ROOT);
$template->publish();
 
 function substr_count_array( $haystack, $needle ) {
     $count = 0;
      $haystack = strtolower($haystack);
     foreach ($needle as $substring) {
		  $substring = strtolower($substring);
          $count += substr_count( $haystack, $substring);
     }
     return $count;
} 
function rpHash($value) { 
    $hash = 5381; 
    $value = strtoupper($value); 
    for($i = 0; $i < strlen($value); $i++) { 
        $hash = (leftShift32($hash, 5) + $hash) + ord(substr($value, $i)); 
    } 
    return $hash; 
} 
 
// Perform a 32bit left shift 
function leftShift32($number, $steps) { 
    // convert to binary (string) 
    $binary = decbin($number); 
    // left-pad with 0's if necessary 
    $binary = str_pad($binary, 32, "0", STR_PAD_LEFT); 
    // left shift manually 
    $binary = $binary.str_repeat("0", $steps); 
    // get the last 32 bits 
    $binary = substr($binary, strlen($binary) - 32); 
    // if it's a positive number return it 
    // otherwise return the 2's complement 
    return ($binary{0} == "0" ? bindec($binary) : 
        -(pow(2, 31) - bindec(substr($binary, 1)))); 
} 
?>
