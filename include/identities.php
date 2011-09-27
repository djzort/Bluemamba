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
              
    Document: include/identities.php
              
*********************************************************************/

class identity_obj
{
	var $name;
	var $email;
	var $replyto;
	var $sig;
}


function identities_parse($str)
{
	$result = array();
	$a = iil_ExplodeQuotedString(";", $str);
	if(is_array($a))
	{
		reset($a);
		while(list($k, $v) = each($a))
		{
			$a2 = iil_ExplodeQuotedString(",", $v);
			if(count($a2) == 4)
			{
				$temp          = new identity_obj;
				$temp->name    = $a2[0];
				$temp->email   = $a2[1];
				$temp->replyto = $a2[2];
				$temp->sig     = $a2[3];
				$result[]      = $temp;
			}
		}
	}
	else
	{
		echo "Not array: ".$a."<br>";
	}
	
	return $result;
}


function identities_package($a)
{
	$result = "";

	if(is_array($a))
	{
		while(list($k, $v) = each($a))
		{
			$result .= $v->name .",". $v->email .",". $v->replyto .",". $v->sig .";";
		}
	}
	
	return $result;
}


?>