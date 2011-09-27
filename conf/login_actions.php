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
              
    Document: conf/login_actions.php
              
    Function: Reconfigures webmail at each login
              Used for rebuilding vital settings if lost
              
*********************************************************************/

include_once("../include/icl.php");
include_once("../include/cache.php");

$conn = iil_Connect($host, $user_name, $password, $AUTH_MODE);
if($conn)
{
	// Prepends newfolder path with rootdir as necessary
	$newfolders = array
	(
		'0' => "INBOX.Sent",
		'1' => "INBOX.Unsent",
		'2' => "INBOX.Drafts",
		'3' => "INBOX.Quarantine",
		'4' => "INBOX.Trash"
	);

	// Create Default Folders
	while(list($i, $newfolder) = each($newfolders))
	{
		if(iil_C_CreateFolder($conn, $newfolder))
		{
			iil_C_Subscribe($conn, $newfolder);
		}
	}

	iil_Close($conn);
}

?>