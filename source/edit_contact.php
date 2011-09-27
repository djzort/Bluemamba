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
              
    Document: source/edit_contacts.php
              
    Function: Interface for viewing/adding/updating contacts.

*********************************************************************/


include("../include/super2global.php");
include("../include/header_main.php");
include("../include/contacts_commons.php");
include("../include/data_manager.php");

// Authenticate
include_once("../include/icl.php");
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if($conn)
{
	iil_Close($conn);
}
else
{
	echo "Authentication failed.";
	echo "</html>\n";
	exit;
}

// Open data manager connection
$dm = new DataManager_obj;
if(!$dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $backend))
{
	echo "Data Manager initialization failed:<br>\n";
	$dm->showError();
}

// Get groups
if(!isset($groups))
{
	$contacts = $dm->read();
    $groups = GetGroups($contacts);
}

// If edit mode, fill in default values
if(isset($edit))
{
	if(!isset($contacts))
	{
		$contacts = $dm->read();
	}
	if(is_array($contacts))
	{
		reset($contacts);
		while(list($k, $foobar) = each($contacts))
		{
			if($contacts[$k]["id"] == $edit)
			{
				$name	  = $contacts[$k]["name"];
				$email	  = $contacts[$k]["email"];
				$email2	  = $contacts[$k]["email2"];
				$group	  = $contacts[$k]["grp"];
				$aim	  = $contacts[$k]["aim"];
				$icq	  = $contacts[$k]["icq"];
				$yahoo	  = $contacts[$k]["yahoo"];
				$msn	  = $contacts[$k]["msn"];
				$jabber	  = $contacts[$k]["jabber"];
				$phone	  = $contacts[$k]["phone"];
				$work	  = $contacts[$k]["work"];
				$cell	  = $contacts[$k]["cell"];
				$fax	  = $contacts[$k]["fax"];
				$address  = $contacts[$k]["address"];
				$url	  = $contacts[$k]["url"];
				$comments = $contacts[$k]["comments"];
			}
		}
	}
}
else
{
	$edit = -1;
}

?>

<form action="contacts.php" method="post" style="display:inline">
	<input type="hidden" name="user" value="<?=$user?>">
	<input type="hidden" name="delete_item" value="<? echo $edit; ?>">	
	<input type="hidden" name="edit" value="<? echo $edit; ?>">
	
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
				&nbsp; <span class="tblheader"><? echo ($edit>0?"Edit":"Add"); ?> Contact</span>
				&nbsp;&nbsp;<a href="contacts.php?user=<?=$sid?>" class="mainHeadingSmall">List Contacts</a>
			</td>
		</tr>
	</table>
	
	<br>
	
	<table border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
			
				<table cellpadding="2" cellspacing="2" border="0">
					<tr>
						<td valign="top">
				
							<table border="0" width="100%" height="100%">
								<tr>
									<td>Name:</td>
									<td><input type="text" name="name" class="textbox" value="<? echo $name; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Email:</td>   
									<td><input type="text" name="email" class="textbox" value="<? echo $email; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Alt. Email:</td>
									<td><input type="text" name="email2" class="textbox" value="<? echo $email2; ?>" size="26"></td>
								<tr>
								</tr>
									<td>URL:</td>
									<td><input type="text" name="url" class="textbox" value="<? echo $url; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Home Phone:</td>
									<td><input type="text" name="phone" class="textbox" value="<? echo $phone; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Work Phone:</td>
									<td><input type="text" name="work" class="textbox" value="<? echo $work; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Cell Phone:</td>
									<td><input type="text" name="cell" class="textbox" value="<? echo $cell; ?>" size="26"></td>
								</tr>
								<tr>
									<td>Fax:</td>
									<td><input type="text" name="fax" class="textbox" value="<? echo $fax; ?>" size="26"></td>
								</tr>
							</table>
	
				
				
						</td>
						<td valign="top">
				
				
							<table border="0" width="100%" height="100%">
								<tr>
									<td valign="top">
										Group:
										<select name="group" class="textbox">
											<option value="_otr_">Other
											<?
											$groups = base64_decode($groups);
											$groups_a = explode(",", $groups);
											
											if(is_array($groups_a))
											{
												while(list($key, $val) = each($groups_a))
												{
													if(!empty($val)) 
													{ 
														echo "<option " . (strcmp($val,$group)==0?"SELECTED":"") . ">$val\n";
													}
												}
											}
											?>
										</select>
									</td>
									<td align="right">
										<input type="text" name="other_group" class="textbox" value="<? echo $other_group; ?>">
									</td>
								</tr>
								<tr>
									<td colspan="2">

										<br>

										<table border="0">
											<tr>
												<td>AIM:</td>
												<td><input type="text" name="aim" class="textbox" value="<? echo $aim; ?>" size="12"></td>
												<td>&nbsp; Yahoo:</td>   
												<td><input type="text" name="yahoo" class="textbox" value="<? echo $yahoo; ?>" size="12"></td>
											</tr>
											<tr>
												<td>ICQ:</td>   
												<td><input type="text" name="icq" class="textbox" value="<? echo $icq; ?>" size="12"></td>
												<td>&nbsp; MSN:</td>   
												<td><input type="text" name="msn" class="textbox" value="<? echo $msn; ?>" size="12"></td>
											</tr>
											<tr>
												<td colspan="4">Jabber: <input type="text" name="jabber" class="textbox" value="<? echo $jabber; ?>" size="20"></td>   
											</tr>
										</table>
				
									</td>
								</tr>
							</table>
				
				
						</td>
					</tr>
					<tr>
						<td valign="bottom">
							Comments:<br>
							<textarea name="comments" rows="6" cols="45"><? echo $comments; ?></textarea>
						</td>
						<td valign="bottom">
							Address:<br>
							<textarea name="address" rows="6" cols="45"><? echo $address;?></textarea>
						</td>
					</tr>


					<tr>
						<td align="left"><input type="submit" name="add" value="<? echo ($edit>0?"Save":"Add"); ?> Contact"></td>
						<td align="right"><? if($edit>0){echo '<input type="submit" name="remove" value="Delete Contact">';} ?></td>
					</tr>
				</table>
	
			</td>
		</tr>
	</table>
</form>
</body>
</html>