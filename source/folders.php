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
              
    Document: source/folders.php
              
    Function: Display Folders

*********************************************************************/

include_once("../include/super2global.php");
include_once("../include/nocache.php");
include_once("../conf/conf.php");


function getFolderStates()
{
	global $loginID, $host;
	
	$data = cache_read($loginID, $host, "folder_states");

	if(!$data) 
	{
		return array("INBOX");
	}
	else
	{
		return $data;
	}
}


function saveFolderStates($folders)
{
	global $loginID, $host;
	
	$result = cache_write($loginID, $host, "folder_states", $folders, false);
	return $result;
}


function removeFolders($array)
{
	if((!is_array($array)) || (count($array)==0))
	{
		return true;
	}
	
	$current = getFolderStates();
	if(is_array($current))
	{
		$save = array();
		while(list($k,$folder) = each($current))
		{
			if(!in_array($folder, $array))
			{
				$save[] = $folder;
			}
		}
		saveFolderStates($save);
	}
}


function addFolders($array)
{
	if((!is_array($array)) || (count($array)==0))
	{
		return true;
	}

	$current = getFolderStates();
	if(is_array($current))
	{
		$save = array_merge($current, $array);
		sort($save);
		saveFolderStates($save);
	}
}


function InArray($array, $item)
{
	if(!is_array($array))
	{
		return false;
	}
	elseif(strcasecmp($item, "inbox") == 0)
	{
		return false;
	}
	else 
	{
		return in_array($item, $array);
	}
}


function ChildInArray($array, $item)
{
	if(!is_array($array)) return false;
    reset($array);
    while(list($k, $v) = each($array))
	{
		$pos = strpos($v, $item);
		if(($pos !== false) && ($pos == 0)) return true;
	}
    return false;
}


function IndentPath($path, $containers, $delim)
{
	$containers->reset();
	$pos = strrpos($path, $delim);
	if($pos > 0)
	{
		$folder = substr($path, $pos);
		$path = substr($path, 0, $pos);
	}
	
	do
	{
		$container = $containers->next();
		if($container)
		{
			$path = str_replace($container, "&nbsp;&nbsp;&nbsp;", $path);
		}
	}
	while($container);
	
	return $path.$folder;
}


