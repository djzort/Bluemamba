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
              
    Document: include/header_main.php

*********************************************************************/

include_once("../include/nocache.php");
include_once("../conf/conf.php");
include_once("../conf/db_conf.php");

// Disable time limit
if(!ini_get("safe_mode"))
{
	@set_time_limit($MAX_EXEC_TIME);
}

// Get current page
$current_page = $_SERVER["PHP_SELF"];
$pos = strrpos($current_page, "/");
if($pos !== false)
{
	$current_page = substr($current_page, $pos+1);
}

// Setup code for onLoad and onUnload
$onUnLoad = "";
if(strpos($current_page, "compose.php") !== false)
{
	$onUnLoad = " onUnload=\"close_popup();\"";
}

$onLoad = "";
if(strpos($current_page, "contacts_popup.php") !== false)
{
	$onLoad = " onLoad=\"acknowledge_popup();\"";
	$onUnLoad = " onUnLoad=\"alert_close();\"";
}

// Continue only if valid session ID
if(isset($user))
{
	$sid = $user;

	include("../include/session_auth.php");
	include("../include/common.php");
	include("../include/global_func.php");

	?>
<html>
	<head>
		<?
		$linkc		= $my_colors["main_link"];
		$bgc		= $my_colors["main_darkbg"];
		$textc		= $my_colors["main_text"];
		$hilitec	= $my_colors["main_hilite"];
		$font_size	= $my_colors["font_size"];
		?>
		<link rel="stylesheet" href="<? echo "css.php?user=" . $user . "&theme=" . $my_prefs["theme"]; ?>" type="text/css">
		<?
		include("../include/javascript.php");
		?>
	</head>
	<?
 	echo '<body text="'.$my_colors["main_light_txt"].'" bgcolor="'.$bgc.'" link="'.$linkc.'" alink="'.$linkc.'" vlink="'.$linkc.'"'.$onLoad.$onUnLoad.'>';
}
else
{
	echo "<body>";
	echo "User unspecified: ".$user;
	echo "</html>\n</body>";
	exit;
}
flush();
?>