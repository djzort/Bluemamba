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
              
    Document: source/css.php
              
    Function: Build CSS Style Sheet

*********************************************************************/

header("Content-Type: text/css");
include_once("../include/super2global.php");
include_once("../conf/conf.php");
include_once("../conf/db_conf.php");
include_once("../include/session_auth.php");

$linkc	   = $my_colors["main_link"];
$bgc	   = $my_colors["main_darkbg"];
$textc	   = $my_colors["main_text"];
$hilitec   = $my_colors["main_hilite"];
$font_size = $my_colors["font_size"];

$raw_css = true;
include("../include/css.php");
?>