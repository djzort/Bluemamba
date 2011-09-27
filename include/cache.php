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
              
    Document: include/cache.php
              
    Function: Unified interface to read/write cache
              
*********************************************************************/

include("../conf/conf.php");

include_once("../conf/db_conf.php");
include_once("../include/idba.php");

$EXISTING_CACHES = array();

function cache_read($user, $host, $key)
{
	global $DB_CACHE_TABLE;
	global $EXISTING_CACHES;
	global $session_dataID;
	
	$db = new idba_obj;
	if(!$db->connect()) return false;
	
	$data = false;
	$sql = "SELECT * FROM $DB_CACHE_TABLE WHERE owner='$session_dataID' and cache_key='$key'";
	$result = $db->query($sql);
	if(($result) && ($db->num_rows($result) > 0))
	{
		$a = $db->fetch_row($result);
		$data = unserialize($a["cache_data"]);
		$EXISTING_CACHES[$key] = $a["id"];
	}
	else
	{
		$result = false;
		$EXISTING_CACHES[$key] = false;
	}
		
	return $data;
}


function cache_write($user, $host, $key, $data, $volatile=true)
{
	global $DB_CACHE_TABLE;
	global $session_dataID;
		
	$db = new idba_obj;
	if(!$db->connect()) return false;
	
	if(!$EXISTING_CACHES[$key])
	{
		$sql = "SELECT id FROM $DB_CACHE_TABLE WHERE owner='$session_dataID' and cache_key='$key'";
		$result = $db->query($sql);
		if (($result) && ($db->num_rows($result)>0))
		{
			$a = $db->fetch_row($result);
			$EXISTING_CACHES[$key] = $a["id"];
		}
		else
		{
			$EXISTING_CACHES[$key] = false;
		}
	}
	
	$data = serialize($data);
	if($EXISTING_CACHES[$key])
	{
		$id      = $EXISTING_CACHES[$key];
		$ownerID = $session_dataID;
		$sql     = "UPDATE $DB_CACHE_TABLE SET cache_data='$data',volatile='$volatile' WHERE id='$id' AND owner='$session_dataID'";
		$result  = $db->query($sql);	
	}
	else
	{
		$ownerID = $session_dataID;
		$sql     = "INSERT INTO $DB_CACHE_TABLE (owner, cache_key, cache_data, cache_ts, volatile) ";
		$sql    .= "VALUES ('$session_dataID', '$key', '$data', '" . time() . "', '$volatile')";
		$result  = $db->query($sql);	
	}
		
	return $result;
}


function cache_clear($user, $host, $key)
{
	global $DB_CACHE_TABLE;
	global $session_dataID;
		
	$db = new idba_obj;
	if(!$db->connect()) return false;
	
	$sql = "UPDATE $DB_CACHE_TABLE SET cache_data='' WHERE owner='$session_dataID' and cache_key=$key'";
	return  $db->query($sql);		
}


function cache_clear_all($user, $host)
{
	global $session_dataID;
	global $DB_CACHE_TABLE;
	
	$db = new idba_obj;
	if(!$db->connect()) return false;

	$expire = time() - (60 * 60 * 24 * 30);  // Timestamp 30 days ago
	$sql  = "DELETE FROM $DB_CACHE_TABLE ";
	$sql .= " WHERE (owner='$session_dataID' and volatile='1')";
	$sql .= " OR (cache_ts < '$expire' and volatile='1')";

	return $db->query($sql);
}

?>