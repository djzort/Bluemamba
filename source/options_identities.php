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
              
    Document: source/options.php
              
    Function: Create/edit/delete identities

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");	
include("../include/identities.php");
include("../include/data_manager.php");

include("../include/options_menu.php");

		
// Authenticate
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if($conn)
{
	$mailboxes = iil_C_ListSubscribed($conn, $ROOTDIR, "*");
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
if($dm->initialize($loginID, $host, $DB_IDENTITIES_TABLE, $DB_TYPE))
{
	// Null
}
else
{
	echo "Data Manager initialization failed:<br>\n";
	$dm->showError();
}


if(isset($add))
{
	if( (empty($new_name)) || (empty($new_email)) ) 
	{
		$error .= "Name or email required";
	}
	else
	{
		$new_email = eregi_replace('[^a-zA-Z0-9@_.-]', '', $new_email);
		$new_replyto = eregi_replace('[^a-zA-Z0-9@_.-]', '', $new_replyto);
		
		$new_ident["name"]    = $new_name;
		$new_ident["email"]   = $new_email;
		$new_ident["replyto"] = $new_replyto;
		$new_ident["sig"]     = $new_sig;
		
		$dm->insert($new_ident);
		
		if($new_default)
		{
			$my_prefs["user_name"]     = $new_name;
			$my_prefs["email_address"] = $new_email;
			$my_prefs["signature1"]    = $new_sig;
			include("../include/save_prefs.php");
		}
		
		$new_name = $new_email = $new_replyto = $new_sig = "";
	}
}

if(isset($save) && ($edit > 0))
{
	// Update
	$edit_email = eregi_replace('[^a-zA-Z0-9@_.-]', '', $edit_email);
	$edit_replyto = eregi_replace('[^a-zA-Z0-9@_.-]', '', $edit_replyto);
	
	$new_ident["name"]    = $edit_name;
	$new_ident["email"]   = $edit_email;
	$new_ident["replyto"] = $edit_replyto;
	$new_ident["sig"]     = $edit_sig;
		
	$dm->update($edit, $new_ident);
	
	if($new_default)
	{
		$my_prefs["user_name"]     = $edit_name;
		$my_prefs["email_address"] = $edit_email;
		$my_prefs["signature1"]    = $edit_sig;
		include("../include/save_prefs.php");
	}

	$edit = 0;
}

if(isset($delete) && ($edit > 0))
{
	// Delete
	if($dm->delete($edit))
	{
		$edit = 0;
		
		// If default, clear it
		if(strcmp($edit_name, $my_prefs["user_name"])==0
			&& strcmp($edit_email, $my_prefs["email_address"])==0
			&& strcmp($edit_sig, $my_prefs["signature1"])==0
			)
		{
			$my_prefs["user_name"]     = "";
			$my_prefs["email_address"] = "";
			$my_prefs["signature1"]    = "";
			include("../include/save_prefs.php");				
		}
		
	}
	else 
	{
		$error .= "Deletion failed<br>\n";
	}
}

if($my_prefs["compose_inside"])
{
	$target = "list2";
}
else
{
	$target = "_blank";
}

$identities_a = $dm->read();

$error .= $dm->error;

?>
<br>
<table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
	<tr valign="top">
		<td>
			<?
			
			echo "<center>\n";
			if(empty($edit))
			{
				?>
				<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
					<tr>
						<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
							&nbsp; <span class="tblheader">Identities</span>
							&nbsp;&nbsp;<a href="options_identities.php?user=<?=$user?>&edit=-1" class="mainHeadingSmall">Add Identity</a>
						</td>
					</tr>
				</table>
	
				<font color="red"><? echo $error; ?></font>

				<br>
				
				<?
				if((is_array($identities_a) && count($identities_a) > 0))
				{
					?>
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr class="tblheader" valign="top">
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>"></td>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">&nbsp; Name</td>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">&nbsp; Email</td>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">&nbsp; Reply To</td>
						</tr>
						<?
	
						reset($identities_a);
						while(list($k, $v) = each($identities_a) )
						{
							$v = $identities_a[$k];
							if($my_prefs["user_name"]      == $v["name"] 
							 && $my_prefs["email_address"] == $v["email"] 
							 && $my_prefs["signature1"]    == $v["sig"])
							{
								$v["default"] = true;
							}
					
							?>
							<tr bgcolor="<?=$my_colors["main_bg"]?>">
								<td valign="middle">
									<a href="options_identities.php?user=<?=$user?>&edit=<? echo $v["id"]; ?>">Edit</a>
								</td>
								<td valign="middle">
									<nobr><a href="compose.php?user=<?=$user?>&sender_identity_id=<? echo $v["id"]; ?>" target="<? echo $target; ?>"><? echo $v["name"]." ".($v["default"]?"(Default)":""); ?></a></nobr>
								</td>
								<td valign="middle"><? echo $v["email"];   ?></td>
								<td valign="middle"><? echo $v["replyto"]; ?></td>
							</tr>
							<tr bgcolor="<?=$my_colors["main_bg"]?>">
								<td colspan="4" valign="middle"><pre><? echo stripslashes($v["sig"]); ?></pre></td>
							</tr>
							<tr>
								<td colspan="4" valign="middle" height="5"></td>
							</tr>
							<?
						}
						?>
					</table>
					<?
				}
				else
				{
					?>
					<table border="0" cellspacing="1" cellpadding="10" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>" align="center">
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
								Your Identities list is empty. 
								<a href="options_identities.php?user=<?=$sid?>&edit=-1" class="mainLight">Add Identity</a>
							</td>
						</tr>
					</table>
					<?
				}

			}
			
			if($edit > 0)
			{
				reset($identities_a);
				while(list($k,$foo) = each($identities_a))
				{
					if($identities_a[$k]["id"] == $edit)
					{
						$v = $identities_a[$k];
					}
				}
				
				if($my_prefs["user_name"] == $v["name"] && $my_prefs["email_address"] == $v["email"] && $my_prefs["signature1"] == $v["sig"])
				{
					$v["default"] = true;
				}
				?>
				<form method="post" action="options_identities.php">
					<input type="hidden" name="user" value="<? echo $user ?>">
					<input type="hidden" name="edit" value="<? echo $edit ?>">
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
								&nbsp; <span class="tblheader">Edit Identity</span>
								&nbsp;&nbsp;<a href="options_identities.php?user=<?=$sid?>" class="mainHeadingSmall">List Identities</a>
							</td>
						</tr>
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
				
								<table>
									<tr>
										<td align="left">
										
											<table>
												<tr>
													<td align="right">Name:</td>
													<td><input type="text" name="edit_name" class="textbox" value="<? echo $v["name"]?>" size="45"></td>
												</tr>
												<tr>
													<td align="right">Email:</td>
													<td><input type="text" name="edit_email" class="textbox" value="<? echo $v["email"] ?>" size="45"></td>
												</tr>
												<tr>
													<td align="right">Reply To:</td>
													<td><input type="text" name="edit_replyto" class="textbox" value="<? echo $v["replyto"] ?>" size="45"></td>
												</tr>
											</table>
							
											<table>
												<tr>
													<td><textarea name="edit_sig" class="textbox" cols="74" rows="5"><? echo htmlspecialchars(stripslashes($v["sig"]))?></textarea></td>
												</tr>
												<tr>
													<td><input type="checkbox" name="new_default" value="1" <? echo ($v["default"]?"checked":"") ?>> Set to default</td>
												</tr>
											</table>
							
											<table width="100%">
												<tr>
													<td align="left"><input type="submit" name="save" value="Save Identity"></td>
													<td align="right"><input type="submit" name="delete" value="Delete Identity"></td>
												</tr>
											</table>
				
										</td>
									</tr>
								</table>

							</td>
						</tr>
					</table>
				</form>
				<?
			}
			
			if($edit == -1)
			{
				?>
				<form method="post" action="options_identities.php">
					<input type="hidden" name="user" value="<?=$user?>">
					<input type="hidden" name="session" value="<?=$user?>">
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
								&nbsp; <span class="tblheader">Add Identity</span>
								&nbsp;&nbsp;<a href="options_identities.php?user=<?=$sid?>" class="mainHeadingSmall">List Identities</a>
							</td>
						</tr>
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
				
								<table>
									<tr>
										<td align="left">
				
											<table>
												<tr>
													<td align="right">Name:</td>
													<td><input type="text" name="new_name" class="textbox" value="<? echo stripslashes($new_name)?>" size="45"></td>
												</tr>
												<tr>
													<td align="right">Email:</td>
													<td><input type="text" name="new_email" class="textbox" value="<? echo $new_email; ?>" size="45"></td>
												</tr>
												<tr>
													<td align="right">Reply To:</td>
													<td><input type="text" name="new_replyto" class="textbox" value="<? echo $new_replyto; ?>" size="45"></td>
												</tr>
											</table>
											
											<table>
												<tr>
													<td><textarea name="new_sig" class="textbox" cols="74" rows="5"><? echo htmlspecialchars(stripslashes($new_sig))?></textarea></td>
												</tr>
												<tr>
													<td><input type="checkbox" name="new_default" value="1"> Set to default</td>
												</tr>
											</table>
							
											<table width="100%">
												<tr>
													<td align="left"><input type="submit" name="add" value="Add Identity"></td>
												</tr>
											</table>
				
										</td>
									</tr>
								</table>
				
								
							</td>
						</tr>
					</table>
				</form>
				<?
				}
			?>

		</td>
	</tr>
</table>
</body>
</html>