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
              
    Document: include/contacts_commons.php
              
    Function: Common functions used by source/contacts.php
              and source/edit_contact.php

*********************************************************************/

function GetGroups($contacts)
{
	if(!is_array($contacts)) return "";
	
	$grp_ka = array();
	$result = array();
	$i      = 0;

	while(list($key, $val) = each($contacts))
	{
		$group = $contacts[$key]["grp"];
		$group = trim(chop($group));
		if( (!empty($group)) && ($grp_ka[$group] != 1) ) 
		{
			$grp_ka[$group] = 1;
		}
	}

	reset($grp_ka);
	while(list($key, $val) = each($grp_ka))
	{
		$result[$i] = $key;
		$i++;
	}
	
	return base64_encode(implode(",", $result));
}

?>