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
              
    Document: include/read_message_tools.php
              
    Function: Read Message Tools

*********************************************************************/

$read_message_tools_counter++;

$folder_url = urlencode($folder);


$target = "list2";
if($my_prefs["preview_window"] == 1)		// Build Targets
{
	$target = "preview";
}
else
{
	if($my_prefs["compose_inside"] != 1)
	{
		$target = "scr" . $user . $folder_url . $id;
	}
}

?>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>">

			<table border="0" cellpadding="0" cellspacing="0" class="mainToolBar">
				<tr>
					<td>&nbsp;</td>
			
						<?
					// Previouse Link
					if(!empty($prev_link))
					{
						echo "<td>". $prev_link ."</td>";
					}
					
					// Folder Name
					if($my_prefs["view_inside"])
					{
						echo "<td> &nbsp; <a href=\"main.php?user=$user&folder=$folder&start=$start&sort_field=$sort_field&sort_order=$sort_order\" target=\"list2\" class=bigTitle>";
						$folder_name = $defaults[$folder];
						if(empty($folder_name))
						{
							$delim = iil_C_GetHierarchyDelimiter($conn);
							$pos = strrpos($folder, $delim);
							if($pos!==false) $pos++;
							$folder_name = substr($folder, $pos);
						}
				
						// Lowercase inbox
						$folder_name = str_replace("INBOX", "Inbox", $folder_name);
						echo $folder_name . "</a> &nbsp; </td>";
					}
					
					// Next Link
					if(!empty($next_link))
					{
						echo "<td>". $next_link ."</td>";
					}
					
					echo "<td>&nbsp; &nbsp; &nbsp;</td>";
					
					if($folder_name == "Drafts")
					{
						// Compose from Draft
						$href = "<a href=\"compose.php?user=$user&draft=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"mainHeadingSmall\">";
						echo "<td><img src=\"" . $THEME . "compose_draft.gif\" border=\"0\" height=14>&nbsp;" . $href . "Compose from Draft</a></td>";
					}
					elseif($folder_name == "Unsent")
					{
						// Resend
						$href  = "<a href=\"compose.php?user=$user&unsent=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "resend.gif\" border=\"0\" height=14>&nbsp;Resend Message</a></td>";
					}
					else
					{
					
						// Reply
						$href = "<a href=\"compose.php?user=$user&replyto=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "reply.gif\" border=\"0\" height=14>&nbsp;Reply</a></td>";
						
						echo "<td>&nbsp;|&nbsp;</td>";
						
						// Reply All
						if($multiple_recipients == true)
						{
							$href = "<a href=\"compose.php?user=$user&replyto=1&replyto_all=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"mainHeadingSmall\">";
							echo "<td>" . $href . "<img src=\"" . $THEME . "replyall.gif\" border=\"0\" height=14>&nbsp;Re. To All</a></td>";
					
							echo "<td>&nbsp;|&nbsp;</td>";
						}
						
						// Forward
						$href = "<a href=\"compose.php?user=$user&forward=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "forward.gif\" border=\"0\" height=14>&nbsp;Forward</a></td>";

					}
					echo "<td>&nbsp;|&nbsp;</td>";
					
					// Delete
					if(!$header->deleted) 
					{
						$href = "<a href=\"main.php?user=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Delete&start=$start\" target=\"list2\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "delete.gif\" border=\"0\" height=14>&nbsp;Delete</a></td>";
					}
					
					if( ($folder_name != "Drafts") && ($folder_name != "Unsent") )
					{
						echo "</td><td>&nbsp;|&nbsp;</td><td>";
					
						// Unread
						$href = "<a href=\"main.php?user=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Unread&start=$start\" target=\"list2\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "unread.gif\" border=\"0\" height=14>&nbsp;Unread</a></td>";
					}
					
					?>	
				</tr>
			</table>
	
		</td>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="right" valign="top">
	
		<form method="POST" action="main.php" style="display:inline"<? if($my_prefs["preview_window"] == 1) {echo " target=\"list2\"";} ?>>
			<input type="hidden" name="user" value="<?=$user?>">
			<input type="hidden" name="folder" value="<?=$folder?>">
			<input type="hidden" name="checkboxes[]" value="<?=$id?>">
			<input type="hidden" name="uids[]" value="<? echo $uid; ?>">
			<input type="hidden" name="start" value="<? echo $start; ?>">
			<input type="hidden" name="max_messages" value="<? echo ($id+1); ?>">
			<?

			if(!is_array($folderlist))
			{
				$cached_folders = cache_read($loginID, $host, "folders");
				if(is_array($cached_folders))
				{
					$folderlist = $cached_folders;
				}
				else
				{
					if($my_prefs["hideUnsubscribed"]) $folderlist = iil_C_ListSubscribed($conn, $ROOTDIR, "*");
					else $folderlist = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
					$cache_result = cache_write($loginID, $host, "folders", $folderlist);
				}
			}

			?>
			<select name="moveto<? echo $read_message_tools_counter; ?>">
				<option value=""></option>
				<? 
				sort($folderlist);
				reset($folderlist);
				while(list($k, $folder2) = each($folderlist))
				{
					echo '<option value="' . $folder2 . '">' . cleanfolder($folder2) . "</option>\n";
				}
				?>
			</select>
			<input type="submit" name="submit" value="Move">
		</form>

		</td>
	</tr>
</table>