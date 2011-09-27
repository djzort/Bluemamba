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
              
    Document: include/nocache.php
              
    Function: Sets HTTP headers to disable cache

*********************************************************************/

$ua = $_SERVER["HTTP_USER_AGENT"];
$isns = stristr($ua, "Mozilla") && (!(stristr($ua, "compatible"))) && (!(stristr($ua, "Safari")));

if (!$isns)
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    			// Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 	// always modified
	header("Cache-Control: no-store, no-cache, must-revalidate");  	// HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");                          			// HTTP/1.0
}
?>