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
              
    Document: include/super2global.php
              
    Function: Convert All POST, GET, FILES into normal variables

*********************************************************************/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

function input_filter($str)
{
	if(ini_get('magic_quotes_gpc')!=1 && is_string($str))
	{
		$str = addslashes($str);
	}
	return $str;
}


if(isset($_GET))
{
	while(list($var, $val) = each($_GET)) 
	{
		$$var = input_filter($val);
	}
}


if(isset($_POST))
{
	while(list($var, $val) = each($_POST))
	{
		$$var = input_filter($val);
	}
}


if(isset($_FILES))
{
	while(list($n, $val) = each($_FILES))
	{
		$$n   = $_FILES[$n]['tmp_name'];

		$var  = $n."_name";
		$$var = $_FILES[$n]['name'];

		$var  = $n."_size";
		$$var = $_FILES[$n]['size'];

		$var  = $n."_type";
		$$var = $_FILES[$n]['type'];
	}
}

?>