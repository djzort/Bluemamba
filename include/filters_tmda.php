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
              
    Document: include/filters_tmda.php
              
    Function: Add, Import, Save, Delete functions for TMDA filtering

*********************************************************************/

// Set TMDA Directory
$tmda_dir = "$USER_BASE_DIR.tmda";
$tmdaDir  = $TMDA_DIR . ereg_replace("[\\/]", "", $loginID.".".$host);

include_once("ftp.php");


// Delete All
if($delete_all == 1)
{
	$error = 'Are you sure you want to delete everything? <a href="?user='. $sid .'&delete_all=2">Yes</a>:<a href="?user='. $sid .'">No</a>';
}
if($delete_all == 2)
{
	if($ftp_conn = xftp_conn())
	{
		// Create wildcard whitelist for current domain
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "*@".$DOMAIN_THEME."\n");
			fclose($fp);
			
			xftp_move(".tmda/lists/wcwhitelist", "$tmdaDir/tempfile");
		}
		
		// Create blank files
		if($fp = fopen("$tmdaDir/tempfile", "w"))
		{
			fwrite($fp, "");
			fclose($fp);
			
			xftp_move(".tmda/lists/whitelist", "$tmdaDir/tempfile");
			xftp_move(".tmda/lists/blacklist", "$tmdaDir/tempfile");
			xftp_move(".tmda/lists/wcblacklist", "$tmdaDir/tempfile");
		}
		unlink("$tmdaDir/tempfile");
	}
}


// Add new email
if($add)
{
	if(strstr($email, "*"))		// Change list if a wildcard
	{
		$list = "wc" . $list;
	}
	
	if($email)
	{					
		$found = false;

		// Load list into an array
		if(is_file("$tmda_dir/lists/$list"))
		{
			$fp = fopen("$tmda_dir/lists/$list", "r");
			while(!feof($fp))
			{
				$line = chop(fgets($fp));
				if(strlen($line) > 1)
				{
					if($email != $line)		// Is email different?
					{
						$counter++;
						$list_emails[$counter] = $line;
					}
					else					// Similar email found
					{
						$found = true;
					}
				}
			}
			fclose($fp);
		}
		
		if($found == false)
		{
			// Add new email at end of list
			$list_emails[$counter + 1] = $email;
	
			// Save list in scratch space
			if($fp = fopen("$tmdaDir/$list", "w"))
			{
				sort($list_emails);
				reset($list_emails);
				foreach($list_emails as $key => $value)
				{
					if(fwrite($fp, $value . "\n") === false)
					{
						$error = "Cannot write to list";
					}
				}
				fclose($fp);
			}
			
			if($ftp_conn = xftp_conn())
			{
				xftp_move(".tmda/lists/$list", "$tmdaDir/$list");
				ftp_close($ftp_conn);
			}
			unlink("$tmdaDir/$list");
		}
		else
		{
			$error = "$email already exists.";
		}
	}
	else
	{
		$error = "Invalid Email";
	}
}


// Import contacts
if($import)
{
	$dmcnt = new DataManager_obj;
	if(!$dmcnt->initialize($loginID, $host, $DB_CONTACTS_TABLE, $backend))
	{
		echo "Data Manager initialization failed:<br>\n";
		$dmcnt->showError();
	}

	// Fetch and sort
	$contacts = $dmcnt->sort("name", "ASC");
	
	if(is_array($contacts) && count($contacts) > 0)
	{
		// Load list into an array
		if(is_file("$tmda_dir/lists/$list"))
		{
			$fp = fopen("$tmda_dir/lists/$list", "r");
			while(!feof($fp))
			{
				$line = chop(fgets($fp));
				if(strlen($line) > 1)
				{
					$counter++;
					$list_emails[$counter] = $line;
				}
			}
			fclose($fp);
		}

		// Add new email at end of list
		$list_emails[$counter + 1] = $email;

		while(list($ckey, $cval) = each($contacts))
		{
			if(!in_array($contacts[$ckey]["email"], $list_emails))
			{ 
				$counter++;
				$list_emails[$counter] = $contacts[$ckey]["email"];
			}
		}

		// Save list in scratch space
		if($fp = fopen("$tmdaDir/$list", "w"))
		{
			sort($list_emails);
			reset($list_emails);
			foreach($list_emails as $key => $value)
			{
				if(fwrite($fp, $value . "\n") === false)
				{
					$error = "Cannot write to list";
				}
			}
			fclose($fp);
		}
		
		if($ftp_conn = xftp_conn())
		{
			xftp_move(".tmda/lists/$list", "$tmdaDir/$list");
			ftp_close($ftp_conn);
		}
		unlink("$tmdaDir/$list");

	}
}



