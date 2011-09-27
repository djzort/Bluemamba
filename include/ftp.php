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
              
    Document: include/ftp.php
              
    Function: Simple FTP

*********************************************************************/

$ftp_conn;
	
// Connect and Login
function xftp_conn()
{
	global $ftp_conn;
	global $loginID;
	global $password;
	
	if(!strstr($loginID, "@"))
	{
		if(!$ftp_conn = ftp_connect("localhost"))
		{
			return false;
		}
	
		if(!$ftp_result = ftp_login($ftp_conn, $loginID, $password))
		{
			return false;
		}
		return $ftp_conn;
	}
	else
	{
		return -1;
	}

}



// Move File
function xftp_move($destination_file, $source_file)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		// Read from file
		if($fp = fopen($source_file, "r"))
		{
			if(!$upload = ftp_fput($ftp_conn, $destination_file, $fp, FTP_ASCII))
			{
				return -1;
			}
		}
		fclose($fp);
	}
	else
	{
		return -1;
	}
}



// Rename File
function xftp_rename($old_file, $new_file)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		if(!$upload = ftp_rename($ftp_conn, $old_file, $new_file))
		{
			return -1;
		}
	}
}



// Delete File
function xftp_delete($file)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		if(!$result = ftp_delete($ftp_conn, $file))
		{
			return -1;
		}
	}
}



// Initiate Remove Files and Directories Recursively
function xftp_rmAll($destination_dir)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		$ar_files = ftp_rawlist($ftp_conn, $destination_dir);
		if(is_array($ar_files))	// Makes sure there are files
		{
			foreach ($ar_files as $file)	// For each file
			{
				if(ereg("([-d][rwxst-]+).* ([0-9]) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9]) ([0-9]{2}:[0-9]{2}) (.+)", $file, $regs))
				{
					if( ($regs[8] == ".") || ($regs[8] == "..") )
					{
						continue;	// Skip current and parent folders
					}
		
					if(substr($regs[1], 0, 1) == "d")	// Check if it is a directory
					{
						xftp_rmAll($destination_dir . "/" . $regs[8]); // If so, use recursion
					}
					else
					{
						ftp_delete($ftp_conn, $destination_dir . "/" . $regs[8]); // If not, delete the file
					}
				}
			}
		}
		ftp_rmdir($ftp_conn, $destination_dir); // Delete empty directories
	}
}



// Create Directory
function xftp_mkdir($directory)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		if(!$result = ftp_mkdir($ftp_conn, $directory))
		{
			return -1;
		}
	}
}



// Change Permissions
function xftp_chmod($file, $permissions)
{
	global $ftp_conn;
	
	if($ftp_conn)
	{
		$chmod_cmd = "CHMOD $permissions $file";
		if(!$result = ftp_site($ftp_conn, $chmod_cmd))
		{
			return -1;
		}
	}
}


?>