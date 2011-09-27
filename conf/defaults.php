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
              
    Document: conf/defaults.php
              
    Function: Default Options  

*********************************************************************/

if(!$loginID) {$loginID = $user;}
$default_email = $loginID.(strstr($loginID, "@")?"":"@".$DOMAIN_THEME);

$default_prefs = array
(
	"colorize_quotes"		=> "1",
	"detect_links"			=> "1",
	"view_max"				=> "15",
	"show_size"				=> "1",
	"delete_trash"			=> "0",
	"user_name"				=> $default_email,
	"email_address"			=> $default_email,
	"signature1"			=> "",
	"show_sig1"				=> "0",
	"sort_field"			=> "DATE",
	"sort_order"			=> "DESC",
	"list_folders"			=> "1",
	"view_inside"			=> "1",
	"preview_window"		=> "1",
	"timezone"				=> "-7",
	"html_in_frame"			=> "0",
	"show_images_inline"	=> "1",
	"subject_edit"			=> "0",
	"advanced_controls"		=> "0",
	"showContacts"			=> "2",
	"showCC"				=> "1",
	"closeAfterSend"		=> "1",
	"showNav"				=> "1",
	"compose_inside"		=> "1",
	"showNumUnread"			=> "1",
	"refresh_folderlist"	=> "1",
	"folderlist_interval"	=> "3",
	"radar_interval"		=> "3",
	"theme"					=> "default",
	"notify"				=> "default",
	"alt_identities"		=> "",
	"main_cols"				=> "camsfdz",
	"main_toolbar"			=> "bt",
	"nav_no_flag"			=> "0",
	"filters"				=> "0",
	"tmda"					=> "0"
);

?>