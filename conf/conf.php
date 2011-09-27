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

    Modified: 09/19/2008
              
    Document: conf/conf.php
              
    Function: Configurations

*********************************************************************/

// Global Conf
$ROOTDIR            = "INBOX";
$CHARSET            = "ISO-8859-1";
$STAY_LOGGED_IN     = true;
$TRUST_USER_ADDRESS = true;

// General Conf
//$DATE_FORMAT = "D, M jS Y h:i A";
$DATE_FORMAT = "m/d/Y h:i A";


// Theme Settings, Use Server Name
$DEFAULT_THEME = "bluemamba.org";
$DOMAIN_THEME  = "";	// Leave Blank to Auto Select
$SPLASH_THEME  = "login";


// Users Directories
$UPLOAD_DIR     = "../data/uploads/";
$CACHE_DIR      = "../data/cache/";
$USER_DIR       = "../data/users/";
$SESSION_DIR    = "../data/sessions/";
$TMDA_DIR       = "../data/tmda/";


// Outgoing Mail
$SMTP_SERVER    = "mail.yourdomain.com";
$SMTP_TYPE      = "sendmail";
$SMTP_USER      = "";
$SMTP_PASSWORD  = "";


// Login Host
$LOGIN_HOST     = "mail.yourdomain.com";
$LOGIN_PORT     = "143";


// Authintication Mode
$AUTH_MODE["imap"] = "plain";
$AUTH_MODE["pop3"] = "plain";
$AUTH_MODE["smtp"] = "";


// Dictionary
$CHECK_SPELLING = true;
$SPELLING_LANG  = "en";
$ASPELL_PATH    = "/usr/bin/aspell";


// TMDA Filtering
$TMDA_ENABLED   = false;


// Spam Prevention
$max_rcpt_message  = 50;
$max_rcpt_session  = 100;
$min_send_interval = 15;
$report_spam_to    = "";


$MAX_EXEC_TIME          = 60;
$MAX_SESSION_TIME       = (60 * 60 * 24);
$MIN_FOLDERLIST_REFRESH = 10;
$MIN_RADAR_REFRESH      = 10;
$MAX_UPLOAD_SIZE        = 0;
$WORD_WRAP              = 74; // Return line after 74 chars


// Tag added to outgoing mail 'Made with webmail!'
$TAG_LINE = "";



/*********************************************************************
    DO NOT MODIFY BELOW HERE
*********************************************************************/

// Get Users Home Directory
$user_info_array = posix_getpwnam("$loginID");
$USER_BASE_DIR   = $user_info_array['dir'] . "/";

// If Domain Theme isn't set then select it based on server name
if(!$DOMAIN_THEME)
{
	$DOMAIN_PARTS = explode(".", $_SERVER['SERVER_NAME']);
	if(count($DOMAIN_PARTS) == 3)	// Full domain is present
	{
		$DOMAIN_THEME = $DOMAIN_PARTS[1] . "." . $DOMAIN_PARTS[2];
	}
	elseif(count($DOMAIN_PARTS) == 2) // No host on domain name
	{
		$DOMAIN_THEME = $DOMAIN_PARTS[0] . "." . $DOMAIN_PARTS[1];
	}
	
	
	if(!is_file("themes/$DOMAIN_THEME/conf.php"))
	{
		$DOMAIN_THEME = $DEFAULT_THEME;
	}
}

// Load Domain Theme Configs
include_once("themes/$DOMAIN_THEME/conf.php");

$SPLASH_THEME = "themes/$DOMAIN_THEME/$SPLASH_THEME";

?>