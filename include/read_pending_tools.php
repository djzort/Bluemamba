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
              
    Document: include/read_pending_tools.php
              
    Function: Read Pending Message Tools

*********************************************************************/

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
	<table border="0" width="100%" height="25" cellpadding="2" cellspacing="0">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
	
				<table border="0" cellpadding="0" cellspacing="0" class="mainToolBar">
					<tr>
						<td>&nbsp;</td>
				
							<td>
							&nbsp; <a href="main_pending.php?user=<?=$user?>" target="list2" class="bigTitle">Pending Messages</a> &nbsp; 
						</td>
						<?
						
						echo "<td>&nbsp; &nbsp; &nbsp;</td>";
						
						// White List
						$href = "<a href=\"main_pending.php?user=$user&whitelist=1&id=$id\" target=\"$target\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "unread.gif\" border=\"0\" height=\"14\">&nbsp;White List</a></td>";
							
						echo "<td>&nbsp;|&nbsp;</td>";
						
						// Black List
						$href = "<a href=\"main_pending.php?user=$user&blacklist=1&id=$id\" target=\"list2\" class=\"mainHeadingSmall\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "delete.gif\" border=\"0\" height=\"14\">&nbsp;Black List</a></td>";
						
						
						?>
					</tr>
				</table>
		
			</td>
		</tr>
	</table>
