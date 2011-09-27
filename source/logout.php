<?
/*********************************************************************

	BlueMamba is a software package created by X6 Industries, Inc.
	Copyright © 2006-2008 X6 Industries, Inc., All Rights Reserved

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    Author:   Travis Schanafelt  >>  travis@bluemamba.org

    Modified: 09/18/2008
              
    Document: source/logout.php
              
    Function: Log user out, Perform session clean up

*********************************************************************/

include_once("../include/super2global.php");
include_once("../conf/conf.php");

// Clear cookie
if($_COOKIE["BLUEMAMBA_SESSION"])
{
	setcookie("BLUEMAMBA_SESSION", "");
}
if($_COOKIE["BLUEMAMBA_SESS_KEY_".$user])
{
	setcookie ("BLUEMAMBA_SESS_KEY_".$user, "", time()-3600, "/", $_SERVER[SERVER_NAME]);
}

if($logout == 1)
{
		$do_not_die = true;
		include("../include/session_auth.php");
		include("../include/icl.php");
?>
<HTML>
<BODY>
<center><br><br><font size="+1"><b>Log Out...</b></font></center>
<?
		// Clean up cache
		iil_ClearCache($loginID, $host);
		
		// Delete any undeleted attachments
		$uploadDir = $UPLOAD_DIR . ereg_replace("[\\/]", "", $loginID.".".$host);
		if(is_dir(realpath($uploadDir)))
		{
			if($handle = opendir($uploadDir))
			{
				while(false !== ($file = readdir($handle)))
				{
					if($file != "." && $file != "..")
					{ 
						$file_path = $uploadDir."/".$file;
						//echo $file_path."<br>\n";
						unlink($file_path);
					} 
				}
				closedir($handle); 
			}
		}	
		
		// Delete cache files
		include_once("../include/cache.php");
		cache_clear_all($loginID, $host);		
		
		// Delete FS session files
		if(is_dir(realpath($SESSION_DIR)))
		{
			if($handle = opendir($SESSION_DIR))
			{
				while(false !== ($file = readdir($handle)))
				{
					$timestamp = time();
					$dash_pos = strpos($file, "-");
					if($dash_pos !== false)
					{
						$timestamp = substr($file, 0, $dash_pos);
					}
					if((is_numeric($timestamp)) && ((time()-$timestamp) > $MAX_SESSION_TIME))
					{
						$file_path = $SESSION_DIR."/".$file;
						unlink($file_path);
					}
				}
				closedir($handle); 
			}
		}
		
		// Log entry
		$log_action = "log out";
		$user_name  = $loginID;
		include("../include/log.php");
		
		// Close session
		include("../include/session_close.php");
        
        if(empty($logout_url)) $logout_url = "index.php";
		?>
		<script>
			parent.location="<? echo $logout_url; ?>";
		</script>
		<?

}

?>
</body>
</html>