if(empty($user))
{
	echo "User unspecified.";
	exit;
}
else
{
	include_once("../include/session_auth.php");
	include_once("../include/global_func.php");
	include_once("../include/icl.php");
	include_once("../include/stack.php");
	include_once("../include/cache.php");

	?>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<?

		if($my_prefs["refresh_folderlist"])
		{
			$tmp      = $my_prefs["folderlist_interval"];
			$interval = $tmp?$tmp:$MIN_FOLDERLIST_REFRESH;
			echo '<META HTTP-EQUIV="refresh"  CONTENT="' . ($interval * 60) . ';URL=folders.php?user=' . $user . '">' . "\n";
		}
		include_once("../include/css.php");
	
		$linkc      = $my_colors["folder_link"];
		$bodyString = '<body bgcolor="'.$my_colors["folder_bg"].'" text="'.$linkc.'" link="'.$linkc.'" alink="'.$linkc.'" vlink="'.$linkc.'" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';

		?>
	</head>

	<? echo $bodyString; ?>

	<table width="100%" border="0" cellspacing="0" cellpadding="0" background="<? echo $THEME; ?>folder_logoback.gif">
		<tr>
			<td align="left"><img src="<? echo $THEME; ?>folder_logo.gif" border="0"></td>
		</tr>
	</table>
	

	<table border="0" cellspacing="0" cellpadding="5" class="folderList">
		<tr>
			<td valign="middle">
				<a href="filters.php?user=<?=$user?>" target="list2">Filters</a> 
				<?
				// Filters Notification
				if($my_prefs["tmda"] || $my_prefs["filters"])
				{
					echo  "Enabled:";
				}
				else
				{
					echo  "Disabled";
				}
				?>
			</td>
			<td valign="middle">
				<? 
					if($my_prefs["filters"])
					{
						echo '<a href="filters.php?user='.$user.'" target="list2"><img src="'.$THEME.'filter_basic.gif" width="16" height="14" border="0" align="texttop" alt="Basic Filters"></a>';
					}
				?>
				<? 
					if($my_prefs["tmda"])
					{
						echo '<a href="filters_tmda.php?user='.$user.'" target="list2"><img src="'.$THEME.'filter_tmda.gif" width="16" height="14" border="0" align="texttop" alt="Advanced Filters"></a>';
					}
				?>
			</td>
		</tr>
	</table>
	

	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="folderList">
		<tr>
			<td>
				<?

				$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
				if($conn)
				{
					
					// Handle emptry_trash request
					if($empty_trash)
					{
						iil_C_ClearFolder($conn, "INBOX.Trash");
					}
				
					// Show heading
					?>
					<p><a href="folders.php?user=<?=$user?>"><b>Folders</b></a>
					&nbsp;&nbsp;[<a href="edit_folders.php?user=<?=$user?>" target="list2">Manage</a>]
					<br><br>
					<?
					
					// Get list of mailboxes
					cache_clear($loginID, $host, "folders");
					$folders = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
					cache_write($loginID, $host, "folders", $folders);


					if(!is_array($folders))
					{
						echo "<b>Failed:</b> " . $conn->error . "<br>\n";
					}
					else
					{
				
						// Get hierarchy delimiter, usually '/' or '.'
						$delim = iil_C_GetHierarchyDelimiter($conn);
					
						// Get list of container folders, because some IMAP server won't return them
						// e.g.  container of "folder/sub" is "folder"
						$folder_container = array();
						$containers = array();
						reset($folders);
						while(list($k, $path) = each($folders))
						{
							while(false !== ($pos = strrpos($path, $delim)))
							{
								$container = substr($path, 0, $pos);
								if($containers[$container] != 1) $containers[$container] = 1;
								$folder_container[$path] = $container;
								$path = substr($path, 0, $pos);
							}
						}
				
						// Make sure containers are in folder list
						reset($containers);
						while(list($container, $v) = each($containers))
						{
							if(!InArray($folders,$container))
							{
								array_push($folders, $container);
							}
						}
						asort($folders);
				
						// Handle subscribe (expand) command
						if($subscribe)
						{
							// Subscribe folder...
							$add_list   = array();
							$v_sub[]    = $folder;
							$add_list[] = $folder;
							
							// And immediate sub-folders
							$folder .= $delim;
							reset($folders);
							while(list($k,$v) = each($folders))
							{
								$pos = strpos($v, $folder);
								if(($pos !== false) && ($pos == 0))
								{
									$pos = strrpos($v, $delim);
									if($pos <= strlen($folder))
									{
										$v_sub[]    = $v;
										$add_list[] = $v;
									}
								}
							}
							if(count($add_list) > 0)
							{
								addFolders($add_list);
							}
						}
				
						// Get list of subscribed (expanded) folders
						$subscribed = getFolderStates();
						
						// Make sure they exist (might've been deleted)
						$temp_subs = array();
						reset($subscribed);
						while(list($k, $path) = each($subscribed))
						{
							if(in_array($path, $folders))
							{
								$temp_subs[] = $path;
							}
						}
						$subscribed = $temp_subs;
						
						// With some servers, only container folders are ignored, so we need to
						// Do it the inefficient way...
						if(is_array($subscribed))
						{
							// make sure the container of every subscribed folder is also in list
							reset($subscribed);
							while(list($k, $path) = each($subscribed))
							{
								// Make sure every folder in path to subscribed folder is also subscribed.
								$original_path = $path;
								while(false !== ($pos = strrpos($path, $delim)))
								{
									$container = substr($path, 0, $pos);
									if(!in_array($container, $subscribed))
									{
										$v_sub[] = $container;
									}
									$path = substr($path, 0, $pos);
								}
								
								// Make sure all folder at same level as subscribed folders are subscribed
								$path = $original_path;
								if(false !== ($pos = strrpos($path, $delim)))
								{
									$container = substr($path, 0, $pos);
									if(!$checked_container[$container])
									{
										reset($folders);
										while(list($k2, $folder) = each($folders))
										{
											// Is "folder" inside "container"?
											$pos = strpos($folder, $container);
											if(($pos !== false) && ($pos == 0))
											{
												// Is $folder immediately inside $container, or further down?
												$pos = strrpos($folder, $delim);
												if($pos <= strlen($container.$delim))
												{
													if(!InArray($subscribed, $folder))
													{
														//*gasp*!  $folder is not subscribed!
														$v_sub[] = $folder;
													}
												}
											}
										}
										$checked_container[$container] = 1;
									}
								}
							}
						}
				
				
						if(is_array($v_sub))
						{
							while(list($k, $v) = each($v_sub))
							{
								if(!in_array($v, $subscribed))
								{
									$subscribed[] = $v;
								}
							}
						}
				
						if(is_array($subscribed))
						{
							sort($subscribed);
							reset($subscribed);
						}
						
						natcasesort($folders);
						$c = sizeof($folders);
						echo "<NOBR>";
				
						// Show default folders (i.e. Inbox, Sent, Trash)
						$unseen_str = "";
						
						$defaults["INBOX"]        = "Inbox";
						$defaults["INBOX.Unsent"] = "Unsent";
						$defaults["INBOX.Drafts"] = "Drafts";
						$defaults["INBOX.Sent"]   = "Sent";
						$defaults["INBOX.Quarantine"]  = "Quarantine";
						$defaults["INBOX.Trash"]  = "Trash";
				
						reset($defaults);
						?>
						<table border="0" cellpadding="0" cellspacing="3" class="folderList">
							<?
							while(list($key, $value) = each($defaults))
							{
								if( ($value != ".") && (!empty($key)) )
								{
									if($my_prefs["showNumUnread"])
									{
										$num_unseen = iil_C_CountUnseen($conn, $key);
										$unseen_str = "";
										if($num_unseen > 0)
										{
											$unseen_str = "&nbsp;(".$num_unseen.")";
										}
									}
									?>
									<tr>
										<td valign="bottom">
											<a href="main.php?folder=<? echo $key ."&user=". $user; ?>" target="list2">
											<img src="<? echo $THEME . strtolower($value); ?>.gif" width="16" height="14" border="0"></a>
										</td>
										<td valign="middle">
											<a href="main.php?folder=<? echo $key ."&user=". $user; ?>" target="list2"><? echo $value; ?></a>
											<?
												echo $unseen_str;
												if(strstr($key, "Trash"))
												{
													echo "&nbsp;[<a href=\"folders.php?user=" . $user . "&empty_trash=1\">Empty</a>]";
												}
											?>
										</td>
									</tr>
									<?
								}
							}
				
							if($my_prefs["tmda"] && $TMDA_ENABLED)	// Use TMDA
							{
									if($my_prefs["showNumUnread"])
									{
										$runcmd = sprintf("sudo %s -c %s -bsA", "/usr/bin/tmda-pending", $USER_BASE_DIR.".tmda/config");
										$retval = exec($runcmd, $msg_list);

										$msg_count = 0;
										for($i = 0; $i < count($msg_list); $i++)
										{
											if(strstr($msg_list[$i], ".msg"))	
											{
												$msg_count++;			// Next Message
											}
										}

										$unseen_str = "";
										if($msg_count > 0)
										{
											$unseen_str = "&nbsp;(".$msg_count.")";
										}
									}
									
								?>
								<tr>
									<td valign="bottom">
										<a href="main_pending.php?&user=<?=$user?>" target="list2"><img src="<? echo $THEME; ?>tmda.gif" width="16" height="14" border="0"></a>
									</td>
									<td valign="middle">
										<a href="main_pending.php?&user=<?=$user?>" target="list2">Pending</a><? echo $unseen_str; ?>
									</td>
								</tr>
								<?
							}			
							?>
						</table>
						<br>
						<?
				
						// Indent according to depth
						$result = array();
						reset($folders);
						while(list($k, $path) = each($folders))
						{
							// We're only going to display folders that are in...
							// Root level, subscribed, or in "INBOX"
							if( ($folder_container[$path] == $ROOTDIR) || (InArray($subscribed, $path)) )
							{
								
								$a = explode($delim, $path);
								$c = count($a);
								$folder = $a[$c - 1];
								if(strcmp($a[0], $ROOTDIR)==0) $c--;
								if(($path[0]!=".") && ($folder[0]!="."))
								{
									for($i = 0; $i < ($c - 1); $i++) 
									{
										$indent[$path] .= "&nbsp;&nbsp;";
									}
									$result[$path] = $folder;
								}
							}
						}
				
						flush();
					
						$blank_img  = '<img src="' . $THEME . 'blank.gif" width="16" height="14" border="0">';
						$open_img   = '<img src="' . $THEME . 'folder_open.gif" width="16" height="14" border="0">';
						$close_img  = '<img src="' . $THEME . 'folder_close.gif" width="16" height="14" border="0">';
				
						// Display folders
						reset($result);
				
						?>
						<table border="0" cellpadding="0" cellspacing="3" class="folderList">
							<tr>
								<td>
									<?
									while(list($path, $display) = each($result))
									{
										if( (!empty($display)) && (($containers[$path]) || (empty($defaults[$path]))) )
										{
											$key = $path;
											if($containers[$path])
											{
												$is_sub  = ChildInArray($subscribed, $path.$delim);
												$button  = "<a href=\"folders.php?user=$user&".($is_sub?"unsubscribe":"subscribe")."=1&folder=".urlencode($path)."\" target=\"list1\">";
												$button .= ($is_sub?"$open_img":"$close_img") . "</a>";
											}
											else
											{
												$button = $blank_img;
											}
											echo "<span style=\"font-size: ".$my_colors["font_size"]."; color: ".$my_colors["folder_bg"]."\"><tt>".$indent[$key]."</tt></span>";
											echo $button;
											
											$unseen_str="";
											if($my_prefs["showNumUnread"])
											{
												$num_unseen = iil_C_CountUnseen($conn, $path);
												if($num_unseen > 0) $unseen_str = "&nbsp;(".$num_unseen.")";
											}
											
											$path    = stripslashes($path);
											$display = stripslashes($display);
											$path    = urlencode($path);
											echo " <a href=\"main.php?folder=$path&user=".$user."\" target=\"list2\">".$display.$unseen_str."</a><BR>\n";
											flush();
										}
									}
									?>
								</td>
							</tr>
						</table>
					<?	
			
					}
					iil_Close($conn);
				}
				?>
			</td>
		</tr>
	</table>
	</body>
	</html>
<?
}
?>