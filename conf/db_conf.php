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
              
    Document: conf/db_conf.php
              
    Function: Configuration options for MySQL database

*********************************************************************/

	$DB_HOST	 = 'localhost';
	$DB_NAME	 = "webmail";
	$DB_USER	 = "webmailuser";
	$DB_PASSWORD = "webmailpass";
	
	$DB_USERS_TABLE      = "users";
	$DB_SESSIONS_TABLE   = "sessions";
	$DB_CONTACTS_TABLE   = "contacts";
	$DB_PREFS_TABLE      = "prefs";
	$DB_COLORS_TABLE     = "colors";
	$DB_IDENTITIES_TABLE = "identities";
	$DB_CALENDAR_TABLE   = "calendar";
	$DB_BOOKMARKS_TABLE  = "bookmarks";
	$DB_CACHE_TABLE      = "cache";
	$DB_FILTERS_TABLE    = "filters";
	$DB_FOLDERS_TABLE    = "folders";
	$DB_LOG_TABLE        = "log";

	$DB_PERSISTENT = false;

?>