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
              
    Document: source/bookmarks.php
              
    Function: Bookmarks

*********************************************************************/

	include("../include/super2global.php");
	include("../include/header_main.php");
	include("../include/icl.php");
	include("../include/data_manager.php");    
	
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
	
	// Open DM connection
	$dm = new DataManager_obj;
	if(!$dm->initialize($loginID, $host, $DB_BOOKMARKS_TABLE, $DB_TYPE))
	{
		echo "Data Manager initialization failed:<br>\n";
		$dm->showError();
	}

	// Add Bookmark
	if(isset($add))
	{
		if( (empty($new_name)) || (empty($new_url)) )
		{
			$error .= "Name or URL required";
		}
		else
		{
			if(!ereg("[fht]+tp[s]*://", $new_url)) 
			{
				$new_url = "http://".$new_url;
			}
			$new_entry = array();
			$new_entry["name"] = $new_name;
			$new_entry["url"] = $new_url;
			$new_entry["grp"] = (empty($new_grp)?$new_grp_other:$new_grp);
			$new_entry["comments"] = $new_comments;
			$new_entry["is_private"] = $new_private;
						
			if($dm->insert($new_entry)) echo "<!-- Inserted //-->";
			else echo "<!-- Not inserted //-->";
			
			$new_name = $new_url = $new_grp = $new_comments = $new_private = $new_grp_other = "";
		}
	}
	
	
	// Edit Bookmark
	if(isset($save) && ($edit > 0))
	{
		if(!ereg("[fht]+tp[s]*://", $edit_url)) 
		{
			$edit_url = "http://".$edit_url;
		}

		$new_entry["name"] = $edit_name;
		$new_entry["url"] = $edit_url;
		$new_entry["grp"] = (empty($edit_grp)?$edit_grp_other:$edit_grp);
		$new_entry["comments"] = $edit_comments;
		$new_entry["is_private"] = $edit_private;
			
		$dm->update($edit, $new_entry);
		
		$edit = 0;
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
	
	// Get sorted list of bookmarks
	$urls_a = $dm->sort("grp", "ASC");
	
	// Get groups and form <option> list
	$groups = $dm->getDistinct("grp", "ASC");
	
	$error .= $dm->error;
	

	// List Bookmarks
	if(empty($edit))
	{
		?>
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr>
				<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
					&nbsp; <span class="tblheader">Bookmarks</span>
					&nbsp;&nbsp;<a href="bookmarks.php?user=<?=$user?>&edit=-1" class="mainHeadingSmall">Add Bookmark</a>
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
		if((is_array($urls_a) && count($urls_a)>0))
		{
			$prev_cat = "";
			reset($urls_a);
			?>
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
				<?
				while(list($k, $v) = each($urls_a))
				{
					$v = $urls_a[$k];
					if($v["grp"] != $prev_cat)
					{
						echo '<tr>';
						echo '<td bgcolor="'.$my_colors["main_head_bg"].'" colspan="4" class="tblheader">&nbsp; '.$v["grp"].'</td>';
						echo '</tr>';
						$prev_cat = $v["grp"];
					}
					?>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td>&nbsp; <a href="bookmarks.php?user=<?=$user?>&edit=<? echo $v["id"]; ?>"><? echo $v["name"]; ?></a></td>
						<td>&nbsp; <a href="<? echo $v["url"]; ?>" target="_blank"><? echo $v["url"]; ?></a></td>
					</tr>
					<?
					if($v["comments"])
					{
						?>
						<tr bgcolor="<?=$my_colors["main_bg"]?>">
							<td colspan='2'>&nbsp; <? echo $v["comments"]; ?></td>
						</tr>
						<?
					}
					?>
					<tr>
						<td colspan="2" height="5"></td>
					</tr>
					<?
				}
				?>
			</table>
			
			<br> <br>
	
			<table border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						Click on name to edit bookmark. Click on the URL, or link, to visit the website
					</td>
				</tr>
			</table>
			<?
		}
		else
		{
			?>
			<table border="0" cellspacing="1" cellpadding="10" width="95%" bgcolor="<?=$my_colors["main_hilite"]?>" align="center">
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						Your Bookmarks list is empty. 
						<a href="bookmarks.php?user=<?=$sid?>&edit=-1" class="mainLight">Add Bookmark</a>
					</td>
				</tr>
			</table>
			<?
		}		
	}


	// Show Edit Form
	if($edit > 0)
	{
		reset($urls_a);
		while(list($k, $foo) = each($urls_a))
		{
			if($urls_a[$k]["id"] == $edit)
			{
				$v = $urls_a[$k];
			}
		}
		?>
		<form method="post" action="<? echo $_SERVER['PHP_SELF']."?user=".$user; ?>" style="display:inline">
			<input type="hidden" name="user" value="<?=$user?>">
			<input type="hidden" name="edit" value="<? echo $edit; ?>">

			<table border="0" cellspacing="1" cellpadding="3" width="100%">
				<tr>
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
						&nbsp; <span class="tblheader">Edit Bookmark</span>
						&nbsp;&nbsp;<a href="bookmarks.php?user=<?=$user?>" class="mainHeadingSmall">List Bookmarks</a>
					</td>
				</tr>
			</table>

			<br>

			<table border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
					
						<table border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td align="center" colspan="2">
								
									<table border="0" cellspacing="0" cellpadding="2">
										<tr>
											<td align="right">Name:</td>
											<td>
												<input type="text" name="edit_name" class="textbox" value="<? echo $v["name"]; ?>" size="25">
												Category:
												<select name="edit_grp" class="textbox">
												<? 
													echo "<option value=\"\">Specify-&gt \n";
													if(is_array($groups) && count($groups)>0)
													{
														while(list($k, $grp) = each($groups))
														{
															echo "<option value=\"$grp\" ".($grp==$v["grp"]?"SELECTED":"").">$grp\n";
														}
													}
												?>
												</select>
												<input type="text" name="edit_grp_other" class="textbox" value="" size="15">
											</td>
										</tr>
										<tr>
											<td align="right">URL:</td>
											<td><input type="text" name="edit_url" class="textbox" value="<? echo $v["url"]; ?>" size="60"></td>
										</tr>
										<tr>
											<td align="right">Comments:</td>
											<td>
											<input type="text" name="edit_comments" class="textbox" value="<? echo htmlspecialchars(stripslashes($v["comments"])); ?>" size="60">
											</td>
										</tr>
									</table>
								
								</td>
							</tr>
							<tr>
								<td align="left"><input type="submit" name="save" value="Edit Bookmark"></td>
								<td align="right"><input type="submit" name="delete" value="Delete Bookmark"></td>
							</tr>
						</table>
						
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
		<form method="post" action="<? echo $_SERVER['PHP_SELF']."?user=".$user; ?>" style="display:inline">
			<input type="hidden" name="user" value="<?=$user?>">
			<input type="hidden" name="session" value="<?=$user?>">

			<table border="0" cellspacing="1" cellpadding="3" width="100%">
				<tr>
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
						&nbsp; <span class="tblheader">Add Bookmark</span>
						&nbsp;&nbsp;<a href="bookmarks.php?user=<?=$user?>" class="mainHeadingSmall">List Bookmarks</a>
					</td>
				</tr>
			</table>

			<br>

			<table border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
					
						<table border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td align="center">
								
									<table border="0" cellspacing="0" cellpadding="2">
										<tr>
											<td align="right">Name:</td>
											<td>
												<input type="text" name="new_name" class="textbox" value="<? echo htmlspecialchars(stripslashes($new_name)); ?>" size="25">
												Category
												<select name="new_grp" class="textbox">
												<?
													echo "<option value=\"\">Specify-&gt; \n";
													if(is_array($groups) && count($groups) > 0)
													{
														while(list($k, $v) = each($groups))
														{
															echo "<option value=\"$v\">$v\n";
														}
													}
												?>
												</select>
												<input type="text" name="new_grp_other" class="textbox" value="<? echo $new_grp_other; ?>" size="15">
											</td>
										</tr>
										<tr>
											<td align="right">URL:</td>
											<td><input type="text" name="new_url" class="textbox" value="<? echo $new_url; ?>" size="60"></td>
										</tr>
										<tr>
											<td align="right">Comments:</td>
											<td><input type="text" name="new_comments" class="textbox" value="<? echo htmlspecialchars(stripslashes($new_comments))?>" size="60"></td>
										</tr>
									</table>

								</td>
							</tr>
							<tr>
								<td align="left"><input type="submit" name="add" value="Add Bookmark"></td>
							</tr>
						</table>
						
					</td>
				</tr>
			</table>
		</form>		
		<?
	}
?>

<br>

</body>
</html>