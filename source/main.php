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
              
    Document: source/main.php
              
    Function: List Messages

*********************************************************************/

$exec_start_time = microtime();

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/ryosdates.php");
include("../include/icl.php");
include("../include/main.php");
include("../include/data_manager.php");
include("../include/cache.php");
include("../include/filters.php");

	if(!$folder)
	{
		echo "Error: folder not specified";
		exit;
	}


	// Initialize some vars
	if(strcmp($folder, "Trash") == 0) $showdeleted = 1;
	if(empty($my_prefs["main_cols"])) $my_prefs["main_cols"] = "camfsdz";
	
	
	// Connect to mail server
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if(!$conn)
	{
		echo "Connection failed -> Host: $host, Login: $loginID, Pass: $password, Auth: $AUTH_MODE, Err: $iil_error <br> ";
		exit;
	}



	// Open DM connection
	$dm = new DataManager_obj;
	
		
	// Move columns
	if($MOVE_FIELDS)
	{
		$report = "Click on the arrows underneath the column headings to move them.<br>
				   When you're done, simply close the window.";
		if($move_col && $move_direction)
		{
			$col_pos = strpos($my_prefs["main_cols"], $move_col);
			if($col_pos !== false)
			{
				if($move_direction == "right")
				{
					$move_direction = 1;
				}
				elseif($move_direction == "left")
				{
					$move_direction = -1;
				}
				$partner_col = $my_prefs["main_cols"][$col_pos+$move_direction];

				if($partner_col)
				{
					$my_prefs["main_cols"][$col_pos + $move_direction] = $move_col;
					$my_prefs["main_cols"][$col_pos] = $partner_col;
					include("../include/save_prefs.php");
				}
			}
		}
	}
	

	if(isset($submit))
	{
		$messages = "";
		
		// compose an IMAP message list string including all checked items
		if( (is_array($uids)) && (implode("", $uids) != "") )
		{
			$checkboxes = iil_C_Search($conn, $folder, "UID ".implode(",", $uids));
		}
		if(is_array($checkboxes))
		{
               $messages = implode(",", $checkboxes);
               $num_checked = count($checkboxes);
		}

		// "Move to trash" is same as "Delete"
		if(($submit == "File") && (strcmp($moveto, "Trash")==0)) $submit = "Delete";



		// Delete all
		if($delete_all == 2 )
		{
			$messages .= "1:" . $delete_all_num;
		}


		// Delete items
		$delete_success = false;
		if(strcmp($submit, "Delete") == 0)
		{

			// If we are not the trash folder, move messages to trash.
			if($folder != "INBOX.Trash")
			{
				if(iil_C_Move($conn, $messages, $folder, "INBOX.Trash") >= 0)
				{
					$delete_success = true;
				}
				else
				{
					$report = "Couldn't move messages to trash: ".$messages;
				}
			}
			else
			{
				if(iil_C_Delete($conn, $folder, $messages) > 0)
				{
					$delete_success = true;
				}
				else
				{
					$report = "Couldn't flag messages as deleted: ".$messages;
				}
			}

		
			// If deleted, format success report
			if($delete_success)
			{
				$report = str_replace("%n", $num_checked, "Delete %n message(s)");
			}
		}

		// Empty trash command
		if( ($submit == "Empty Trash") && ($expunge == 1) )
		{
			if($folder == "INBOX.Trash")
			{
				if(!iil_C_ClearFolder($conn, $folder))
				{
					$error = "Couldn't empty trash (".$conn->error.")";
				}
			}
		}


		// Move items
		if( ($submit == "File") || ($submit == "Move") )
		{
			$moveto = $moveto1;
			if(empty($moveto))
			{
				$moveto = $moveto2;
			}
			
			if(strcasecmp($folder, "Trash") == 0)
			{
				iil_C_Undelete($conn, $folder, $messages);
			}
			if($illresult = iil_C_Move($conn, $messages, $folder, $moveto) >= 0)
			{
				$moveto_str = str_replace("INBOX.", "", $moveto);
				$moveto_str = str_replace(".", "/", $moveto_str);
				$moveto_str = str_replace("INBOX", "Inbox", $moveto_str);
				
				$report = str_replace("%n", $num_checked, "Moved %n message(s) to %f");
				$report = str_replace("%f", $moveto_str, $report);
			}
			else
			{
				$report = "Next";
			}
		}

		

		// Mark as unread
		if($submit == "Unread")
		{
			iil_C_Unseen($conn, $folder, $messages);
			$reload_folders = true;
			$selected_boxes = $checkboxes;
		}
		
		// Mark as read
		if($submit == "Read")
		{
			iil_C_Flag($conn, $folder, $messages, "SEEN");
			$reload_folders = true;
			$selected_boxes = $checkboxes;
		}
	} // End if submit




	// If search results were moved or deleted, stop execution here.
	if(isset($search_done))
	{
		echo "<p>Request completed.\n";
		echo "</body></html>";
		exit;
	}
	
	// Initialize sort field and sort order  (set to default prefernce values if not specified	
	if(empty($sort_field)) $sort_field = strtolower($my_prefs["sort_field"]);
	if(empty($sort_order)) $sort_order = $my_prefs["sort_order"];
	
	if( ($folder == "INBOX") && (empty($search) || empty($search_criteria)) )
	{

		// Load Users Filters
		if($dm->initialize($loginID, $host, $DB_FILTERS_TABLE, $DB_TYPE))
		{
			$filters = $dm->read();
		}
		else
		{
			$filters = false;
		}
	
	
		// Start Filtering, if there are filters and in the inbox. Do not run if searching
		if($filters)
		{
			reset($filters);
			while( list($i, $value) = each($filters) )
			{
				$filter_msg   = "";
				$filter_list  = "";
				$filter_field = "";

				if($filters[$i]['type'] == 1) { $filter_field = "FROM";    }
				if($filters[$i]['type'] == 2) { $filter_field = "SUBJECT"; }
				if($filters[$i]['type'] == 3) { $filter_field = "BODY";    }

				$filter_msg = iil_C_Search($conn, "INBOX", "ALL $filter_field \"" . $filters[$i]['syntax'] . "\"");

				if(is_array($filter_msg)) 
				{
					$filter_list = implode(",", $filter_msg);
					if(strlen($filter_list) > 1)	// Are there Messages to be filtered?
					{
						
						// Copy message to destination then mark message as deleted, will be removed by 'expunge' below
						if(iil_C_Copy($conn, $filter_list, $folder, $filters[$i]['moveto']) >= 0)
						{
							if(iil_C_Flag($conn, $folder, $filter_list, "DELETED") >= 0)
							{
								$report = "Message(s) Filtered";
							}
						}

					}
				}

			}	// Next Filter
		}	// End Filtering
	}	// End if Inbox





	// Expunge filtered messages, or any messages marked deleted
	iil_C_Expunge($conn, $folder);
	




	// Retreive message list (search, or list all in folder)
	if((!empty($search)) || (!empty($search_criteria)))
	{
		$criteria = "";
		$error = "";
		$date = $month . "/" . $day . "/" . $year;
		if(empty($search_criteria))
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
				$date = iil_FormatSearchDate($date_a[0], $date_a[1], $date_a[2]);
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
			
			?>
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						Searching for <?=$criteria?> in &quot;<?=cleanfolder($folder)?>&quot;.
					</td>
				</tr>
			</table>
			<br>
			<?
			flush();
			
			// Search
			$messages_a = iil_C_Search($conn, $folder, $criteria);
			if($messages_a !== false)
			{
				$total_num = count($messages_a);
				if(is_array($messages_a)) 
				{
					$messages_str = implode(",", $messages_a);
				}
				else
				{
					$messages_str = "";
				}
				flush();
			}
			else
			{
				$error = $conn->error; 
				flush();
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
			$messages_str = "1:".$total_num;
		}
		else
		{
			$messages_str = "";
		}
		$index_failed = false;
	}

	
	// Figure out which/how many messages to fetch
	if( (empty($start)) || (!isset($start)) )  { $start = 0; }
	$num_show   = $my_prefs["view_max"];
	if($num_show < 5) { $num_show = 5; }
	$next_start = $start + $num_show;
	$prev_start = $start - $num_show;
	if($prev_start < 0) { $prev_start = 0; }


		
	// If there are more messages than will be displayed,
	// Create an index array, sort, then figure out which messages to fetch
	if(($total_num - $num_show) > 0)
	{
		
		// Attempt ot read from cache
		$read_cache = false;
		if(file_exists(realpath($CACHE_DIR)))
		{
			$cache_path = $CACHE_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
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
		reset($index_a);
		$i = count($index_a);
		$k = 0;
		$id_a = "";
		
		// Loop backwards, newer messages run from high # to low
		while(list($key, $val) = each ($index_a))
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
		if(is_array($id_a)) $messages_str = implode(",", $id_a);
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




		

	// Start form
	?>
	<form name="messages" method="POST" action="main.php">
	<?

	// Show folder name, num messages, page selection pop-up
	if($headers == false) $headers = array();

	?>
	<table border="0" cellspacing="0" cellpadding="5" width="100%" height="26">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left" valign="middle">
				&nbsp;
				<span class="bigTitle">
				<?

				$disp_folderName = str_replace("INBOX.", "", $folder);
				$disp_folderName = str_replace(".", "/", $disp_folderName);
				$disp_folderName = str_replace("INBOX", "Inbox", $disp_folderName);
				if(empty($search))
				{
					echo urldecode($disp_folderName);
				}
		
				?>
				</span>
				&nbsp;&nbsp;
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

	
	// Show error messages, and reports
	if(!empty($error))
	{
		echo "<p><center><span style=\"color: red\">$error</span></center></p>";
	}

	$c_date["day"]   = GetCurrentDay();
	$c_date["month"] = GetCurrentMonth();
	$c_date["year"]  = GetCurrentYear();

	if(count($headers) > 0)
	{
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
						while(list($i, $value) = each($folder_type))
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
							<select name="start" class="textbox">
								<?
								$c = 0;
								while($c < $total_num)
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

			// Show tool bar
			if(strpos($my_prefs["main_toolbar"], "t") !== false)
			{
				include("../include/main_tools.php");
			}

			// Main list
			$num_cols = strlen($my_prefs["main_cols"]);
			
			?>
			<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
				<tr height="25">
					<?
	
					$tbl_header["c"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center' class='tblheader'><a href=javascript:SelectAllMessages(true) class='tblheader'><img src='" . $THEME . "plus_header.gif' border='0'></a>|<a href=javascript:SelectAllMessages(false) class='tblheader'><img src='" . $THEME . "minus_header.gif' border='0'></a> </td>";
					$tbl_header["a"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center'> <img src=\"" . $THEME . "att_header.gif\"> </td>";
					$tbl_header["m"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\" align='center'> <img src=\"" . $THEME . "unread.gif\"> </td>";
					$tbl_header["s"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("subject", "Subject")."&nbsp;</td>";
					$tbl_header["f"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("from", $fromheading)."&nbsp;</td>";
					$tbl_header["d"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("date", "Date")."&nbsp;</td>";
					$tbl_header["z"] = "<td bgcolor=\"". $my_colors["main_head_bg"] ."\">&nbsp;".FormFieldHeader("size", "Size")."&nbsp;</td>";
	
					for($i=0; $i<$num_cols; $i++)
					{
						echo $tbl_header[$my_prefs["main_cols"][$i]];
					}
					
					?>
				</tr>
				<?


			if($MOVE_FIELDS)
			{
				?>
				<tr bgcolor="<?=$my_colors["main_head_bg"] ?>">
					<?
					$base_url = "main.php?folder=".urlencode($folder)."&start=$start&user=$user&sort_field=$sort_field&sort_order=$sort_order";
					$base_url.= "&MOVE_FIELDS=1";
					for($i = 0; $i < $num_cols; $i++)
					{
						echo ShowFieldControls($my_prefs["main_cols"][$i], $base_url, $i, $num_cols);
					}
					?>
				</tr>
				<?
			}


			// Build Message Listing

			$display_i = 0;
			$prev_id = "";
			while(list($key, $val) = each($headers))
			{
				$header   =  $headers[$key];
				$id       =  $header->id;
				$seen     = ($header->seen     ? "Y":"N");
				$answered = ($header->answered ? "Y":"N");

				// Message Listing Spacer
				echo "<tr><td bgcolor=\"" . $my_colors["main_bg"] . "\" colspan=7 height=0></td></tr>";

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
					$row["s"]  = "<td>&nbsp;<a href=\"read_message.php?".$args."\" ";

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
						$row["f"] = "<td>&nbsp;".LangDecodeAddressList($header->to, $CHARSET, $user)."</td>\n";
					}
					else
					{
						$row["f"] = "<td>&nbsp;".LangDecodeAddressList($header->from, $CHARSET, $user)."</td>\n";
					}
	
	
					// Show date/time
					$email_date = date($DATE_FORMAT, strtotime($header->date));
					$row["d"] = "<td>&nbsp;<nobr>" . $email_date . "&nbsp;</nobr></td>\n";


					// Show size
					$row["z"] = "<td>&nbsp;<nobr>" . ShowBytes($header->size) . "</nobr></td>\n";


					for($i = 0; $i < $num_cols; $i++)
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
			if(isset($search)) echo "<input type=hidden name=search_done value=1>\n";
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

			if($my_prefs["showNumUnread"])
			{
				/*
				echo "\n<script language=\"JavaScript\">\n";
				echo "parent.list1.location=\"folders.php?user=" . $user . "\";\n";
				//echo "parent.list1.location=\"folders.php?user=" . $user . "&subscribe=1&folder=".urlencode($folder)."\";\n";
				echo "</script>\n";
				*/
			}
		}
		else
		{
			if(!empty($search))
			{
				$no_messages = 'There are no messages found in this folder';
			}
			else
			{
				$no_messages = 'There are no messages in this folder';
			}
		}
	}
	else
	{
		if(!empty($search))
		{
			$no_messages = 'There are no messages found in this folder';
		}
		else
		{
			$no_messages = 'There are no messages in this folder';
		}
	}
	
	if($no_messages)
	{
		?>
		<br>
		<table border="0" width="95%" align="center" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
			<tr>
				<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
					<? echo $no_messages; ?>
				</td>
			</tr>
		</table>
		<?
	}

	iil_Close($conn);

?>
<br>
</body>
</html>