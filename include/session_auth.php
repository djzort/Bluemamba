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
              
    Document: include/session_auth.php
              
    Function: Session Authentication
              
******************************************************************/

include_once("../conf/conf.php");

// Sanitize session ID
$user = eregi_replace("[^0-9-]", "", $user);

// Time out
if (!$STAY_LOGGED_IN)
{
	$session_parts = explode("-", $user);
	$in_time = $session_parts[0];
	$valid_time = time() - $MAX_SESSION_TIME;
	if ($in_time < $valid_time)
	{
		echo "Session timeout.  Please log out.";
		if (!$do_not_die) exit;
	}
}


// Get session ID
$session_cookie = false;
if (!empty($BLUEMAMBA_SESSION))
{
	$user = $BLUEMAMBA_SESSION;
	$session_cookie = true;
}


include_once("../include/encryption.php");
 

$my_prefs = false;
$my_colors = false;

$dataID = 0;

// Connect to database
include_once("../conf/db_conf.php");
include_once("../include/idba.php");

$db = new idba_obj;
if ($db->connect())
{
		//get session info
		$result = $db->query("select * from $DB_SESSIONS_TABLE where sid = '$user'");
		if (($result) && ($db->num_rows($result)==1))
		{
			$a 				= $db->fetch_row($result);
			$encLogin 		= $a["login"];
			$encPass 		= $a["password"];
			$encHost 		= $a["host"];
			$userPath 		= $a["path"];
			$dataID 		= $a["dataID"];
			$port 			= $a["port"];
			$lastSend 		= $a["lastSend"];
			$numSent 		= $a["numSent"];
			$userLevel 		= $a["userLevel"];
			$inTime 		= $a["inTime"];
			$session_dataID = $dataID;
			
			$ttl = time() - $inTime;
			if ($STAY_LOGGED_IN && ($MAX_SESSION_TIME/10)<$ttl)
			{
				// if session time remaining is 10% of max session lifespan, update so we stay logged in
				$db->query("UPDATE $DB_SESSIONS_TABLE SET inTime=".time()." WHERE sid='$user'");
			}
		}
		else
		{
			echo "<html>";
			echo "Invalid session ID: $user<br>\n";
			echo "Please <a href=\"index.php\" ".(ereg("index.php",$_SERVER['PHP_SELF'])?"":"target=\"_parent\"").">log back in</a>.";
			echo "</html>";
		}
		
		// Get prefs
		if ((!empty($DB_PREFS_TABLE)) && ($dataID > 0))
		{
			$r = $db->query("select * from $DB_PREFS_TABLE where id='$dataID'");
			if (($r) && ($db->num_rows($r)==1)) $my_prefs = $db->fetch_row($r);
			if ($port==110) $my_prefs["list_folders"] = 0;
		}

		// Get colors
		if ((!empty($DB_COLORS_TABLE)) && ($dataID > 0))
		{
			$r = $db->query("select * from $DB_COLORS_TABLE where id='$dataID'");
			if (($r) && ($db->num_rows($r)==1)) $my_colors = $db->fetch_row($r);
		}
}
else
{
	echo "DB connection failed<br>\n";
}

//--------- END DB Specific stuff -----------

$ipkey 		= GetSessionEncKey($user);
$loginID 	= DecodeMessage($ipkey, $encLogin);
$password 	= DecodeMessage($ipkey, $encPass);
$host 		= DecodeMessage($ipkey, $encHost);

if ( ($dataID==0) && (!$do_not_die) )
{
	exit;
}


// Load Theme Configs
if(empty($my_prefs["theme"])) $my_prefs["theme"] = 'default';

$THEME = "themes/" . $DOMAIN_THEME . "/colors/" . $my_prefs["theme"] . "/";

if ( is_file($THEME . "theme.php") == false )			// Does theme exist?
{	
	$THEME = "themes/" . $DOMAIN_THEME . "/colors/default/";
}

include($THEME . "theme.php");


// Remove session ID if cookies are used
if ($session_cookie) $user = "";

?>