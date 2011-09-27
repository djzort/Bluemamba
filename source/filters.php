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
              
    Document: source/filters.php
              
    Function: Filter Editor

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../include/data_manager.php");
include("../include/filters.php");

include("../include/filters_menu.php");


// Authenticate
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if($conn)
{
	$mailboxes = iil_C_ListSubscribed($conn, "INBOX", "*");
	sort($mailboxes);
	iil_Close($conn);
}
else
{
	echo "Authentication failed.";
	echo "</body></html>\n";
	exit;
}


// Enable / Deenable
if($enable == 1)
{
	$my_prefs['filters'] = "1";
}
elseif($enable == -1)
{
	$my_prefs['filters'] = "0";
}
if(isset($enable))
{
	include_once("../include/save_prefs.php");
	// Refresh Folders List
	echo "\n<script language=\"JavaScript\">\n";
	echo "parent.list1.location=\"folders.php?user=" . $user . "\";\n";
	echo "</script>\n";
}

?>
<br>
<table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
	<tr valign="top">
		<td>
			<?
			if($my_prefs["filters"] == 1)	// Use Filters
			{

				// Open DM connection
				$dm = new DataManager_obj;
				if(!$dm->initialize($loginID, $host, $DB_FILTERS_TABLE, $DB_TYPE))
				{
					echo "Data Manager initialization failed:<br>\n";
					$dm->showError();
				}

				// Add Bookmark
				if(isset($add))
				{
					if( (empty($add_syntax)) || (empty($add_moveto)) )
					{
						$error .= "Search for and move to required";
					}
					else
					{
						$new_entry = array();
						$new_entry["syntax"] = $add_syntax;
						$new_entry["type"]   = $add_type;
						$new_entry["moveto"] = $add_moveto;

						if ($dm->insert($new_entry)) echo "<!-- Inserted //-->";
						else echo "<!-- Not inserted //-->";

						$new_syntax = $new_type = $new_moveto = "";
					}
				}
				
				
				// Edit Bookmark
				if(isset($save) && ($edit > 0))
				{
					if( empty($edit_syntax) )
					{
						$error .= "Search for required";
					}
					else
					{
						$edit_entry = array();
						$edit_entry["syntax"] = $edit_syntax;
						$edit_entry["type"]   = $edit_type;
						$edit_entry["moveto"] = $edit_moveto;
							
						$dm->update($edit, $edit_entry);
						
						$edit = 0;
					}
				}
				
				
				// Delete Bookmark
				if(isset($delete) && ($edit > 0))
				{
					if($dm->delete($edit))
					{
						$edit = 0;
					}
					else
					{
						$error .= "Deletion failed<br>\n";
					}
				}
				
				
				// Get sorted list of filters
				$types  = $dm->sort("type", "ASC");
				
				// Get groups and form <option> list
				$groups = $dm->getDistinct("type", "ASC");
				
				$error .= $dm->error;

			
				// List filters
				if(empty($edit))
				{
					?>
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
								&nbsp; <span class="tblheader">Basic Filters</span>&nbsp;&nbsp;
								<a href="filters.php?user=<?=$user?>&edit=-1" class="mainHeadingSmall">Add Filter</a>
								<a href="filters.php?user=<?=$user?>&enable=-1" class="mainHeadingSmall">Disable</a>
							</td>
						</tr>
					</table>
			
					<?
					if($error)
					{
						echo '<br> <br> <font color="#FF0000">' . $error .'</font> <br> ';
					}
					?>
					<br>
					
					<?
					if((is_array($types) && count($types) > 0))
					{
						$prev_type = "";
						reset($types);
						?>
						<table border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
							<tr bgcolor="<?=$my_colors["main_head_bg"] ?>" colspan="2" class="tblheader">
								<td valign="middle">&nbsp; Search For</td>
								<td valign="middle">&nbsp; Move Message To</td>
							</tr>
							<?
							while(list($key, $val) = each($types))
							{
								$val = $types[$key];
								if($val["type"] != $prev_type)
								{
									?>
									<tr>
										<td colspan="2">&nbsp; <b><? echo $filter_types[$val["type"]]; ?></b></td>
									</tr>
									<?
									$prev_type = $val["type"];
								}
								$val["moveto"] = cleanfolder($val["moveto"]);
								
								?>
								<tr bgcolor="<?=$my_colors["main_bg"]?>">
									<td valign="middle">&nbsp; <a href="filters.php?user=<?=$user?>&edit=<? echo $val["id"]; ?>"><? echo $val["syntax"]; ?></a></td>
									<td valign="middle">&nbsp; <a href="filters.php?user=<?=$user?>&edit=<? echo $val["id"]; ?>"><? echo $val["moveto"]; ?></a></td>
								</tr>
								<?
							}
							?>
						</table>
						
						<br> <br>
						
						<table border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
									Click &quot;Search For&quot; or &quot;Move Message To&quot; to edit filter.
								</td>
							</tr>
						</table>
						<?
					}
					else
					{
						?>
						<table width="100%" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
									Your Filters list is empty. <a href="filters.php?user=<?=$sid?>&edit=-1" class="mainLight">Add Filter</a>
								</td>
							</tr>
						</table>
						<?
					}
					
				}
				
				
				// Show Edit Form
				if($edit > 0)
				{
					reset($types);
					while(list($key, $g) = each($types))
					{
						if($types[$key]["id"] == $edit)
						{
							$val = $types[$key];
						}
					}
					?>
					<form method="post" action="<? echo "filters.php?user=".$user; ?>" style="display:inline">
						<input type="hidden" name="user" value="<? echo $user ?>">
						<input type="hidden" name="edit" value="<? echo $edit ?>">
						<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
							<tr>
								<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
									&nbsp; <span class="tblheader">Edit Filter</span>
									&nbsp;&nbsp;<a href="filters.php?user=<?=$sid?>" class="mainHeadingSmall">List Filters</a>
								</td>
							</tr>
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
								
									<table>
										<tr>
											<td align="right">Search for</td>
											<td colspan="3">
												<input type="text" name="edit_syntax" class="textbox" value="<? echo $val["syntax"]; ?>" size="60">
											</td>
										</tr>
										<tr>
											<td align="right">Search within </td>
											<td>
												<select name="edit_type" class="textbox">
												<? 
													while(list($key, $type) = each($filter_types))
													{
														echo '<option value="'. $key .'"'. ($key == $val["type"] ? " SELECTED":"").'>' . $type;
													}
												?>
												</select>
											</td>
					
											<td align="right">If found move to</td>
											<td>
												<select name="edit_moveto" class="textbox">
												<? 
													while(list($key, $box_name) = each($mailboxes))
													{
														if(issysfolder($box_name) == false)
														{
															echo '<option value="'. $box_name .'"'. ($box_name == $val["moveto"] ? " SELECTED":"").'>' . cleanfolder($box_name);
														}
													}
												?>
												</select>
											</td>
										</tr>
									</table>
									<input type="submit" name="save" value="Save Filter">
									<input type="submit" name="delete" value="Delete Filter">
					
								</td>
							</tr>
						</table>
					</form>
					<?
				}
			
			
				// Show Add Form
				if($edit == -1)
				{
					?>
					<form method="post" action="<? echo "filters.php?user=".$user; ?>" style="display:inline">
						<input type="hidden" name="user" value="<?=$user?>">
						<input type="hidden" name="session" value="<?=$user?>">
						<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
							<tr>
								<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
									&nbsp; <span class="tblheader">Add Filter</span>
									&nbsp;&nbsp;<a href="filters.php?user=<?=$sid?>" class="mainHeadingSmall">List Filters</a>
								</td>
							</tr>
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
					
									<table>
										<tr>
											<td align="right">Search for</td>
											<td colspan="3">
												<input type="text" name="add_syntax" class="textbox" size="60">
											</td>
										</tr>
										<tr>
											<td align="right">Search within </td>
											<td>
												<select name="add_type" class="textbox">
												<? 
													while(list($key, $type) = each($filter_types))
													{
														echo '<option value="'. $key .'">' . $type;
													}
												?>
												</select>
											</td>
					
											<td align="right">If found move to</td>
											<td>
												<select name="add_moveto" class="textbox">
												<? 
													while(list($key, $box_name) = each($mailboxes))
													{
														if((issysfolder($box_name) == false) || ($box_name == "INBOX.Trash"))
														{
															echo '<option value="'. $box_name .'">' . cleanfolder($box_name);
														}
													}
												?>
												</select>
											</td>
										</tr>
									</table>
									<input type="submit" name="add" value="Add Filter">
					
								</td>
							</tr>
						</table>
					</form>		
				<?
				}
				
				
				if(isset($edit))
				{
					?>
					<br> <br>
					<table border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
								Please keep in mind, the more filters you have the slower your email will become.
								<br>
								Also, be careful what you type. If you add a filter for 'the' and any incoming email that contains it will be filtered.
							</td>
						</tr>
					</table>
					<?
				}

			}
			else	// No Basic Filters
			{
				?>
				<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
					<tr>
						<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
							&nbsp; <span class="tblheader">Basic Filters</span>
							&nbsp;&nbsp;<a href="filters.php?user=<?=$user?>&enable=1" class="mainHeadingSmall">Enable</a>
						</td>
					</tr>
				</table>
			
				<br>
			
				<table width="100%" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
					<tr>
						<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
							Basic Filters are not enabled. <a href="filters.php?user=<?=$user?>&enable=1" class="mainLight">Click here to enable</a>.
						</td>
					</tr>
				</table>
				<?
			}
			?>
		</td>
	</tr>
</table>
<br>
</body>
</html>