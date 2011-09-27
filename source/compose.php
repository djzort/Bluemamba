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
              
    Document: source/compose.php
              
    Function: Provide interface for creating messages
              Provide interface for uploading attachments

*********************************************************************/

include_once("../include/super2global.php");
include_once("../include/header_main.php");
include_once("../include/icl.php");
include_once("../include/version.php");
include_once("../include/mod_base64.php");
include_once("../include/compose.php");
include_once("../conf/defaults.php");


function showForm(){}

?>
<script language="JavaScript" type="text/javascript" src="wysiwyg/wysiwyg.js"></script>
<form name="messageform" enctype="multipart/form-data" action="compose.php?user=<?=$user?>" method="POST" onSubmit='DeselectAdresses(); close_popup(); return true;' style="display:inline">
	<input type="hidden" name="user" value="<?=$user?>">
	<input type="hidden" name="show_contacts" value="<?=$show_contacts?>">
	<input type="hidden" name="show_cc" value="<?=$show_cc?>">
<?
if($no_subject)
{
	?><input type="hidden" name="confirm_no_subject" value="1"><?
}

if(($replyto) || ($in_reply_to))
{
	if(empty($in_reply_to)) 
	{
		$in_reply_to = $folder.":".$uid;
	}
	?>
	<input type="hidden" name="in_reply_to" value="<? echo $in_reply_to; ?>">
	<input type="hidden" name="replyto_messageID" value="$<? echo replyto_messageID; ?>">
	<?
}
elseif(($forward) || ($forward_of))
{
	if(empty($forward_of))
	{
		$forward_of = $folder.":".$uid;
	}
	?><input type="hidden" name="forward_of" value="<? echo $forward_of; ?>"><?
}

if(is_array($fwd_att_list))
{
	reset($fwd_att_list);
	while(list($file, $v) = each($fwd_att_list))
	{
		?><input type="hidden" name="<? echo $fwd_att_list[$file]; ?>" value="1"><?
	}
}

if(!empty($folder))
{
	?><input type="hidden" name="folder" value="<?=$folder?>"><?
}

?>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>">

			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td class="bigTitle">
						&nbsp; Compose Message
					</td>
					<td align="right">
						&nbsp;<?
						if(!$my_prefs["compose_inside"])
						{
							$jsclose = "<a href=\"javascript:window.close();\" class=\"mainHeadingSmall\">Close Window</a>";
							echo "<SCRIPT type=\"text/javascript\" language=\"JavaScript1.2\">\n document.write('$jsclose'); \n</SCRIPT>";
						}
						?>
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
			<?
			if($CHECK_SPELLING) 
			{ 
				?><input type="submit" name="run_spellcheck" value="Check Spelling"> &nbsp; &nbsp; &nbsp; &nbsp; <?
			}
			?>
			<input type="submit" name="savedraft" value="Save as Draft"> &nbsp; &nbsp; &nbsp; &nbsp; 
			<input type="submit" name="send" value="Send Message">
		</td>
	</tr>
</table>

<br>

<?

if(!empty($error))
{
	echo '<font color="red">'.$error.'</font><br>';
}
$to  = encodeUTFSafeHTML($to);
$cc  = encodeUTFSafeHTML($cc);
$bcc = encodeUTFSafeHTML($bcc);

$email_address = htmlspecialchars($original_from);

