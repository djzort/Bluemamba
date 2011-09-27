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
              
    Document: source/search_post.php
              
    Function: List Searched Messages

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/ryosdates.php");
include("../include/icl.php");
include("../include/main.php");
include("../include/data_manager.php");
include("../include/cache.php");
include("../include/javascript.php");


// Open DM connection
$dm = new DataManager_obj;


if(!$folder)
{
	echo "Error: folder not specified";
	exit;
}


// Initialize some vars
if( empty($my_prefs["main_cols"]) )
{
	$my_prefs["main_cols"] = "camfsdz";
}


// Connect to mail server
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if(!$conn)
{
	echo "Connection failed: $iil_error <br> ";
	exit;
}



if( isset($submit) )
{
	$messages = "";
	
	// Compose an IMAP message list string including all checked items
	if( is_array($uids) && (implode("", $uids) != "") )
	{
		$checkboxes = iil_C_Search($conn, $folder, "UID ".implode(",", $uids));
	}
	if( is_array($checkboxes) )
	{
		$messages    = implode(",", $checkboxes);
		$num_checked = count($checkboxes);
	}

}


// If search results were moved or deleted, stop execution here.
if(isset($search_done))
{
	$error = "<p>Request completed.\n";
}

// Initialize sort field and sort order  (set to default prefernce values if not specified
if(empty($sort_field)) $sort_field = $my_prefs["sort_field"];
if(empty($sort_order)) $sort_order = $my_prefs["sort_order"];



// Figure out which/how many messages to fetch
if( (empty($start)) || (!isset($start)) ) 
{
	$start = 0;
}
$num_show = $my_prefs["view_max"];
if($num_show < 5)
{
	$num_show = 5;
}
$next_start = $start + $num_show;
$prev_start = $start - $num_show;
if($prev_start < 0)
{
	$prev_start = 0;
}


// Get Folders List
$searchlist = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
sort($searchlist);

if($folder != "allfolders")
{
	unset($searchlist);  		// Clear out full list
	$searchlist[0] = $folder;	// Set array to one folder
}


