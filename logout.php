<?PHP
    require 'includes/master.inc.php';
    $template = new Template;
    $filename = "user".$Auth->id.".txt";
    /*$old = getcwd(); // Save the current directory
    chdir(DOC_ROOT."/forum");
    unlink($filename);
    chdir($old); // Restore the old working directory    
    $Auth->loginUrl = ""; */
    $kill = $Auth->nid;
    distroy_session($kill,$database);
    $Auth->logout();
    //die ("got here ".$site->settings['url'].'/index.php');
   if(!empty($_SERVER['HTTP_REFERER'])){
       redirect( $_SERVER['HTTP_REFERER']);
	}
	redirect('/index.php');
    
?>