?>
<table border="0" cellspacing="0" cellpadding="0" width="95%" align="center">
	<tr>
		<td>

			<table border="0" cellspacing="1" cellpadding="3" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"> &nbsp;Message Header</td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">
			
						<table border="0" class="mainLight" align="center">
							<tr>
								<td align="right">Subject:</td>
								<td><input type="text" class="textbox" name="subject" value="<? echo encodeUTFSafeHTML(stripslashes($subject)); ?>" size="<? echo $WORD_WRAP; ?>" onKeyUp="fixtitle('Compose Message');"></td>
							</tr>
							<tr>
								<td align="right">From:</td>
								<td height="20">
								<?
									if(($alt_identities) && (count($alt_identities) > 1))
									{
										?>
										<select name="sender_identity_id">
										<option value="-1">
											<?
											echo LangDecodeSubject($email_address, $CHARSET);
					
											while(list($key, $ident_a) = each($alt_identities))
											{
												if( $ident_a["name"] != $my_prefs["user_name"] || $ident_a["email"] != $my_prefs["email_address"] )
												{
													echo "<option value=\"$key\" ".($key==$sender_identity_id?"SELECTED":"").">";
													echo "\"".$ident_a["name"]."\"&nbsp;&nbsp;&lt;".$ident_a["email"]."&gt;\n";
												}
											}
											?>
										</select>
										<?
									}
									else
									{
										echo LangDecodeSubject($email_address, $CHARSET);
									}
								?>
								</td>
							</tr>
							<?
						
							$contacts_shown = false;
							if($my_prefs["showContacts"] != 0)
							{
								$contacts_shown = true;
								?>
								<tr>
									<td colspan="2" height="10"></td>
								</tr>
								<tr>
									<td align="right" valign="top">
									<?
										if($my_prefs["showContacts"] == 2)
										{				
											// Load Contacts
											include_once("../include/data_manager.php");
											$source_name = $DB_CONTACTS_TABLE;
											if(empty($source_name)) $source_name = "contacts";
											$dm = new DataManager_obj;
											if($dm->initialize($loginID, $host, $source_name, $DB_TYPE))
											{
												if(empty($sort_field)) $sort_field = "name";
												if(empty($sort_order)) $sort_order = "ASC";
												$contacts = $dm->sort($sort_field, $sort_order);
											}
											else
											{
												echo "Data Manager initialization failed:<br>\n";
												$dm->showError();
											}
											
											if((is_array($contacts)) && (count($contacts) > 0))
											{
												?>
												<select name="to_a_field">
													<option value="to">To:
													<?
													if(($my_prefs["showCC"] == 1) || ($show_cc))
													{
														?>
														<option value="cc">CC:
														<option value="bcc">BCC:
														<?
													}
													?>
												</select>
												<br> <br>
												<script type="text/javascript" language="JavaScript1.2">
													document.write('<input type="button" name="add_contacts" value="Add" onClick="CopyAdresses()">');
												</script>
												<noscript>
													<input type="submit" name="add_contacts" value="Add"><br>
												</noscript>
												<?
											}
										}				
									?>
								</td>
								<td>
									<?
			
									if($my_prefs["showContacts"] == 1)
									{
										// Show contacts button
										echo "<input type=\"hidden\" name=\"new_show_contacts\" value=\"1\">\n";
										$showcon_link  = "<a href=\"javascript:open_popup('contacts_popup.php?user=$user')\" class=\"mainLight\">";
										$showcon_link .= "<img src=\"" . $THEME . "addc.gif\" border='0'></a> " . $showcon_link . "Show Contacts</a>";
										$showcon_link = addslashes($showcon_link);
										echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">\n";
										echo "document.write('$showcon_link');\n";
										echo "</script>\n";
									}
									
									if($my_prefs["showContacts"] == 2)
									{
									
										// Display "select" box with contacts
										if((is_array($contacts)) && (count($contacts) > 0))
										{
											?>
											<select name="to_a[]" multiple size="7" ondblclick='CopyAdresses(); return true;'>
											<?
											while(list($key, $foobar) = each($contacts))
											{
												$contact = $contacts[$key];
												if(!empty($contact["email"]))
												{
													$line = $contact["name"]."  <".$contact["email"].">";
													echo "<option>".htmlspecialchars($line)."\n";
												}
												if(!empty($contact["email2"]))
												{
													$line = $contact["name"]."  <".$contact["email2"].">";
													echo "<option>".htmlspecialchars($line)."\n";
												}
											}
											?>
											</select>
											<?
										}
									}
								?>
								</td>
							</tr>
							<tr>
								<td colspan="2" height="10"></td>
							</tr>
							<?
							}
						
					
							// Display to field
							?>
							<tr>
								<td align="right">To:</td>
								<td>
								<?
									if(strlen($to) < 60)
									{	?><input type="text" class="textbox" name="to" value="<? echo stripslashes($to); ?>" size="<? echo $WORD_WRAP; ?>"><?	}
									else
									{	?><textarea name="to" class="textbox" cols="<? echo $WORD_WRAP; ?>" rows="3"><? echo stripslashes($to) ?></textarea><?	}
								?>
								</td>
							</tr>
							<?
						
					
							// Display cc box
							$cc_field_shown = false;
							if((!empty($cc)) || ($my_prefs["showCC"] == 1) || ($show_cc))
							{
								$cc_field_shown = true;
								?>
								<tr>
									<td align="right">CC:</td>
									<td>
									<?
									if(strlen($cc) < 60)
									{
										?><input type="text" class="textbox" name="cc" size="<? echo $WORD_WRAP; ?>" value="<? echo stripslashes($cc); ?>"><?
									}
									else
									{
										?><textarea name="cc" class="textbox" cols="<? echo $WORD_WRAP; ?>" rows="3"><? echo stripslashes($cc); ?></textarea><?
									}
									?>
									</td>
								</tr>
								<?
							}
								
						
							// Display bcc box
							$bcc_field_shown = false;
							if((!empty($bcc)) || ($my_prefs["showCC"] == 1) || ($show_cc))
							{
								$bcc_field_shown = true;
								?>
								<tr>
									<td align="right">BCC:</td>
									<td>
									<?
									if(strlen($bcc) < 60)
									{
										?><input type="text" name="bcc" class="textbox" size="<? echo $WORD_WRAP; ?>" value="<? echo stripslashes($bcc); ?>"><?
									}
									else
									{
										?><textarea name="bcc" class="textbox" cols="<? echo $WORD_WRAP; ?>" rows="3"><? echo stripslashes($bcc); ?></textarea><?
									}
									?>
									</td>
								</tr>
								<?
							}
						
					
							// Show attachments
							?>
							<tr>
								<td colspan="2" height="10"></td>
							</tr>
							<tr>
								<td align="right" valign="top">Attach:</td>
								<td valign="top">
								<?
									if((is_array($uploaded_files)) && (count($uploaded_files)>0))
									{
										?>
										<table border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
										<?
										reset($uploaded_files);
										while(list($k, $file) = each($uploaded_files))
										{
											$file_parts = explode(".", $file);
											?>
											<tr bgcolor="<?=$my_colors["main_bg"]?>">
												<td valign="middle"><input type="checkbox" name="attach[<? echo $file; ?>]" value="1" <? echo ($attach[$file]==1?"CHECKED":""); ?>></td>
												<td valign="middle"><? echo mod_base64_decode($file_parts[1]); ?>&nbsp;</td>
												<td valign="middle" class="small"><? echo mod_base64_decode($file_parts[3]); ?>bytes&nbsp;</td>
												<td valign="middle" class="small">(<? echo mod_base64_decode($file_parts[2]); ?>)</td>
											</tr>
											<?
										}
										?>
										</table>
										<?
									}
								
									if($MAX_UPLOAD_SIZE)
									{
										$max_file_size = $MAX_UPLOAD_SIZE;
									}
									else
									{
										$max_file_size = ini_get('upload_max_filesize');
									}

									if(eregi("M$", $max_file_size))
									{
										$max_file_size = (int)$max_file_size * 1000000;
									}
									elseif(eregi("K$", $max_file_size))
									{
										$max_file_size = (int)$max_file_size * 1000;
									}
									?>
									<input type="hidden" name="MAX_FILE_SIZE" value="<? echo $max_file_size; ?>">
									<input type="file"   name="userfile" class="textbox">
									<input type="submit" name="upload" value="Upload">
								</td>
							</tr>
						</table>
			
					</td>
				</tr>
			</table>
				
			<?
			if(($CHECK_SPELLING && $run_spellcheck) || $correct_spelling)
			{
				?>
				<br>
	
				<table border="0" cellspacing="1" cellpadding="3" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
					<tr class="tblheader">
						<td bgcolor="<?=$my_colors["main_head_bg"] ?>"> &nbsp;Spell Check</td>
					</tr>
					<tr>
						<td bgcolor="<?=$my_colors["main_bg"]?>">
	
							<table border="0" cellspacing="0" cellpadding="7" align="center">
								<tr>
									<td>
										<?
										if($CHECK_SPELLING && $run_spellcheck)
										{
											include_once("../include/spellcheck.php");
										
											// Run spell check
											$result = splchk_check($message, $SPELLING_LANG);
											if($result)
											{
												$words = $result["words"];
												$positions = $result["pos"];
												if(count($positions) > 0)
												{
													// Show errors and possible corrections
													echo "<b>Possible Spelling Errors</b> <br>\n";
										
													$splstr["ignore"]   = "ignore";
													$splstr["delete"]   = "delete";
													$splstr["correct"]  = "Correct Spelling";
													$splstr["nochange"] = "No Changes";
													$splstr["formname"] = "messageform";
												
													splchk_showform($positions, $words, $splstr);
												}
												else
												{
													// Show "no changes needed"
													echo "No spelling errors found.";
												}
											}
											else
											{
												echo "Spell checking not available for specified language.";
											}
										}
										elseif($correct_spelling)
										{
											// Correct spelling
											include_once("../include/spellcheck.php");
										
											// Do some shifting here...
											while(list($num, $word) = each($words))
											{
												$correct_var = "correct" . $num;
												$correct[$num] = $$correct_var;
											}
											?>
											<b>Spelling Changes</b><br>
											<?
											// Do the actual corrections
											$message = splchk_correct($message, $words, $offsets, $suggestions, $correct);
										}
										?>
								</td>
							</tr>
						</table>
					
					</td>
				</tr>
			</table>
			<?
			}
			?>
		
			<br>

			<table border="0" cellspacing="1" cellpadding="3" width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
				<tr class="tblheader">
					<td bgcolor="<?=$my_colors["main_head_bg"] ?>"> &nbsp;Message Body</td>
				</tr>
				<tr>
					<td bgcolor="<?=$my_colors["main_bg"]?>">
					
						<table border="0" cellspacing="0" cellpadding="7" align="center">
							<tr>
								<td align="center">
									<textarea name="message" id="message" class="textbox" rows="20" cols="<? echo $WORD_WRAP + 10; ?>" wrap="virtual"><? echo "\n".encodeUTFSafeHTML($message); ?></TEXTAREA>
								</td>
							</tr>
							<tr>
								<td class="mainLight">
									<input type="checkbox" name="attach_sig" value="1"<? echo ($my_prefs["show_sig1"]==1?" CHECKED":""); ?>> Attach signature
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
			<?
			if($CHECK_SPELLING) 
			{ 
				?><input type="submit" name="run_spellcheck" value="Check Spelling"> &nbsp; &nbsp; &nbsp; &nbsp; <?
			}
			?>
			<input type="submit" name="savedraft" value="Save as Draft"> &nbsp; &nbsp; &nbsp; &nbsp; 
			<input type="submit" id="button" name="send" value="Send Message">
		</td>
	</tr>
</table>
</form>
<script language="javascript1.2">
  generate_wysiwyg('messageNONE');
</script>

<br>
<script type="text/javascript">
	var _p = this.parent;
	if(_p==this)
	{
		_p.document.title = "Compose Message";
	}
</script>

</body>
</html>