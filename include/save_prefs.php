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
              
    Document: include/save_prefs.php
              
    Function: Save User Prefs

*********************************************************************/

include_once("../conf/conf.php");

include_once("../include/array2sql.php");
include_once("../conf/db_conf.php");
include_once("../include/idba.php");

$iOpened = false;
	
$prefs_summary = trim(chop(implode("", $my_prefs)));

if(($session_dataID > 0) && (strlen($prefs_summary) > 10))
{
	// Connect to db
	$db = new idba_obj;
	if($db->connect())
	{
		// Update
		$sql  = Array2SQL($DB_PREFS_TABLE, $my_prefs, "UPDATE");
		$sql .= " WHERE id=$session_dataID";
		if(!$db->query($sql))
		{
			echo "DB query failed: $sql <br>\n";
		}
	}
	else
	{
		echo "DB connection failed.<br>\n";
	}
}

?>
