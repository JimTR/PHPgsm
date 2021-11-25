<?php
/*
 * class.template.php
 * 
 * Copyright 2014 Jim <jim@noideersoftware.co.uk>
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
	$build = "5460-1226856513";
class Template {

   public $template;

   function load($filepath, $set = true) {
      /* defaults to adding template start & finish comnents
       * $file_name gets the tempate name for the comment
       */  
      $filecontents = file_get_contents($filepath);
      $file_name = basename($filepath);
      if ($set == 1 ) 
             {$this->template = add_comments ($file_name,$filecontents);}
      else 
          {$this->template = $filecontents;}
      return $this->template; 
   }

   function replace($var, $content) {
		/*  replaces a single content variable
		 *  use replace_vars to add multiple content  
		*/ 
		$this->template = str_replace("<!--#$var#-->", $content, $this->template);
        $this->template = str_replace("#$var#", $content, $this->template);

   }

   function publish($safe = true) {
		/*	eval will allow running arbituary PHP code so we can within sub code so echo may be more usefull
		 *  however the removephp function will remove php from the complete output
		 * this function defaults to safe (echo)  */
		  
		  if (!$safe) {
			  
			eval('?>'.$this->template.'<?'); 
		}
		else {
			
     		echo $this->template;
		}

   }
   
   function removephp() {
	   /* remove php if required
	   *  early disable plugin code needs rework as plugins are now run via their own class
	   *  this function removes all php tags and the code between them, in attempt to stop php code being executed from user supplied content
	   *  if used, this function should be called before the publish function is called
	   */ 
	   do {
		   
	   $start = stripos($this->template, "<?");// tag start
	   if ($start <>0){
	   $end = stripos($this->template, "?>");// tag end
	   $chr = $end-$start;
	   $getrid= substr($this->template, $start, $chr+2);// full string to replace
	   $this->template = str_replace($getrid,"",$this->template);
   }
	   	}
	    while ($start >0);  
	
   }

	function listv ($file) {
		/* return template with the vars in place from a file
		 * use $file as the variable list file in php array format
		 * each var populated or left blank if the var has no value
		 * used for constant text such as language variables
		 */ 
		  include $file;
		 $lang->group = &$l;
		
		 foreach ($lang->group as $k => $v) {
			 $this->template = str_replace("<!--#$k#-->", $v, $this->template);
			 $this->template = str_replace("#$k#", $v, $this->template);
			}
 
	} 
	
		function get_template() {
		/* returns a template, main use is for sub templates
		 * this function must be called AFTER the sub template variables have been replaced.
		 * and before the major template is published or had it variables replaced
		 * it used to add repetitive data (in a sub template) to a major template
		 */ 
		$sub_template = $this->template ;
		return $sub_template;
	}  
	
	function replace_vars($vars) {
		/* replace vars as an array
		 * supply an array
		 * you should have defined the template with the template->load function
		 * simular to listv but uses an array rather than a file
		 */
		 foreach ($vars as $k => $v) {
			 $this->template = str_replace("<!--#$k#-->", $v, $this->template); // adds hidden stuff
			 $this->template = str_replace("#$k#", $v, $this->template);
		 } 
	 }
	 
}
	function code_hook($hook_name) {
		/* run plugin code
		* spool through the plugins looking for the hook 
		* then add the code as an include in html
		* code_hook should be called before remove_php
		 or should this be in a pre parse in another class ?
		 * plugin hook now defined as #plugin_$hook_name#
		 * then run it from function $hook_name_run
		 */   
	}
	
	function add_comments ($template_name,$filecontents) {
		// adds comments to the begining and end of each template
		// this does sort of point at each template should have a unique name 
		// so it can be debuged for html errors, perhaps add the module name so templates can have the same name but appear in a different module ? 
		$filecontents =  "<!-- start ".$template_name." -->".$filecontents ."<!-- end ".$template_name." -->";
		return $filecontents;
	}  
	
	function remove_comments() {
		/*remove all file comments
			* This if called after the add_comments function or replace_var function it will remove them as well 
			* does it need a peram to maintain them ?
			*/	
		do {
			$start = stripos($this->template, "<!--");
			
				 if ($start <> 0) {
					$end = stripos($this->template, "-->");// tag end
					$chr = $end-$start;
					$getrid= substr($this->template, $start, $chr+3);// full string to replace
					$this->template = str_replace($getrid,"",$this->template);
				}
		}
		while ($start > 0);
	}
?>
