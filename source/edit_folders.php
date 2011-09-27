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
              
    Document: source/edit_folders.php
              
    Function: Edit Folders

*********************************************************************/

function decodePath($path, $delimiter)
{
	$parts = explode($delimiter, $path);
	while(list($key, $part) = each($parts))
	{
		$parts[$key] = urldecode($part);
	}
	$path = implode($delimiter, $parts);

	return $path;
}

function encodePath($path, $delimiter)
{
		$parts = explode($delimiter, $path);
		while(list($key, $part) = each($parts))
		{
			$parts[$key]=urlencode($part);
		}
		$path=implode($delimiter, $parts);
		
		return $path;
}

function prependRootdir($ROOTDIR, $folder, $delim)
{
	if(empty($ROOTDIR))
	{
		return $folder;
	}
	
	$pos = strpos($folder, $ROOTDIR);
	if (($pos!==false) && ($pos==0))
	{
		return $folder;
	}
	else
	{
		return $ROOTDIR.($ROOTDIR[strlen($ROOTDIR)-1]!=$delim?$delim:"").$folder;
	}
}


include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../include/data_manager.php");
include("../include/cache.php");

$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if(!$conn)
{
	echo "failed";
}
else
{

	// Open DM connection
	$dm = new DataManager_obj;
	if(!($dm->initialize($loginID, $host, $DB_FOLDERS_TABLE, $DB_TYPE)))
	{
		echo "Data Manager initialization failed:<br>\n";
		$dm->showError();
	}

	?>
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
				&nbsp; <span class="tblheader">Manage Folders</span>
			</td>
		</tr>
	</table>

	<br>

	<table border="0" cellspacing="1" cellpadding="1" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
		<tr bgcolor="<?=$my_colors["main_bg"]?>">
			<td align="left">

				<table width="100%" cellpadding="5" cellspacing="1">
					<tr>
						<td>
						<?
			
							$hDelimiter = iil_C_GetHierarchyDelimiter($conn);
							flush();
							
							$modified = false;
							$error    = "";
				
							/********* Handle New Folder *******/
				
							if ( isset($newfolder) && !isset($subfolder) )
							{
						
								// Don't allow Special characters
								if( strstr($newfolder, "/") || strstr($newfolder, "\\") || 
									strstr($newfolder, "+") || strstr($newfolder, "*")  || 
									strstr($newfolder, "~") || strstr($newfolder, ".")   )
								{
									$error = "Could not create folder: Special characters are not allowed";
								}
								else
								{
									// prepend folder path with rootdir as necessary
									$newfolder = prependRootdir($ROOTDIR, $newfolder, $hDelimiter);
						
									$unencNF = cleanfolder($newfolder);
						
									// create new folder
									if (iil_C_CreateFolder($conn, $newfolder))
									{
										iil_C_Subscribe($conn, $newfolder);
										$error = "Created folder: " . $unencNF;
										$modified = true;
									
										$new_entry = array();
										$new_entry["name"] = $newfolder;
										$new_entry["type"] = $type;
													
										if ($dm->insert($new_entry)) echo "<!-- Inserted //-->";
										else echo "<!-- Not inserted //-->";
			
									}
									else
									{
										$error = "Could not create folder: " . $unencNF . "<br>" . $conn->error;
									}
								}
							}
							/************************/
						
						
						
							/********* Handle New Sub Folder *******/
							if (isset($subfolder) && isset($newfolder))
							{
								// Don't allow Special characters
								if( strstr($newfolder, "/") || strstr($newfolder, "\\") || 
									strstr($newfolder, "+") || strstr($newfolder, "*")  || 
									strstr($newfolder, "~") || strstr($newfolder, ".")   )
								{
									$error = "Could not create folder: Special characters are not allowed";
								}
								else
								{
									// prepend folder path with rootdir as necessary
									$newfolder = $subfolder .".". $newfolder;
									$newfolder = prependRootdir($ROOTDIR, $newfolder, $hDelimiter);
											
									$unencNF = cleanfolder($newfolder);
						
									// create new folder
									if (iil_C_CreateFolder($conn, $newfolder))
									{
										iil_C_Subscribe($conn, $newfolder);
										$error = "Created sub folder: " . $unencNF;
										$modified = true;
			
										$new_entry = array();
										$new_entry["name"] = $newfolder;
										$new_entry["type"] = $type;
													
										if ($dm->insert($new_entry)) echo "<!-- Inserted //-->";
										else echo "<!-- Not inserted //-->";
			
									}
									else
									{
										$error = "Could not create sub folder: " . $unencNF . "<br>" . $conn->error;
									}
								}
							}
							/************************/
						
						
							
							/********* Handle Delete Folder ********/
							if (isset($delmenu))
							{
						
								// Make sure it's unsubscribed
								iil_C_UnSubscribe($conn, $delmenu);
						
								$unencDF = cleanfolder($delmenu);
						
								// Delete...
								if ((empty($defaults[$unencDF])) && (iil_C_DeleteFolder($conn, decodePath($delmenu, $hDelimiter))))
								{
									$error = "Deleted folder: " . $unencDF;
									$modified = true;
								}
								else
								{
									$error = "Could not delete folder: " . $unencDF;
								}
						
							}
							/***************************/
						
						
						
						
							/********* Handle Rename Folder ********/
							if ( isset($newname) && isset($oldname) )
							{
								// Make folder unsubscribed
								iil_C_UnSubscribe($conn, $oldname);
								
								// Don't allow Special characters
								if( strstr($newname, "/") || strstr($newname, "\\") || 
									strstr($newname, "+") || strstr($newname, "*")  || 
									strstr($newname, "~") || strstr($newname, ".")   )
								{
									$error = "Could not create folder: Special characters are not allowed";
								}
								else
								{
						
									// Don't allow user to rename system folders
									if(issysfolder($oldname))
									{
										$error = "Cannot rename system folders. ".cleanfolder($oldname)." --> ".cleanfolder($newname)."";
									}
									else
									{
										$unencNF = cleanfolder($oldname);
										$unencNFnew = cleanfolder($newname);
						
										if(strstr($unencNF, "/"))	// Are there sub folders in oldname?
										{
											// Only rename last folder, don't rename it all
										
						
											$parts = explode("/", $unencNF);
											$count = count($parts);
											$lastfolder = $parts[$count-1];		// Get last folder name
						
											$newname = str_replace($lastfolder, $newname, $oldname);	// Replace Last folder with newname
										}
			
										if (!strstr($newname, "INBOX."))	// Add inbox prefix if needed.
										{
											$newname = "INBOX." . $newname;
										}
						
										// Rename
										if (iil_C_RenameFolder($conn, $oldname, $newname))
										{
											$error = "Renamed folder: $unencNF --> $unencNFnew";
											$modified = true;
										}
										else
										{
											$error = "Could not rename folder: $unencNF --> $unencNFnew";
										}
									}
								}
							}
							/***************************/
			
			
							/********* Handle Change Folder Type *******/
				
							if ( isset($changename) && isset($type) )
							{
								if($folder_type = $dm->read())
								{
									while( list($i, $value) = each($folder_type))
									{
										if($changename == $folder_type[$i]['name'])
										{
											$changeid = $folder_type[$i]['id'];
				
											$update_entry = array();
											$update_entry["name"] = $changename;
											$update_entry["type"] = $type;
														
											if ($dm->update($changeid, $update_entry)) 
											{
												$error = "Changed folder type: ". cleanfolder($changename) ." --> $type";
												$modified = true;
											}
											else
											{
												$error = "Unable to change folder type: ". cleanfolder($changename) ." --> $type";
											}
				
											break 1;
										}
									}
								}
			
								// Folder isn't in database, so add new entry
								if( !$modified && !$error )
								{
									$new_entry = array();
									$new_entry["name"] = $changename;
									$new_entry["type"] = $type;
												
									if ($dm->insert($new_entry))
									{
										$error = "Changed folder type: ". cleanfolder($changename) ." --> $type";
										$modified = true;
									}
									else
									{
										$error = "Unable to change folder type: ". cleanfolder($changename) ." --> $type";
									}
			
								}
			
							}
							/************************/
			
			
							if ($modified)
							{
								echo '<font color="#00CC00">'.$error.'</font>';
								echo "<script> parent.list1.location='folders.php?user=$user'; </script>\n";
								cache_clear($loginID, $host, "folders");
							}
							else
							{
								echo '<font color="#FF0000">' . $error . '</font>';
							}
						
							// Get all folders
							$mailboxes = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
							if ($mailboxes) sort($mailboxes);
							
							// Get subscribed folders...
							$subscribed = iil_C_ListSubscribed($conn, $ROOTDIR, "*");
							if ( ($subscribed) && (count($subscribed) > 0) )
							{
								sort($subscribed);
								$unsubscribed = array_diff($mailboxes, $subscribed);
							}
							else
							{
								echo "Error fetching subscribed folders: " . $conn->error . "<br>";
							}
						
							// Create Folders List
							$folderList = "";
							if($mailboxes)
							{
								while ( list($k, $folder) = each($mailboxes) )
								{
									if(issysfolder($folder) == false)
									{
										$strfolder = cleanfolder($folder);
										$folderList .= "<option value=\"$folder\">$strfolder\n";
									}
								}	
							}	
							?>
			
							<table border="0" cellspacing="1" cellpadding="2" width="100%">
								<tr>
									<td>
										<form method="POST" style="display:inline">
											<b>Create folder</b><br>
											<input type="hidden" name="user" value="<?=$user?>">
											<input type="text" class="textbox" name="newfolder">
											&nbsp;Type: 
											<select name="type" class="textbox">
												<option value="From">From
												<option value="To">To
											</select>
											<input type="submit" name="create" value="Create">
										</form>
									</td>
								</tr>
								<?
								
								if( !empty($folderList) )	// Folder Options
								{
								?>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>
										<form method="POST" style="display:inline">
											<b>Create sub folder</b><br>
											<input type="hidden" name="user" value="<?=$user?>">
											<select name="subfolder" class="textbox">
												<? echo $folderList; ?>
											</select>
											<font size="+1"> / </font>
											<input type="text" name="newfolder" class="textbox">
											&nbsp;Type: 
											<select name="type" class="textbox">
												<option value="From">From
												<option value="To">To
											</select>
											<input type="submit" name="createsub" value="Create">
										</form>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>
										<form method="POST" style="display:inline">
											<b>Delete folder</b><br>
											<input type="hidden" name="user" value="<?=$user?>" class="textbox">
											<select name="delmenu" class="textbox">
												<? echo $folderList; ?>
											</select>
											<input type="submit" name="delete" value="Delete">
										</form>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>
										<form method="POST" style="display:inline">
											<b>Rename folder</b><br>
											<input type="hidden" name="user" value="<?=$user?>" class="textbox">
											<select name="oldname" class="textbox">
												<? echo $folderList; ?>
											</select>
											--<font size="+1">&gt;</font>
											<input type="text" name="newname" class="textbox">
											<input type="submit" name="rename" value="Rename">
										</form>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td>
										<form method="POST" style="display:inline">
											<b>Change folder type</b><br>
											<input type="hidden" name="user" value="<?=$user?>" class="textbox">
											<select name="changename" class="textbox">
												<? echo $folderList; ?>
											</select>
											&nbsp;Type: 
											<select name="type" class="textbox">
												<option value="From">From
												<option value="To">To
											</select>
											<input type="submit" name="change" value="Change">
										</form>
									</td>
								</tr>
								<?
								}
								?>
							</table>
			
						</td>
					</tr>
				</table>
	
			</td>
		</tr>
	</table>
	<?

	iil_Close($conn);

	}
?>
</BODY></HTML>