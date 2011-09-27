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
              
    Document: source/main_pending.php
              
    Function: List TMDA Pending Messages

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/ryosdates.php");
include("../include/icl.php");
include("../include/main.php");
include("../include/data_manager.php");
include("../include/cache.php");
include("../include/filters.php");
include("../include/javascript.php");

$num_cols = strlen($my_prefs["main_cols"]);

if(is_array($checkboxes))
{
	$msg_ids = implode(" ", $checkboxes);
}
if(isset($id))
{
	$msg_ids = $id;
}

if($msg_ids)
{
	if($whitelist)
	{
		$output = shell_exec('/usr/bin/sudo /usr/bin/tmda-pending -c ' . $USER_BASE_DIR . '.tmda/config -br ' . $msg_ids);
		$error  = "White Listed $num_checked messages. $output";
		usleep(1000000); // Wait 1 seconds. Released Pending Message(s) needs to be remailed
	}
	
	if($blacklist)
	{
		$output = shell_exec('/usr/bin/sudo /usr/bin/tmda-pending -c ' . $USER_BASE_DIR . '.tmda/config -bd ' . $msg_ids);
		$error  = "Black Listed $num_checked messages. $output";
	}
}


?>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left" valign="middle">
			&nbsp; <span class="bigTitle">Pending Messages</span>
		</td>
	</tr>
</table>
<form name="messages" method="POST" action="main_pending.php" style="display:inline">
<?

$runcmd = sprintf("sudo %s -c %s -bsA", "/usr/bin/tmda-pending", $USER_BASE_DIR.".tmda/config");
$retval = exec($runcmd, $msg_list);

// Loop trought message list and parse
for($i = 0; $i < count($msg_list); $i++)
{
    $msg_line = $msg_list[$i];

	if(strstr($msg_line, ".msg"))	
	{
		$msg_count++;			// Next Message

		// Extract Message ID
	    ereg('(.*).msg', $msg_line, $reg);
		$messages[$msg_count]['id'] = $reg[1].".msg";

		// Extract Size
	    ereg('/ (.*)\)', $msg_line, $reg);
		$messages[$msg_count]['size'] = $reg[1];
	}

	if(strstr($msg_line, "Date:"))	// Extract Date
	{
	    ereg('Date: (.*)', $msg_line, $reg);
		$messages[$msg_count]['date'] = $reg[1];
	}

	if(strstr($msg_line, "From:"))	// Extract From
	{
	    ereg('From: (.*)', $msg_line, $reg);
		$messages[$msg_count]['from'] = $reg[1];
	}

	if(strstr($msg_line, "To:"))	// Extract To
	{
	    ereg('To: (.*)', $msg_line, $reg);
		$messages[$msg_count]['to'] = $reg[1];
	}

	if(strstr($msg_line, "Subj:"))	// Extract Subject
	{
	    ereg('Subj: (.*)', $msg_line, $reg);
		$messages[$msg_count]['subj'] = $reg[1];
	}
}

