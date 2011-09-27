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
              Shannon Mitchell

    Modified: 09/18/2008
              
    Document: include/tmda_datafunc.php
              
    Function: TMDA Data Manipulation. Enables, Disables, Creates 
              and Removes the TMDA Data structure.

*********************************************************************/


// Figure Which function to start
if($my_prefs["tmda"] == 1)
{
	tmda_enable();
}
else
{
	tmda_disable();
}



// Check TMDA data for existance and enabled state
function tmda_exist()
{
	global $USER_BASE_DIR;
	
	$qmail_a   = $USER_BASE_DIR . ".qmail";			// Qmail Control File
	$qmail_b   = $USER_BASE_DIR . ".qmail-default";	// Qmail Control File
	$tmda_dir  = $USER_BASE_DIR . ".tmda";			// TMDA Folder

	if(!is_file($qmail_a) && !is_file($qmail_b) && !is_dir($tmda_dir))
	{
		return 0;	// Doesn't Exist
	}
	
	if(is_file($qmail_a) && is_file($qmail_b) && is_dir($tmda_dir))
	{
		return 1;	// Exists and Enabled
	}
	
	if(is_file("$USER_BASE_DIR.dis_tmda") && is_file("$USER_BASE_DIR.dis_tmda_default") && is_dir($tmda_dir))
	{
		return 2;	// Exists and Disabled
	}
}

	

function tmda_enable()
{
	$result = tmda_exist();
	if($result == 0)	// Create TMDA
	{
		tmda_setup();
	}
	if($result == 2)	// Enable TMDA
	{
		include_once("ftp.php");
		
		if($ftp_conn = xftp_conn())
		{
			xftp_rename(".dis_tmda",         ".qmail"        );
			xftp_rename(".dis_tmda_default", ".qmail-default");
			xftp_rename(".dis_forward",      ".forward");
		}
		
		if($ftp_conn)
		{
			ftp_close($ftp_conn);
		}
	}
}

	

function tmda_disable()
{
	$result = tmda_exist();
	if($result == 1)	// Disable TMDA
	{
		include_once("ftp.php");
		
		if($ftp_conn = xftp_conn())
		{
			xftp_rename(".qmail",         ".dis_tmda");
			xftp_rename(".qmail-default", ".dis_tmda_default");
			xftp_rename(".forward",       ".dis_forward");
		}

		if($ftp_conn)
		{
			ftp_close($ftp_conn);
		}
	}
}



