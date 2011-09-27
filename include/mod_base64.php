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
              
    Document: include/mod_base64.php
              
    Function: base64 that uses '_' instead of '/' character for 
              use in file names.

*********************************************************************/

function mod_base64_encode($str)
{
	return str_replace("/","_", base64_encode($str));
}

function mod_base64_decode($str)
{
	return base64_decode(str_replace("_", "/", $str));
}

?>