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
              
    Document: include/write_sinc.php
              
    Function: Gets config from ../conf/conf.php

*********************************************************************/

include("../conf/conf.php");

$user_name = strtolower($user_name);
$host      = strtolower($host);


function GetPrefsFolder($user, $host)
{
	global $UESR_DIR;
	
	$result=false;
	$path = $USER_DIR.ereg_replace("[\\/]", "", $user.".".$host);
	if(file_exists(realpath($path)))
	{
		$result = $path;
	}
	else
	{
		if(mkdir($path, 0770)) 
		{
			$result = $path;
		}
	}
	return $result;
}

function GetSettings($result, $file)
{
	$lines = file($file);
	if(is_array($lines))
	{
		while(list($k, $line) = each($lines))
		{
			list($key, $val) = explode(":", $line);
			$result[$key] = base64_decode($val);
		}
	}
	else
	{
		$result = false;
	}
	
	return $result;
}

include_once("../include/array2php.php");
include_once("../include/array2sql.php");
include_once("../conf/db_conf.php");

// Initialize some vars
$prefs_saved  = false;
$colors_saved = false;
$new_user     = false;

// Create session ID
if(!isset($session))
{
	$session = time() . "-" . GenerateRandomString(5, "0123456789");
	$user    = $session;
}

// Generate random session key
$key   = GenerateMessage(strlen($password)+5);
$ipkey = InitSessionEncKey($session);        

// Encrypt login ID, host, and passwords
$encpass = EncryptMessage($ipkey, $password);
$encHost = EncryptMessage($ipkey, $host);
$encUser = EncryptMessage($ipkey, $user_name);
if(!empty($path)) $encPath = EncryptMessage($ipkey, $path);

// Connect to database
include_once("../include/idba.php");
$db = new idba_obj;
if($db->connect())
{
	// Check users table, create entry if necessary
	$sql = "select id, userLevel from $DB_USERS_TABLE where (login='$user_name') and (host='$host')";
	$r   = $db->query($sql);
	if($r)
	{
		if($db->num_rows($r) < 1)
		{
			// If user not in db, insert
			$now  = time();
			$sql  = "insert into $DB_USERS_TABLE (login, host, dateCreated, lastLogin, userLevel) ";
			$sql .= "values ('$user_name', '$host', '$now', '$now', 0)";
			if(!$db->query($sql))
			{
				$error .="DB error: Couldn't add user to users table<br>\n";
				echo "\n<!--\nSQL:$sql\nERROR:".$db->error()."\n//-->\n";
			}
			else
			{
				$dataID    = $db->insert_id();
				$userLevel = 0;
				$new_user  = true;
			}
	
			// Create record in prefs
			if( (empty($error)) && (!empty($DB_PREFS_TABLE)) )
			{
				$my_prefs = $default_prefs;
				$my_prefs["id"] = $dataID;
				$sql = Array2SQL($DB_PREFS_TABLE, $my_prefs, "INSERT");
				if($db->query($sql))
				{
					$prefs_saved = true;
				}
				else
				{
					$error .= "DB error: Couldn't insert into $DB_PREFS_TABLE<br>\n";
					echo "\n<!--\nSQL:$sql\nERROR:".$db->error()."\n//-->\n";
					$db->query("delete from $DB_USERS_TABLE where id='$dataID'");
				}
			}
			
		}
		else
		{
			$dataID       = $session_dataID = $db->result($r, 0, "id");
			$userLevel    = $db->result($r, 0, "userLevel");
		}
		
	}
	else
	{
		$error .= "DB error: Couldn't access users table <br>\n";
	}
	
	// Initialize session
	if(empty($error))
	{
		if(empty($port)) $port = 143;
		$sql  = "insert into $DB_SESSIONS_TABLE (sid, login, password, host, path, dataID, port, userLevel, inTime)";
		$sql .= " values ('$user', '$encUser', '$encpass', '$encHost', '$encPath', '$dataID', '$port', '$userLevel', ".time().")";
		if(!$db->query($sql))
		{
			$error .= "DB Insert failed: ".$db->error()." <br>\n";
		}
	
		$sql = "update $DB_USERS_TABLE set lastLogin='".time()."' where id='$dataID'";
		if(!$db->query($sql))
		{
			$error .= "DB Update failed: ".$db->error()." <br>\n";
		}
	}
}
else
{
	$error .= "DB connection failed. <br>\n";
}


if(!empty($error))
{
	$session = "";
	$user    = $user_name;
}



if(empty($error))
{
	// Prep uploads dir
	$uploadDir = $UPLOAD_DIR;
	if(empty($uploadDir)) $uploadDir = "../uploads/";
	if(!is_dir(realpath($uploadDir)))
	{
		$error .= "Invalid uploads directory<br>\n";
	}
	else
	{
		$uploadDir = $uploadDir . ereg_replace("[\\/]", "", $user_name.".".$host);
		if(!is_dir(realpath($uploadDir))) 
		{
			mkdir($uploadDir, 0770);
		}
	}
	
	// Prep TMDA dir
	if(empty($TMDA_DIR)) $TMDA_DIR = "../tmda/";
	if(is_dir(realpath($TMDA_DIR)))
	{
		$tmdaDir = $TMDA_DIR . ereg_replace("[\\/]", "", $user_name.".".$host);
		if(!is_dir(realpath($tmdaDir)))
		{
			mkdir($tmdaDir, 0770);
		}
	}
	
	if( !file_exists( realpath($uploadDir) ) ) 
	{
		$error .= "Invalid uploads directory<br>\n";
	}
	
	include_once("../include/cache.php");
	cache_clear_all($loginID, $host);
}

?>