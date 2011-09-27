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
              
    Document: conf/new_user.php
              
    Function: Configures webmail for new users
              
*********************************************************************/

include_once("../include/icl.php");
include_once("../include/cache.php");
include_once("../include/data_manager.php"); 


// Perform New User Setup
if(!strstr($user, "@"))
{
	$user = $user ."@". $DOMAIN_THEME;
	$user_name = $user;
}



// Initialize bookmarks 
$dm = new DataManager_obj;
if($dm->initialize($loginID, $host, $DB_BOOKMARKS_TABLE, $DB_TYPE))
{
	$new_entry = array();

	$new_entry["name"] = "Gazood";
	$new_entry["url"] = "http://www.gazood.com";
	$new_entry["grp"] = "Gazood";
	$dm->insert($new_entry);

	$new_entry["name"] = "Webmail";
	$new_entry["url"] = "http://www.gazood.com/webmail";
	$new_entry["grp"] = "Gazood";
	$dm->insert($new_entry);
} 
unset($dm);



// Initialize Contacts 
$dm = new DataManager_obj;
if($dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $DB_TYPE))
{
	$new_contact_array = array
	(
		"owner"    => $session_dataID,
		"name"     => "Gazood Support",
		"email"    => "support@gazood.com",
		"phone"    => "",
		"work"     => "",
		"url"      => "http://www.gazood.com"
	);
	
	!$dm->insert($new_contact_array);
}
unset($dm);
	

?>