if(is_array($messages))
{
	reset($messages);
	?>
	<table width="100%" border="0">
		<tr>
			<td align="center" class="mainLightSmall">
				&nbsp;<? echo $error; ?>&nbsp;
			</td>
		</tr>
	</table>
	<?
	
	// Show tool bar
	if(strpos($my_prefs["main_toolbar"], "t") !== false)
	{
		?>
		<table width="100%">
			<tr>
				<td align="left">
					<input type="submit" name="whitelist" value="White List">&nbsp;
					<input type="submit" name="blacklist" value="Black List">
				</td>
			</tr>
		</table>
		<?
	}
	?>

	<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
		<tr class="tblheader">
			<?
	
			$tbl_header["c"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center' class='tblheader'><a href=javascript:SelectAllMessages(true) class='tblheader'><img src='" . $THEME . "plus_header.gif' border='0'></a>|<a href=javascript:SelectAllMessages(false) class='tblheader'><img src='" . $THEME . "minus_header.gif' border='0'></a> </td>";
			$tbl_header["s"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;Subject&nbsp;</td>";
			$tbl_header["f"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;From&nbsp;</td>";
			$tbl_header["d"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;Date&nbsp;</td>";
			$tbl_header["z"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;Size&nbsp;</td>";

			for($i = 0; $i < $num_cols; $i++)
			{
				echo $tbl_header[$my_prefs["main_cols"][$i]];
			}
			
			?>
		</tr>
		<?
	
		// Build Message Listing
	
		$display_i = 0;
		$prev_id = "";
		while(list($key, $val) = each($messages))
		{
			$message  =  $messages[$key];
			$id       =  $message['id'];
	
			// Message Listing Spacer
			echo "<tr><td bgcolor=\"" . $my_colors["main_bg"] . "\" colspan=7 height=1></td></tr>";
	
			$display_i++;
			echo "\n<tr bgcolor=\"" . $my_colors["main_bg"] . "\">\n";
			
	
			// Show checkbox
			$row["c"]  = "<td align='center'><input type=\"checkbox\" name=\"checkboxes[]\" value=\"$id\" ";
			$row["c"] .= (isset($check_all)?"CHECKED":"");
			if(!isset($uncheck_all)) $row["c"] .= (($spam) && (isSpam($header->Subject)>0) ? "CHECKED":"");
			if(is_array($selected_boxes) && in_array($id, $selected_boxes)) $row["c"] .= "CHECKED";
			$row["c"] .= "></td>\n";
	
	
	
		
			// Show Subject
			$subject = trim(chop($message['subj']));
			if(empty($subject)) $subject = "untitled";
			$args  = "user=$user&folder=".urlencode($folder)."&id=$id&uid=".$header->uid."&start=$start";
			$args .= "&num_msgs=$total_num&sort_field=$sort_field&sort_order=$sort_order";
			$row["s"]  = "<td><a href=\"read_pending.php?".$args."\" ";
	
			if($my_prefs["view_inside"] != 1)		// Build Targets
			{
				$row["s"] .= 'target="scr' . $user . urlencode($folder) . $id . '"';
			}
			else
			{
				if($my_prefs["preview_window"] == 1)
				{
					$row["s"] .= 'target="preview"';
				}
			}
	
			$row["s"] .= ">" . ($seen=="N"?"<B>":"") .encodeUTFSafeHTML( LangDecodeSubject($subject, $CHARSET) ) . ($seen=="N"?"</B>":"")."</a></td>\n";
	
	
			// Show sender||recipient
			$sender = LangDecodeAddressList($message['from'], $CHARSET, $user); 
			$row["f"] = "<td>$sender</td>\n";
	
	
			// Show date/time
			$email_date = trim(chop($message['date']));
			$row["d"] = "<td><nobr>" . $email_date . "&nbsp;</nobr></td>\n";
	
	
			// Show size
			$size = trim(chop($message['size']));
			$row["z"] = "<td><nobr>" . $size . "</nobr></td>\n";
	
	
			for ($i = 0; $i < $num_cols; $i++)
			{
				echo $row[$my_prefs["main_cols"][$i]];
			}
			
			echo "</tr>\n";
			flush();
	
			$i++;
		}
	?>
	</table>
	
	<?
	echo "<input type=hidden name=\"user\" value=\"".       $user       ."\">\n";
	echo "<input type=hidden name=\"folder\" value=\"".     $folder     ."\">\n";
	echo "<input type=hidden name=\"sort_field\" value=\"". $sort_field ."\">\n";
	echo "<input type=hidden name=\"sort_order\" value=\"". $sort_order ."\">\n";
	if(isset($search)) echo "<input type=hidden name=search_done value=1>\n";
	echo "<input type=\"hidden\" name=\"max_messages\" value=\"".$display_i."\">\n";
	
	// Show tool bar
	if(strpos($my_prefs["main_toolbar"], "b") !== false)
	{
		?>
		<table width="100%">
			<tr>
				<td align="left">
					<input type="submit" name="whitelist" value="White List">&nbsp;
					<input type="submit" name="blacklist" value="Black List">
				</td>
			</tr>
		</table>
		<?
	}
	?>
	</form>
	
	<br> <br>
	
	<table align="center" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
			
				Click &quot;White List&quot; to move the message(s) into your inbox and add the sender to your White List<br>
				&quot;Black List&quot; will delete the message(s) and add the sender to your Black List.
			
			</td>
		</tr>
	</table>
	<?
}
else
{
	?>
	<br>
	<table border="0" width="95%" align="center" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
				There are no pending messages.
			</td>
		</tr>
	</table>
	<?
}

?>
<br>
</body>
</html>