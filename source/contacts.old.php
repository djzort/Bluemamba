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
              
    Document: source/contacts.php
              
    Function: List basic information of all contacts. 
              Offer links to
                 - view/edit contact
                 - send email to contact
                 - add new contact
              Process posted data to edit/add/remove contacts information              

*********************************************************************/


function FormatHeaderLink($user, $label, $color, $new_sort_field, $sort_field, $sort_order)
{
	if(strcasecmp($new_sort_field, $sort_field) == 0)
	{
		if(strcasecmp($sort_order, "ASC") == 0)
		{
			$sort_order = "DESC";
		}
		else
		{
			$sort_order = "ASC";
		}
	}
	$link  = "<a href=\"contacts.php?user=$user&sort_field=$new_sort_field&sort_order=$sort_order\" class=\"mainHeading\">";
	$link .= "<b>".$label."</b></a>";
	return $link;
}

include("../include/super2global.php");
include("../include/contacts_commons.php");
include_once("../include/data_manager.php");
if(isset($user))
{
	include("../include/header_main.php");

	//authenticate
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

	?>
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
				&nbsp; <span class="tblheader">Contacts</span>
				&nbsp;&nbsp;<a href="edit_contact.php?user=<?=$sid?>&edit=-1" class="mainHeadingSmall">Add Contact</a>
			</td>
		</tr>
	</table>
	
	<br>
	
	<table border="0" cellspacing="0" cellpadding="0" width="95%" align="center">
		<tr>
			<td align="left">
				<?
			
				// Open data manager connection
				$dm = new DataManager_obj;
				if(!$dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $backend))
				{
					echo "Data Manager initialization failed:<br>\n";
					$dm->showError();
				}
				
				// Do add
				if(isset($add))
				{
					// Set group if "other"
					if(strcmp($group,"_otr_") == 0)
					{
						$group = $other_group;
					}
					
					// Create Data Array
					$new_contact_array = array
					(
						"owner"    => $session_dataID,
						"name"     => $name,
						"email"    => $email,
						"email2"   => $email2,
						"grp"      => $group,
						"aim"      => $aim,
						"icq"      => $icq,
						"yahoo"    => $yahoo,
						"msn"      => $msn,
						"jabber"   => $jabber,
						"phone"    => $phone,
						"work"     => $work,
						"cell"     => $cell,
						"fax"      => $fax,
						"address"  => $address,
						"url"      => $url,
						"comments" => $comments
					);
					
					if($edit <= 0)	// if not edit (i.e. new), do an insert
					{
						if(!$dm->insert($new_contact_array))
						{
							echo "Insert failed<br>";
							$dm->showError();
						}
					}
					else			// is edit, do an update
					{
						if(!$dm->update($edit, $new_contact_array))
						{
							echo "update failed<br>";
							$dm->showError();
						}
					}
				}
				else if(isset($delete))
				{							// Delete entry
					$dm->delete($delete_item);
				}
				elseif(isset($remove))
				{							// Confirm removal of entry
					echo "<font color=FF0000>Are you sure you would like to delete entry for ".$name." ?</font>\n";
					echo "[<a href=\"contacts.php?user=$sid&delete=1&delete_item=$delete_item\" class=\"mainLight\">Delete</a>]\n";
					echo "[<a href=\"contacts.php?user=$sid\" class=\"mainLight\">Cancel</a><br> <br> \n";
				}
				
				// Initialize sort fields and order
				if(empty($sort_field)) $sort_field = "name";
				if(empty($sort_order)) $sort_order = "ASC";
				
				// Fetch and sort
				$contacts = $dm->sort($sort_field, $sort_order);
				$numContacts = count($contacts);
			
				// Show error, if any
				if(!empty($error)) echo "<p>".$error."</p>";
				
				
				$groups = GetGroups($contacts);
			
				// Show contacts
				if(is_array($contacts) && count($contacts) > 0)
				{
					reset($contacts);
					$target = ($my_prefs["compose_inside"]?"list2":"_blank");
					?>
					<form method="POST" action="compose.php" target="<?=$target?>" style="display:inline">
						<input type="hidden" name="user" value="<?=$user?>">
						<?
						echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" bgcolor=\"".$my_colors["main_hilite"]."\">\n";
						echo "<tr>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\" class=\"mainHeading\" width=1>&nbsp;<b>To</b>&nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\" class=\"mainHeading\" width=1>&nbsp;<b>CC</b>&nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\" class=\"mainHeading\" width=1>&nbsp;<b>BCC</b>&nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\">&nbsp; " . FormatHeaderLink($user, "Name",  $textc, "name",     $sort_field, $sort_order) . " &nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\">&nbsp; " . FormatHeaderLink($user, "Email", $textc, "email",    $sort_field, $sort_order) . " &nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\">&nbsp; " . FormatHeaderLink($user, "Group", $textc, "grp,name", $sort_field, $sort_order) . " &nbsp;</td>";
						echo "</tr>";
						while(list($k1, $foobar) = each($contacts))
						{
							echo "<tr>\n";
							$a = $contacts[$k1];
							$id = $a["id"];
							$toString = (!empty($a["name"])?"\"".$a["name"]."\" ":"")."<".$a["email"].">";
							$toString = urlencode($toString);
							if(empty($a["name"])) $a["name"]="--";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\"><input type=\"checkbox\" name=\"contact_to[]\" value=\"$toString\"></td>";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\"><input type=\"checkbox\" name=\"contact_cc[]\" value=\"$toString\"></td>";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\"><input type=\"checkbox\" name=\"contact_bcc[]\" value=\"$toString\"></td>";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\">&nbsp; <a href=\"edit_contact.php?user=$sid&k=$k1&edit=$id\">" . $a["name"] . "</a> &nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\">&nbsp; <a href=\"compose.php?user=$sid&to=$toString\" target=$target>" . $a["email"] . "</a> &nbsp;</td>";
							echo "<td bgcolor=\"".$my_colors["main_bg"]."\">&nbsp; ".$a["grp"]."</td>";
							echo "</tr>\n";
						}
						?>
						</table>
						<br>
						<input type="submit" name="contacts_submit" value="Compose">
					</form>
					
					<br> <br>
			
					<table border="0" cellspacing="1" cellpadding="10" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
								Click on name to view or edit contact. Click on the email to compose a message to that contact.<br>
								You can also click the check boxes for more than one of your contacts, click the Compose button, and send a message.
							</td>
						</tr>
					</table>
			
					<?
				}
				else
				{
					?>
					<table border="0" cellspacing="1" cellpadding="10" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
						<tr>
							<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
								Your Contacts list is empty. 
								<a href="edit_contact.php?user=<?=$sid?>&edit=-1" class="mainLight">Add Contact</a>
							</td>
						</tr>
					</table>
					<?
				}
			}
			?>
		</td>
	</tr>
</table>
</body>
</html>