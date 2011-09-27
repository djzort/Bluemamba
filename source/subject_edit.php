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
              
    Document: source/read_message.php
              
    Function: Display important message headers
              Display message structure (attachments, multi-parts)
              Display message body (text, images, etc)
              Provide interfaces to delete/undelete or move messages
              Provide interface to view/download message parts
              Provide interface to forward/reply to message

*********************************************************************/


include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../include/mime.php");
include("../include/cache.php");

// Make sure folder is specified
if(empty($folder))
{
	echo "Folder not specified or invalid<br></body></html>";
	exit;
}
else
{
	$folder_ulr = urlencode($folder);
}

// Make sure message id is specified
if(empty($id))
{
	echo "Invalid or unspecified message id<br>\n";
	echo "</body></html>";
	exit;
}

// Connect to mail server
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if(!$conn)
{
	echo "<p>Failed to connect to mail server: $iil_error<br></body</html>";
	exit;
}

?>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr class="bigTitle">
		<td bgcolor="<?=$my_colors["main_head_bg"]?>">&nbsp; Edit Message Subject</td>
	</tr>
</table>

<br>

<table border="0" width="95%" cellspacing="1" cellpadding="3" align="center" bgcolor="<?=$my_colors["main_hilite"]?>">
	<tr>
		<td bgcolor="<?=$my_colors["main_bg"]?>">
			<?
			
			if($new_subject != "")
			{
				$new_subject = stripslashes($new_subject);
				if(!iil_C_ChangeSubject($conn, $folder, $id, $new_subject))
				{
					echo "<p>An error has occurred while changing the message subject: $iil_error<br></body</html>";
					exit;
				}
				else
				{
					?>
					<table border="0" cellspacing="1" cellpadding="2">
						<tr>
							<td>
								Subject successfully updated.
							</td>
						</tr>
						<tr>
							<td>
								<a href="main.php?user=<?=$user?>&folder=<?=$folder?>" target="list2">Return to folder</a>
							</td>
						</tr>
					</table>
					<?
				}
				
			}
			else
			{
			
				// Get message info
				$header        = iil_C_FetchHeader($conn, $folder, $id);
				$structure_str = iil_C_FetchStructureString($conn, $folder, $id); 
				flush();
				$structure     = iml_GetRawStructureArray($structure_str);
				$num_parts     = iml_GetNumParts($structure, $part);
				$parent_type   = iml_GetPartTypeCode($structure, $part);
				$uid           = $header->uid;
				
				if( ($parent_type == 1) && ($num_parts == 1) )
				{
					$part = 1;
					$num_parts   = iml_GetNumParts($structure, $part);
					$parent_type = iml_GetPartTypeCode($structure, $part);
				}
				
				flush();				
				
				?>
				<form method="post" action="subject_edit.php" style="display:inline">
					<input type="hidden" name="user"   value="<?=$user?>">
					<input type="hidden" name="folder" value="<?=$folder?>">
					<input type="hidden" name="id"     value="<?=$id?>">
					<table border="0" cellspacing="1" cellpadding="2">
						<tr>
							<td>
								Current Subject
							</td>
							<td colspan="3">
								 &nbsp; <b><? echo encodeUTFSafeHTML($header->subject); ?></b>
							</td>
						</tr>
				
						<tr>
							<td>
								New Subject
							</td>
							<td>
								&nbsp; <input type="text" name="new_subject" class="textbox">
							</td>
						</tr>
				
						<tr>
							<td colspan="2">
								<input type="submit" name="submit" value="Submit">
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
<?


iil_Close($conn);
?>
<br>
</body>
</html>