// Loop for every folder
while ( list($key, $folder) = each($searchlist) )
{

	// Retreive message list (search, or list all in folder)
	if( isset($search) || isset($search_criteria) )
	{
		$criteria = "";
		$error    = "";
		$date     = $month . "/" . $day . "/" . $year;
		if( empty($search_criteria) )
		{
			// Check criteria
			if($date_operand == "ignore")
			{
				if($field == "-")
				{
					$error = "Invalid search criteria:  Invalid field";
				}
				if(empty($string))
				{
					$error = "Invalid search criteria:  Empty search string";
				}
			}
			else if( (empty($date)) || ($date == "mm/dd/yyyy") )
			{
				$error = "Invalid search criteria:  Date not specified";
			}
			if(!empty($date))
			{
				$date_a = explode("/", $date);
				$date   = iil_FormatSearchDate($date_a[0], $date_a[1], $date_a[2]);
			}
		}
		if($error == "")
		{
			// Format search string
			if(empty($search_criteria))
			{
				$criteria = "ALL";
				if($field!="-")
				{
					$criteria.=" $field \"$string\"";
				}
				if($date_operand != "ignore")
				{
					$criteria .= " $date_operand $date";
				}
				$search_criteria = $criteria;
			}
			else
			{
				$search_criteria = stripslashes($search_criteria);
				$criteria = $search_criteria;
			}
			
			$error = "Searching \"$criteria\" in $folder";
			flush();
			
			$messages_a = iil_C_Search($conn, $folder, $criteria);
			if($messages_a !== false)
			{
				$total_num = count($messages_a);
				if( is_array($messages_a) )
				{
					$messages_str = implode(",", $messages_a);
				}
				else
				{
					$messages_str = "";
				}
			}
			else
			{
				$error = $conn->error; 
			}
			
		}
		else
		{
			$headers = false;
		}
	}
	else
	{
		$total_num = iil_C_CountMessages($conn, $folder);
		if($total_num > 0)
		{
			$messages_str = "1:" . $total_num;
		}
		else
		{
			$messages_str = "";
		}
		$index_failed = false;
	}
	
	
	
	
		
	// If there are more messages than will be displayed, create an index array, 
	// sort, then figure out which messages to fetch 
	if( ($total_num - $num_show) > 0 )
	{
		// Attempt ot read from cache
		$read_cache = false;
		if(file_exists(realpath($CACHE_DIR)))
		{
			$cache_path = $CACHE_DIR . ereg_replace("[\\/]", "", $loginID .".". $host);
			$index_a = main_ReadCache($cache_path, $folder, $messages_str, $sort_field, $read_cache);
		}
		
		// If there are "recent" messages, ignore cache
		$recent = iil_C_CheckForRecent($conn, $folder);
		if($recent > 0) $read_cache = false;
		
		// If not read from cache, go to server
		if(!$read_cache)
		{
			$index_a = iil_C_FetchHeaderIndex($conn, $folder, $messages_str, $sort_field);
		}
		
		// Clear Vars
		if(is_array($index_a))
		{
			reset($index_a);
			$i = count($index_a);
			$k = 0;
			$id_a = "";
			
			// Loop backwards, newer messages run from high # to low
			while (list($key, $val) = each ($index_a))
			{
				$i--;
				if($i >= $start)
				{
					if($i < $next_start)
					{
						$k++;
						$id_a[$k] = $key;
					}
				}
			}
			if( is_array($id_a) )
			{
				$messages_str = implode(",", $id_a);
			}
		}
		flush();
	}
	
	
	// Fetch headers
	if($messages_str != "")
	{
		$headers = iil_C_FetchHeaders($conn, $folder, $messages_str);
		$headers = iil_SortHeaders($headers, $sort_field, $sort_order);
	}
	else
	{
		$headers = false;
	}
	
		
	// Show folder name, num messages, page selection pop-up
	if($headers == false) $headers = array();
	
	if(count($headers) > 0)
	{

		// Start form
		?>
		<br>
		<table border="0" width="100%" align="center" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
			<tr>
				<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
					Searching for <? echo $criteria; ?> in &quot;<? echo cleanfolder($folder); ?>&quot;.
				</td>
			</tr>
		</table>
		<br>

		<form name="messages" method="POST" action="main.php" style="display:inline">
		<table border="0" cellspacing="1" cellpadding="3" width="100%">
			<tr>
				<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left" valign="middle">
					&nbsp;
					<span class="bigTitle">
						<?
						echo cleanfolder($folder);
						?>
					</span>
					&nbsp;&nbsp;
					<span>
						<?
						if(strcasecmp("INBOX", $folder)==0)
						{
							echo " <a href=\"main.php?user=$user&folder=$folder\" class=\"mainHeadingSmall\">Check New</a> ";
						}
				
						if(strcmp($folder, "Trash") != 0)
						{
							echo " <a href=\"main.php?user=$user&folder=$folder&delete_all=1\" class=\"mainHeadingSmall\">Delete All</a> ";
						}
						?>
					</span>
		
				</td>
			</tr>
		</table>
		<?
		
		
		// Confirm "delete all" request
		if($delete_all == 1)
		{
			echo "<p>".str_replace("%f", $disp_folderName, "Are you sure you want to delete ALL messages in %f?");
			echo "<span class=\"small\"> [<a href=\"main.php?user=$user&folder=$folder&delete_all=2&delete_all_num=$total_num&submit=Delete\">Delete All</a>] </span>";
			echo "<span class=\"small\"> [<a href=\"main.php?user=$user&folder=$folder\">Cancel</a>] </span>";
		}
		
	
		$c_date["day"]   = GetCurrentDay();
		$c_date["month"] = GetCurrentMonth();
		$c_date["year"]  = GetCurrentYear();
	
		if(!isset($start)) $start=0;
		$i = 0;
	
		if(sizeof($headers)>0)
		{
			// Show "To" field or "From" field?
			if( ($folder == "INBOX.Sent") || ($folder == "INBOX.Unsent") )
			{
				$fromheading = "To";
			}
			else
			{
				$fromheading = "From";
				
				// Check users folder type setting
				if($dm->initialize($loginID, $host, $DB_FOLDERS_TABLE, $DB_TYPE))
				{
					if($folder_type = $dm->read())
					{
						while( list($i, $value) = each($folder_type))
						{
							if($folder == $folder_type[$i]['name'])
							{
								$fromheading = $folder_type[$i]['type'];
								break 1;
							}
						}
					}
				}
				else
				{
					echo "Data Manager initialization failed:<br>\n";
					$dm->showError();
				}
			}			
			
			if($fromheading == "To")
			{
				$showto = true;
			}
			
	
	
			// Show num msgs and any notices
			?>
			<table width="100%" border="0">
				<tr>
					<td valign="middle" align="left" class="mainLightSmall">
						<?
						echo str_replace("%p", ($num_show > $total_num ? $total_num : $num_show), str_replace("%n", $total_num, "Showing %p of %n")) . "&nbsp;";
						?>			
					</td>
					<td valign="middle" align="center" class="mainLightSmall">
						<?
						echo $report;
						?>
					</td>
					<td valign="bottom" align="right" class="mainLightSmall">
						<?
	
						// Page controls
						$num_items = $total_num;
						if($num_items > $num_show)
						{
							if($prev_start < $start)
							{
								$args = "&sort_field=$sort_field&sort_order=$sort_order&start=$prev_start";
								if(!empty($search_criteria)) $args .= "&search_criteria=".urlencode($search_criteria);
								echo " [<a href=\"main.php?user=$sid&folder=".urlencode($folder).$args."\" class=\"mainLightSmall\">";
								echo "Previous $num_show</a>] ";
							}
			
							if($next_start < $num_items)
							{
								$num_next_str = $num_show;
								if(($num_items - $next_start) < $num_show) $num_next_str = $num_items - $next_start;
								$args = "&sort_field=$sort_field&sort_order=$sort_order&start=$next_start";
								if(!empty($search_criteria)) $args .= "&search_criteria=".urlencode($search_criteria);
								echo " [<a href=\"main.php?user=$sid&folder=".urlencode($folder).$args."\" class=\"mainLightSmall\">";
								echo "Next $num_next_str</a>] ";
							}
							?>
							<select name="start" class="small">
								<?
								$c = 0;
								while ($c < $total_num)
								{
									$c2 = ($c + $num_show);
									if($c2 > $total_num) $c2 = $total_num;
									echo "<option value=" . $c . ($c == $start?" SELECTED":"").">" . ($c+1) . "-" . $c2 . "\n";
									$c = $c + $num_show;
								}
								?>
							</select>
							<input type="submit" value="Show">
							<?
						}
						?>
					</td>
				</tr>
			</table>
			<?
	
	
			$main_tools_counter = 0;
			// Show tool bar
			if(strpos($my_prefs["main_toolbar"], "t") !== false)
			{
				include("../include/main_tools.php");
			}
	
			// Main list
			$num_cols = strlen($my_prefs["main_cols"]);
			
			?>
			<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
				<tr>
					<?
	
					$tbl_header["c"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center' class='tblheader'><a href=javascript:SelectAllMessages(true) class='tblheader'><img src='" . $THEME . "plus_header.gif' border='0'></a>|<a href=javascript:SelectAllMessages(false) class='tblheader'><img src='" . $THEME . "minus_header.gif' border='0'></a> </td>";
					$tbl_header["a"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center'> <img src=\"" . $THEME . "att_header.gif\"> </td>";
					$tbl_header["m"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center'> <img src=\"" . $THEME . "reply.gif\"> </td>";
					$tbl_header["s"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("subject", "Subject")."&nbsp;</td>";
					$tbl_header["f"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("from", $fromheading)."&nbsp;</td>";
					$tbl_header["d"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("date", "Date")."&nbsp;</td>";
					$tbl_header["z"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("size", "Size")."&nbsp;</td>";
	
					for ($i=0; $i<$num_cols; $i++)
					{
						echo $tbl_header[$my_prefs["main_cols"][$i]];
					}
					
					?>
				</tr>
				<?
	
	
	
			// Build Message Listing
	
			$display_i = 0;
			$prev_id = "";
			while ( list($key, $val) = each($headers) )
			{
				$header   =  $headers[$key];
				$id       =  $header->id;
				$seen     = ($header->seen     ? "Y":"N");
				$answered = ($header->answered ? "Y":"N");
	
				// Message Listing Spacer
				echo "<tr><td bgcolor=\"" . $my_colors["main_bg"] . "\" colspan=7 height=1></td></tr>";
	
				if(!$header->deleted)
				{
					$display_i++;
					echo "\n<tr bgcolor=\"" . $my_colors["main_bg"] . "\">\n";
					
	
					// Show checkbox
					$row["c"]  = "<td align='center'><input type=\"checkbox\" name=\"checkboxes[]\" value=\"$id\" ";
					$row["c"] .= (isset($check_all)?"CHECKED":"");
					if(!isset($uncheck_all)) $row["c"] .= (($spam) && (isSpam($header->Subject)>0) ? "CHECKED":"");
					if(is_array($selected_boxes) && in_array($id, $selected_boxes)) $row["c"] .= "CHECKED";
					$row["c"] .= "></td>\n";
	
	
					// Attachments?
					$row["a"] = "<td align='center'>";
					if(preg_match("/multipart\/m/i", $header->ctype)==TRUE)
					{
						$row["a"].= "<img src=\"" . $THEME . "att.gif\">";
					}
					$row["a"].= "</td>\n";
	
	
					// Show flags
					$flag_img = "";
					if($answered == "Y") $flag_img = "reply.gif";
					elseif($seen == "N") $flag_img = "unread.gif";
					
					if($flag_img)
					{
						$row["m"] = "<td align='center'><img src=\"" . $THEME . $flag_img . "\"></td>\n";
					}
					else
					{
						$row["m"] = "<td align='center'>&nbsp;</td>\n";
					}
	
				
					// Show Subject
					$subject = trim(chop($header->subject));
					if(empty($subject)) $subject = "untitled";
					$args  = "user=$user&folder=".urlencode($folder)."&id=$id&uid=".$header->uid."&start=$start";
					$args .= "&num_msgs=$total_num&sort_field=$sort_field&sort_order=$sort_order";
					$row["s"]  = "<td><a href=\"read_message.php?".$args."\" ";
	
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
	
					$row["s"] .= ">" . ($seen=="N"?"<B>":"") . encodeUTFSafeHTML(LangDecodeSubject($subject, $CHARSET)).($seen=="N"?"</B>":"")."</a></td>\n";
	
	
					// Show sender||recipient
					if($showto)
					{
						$row["f"] = "<td>".LangDecodeAddressList($header->to, $CHARSET, $user)."</td>\n";
					}
					else
					{
						$row["f"] = "<td>".LangDecodeAddressList($header->from, $CHARSET, $user)."</td>\n";
					}
	
	
					// Show date/time
					$email_date = date("D, M jS Y h:i A", strtotime($header->date));
					$row["d"] = "<td><nobr>" . $email_date . "&nbsp;</nobr></td>\n";
	
	
					// Show size
					$row["z"] = "<td><nobr>" . ShowBytes($header->size) . "</nobr></td>\n";
	
	
					for ($i = 0; $i < $num_cols; $i++)
					{
						echo $row[$my_prefs["main_cols"][$i]];
					}
					
					echo "</tr>\n";
					flush();
	
					$i++;
				}
			}
			echo "</table>";
	
			echo "<input type=hidden name=\"user\" value=\"".       $user       ."\">\n";
			echo "<input type=hidden name=\"folder\" value=\"".     $folder     ."\">\n";
			echo "<input type=hidden name=\"sort_field\" value=\"". $sort_field ."\">\n";
			echo "<input type=hidden name=\"sort_order\" value=\"". $sort_order ."\">\n";
			echo "<input type=\"hidden\" name=\"max_messages\" value=\"".$display_i."\">\n";
			
			// Show tool bar
			if(strpos($my_prefs["main_toolbar"], "b") !== false)
			{
				include("../include/main_tools.php");
			}
			
			echo "</form>\n";
			
			if($folder == "INBOX")
			{
				echo "\n<script language=\"JavaScript\">\n";
				echo "parent.radar.location=\"radar.php?user=".$user."\";\n";
				echo "</script>\n";
			}
	
			if($my_prefs["showNumUnread"] && ($folder == "INBOX"))
			{
				echo "\n<script language=\"JavaScript\">\n";
				echo "parent.list1.location=\"folders.php?user=" . $user . "\";\n";
				echo "</script>\n";
			}
		}

		echo "<br> <br>";
	}

} // Folder Loop
iil_Close($conn);

?>
</body>
</html>