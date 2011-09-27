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
              
    Document: include/session_close.php
              
    Function: Close Session
	
*********************************************************************/

	if($BLUEMAMBA_SESSION)
	{
		$user = $BLUEMAMBA_SESSION;
	}

	// Delete this session and any old unclosed ones
	include_once("../conf/db_conf.php");
	include_once("../conf/conf.php");
	
	// Connect to db
	include_once("../include/idba.php");
	$db = new idba_obj;
	if($db->connect())
	{
			$expTime = time() - $MAX_SESSION_TIME; //close all session that are over 24 hours old
			$sql = "delete from $DB_SESSIONS_TABLE where (sid = '$user') or (inTime < $expTime)";
			if(!$db->query($sql)) echo "DB query failed: $sql <br>\n";
	}
	else
	{
		echo "DB connection failed.<br>\n";
	}
?>