// Save edited email
if($save)
{
	if($email)
	{
		if(strstr($edit_id, "wc"))	// Change list if a wildcard
		{
			$prevlist = "wc" . $prevlist;
			$edit_id  = str_replace("wc", "", $edit_id);
		}

		if(strstr($email, "*"))		// Change list if a wildcard
		{
			$list = "wc" . $list;
		}

		$counter = 0;

		// Load previous list into an array
		if(is_file("$tmda_dir/lists/$prevlist"))
		{
			$fp = fopen("$tmda_dir/lists/$prevlist", "r");
			while(!feof($fp))
			{
				$line = chop(fgets($fp));
				if(strlen($line) > 1)
				{
					$counter++;
					$prevlist_emails[$counter] = $line;
				}
			}
			fclose($fp);
		}

		if($prevlist == $list)		// Edit email in same list
		{
			// Update email in prevlist
			$prevlist_emails[$edit_id] = $email;
		}
		else						// Edit email in new list
		{
			// Delete email from prevlist
			unset($prevlist_emails[$edit_id]);

			// Load destination list into an array
			if(is_file("$tmda_dir/lists/$list"))
			{
				$fp = fopen("$tmda_dir/lists/$list", "r");
				while(!feof($fp))
				{
					// Chop out \n char at end of line
					$line = chop(fgets($fp));
					if(strlen($line) > 1)
					{
						$counter++;
						$movelist_emails[$counter] = $line;
					}
				}
				fclose($fp);
			}
			
			// Add email at end of list
			$movelist_emails[$counter + 1] = $email;
		}

		
		// Save edited prevlist
		if($fp = fopen("$tmdaDir/$prevlist", "w"))
		{
			sort($prevlist_emails);
			reset($prevlist_emails);
			foreach($prevlist_emails as $key => $value)
			{
				if(fwrite($fp, $value . "\n") == false)
				{
					$error = "Cannot write to $tmdaDir / $prevlist";
				}
			}
			fclose($fp);

			if($ftp_conn = xftp_conn())
			{
				xftp_move(".tmda/lists/$prevlist", "$tmdaDir/$prevlist");
				ftp_close($ftp_conn);
			}
			unlink("$tmdaDir/$prevlist");
		}


		// Save list if the email is moving to another list
		if($prevlist != $list)
		{
			if($fp = fopen("$tmdaDir/$list", "w"))
			{
				sort($movelist_emails);
				reset($movelist_emails);
				foreach($movelist_emails as $key => $value)
				{
					if(fwrite($fp, $value . "\n") == false)
					{
						$error = "Cannot write to $list";
					}
				}
				fclose($fp);

				if($ftp_conn = xftp_conn())
				{
					xftp_move(".tmda/lists/$list", "$tmdaDir/$list");
					ftp_close($ftp_conn);
				}
				unlink("$tmdaDir/$list");
			}
		}
	}
	else
	{
		$error = "Invalid Email";
	}
}



// Delete email from list
if($delete)
{
	if(strstr($edit_id, "wc"))	// Change list if a wildcard
	{
		$prevlist = "wc" . $prevlist;
		$edit_id  = str_replace("wc", "", $edit_id);
	}
	
	// Load list into an array
	$found = false;
	if(is_file("$tmda_dir/lists/$prevlist"))
	{
		$fp = fopen("$tmda_dir/lists/$prevlist", "r");
		while(!feof($fp))
		{
			$line = chop(fgets($fp));
			if(strlen($line) > 1)
			{
				$counter++;
				$list_emails[$counter] = $line;
			}
		}
		fclose($fp);
	}
	
	unset($list_emails[$edit_id]);	// Delete email
	
	// Save list in scratch space
	if($fp = fopen("$tmdaDir/$prevlist", "w"))
	{
		sort($list_emails);
		reset($list_emails);
		foreach($list_emails as $key => $value)
		{
			if(fwrite($fp, $value . "\n") === false)
			{
				$error = "Cannot write to $list";
			}
		}
		fclose($fp);
	}
	
	if($ftp_conn = xftp_conn())
	{
		xftp_move(".tmda/lists/$prevlist", "$tmdaDir/$prevlist");
		ftp_close($ftp_conn);
	}
	unlink("$tmdaDir/$prevlist");
}
	

?>