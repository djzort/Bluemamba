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
              
    Document: include/log.php
              
    Function: Logging
              
*********************************************************************/

include_once("../conf/conf.php");

if ($log_active)
{
	
	$log_comment = str_replace("<br>", "", $log_comment);
	$log_comment = str_replace("\n",  "", $log_comment);

	if (!empty($log_action))
	{
		$log_time      = date("M d Y H:i:s", time());
		$log_timestamp = time();
		$log_ip        = $_SERVER["REMOTE_ADDR"];
		$log_account   = $user_name . "@" . $host;
		$log_comment   = $log_comment . $error;

		include_once("../conf/db_conf.php");
		include_once("../include/idba.php");
		include_once("../include/array2sql.php");
		
		$db = new idba_obj;
		if ($db->connect())
		{
			$backend_array = array
			(
				"logTime"      => date("Y-m-d H:i:s", time()),
				"logTimeStamp" => $log_timestamp,
				"userID"       => $dataID,
				"account"      => $log_account,
				"action"       => $log_action,
				"comment"      => $log_comment,
				"ip"           => $log_ip
			);
			$sql = Array2SQL($DB_LOG_TABLE, $backend_array, "INSERT");
			$db->query($sql);
		}

	}
}
?>