function tmda_setup()
{
	global $loginID, $password, $host;
	global $TMDA_DIR, $USER_BASE_DIR;
	global $DOMAIN_THEME;

	include_once("ftp.php");
	
	if($ftp_conn = xftp_conn())
	{

		$tmdaDir  = $TMDA_DIR . ereg_replace("[\\/]", "", $loginID.".".$host);

	
		// Create Directory Structure
		xftp_mkdir(".tmda");
		xftp_mkdir(".tmda/filters");
		xftp_mkdir(".tmda/lists");
		xftp_mkdir(".tmda/logs");
		xftp_mkdir(".tmda/pending");
		xftp_mkdir(".tmda/responses");
	
	
	
		// Create Qmail control files
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "|preline /usr/bin/tmda-filter ./Maildir/");
			fclose($fp);
			xftp_move(".qmail",         "$tmdaDir/tempfile");
			xftp_move(".qmail-default", "$tmdaDir/tempfile");
		}
		
		
		// Create Postfix control files
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "|/usr/bin/python /usr/bin/tmda-filter");
			fclose($fp);
			xftp_move(".forward", "$tmdaDir/tempfile");
		}
		
	
	
		// Create Crypt Key
		$tmda_key = shell_exec('/usr/bin/tmda-keygen -b');
		if( ($fp = fopen("$tmdaDir/tempfile", "w")) && ($tmda_key) )
		{
			fwrite($fp, $tmda_key);
			fclose($fp);
			xftp_move(".tmda/crypt_key", "$tmdaDir/tempfile");
			xftp_chmod(".tmda/crypt_key", "0600");
			unset($tmda_key);
		}
	
	
	
		// Configurations
		$conf  = 'FILTER_INCOMING = "' . $USER_BASE_DIR . '.tmda/filters/incoming"' . "\n";
		$conf .= 'CONFIRM_APPEND = "' . $USER_BASE_DIR . '.tmda/lists/whitelist"' . "\n";
		$conf .= 'DELIVERY = "' . $USER_BASE_DIR . 'Maildir/"' . "\n";
		$conf .= 'PENDING_DIR = "' . $USER_BASE_DIR . '.tmda/pending/";' . "\n";
		$conf .= 'PENDING_LIFETIME = "2w";' . "\n";
		$conf .= 'PENDING_RELEASE_APPEND = "' . $USER_BASE_DIR . '.tmda/lists/whitelist"' . "\n";
		$conf .= 'PENDING_WHITELIST_APPEND = "' . $USER_BASE_DIR . '.tmda/lists/whitelist"' . "\n";
		$conf .= 'PENDING_DELETE_APPEND = "' . $USER_BASE_DIR . '.tmda/lists/blacklist"' . "\n";
		$conf .= 'PENDING_BLACKLIST_APPEND = "' . $USER_BASE_DIR . '.tmda/lists/blacklist"' . "\n";
		$conf .= 'TEMPLATE_DIR = "/usr/share/tmda/"' . "\n";
		$conf .= 'MAIL_TRANSPORT = "smtp"' . "\n";
		$conf .= 'SMTPHOST = "mail.kvinet.com"' . "\n";
		$conf .= 'CRYPT_KEY_FILE = "' . $USER_BASE_DIR . '.tmda/crypt_key"' . "\n";
		$conf .= '#LOGFILE_DEBUG = "' . $USER_BASE_DIR . '.tmda/logs/debug"' . "\n";
		$conf .= '#LOGFILE_INCOMING = "' . $USER_BASE_DIR . '.tmda/logs/incoming"' . "\n";

		if( ($fp = fopen("$tmdaDir/tempfile", "w")) && ($conf) )
		{
			fwrite($fp, $conf);
			fclose($fp);
			xftp_move(".tmda/config", "$tmdaDir/tempfile");
			unset($conf);
		}
	
	
	
		// Incoming Filters
		$filt  = '# Accept Bounces' . "\n";
		$filt .= 'from <> ok' . "\n";
		$filt .= '# Drop/Accept blacklist and whitelists' . "\n";
		$filt .= 'from-file -autodbm ' . $USER_BASE_DIR . '.tmda/lists/whitelist accept' . "\n";
		$filt .= 'from-file ' . $USER_BASE_DIR . '.tmda/lists/wcwhitelist accept' . "\n";
		$filt .= 'from-file -autodbm ' . $USER_BASE_DIR . '.tmda/lists/blacklist drop' . "\n";
		$filt .= 'from-file ' . $USER_BASE_DIR . '.tmda/lists/wcblacklist drop' . "\n";
	
		if( ($fp = fopen("$tmdaDir/tempfile", "w")) && ($filt) )
		{
			fwrite($fp, $filt);
			fclose($fp);
			xftp_move(".tmda/filters/incoming", "$tmdaDir/tempfile");
			unset($filt);
		}
	
	

		// Create Whitelist for current domain
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "*@".$DOMAIN_THEME."\n");
			fclose($fp);
			
			xftp_move(".tmda/lists/wcwhitelist", "$tmdaDir/tempfile");
		}

	
	
		// Create Blank File
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "");
			fclose($fp);
	
			// Create Base files for TMDA
			xftp_move(".tmda/lists/whitelist", "$tmdaDir/tempfile");
			xftp_move(".tmda/lists/blacklist", "$tmdaDir/tempfile");
			xftp_move(".tmda/lists/wcblacklist", "$tmdaDir/tempfile");
			
			xftp_move(".tmda/logs/debug", "$tmdaDir/tempfile");
			xftp_move(".tmda/logs/incoming", "$tmdaDir/tempfile");
			
			xftp_move(".tmda/pending/challenge", "$tmdaDir/tempfile");
			xftp_move(".tmda/pending/response", "$tmdaDir/tempfile");
			
			xftp_move(".tmda/responses/challenge", "$tmdaDir/tempfile");
			xftp_move(".tmda/responses/response", "$tmdaDir/tempfile");
		}

		// Remove temp file we used
		unlink("$tmdaDir/tempfile");

		if($ftp_conn)
		{
			ftp_close($ftp_conn);
		}
	}
}



function tmda_delete()
{
	$result = tmda_exist();
	if($result != 0)	// Only run if state is other than non existant.
	{
		include_once("ftp.php");
		
		if($ftp_conn = xftp_conn())
		{
			if($result == 1)	// Remove Enabled TMDA
			{
				xftp_delete(".qmail");
				xftp_delete(".qmail-default");
				xftp_delete(".forward");
			}
			
			if($result == 2)	// Remove Disabled TMDA
			{
				xftp_delete(".dis_tmda");
				xftp_delete(".dis_tmda_default");
				xftp_delete(".dis_forward");
			}
	
			xftp_rmAll(".tmda/");	// Remove TMDA directory and sub data
		}
		
		if($ftp_conn)
		{
			ftp_close($ftp_conn);
		}
	}
}


?>