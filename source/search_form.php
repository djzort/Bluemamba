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
              
    Document: source/search_form.php
              
    Function: Message Search

*********************************************************************/

include("../include/super2global.php");
include("../include/nocache.php");
include("../include/header_main.php");
include("../include/icl.php");


// Form folder list
$conn = iil_Connect($host, $loginID, $password);
if ($conn)
{
	$folders = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
	sort($folders);
	iil_Close($conn);
}

?>

<form method="post" action="search_post.php" style="display:inline">
	<input type="hidden" name="user" value="<?=$sid?>">
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr class="bigTitle">
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">&nbsp; Search</td>
		</tr>
	</table>
	
	<br>
	
	<table border="0" width="95%" cellspacing="1" cellpadding="3" align="center" bgcolor="<?=$my_colors["main_hilite"]?>">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>">
	
				<table border="0" cellspacing="1" cellpadding="2">
					<tr>
						<td>
							Search in folder
						</td>
						<td colspan="3">
							<select name="folder" class="textbox">
								<option value="allfolders">All Folders
								<?
								while ( list($k, $folder) = each($folders) )
								{
									$dispfolder = str_replace('INBOX.', '', $folder);
									$dispfolder = str_replace('.', '/', $dispfolder);
									$dispfolder = str_replace('INBOX', 'Inbox', $dispfolder);
									echo '<option value="' . $folder . '">'. $dispfolder;
								}
								?>
							</select>
						</td>
					</tr>
	
					<tr>
						<td>
							Where the field
						</td>
						<td>
							<select name="field" class="textbox">
								<option value="BODY" selected>Message Body
								<option value="SUBJECT">Subject
								<option value="TO">To
								<option value="FROM">From
							</select>
						</td>
						<td>
							Contains
						</td>
						<td>
							<input type="text" name="string" class="textbox">
						</td>
					</tr>
	
					<tr>
						<td>
							Where the date
						</td>
						<td>
							<select name="date_operand" class="textbox">
								<option value="ignore" selected>Doesn't Matter
								<option value="ON">Is On
								<option value="SINCE">Is After
								<option value="BEFORE">Is Before
							</select>
						</td>
						<td colspan="2">
							<input type="text" name="month" value="mm"   size="2" class="textbox"> <b>/</b>
							<input type="text" name="day"   value="dd"   size="2" class="textbox"> <b>/</b>
							<input type="text" name="year"  value="yyyy" size="4" class="textbox">
						</td>
					</tr>
	
					<tr>
						<td colspan="4">
							<input type="submit" name="search" value="Search">
						</td>
					</tr>
				</table>
	
			</td>
		</tr>
	</table>
</form>

<br>
<br>

<table border="0" cellspacing="1" cellpadding="10" align="center" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%">
	<tr>
		<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
			Searching in &quot;All Folders&quot; may slow down your search.
		</td>
	</tr>
</table>

</body>
</html>