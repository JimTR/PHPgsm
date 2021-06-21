<?PHP
	define ('BUILD',"4395-3939278000");
    class DBSession
    {
        public static function register()
        {
            //ini_set('session.save_handler', 'user');
            //session_set_save_handler(array('DBSession', 'open'), array('DBSession', 'close'), array('DBSession', 'read'), array('DBSession', 'write'), array('DBSession', 'destroy'), array('DBSession', 'gc'));
//echo "register end<br>";
        }

        public static function open()
        {
            $db = new db;
           
            if ($db->link) {return true;} 
            return false ;
        }

        public static function close()
        {
            return true;
        }

        public static function read($id)
        {
            $db = new db;
            $db->query("SELECT * FROM `sessions` WHERE `id` = '".$id."'");
            return $db->affected(); //? $db->getValue();
        }

        public static function write($id, $data)
        {
            $Auth = Auth ::getAuth();
            $db = new db;
            $ip = getip();
            $nid = $_SESSION['nid'];
            $ua = $db->escape($_SERVER['HTTP_USER_AGENT']);
            $location = $db->escape($_SERVER['REQUEST_URI']);
            if (!empty($nid)){ 
				if ($Auth->level === "admin")
				{ $usertype = 104;}
				else{
				$usertype = 1;
			}
			    //echo "we have a written !";
				}
            elseif (strpos(strtolower($ua),'bot',0) >0 or strpos(strtolower($ua),'spider',0) >0 or strpos(strtolower($ua),' slurp',0) >0)
            {
				$usertype = 2;
				$last_visit = time();
				$botnamestart = strpos($ua,'compatible;',0) +1;
			    $botnamefinish = strpos($ua,';',$botnamestart);
			    $botname = $botnamefinish - $botnamestart;
			 if ($botname >0)
			{
				//todo pretty the return string
				$botname = substr($ua,$botnamestart,$botname);
								
			}
			else {$botname ="unknown";}
				
				 $sql = "INSERT INTO `bots` (`ip`, `last_visit`, `user_agent`,`bot_name`,`visits` ) VALUES ('".$ip."','".$last_visit."','".$ua."','".$botname."' , '1' ) 
				 ON DUPLICATE KEY UPDATE `last_visit` = '".$last_visit ."' ,  `user_agent` = '".$ua."' , `bot_name` = '".$botname."' , visits= visits +1";
                 $db->query($sql);
                 
				}
            else {
				// add users to update rather than add here like monitors and bots ?
				$usertype = 0;
				}
            
            $data = $db->escape($data);
            
            $updated_on = time();
            if ($ua){
            $db->query("INSERT INTO `sessions` (`id`, `updated_on`, `ip`, `location`, `useragent` , `nid` , `usertype` ) VALUES ('".$id."','".$updated_on."','".$ip."','".$location."','".$ua."', '".$nid."', '".$usertype."') 
            ON DUPLICATE KEY UPDATE `updated_on` = ".$updated_on ." , `ip` = '".$ip."' , location = '".$location."',  useragent = '".$ua."', nid = '".$nid."', usertype = '".$usertype."'" );
		}
            return ($db->affected() == 1);
        }

        public static function destroy($id)
        {
            $db = new db;
            $db->query("DELETE FROM `sessions` WHERE `id` = '". $id."'");
            return ($db->affected() == 1);
        }

        public static function gc($maxi)
        {
            $db = new db;
            if (!$maxi) { $maxi = 15*60;} 
            /* use a default if not defined
             * and mysql events are switched off
             * test for mysql events ...
             * if you can not switch on do it or else exit ! 
             */ 
            $time = time() - $maxi;
           // echo " time = ".$maxi.'  current = '.time().'<br>' ;
            $sql = "DELETE FROM `sessions` WHERE `updated_on` < ".$time;
           // echo $sql;
            $db->query($sql);
           // add clean up code for attachments 
            return true;
        }
        public function get_bot($ua)
        { 
			/* tries its best to return the bot name from the UA !
			 * added 01-02-2015
			 */ 
			 //$botnamestart = strpos($ua,';',0) +1;
			 //$botnamefinish = strpos($ua,';',$botnamestart) -1;
			 //$botname = $botnamefinish - $botnamestart;
			 //if ($botname >0)
			//{
				//$botstring = substr($ua,$botnamestart,$botname);
				// now process the bits in the string
				//return $botstring;
				
			//}
			//else {return "Unknown";}
			return "function hit !";
			 // gives the sub string that is perhaps the name !
			  
    }
}
