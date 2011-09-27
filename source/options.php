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
              
    Function: Provide interface for setting general options.
              Form posts to index.php (the frame page), changes are 
			  saved to back end, and all frames are reloaded (so 
			  that changes apply to all frames).

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../conf/defaults.php");

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

if($error)
{
	echo '<font color="#FF0000">'. $error .'</font>';
}

?>
<form method="post" action="index.php" target="_top" onSubmit='close_popup(); return true;' style="display:inline">
	<input type="hidden" name="loginID" value="<? echo $loginID; ?>">
	<input type="hidden" name="user" value="<?=$user?>">
	<input type="hidden" name="session" value="<?=$user?>">

<table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td align="right">
			<input type="hidden" name="do_options" value="1">
			<input type="submit" name="apply" value="Apply"> &nbsp; &nbsp; 
			<input type="submit" name="cancel" value="Cancel"> &nbsp; &nbsp; 
			<input type="submit" name="revert" value="Defaults">
		</td>
	</tr>
</table>

<br>

<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr valign="top">
		<td width="50%">

			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%" height="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Default Identity</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="3" width="100%">
							<tr>
								<td>
									Name: <a href="options_identities.php?user=<? echo $user;?>"><? echo ($my_prefs["user_name"]?$my_prefs["user_name"]:"Unspecified"); ?></a>
									<input type=hidden name="user_name" value="<? echo $my_prefs["user_name"]; ?>">
									<br>
									Email: <a href="options_identities.php?user=<? echo $user;?>"><? echo ($my_prefs["email_address"]?$my_prefs["email_address"]:"Unspecified"); ?></a>
									<input type=hidden name="email_address" value="<? echo $my_prefs["email_address"]; ?>">
								</td>
							</tr>
							<tr>
								<td>
									<textarea name="signature1" class="textbox" rows=5 cols=30><? echo $my_prefs["signature1"]; ?></textarea>
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="show_sig1" value="1" <? echo ($my_prefs["show_sig1"]==1?"CHECKED":""); ?>> Show signature by default
								</td>
							</tr>
						</table>
	
					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Read Message Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="1">
							<tr>
								<td>
									<input type="checkbox" name="preview_window" value="1" <? echo ($my_prefs["preview_window"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Read message in a preview window. (Split Windows)
								</td>
							</tr>
							<tr>
								<td colspan="2">

									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td>
												&nbsp; &nbsp;
											</td>
											<td>
												<input type="checkbox" name="view_inside" value="1" <? echo ($my_prefs["view_inside"]==1?"CHECKED":""); ?>>
											</td>
											<td>
												&nbsp;Read message in main window.
											</td>
										</tr>
									</table>

								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="showNav" value="1" <? echo ($my_prefs["showNav"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Show mail folder navigation links.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="nav_no_flag" value="1" <? echo ($my_prefs["nav_no_flag"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Don't mark messages &quot;read&quot; when reading messages.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="html_in_frame" value="1" <? echo ($my_prefs["html_in_frame"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Show HTML messages inline.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="show_images_inline" value="1" <? echo ($my_prefs["show_images_inline"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Show images inline.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="colorize_quotes" value="1" <? echo ($my_prefs["colorize_quotes"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Colorize quotes.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="detect_links" value="1" <? echo ($my_prefs["detect_links"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Convert URLs to links.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="subject_edit" value="1" <? echo ($my_prefs["subject_edit"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Subject editing on message.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="advanced_controls" value="1" <? echo ($my_prefs["advanced_controls"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Advanced controls for Web Mail.
								</td>
							</tr>
						</table>
	
					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Theme Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="3" width="100%">
							<tr>
								<td>
									Theme: <select name="theme">
										<?
										while ( list($k, $v) = each($THEME_LIST) )
										{
											echo "<option value=\"$k\"" . ($k==$my_prefs["theme"]?" SELECTED":"").">$v \n";
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									Play <select name="notify">
										<option value="">No
										<?
										while ( list($k, $v) = each($NOTIFY_SOUNDS) )
										{
											echo "<option value=\"$k\"" . ($k==$my_prefs["notify"]?" SELECTED":"").">$v \n";
										}
										?>
									</select> sound when I recieve new messages.						
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>


		</td>
		<td width="1">
			&nbsp;&nbsp;
		</td>
		<td width="50%">


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;General Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="2">
							<tr>
								<td>
									I live in the <select name="timezone">
									<?
										$timezones = array
										(
											"-10" => "Hawaii",
											"-9"  => "Alaska",
											"-8"  => "Pacific",
											"-7"  => "Mountain",
											"-6"  => "Central",
											"-5"  => "Eastern",
											"-4"  => "Atlantic",
										);
										while ( list($k, $v) = each($timezones) )
										{
											echo "<option value=\"$k\"" . ($k==$my_prefs["timezone"]?" SELECTED":"").">$v \n";
										}
									?>
									</select> time zone.
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Filtering Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="1">
							<tr>
								<td width="5">
									<input type="checkbox" name="filters" value="1" <? echo ($my_prefs["filters"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Enable Basic Filtering
								</td>
							</tr>
							<? 
							
							if($TMDA_ENABLED)
							{
							?>
							<tr>
								<td width="5">
									<input type="checkbox" name="tmda" value="1" <? echo ($my_prefs["tmda"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Enable Advanced Filtering
								</td>
							</tr>
							<?
							}
							?>
						</table>

					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;List View Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="2">
							<tr>
								<td>
									Show up to <input type=text class="textbox" name="view_max" value="<? echo $my_prefs["view_max"]; ?>" size="3"> items
								</td>
							</tr>
							<tr>
								<td>
									Sort by: <select name="sort_field">
									<?
										$sort_fields = array("DATE"=>"Date", "SUBJECT"=>"Subject", "SIZE"=>"Size");
										DefaultOptions($sort_fields, $my_prefs["sort_field"]); 
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									Sort order: <select name="sort_order">
									<? 
										$sort_orders = array("ASC"=>"Ascending", "DESC"=>"Descending");
										DefaultOptions($sort_orders, $my_prefs["sort_order"]); 
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									Show tool bar at: <select name="main_toolbar">
									<?
										$tblocation = array("b" => "bottom", "t" => "top", "bt" => "top and bottom");
										while(list($k, $v) = each($tblocation))
										{
											echo "<option value=\"$k\" " . ($k==$my_prefs["main_toolbar"]?"SELECTED":"").">$v \n";
										}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									Check for new messages every <input type="text" class="textbox" name="radar_interval" value="<? echo $my_prefs["radar_interval"] ?>" size="4"> minutes
								</td>
							</tr>
							<tr>
								<td>
									<br>
									Rearrange columns: <a href="main.php?user=<?=$user?>&folder=INBOX&MOVE_FIELDS=1" target="_blank"><b>click here</b></a>
									<input type="hidden" name="main_cols" value="<? echo $my_prefs["main_cols"]; ?>">
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Folder Options</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="1">
							<tr>
								<td>
									<input type="checkbox" name="showNumUnread" value="1" <? echo ($my_prefs["showNumUnread"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Show number of unread messages if folder list.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="refresh_folderlist" value="1" <? echo ($my_prefs["refresh_folderlist"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Refresh folder list every &nbsp;<input type="text" class="textbox" name="folderlist_interval" value="<? echo $my_prefs["folderlist_interval"] ?>" size="4">&nbsp; minutes.
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>


			<br>


			<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"><b> &nbsp;Compose</b></td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">

						<table border="0" cellspacing="1" cellpadding="1">
							<tr>
								<td>
									<input type="checkbox" name="compose_html" value="1" <? echo ($my_prefs["compose_html"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Use advanced message composer (HTML Email).
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="compose_inside" value="1" <? echo ($my_prefs["compose_inside"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Compose message in main window.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="showCC" value="1" <? echo ($my_prefs["showCC"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Show CC/BCC fields.
								</td>
							</tr>
							<tr>
								<td>
									<input type="checkbox" name="closeAfterSend" value="1" <? echo ($my_prefs["closeAfterSend"]==1?"CHECKED":""); ?>>
								</td>
								<td>
									Close after sending.
								</td>
							</tr>
							<tr>
								<td colspan="2">
									&nbsp;Show contacts in <select name="showContacts">
										<option value="0" <? echo ($my_prefs["showContacts"]==0?"SELECTED":""); ?>>None
										<option value="1" <? echo ($my_prefs["showContacts"]==1?"SELECTED":""); ?>>Popup Window
										<option value="2" <? echo ($my_prefs["showContacts"]==2?"SELECTED":""); ?>>Compose Window
									</select>
								</td>
							</tr>
						</table>

					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>

<br>

<table border="0" width="95%" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td align="right">
			<input type="hidden" name="do_options" value="1">
			<input type="submit" name="apply" value="Apply"> &nbsp; &nbsp; 
			<input type="submit" name="cancel" value="Cancel"> &nbsp; &nbsp; 
			<input type="submit" name="revert" value="Defaults">
		</td>
	</tr>
</table>

<br>

</form>
</body>
</html>
