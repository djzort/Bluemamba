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
              
    Document: include/main_tools.php
              
    Function: List Messages

*********************************************************************/

$main_tools_counter++;
?>
<table width="100%" height="24">
	<tr>
		<td>
			<?
			if (strcmp($folder, $my_prefs["trash_name"]) == 0)
			{
				?>
				<input type="hidden" name="expunge" value="1">
				<input type="submit" name="submit" value="Empty Trash">
				<?
			}
			else
			{
				?><input type="submit" name="submit" value="Delete"><?
			}
			?>
		</td>
		<td class="mainLight">
			Mark as
			<input type="submit" name="submit" value="Read">
			<input type="submit" name="submit" value="Unread">
		</td>
		<td align="right">
			<?
			
			if (!is_array($folderlist))
			{
				$cached_folders = cache_read($loginID, $host, "folders");
			
				if (is_array($cached_folders))
				{
					echo "<!-- Read cache! //-->\n";
					$folderlist = $cached_folders;
				}
				else
				{
					echo "<!-- No cache...";
					if ($my_prefs["hideUnsubscribed"]) $folderlist = iil_C_ListSubscribed($conn, $ROOTDIR, "*");
					else $folderlist = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
					$cache_result = cache_write($loginID, $host, "folders", $folderlist);
					echo "write: $cache_result //-->\n";
				}
			}
			
			?>
			<select name="moveto<? echo $main_tools_counter; ?>">
				<option value=""></option>
				<?
				sort($folderlist);
	
				while(list($k, $folder2) = each($folderlist))
				{
					echo '<option value="' . $folder2 . '">' . cleanfolder($folder2) . "</option>\n";
				}	
				?>
			</select>
			<input type="submit" name="submit" value="Move">

		</td>
	</tr>
</table>

<?
$main_tool_shown = true;
?>