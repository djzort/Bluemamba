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

	// Totally useless code ???
	$this_folder = $folder;
	if($undelete)
	{
		iil_C_Undelete($conn, $folder, $id);
	}
	
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
	
	// Flag as seen, if not traversing (i.e. using prev/next links)
	if((!$trav) || (!$my_prefs["nav_no_flag"]))
	{
		// Flag as read
		iil_C_Flag($conn, $folder, $id, "SEEN");

		// Reload folder list to refresh num unread
		if(($my_prefs["list_folders"]) && ($my_prefs["showNumUnread"]))
		{
			echo "\n<script language=\"JavaScript\">\n";
			echo "parent.list1.location=\"folders.php?user=".$user."\";\n";
			echo "</script>\n";
		}
	}
	

	// Generate next/previous links
	$next_link = "";
	$prev_link = "";
	if(($my_prefs["showNav"]) && (isset($sort_field)) && (isset($sort_order)))
	{
		// Fetch index
		// Attempt to read from cache
		include("../include/main.php");
		$read_cache = false;
		if(file_exists(realpath($CACHE_DIR)))
		{
			$cache_path = $CACHE_DIR . ereg_replace("[\\/]", "", $loginID . "." . $host);
			$index_a    = main_ReadCache($cache_path, $folder, "1:" . $num_msgs, $sort_field, $read_cache);
		}
		
		// If read form cache fails, go to server					
		if(!$read_cache) $index_a = iil_C_FetchHeaderIndex($conn, $folder, "1:" . $num_msgs, $sort_field);
		
		if($index_a !== false)
		{
			// Sort index
			if(strcasecmp($sort_order, "ASC") == 0) asort($index_a);
			elseif(strcasecmp($sort_order, "DESC") == 0) arsort($index_a);
			
			// Generate array where key is continuous and data contains message indices
			$count = 0;
			while(list($index_id, $blah) = each($index_a))
			{
				$table[$count] = $index_id;
				$count++;
			}
			
			// Look for current message
			$current_key = array_search($id, $table);
			$prev_id = $table[$current_key-1];
			$next_id = $table[$current_key+1];
		}
		elseif($sort_field == "DATE")
		{
			// If indexing failed, and ordered by date, just use id
			if($sort_order == "DESC")
			{
				$prev_id = $id + 1;
				$next_id = $id - 1;
				if($prev_id > $num_msgs) $prev_id = -1;
			}
			elseif($sort_order=="ASC")
			{
				$prev_id = $id - 1;
				$next_id = $id + 1;
				if($next_id > $num_msgs) $next_id = -1;
			}
		}
		
		
		if($prev_id > 0)
		{
			$prev_img = "<img border=\"0\" src=\"" . $THEME . "leftarrow.gif\">";
			$args = "user=$user&folder=" . urlencode($folder) . "&id=$prev_id&start=$start";
			$args.= "&num_msgs=$num_msgs&sort_field=$sort_field&sort_order=$sort_order&trav=1";
			$prev_link = "<a href=\"read_message.php?".$args."\" class=mainHeading>" . $prev_img . "</a>";
		}
		if($next_id > 0)
		{
			$next_img = "<img border=\"0\" src=\"" . $THEME . "rightarrow.gif\">";
			$args = "user=$user&folder=" . urlencode($folder) . "&id=$next_id&start=$start";
			$args.= "&num_msgs=$num_msgs&sort_field=$sort_field&sort_order=$sort_order&trav=1";
			$next_link = "<a href=\"read_message.php?" . $args . "\" class=mainHeading>" . $next_img . "</a>";
		}
	}
	
	
	// Determine if there are multiple recipients (or recipients other than self)
	// This, in turn, determines whether or not to show the "reply all" link
	if((!empty($header->cc)) || (substr_count($header->to, "@") > 1))
	{
		$multiple_recipients = true;
	}
	elseif( empty($header->replyto) && substr_count($header->to, "@") == 1 )
	{
		$multiple_recipients = true;
		
		$to_a = LangParseAddressList($header->to);
		$to_address = $to_a[0]["address"];
		
		if( !empty($my_prefs["email"]) && strcasecmp($to_address, $my_prefs["email"]) == 0 )
		{
			// One recipient, main address
			$multiple_recipients = false;
		}
		else
		{
			// One recipient.  check if known address for user
			include_once("../include/data_manager.php");
			$dm = new DataManager_obj;
			if($dm->initialize($loginID, $host, $DB_IDENTITIES_TABLE, $DB_TYPE))
			{
				$identities_a = $dm->read();
				if(is_array($identities_a))
				{
					reset($identities_a);
					while ( list($k, $v) = each($identities_a) )
					{
						$v = $identities_a[$k];
						if(strcasecmp($v["email"], $to_address)==0
							|| strcasecmp($v["replyto"], $to_address)==0)
						{
								$multiple_recipients = false;
						}
					}
				}
			}
		}
	}
	else 
	{
		$multiple_recipients = false;
	}

	flush();				

	// Show toolbar
	include("../include/read_message_tools.php");

	?>

	<br>

	<table width="100%" bgcolor="<?=$my_colors["main_hilite"]?>" border="0" cellspacing="1" cellpadding="1">
		<tr>
			<td>
	
				<table width="100%" height="25" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_head_bg"] ?>">
					<tr>
						<td valign="middle" class="tblheader">
							<b> &nbsp; <? echo encodeUTFSafeHTML($header->subject); ?></b>
						</td>
						<td align="right" valign="middle" class="mainToolBar">
							<a href="<? echo "view.php?user=$user&folder=$folder_url&id=$id&printer_friendly=1"; ?>" target="_blank" class="mainHeadingSmall">Print</a>
							<? 	
								// Display Edit Subject Link
								if($my_prefs["subject_edit"]==1)
								{
									echo " | <a href=\"subject_edit.php?user=$user&folder=$folder_url&id=$id\" target=\"$target\" class=\"mainHeadingSmall\">Edit Subject</a>"; 
								}
								if($my_prefs["advanced_controls"]==1)
								{
									echo " | <a href=\"view.php?user=$user&folder=$folder_url&id=$id&source=1\" target=\"_blank\" class=\"mainHeadingSmall\">Source</a>";
									echo " | <a href=\"view.php?user=$user&folder=$folder_url&id=$id&show_header=1\" target=\"_blank\" class=\"mainHeadingSmall\">Show Header</a>";
								}
								if($report_spam_to)
								{
									echo " | <a href=\"compose.php?user=$user&folder=$folder_url&forward=1&id=$id&show_header=1&to=".urlencode($report_spam_to)."\" class=\"mainHeadingSmall\">Report Spam</a>";
								}
							?>
							&nbsp;&nbsp;
						</td>
					</tr>
				</table>

				<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>Date:</b> &nbsp; <? echo encodeUTFSafeHTML(date($DATE_FORMAT, strtotime($header->date))); ?>
							&nbsp; &nbsp; &nbsp; 
							&nbsp; <b>Size:</b> &nbsp; <? echo ShowBytes($header->size); ?>
						</td>
					</tr>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>From:</b> &nbsp; <? echo LangDecodeAddressList($header->from, $CHARSET, $user); ?>
						</td>
					</tr>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>To:</b> &nbsp; <? echo LangDecodeAddressList($header->to, $CHARSET, $user); ?>
						</td>
					</tr>
					<?
			
					if(!empty($header->cc))
					{
						?>
						<tr bgcolor="<?=$my_colors["main_bg"]?>">
							<td valign="top">
								&nbsp; <b>CC:</b> &nbsp; <? echo LangDecodeAddressList($header->cc,  $CHARSET, $user); ?>
							</td>
						</tr>
						<?
					}
			
					if(!empty($header->replyto))
					{
						?>
						<tr bgcolor="<?=$my_colors["main_bg"]?>">
							<td valign="top">
								&nbsp; <b>Reply To:</b> &nbsp; <? echo LangDecodeAddressList($header->replyto,  $CHARSET, $user); ?>
							</td>
						</tr>
						<?
					}
				
			
					// Show attachments
					if($num_parts > 0)
					{
						?>
						<tr bgcolor="<?=$my_colors["main_bg"]?>">
							<td valign="top">
								&nbsp; <b>Attachments:</b><br>
								<table size="100%" border="0">
									<tr><td colspan='7'></td></tr>
										<?
								
										$icons_a = array("text.gif", "multi.gif", "multi.gif", "application.gif", "music.gif", "image.gif", "movie.gif", "unknown.gif");
								
										$k = 0;
										for($i = 1; $i <= $num_parts; $i++)
										{
											// Get attachment info
											if($parent_type == 1)
											{
												$code = $part . (empty($part)?"":".") . $i;
											}
											elseif($parent_type == 2)
											{
												$code = $part . (empty($part)?"":".") . $i;
											}
												
											$type        = iml_GetPartTypeCode($structure, $code);
											$name        = iml_GetPartName($structure, $code);
											$typestring  = iml_GetPartTypeString($structure,$code);
											list($dummy, $subtype) = explode("/", $typestring);
											$bytes       = iml_GetPartSize($structure,$code);
											$encoding    = iml_GetPartEncodingCode($structure, $code);
											$disposition = iml_GetPartDisposition($structure, $code);
								
											// Only Show attachments
											//if($disposition == "attachment" || $disposition == "inline")
											//{
												$k++;
												if((($k-1) % 3) == 0) echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
												
												// Format href
												if( ($type==1) || ($type==2) || ( ($type==3) && (strcasecmp($subtype, "ms-tnef")==0)) ) 
												{
													$href = "read_message.php?user=$user&folder=$folder_url&id=$id&part=" . $code;
												}
												else
												{
													$href = "view.php?user=$user&folder=$folder_url&id=$id&part=" . $code;
												}
												
												// Show icon, file name, size
												if($name != -1 || strlen($name) != 0)
												{
												?>
													<td valign="middle">
														<a href="<? echo $href; ?>" target="_blank"><img src="<? echo $THEME . $icons_a[$type]; ?>" border="0"></a>
													</td>
													<td valign="middle" class="small">
														<a href="<? echo $href; ?>" target="_blank"><?
															echo LangDecodeSubject($name, $my_charset);
															if($bytes > 0) echo " [".ShowBytes($bytes)."]";
														?></a> &nbsp &nbsp
													</td>
												<?
												}
												if(($k % 3) == 0) echo "</tr><tr><td colspan='7'></td></tr>";
											//}
										}
										?>
									</tr>
								</table>
								
							</td>
						</tr>
						<?
					}
				
					?>
					<tr bgcolor="<?=$my_colors["main_hilite"]?>" align="center">
						<td>
							&nbsp;
							<?
							if($header->answered)
							{
								echo " <b>You have replied to this message</b> &nbsp;";
							}
							?>
						</td>
					</tr>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td>
					
							<table width="90%" align="left" border="0" cellpadding="5">
								<tr>
									<td>
				
										<?
										/***** BEGIN READ MESSAGE HANDLER ****/
										
										// Now include the handler that determines what to display and how	
										include("../include/read_message_handler.php");	
										
										/***** END READ MESSAGE HANDLER *****/
										?>
				
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

<?

	//show toolbar
	include("../include/read_message_tools.php");

	iil_Close($conn);

?>
<br>
</body>
</html>