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
              
    Document: include/compose.php
              
    Function: Form MIME format (RFC822) compliant messages
              Send message
              Save to "sent items" folder if so specified

*********************************************************************/

function RemoveDoubleAddresses($to)
{
	$to_adr = iil_ExplodeQuotedString(",", $to);
	$adresses = array();
	$contacts = array();
	foreach($to_adr as $addr)
	{
		$addr = trim($addr);
		if(preg_match("/(.*<)?.*?([^\s\"\']+@[^\s>\"\']+)/", $addr, $email))
		{
			$email = strtolower($email[2]);
			if(!in_array($email, $adresses))						// New address
			{
				array_push($adresses, $email);
				$contacts[$email] = $addr;
			} 
			elseif(strlen($contacts[$email]) < strlen($addr)) 		// Address already in list and name is longer
			{	
				$contacts[$email] = trim($addr);
			}
		}
	}
	return implode(", ",$contacts);
}


function ResolveContactsGroup($str)
{
	global $contacts;
	
	$tokens = explode(" ", $str);
	if(!is_array($tokens)) return $str;
	
	while(list($k, $token) = each($tokens))
	{
		if(ereg("@contacts.group", $token))
		{
			if(ereg("^<", $token)) $token = substr($token, 1);
			list($group, $junk) = explode("@contacts.", $token);
			$group = base64_decode($group);
			$newstr = "";
			reset($contacts);
			while(list($blah, $contact) = each($contacts))
			{
				if($contact["grp"]==$group && !empty($contact["email"]))
				{
					$newstr.= (!empty($newstr)?", ":"");
					$newstr.= "\"".$contact["name"]."\" <".$contact["email"].">";
				}
			}
			if(ereg(",$", $token)) $newstr.= ",";
			$tokens[$k] = $newstr;
			if(ereg(str_replace(" ", "_", $group), $tokens[$k-1])) $tokens[$k-1] = "";
		}
	}
	
	return implode(" ", $tokens);
}


if(ini_get('file_uploads') != 1)
{
	echo "Error:  Make sure the 'file_uploads' directive is enabled (set to 'On' or '1') in your php.ini file";
}



/******* Init values *******/
if(!isset($attachments))    $attachments   = 0;
if(isset($change_contacts)) $show_contacts = $new_show_contacts;
if(isset($change_show_cc))  $show_cc       = $new_show_cc;


// Read alternate identities
include_once("../include/data_manager.php");
$ident_dm = new DataManager_obj;
if($ident_dm->initialize($loginID, $host, $DB_IDENTITIES_TABLE, $DB_TYPE))
{
	$alt_identities = $ident_dm->read();
}


// Handle addresses submitted from contacts list 
//(in contacts window)
if(is_array($contact_to))  $to  .= (empty($to)?"":", ").urldecode(implode(", ", $contact_to));
if(is_array($contact_cc))  $cc  .= (empty($cc)?"":", ").urldecode(implode(", ", $contact_cc));
if(is_array($contact_bcc)) $bcc .= (empty($bcc)?"":", ").urldecode(implode(", ", $contact_bcc));

//(in compose window)
if((isset($to_a)) && (is_array($to_a)))
{
    reset($to_a);
    while(list($key, $val) = each($to_a))
	{
		$$to_a_field .= ($$to_a_field!=""?", ":"").stripslashes($val);
	}
}


// Generate authenticated email address
$sender_addr = $loginID;

// Do we need to add the host?
if(strpos($loginID, "@") == 0)
{
	$host_pieces = explode(".", $host);
	if($host_pieces[2])
	{
		$sender_addr .= "@" . $host_pieces[1] . "." . $host_pieces[2];
	}
	else
	{
		$sender_addr .= "@" . $host;
	}
}



// Generate user's name
$from_name = $my_prefs["user_name"];
$from_name = LangEncodeSubject($from_name, $CHARSET);
if((!empty($from_name)) && (count(explode(" ", $from_name)) > 1)) $from_name = '"' . $from_name . '"';

if($TRUST_USER_ADDRESS)
{
    // If email address is specified in prefs, use that in the "From"
    // field, and set the Sender field to an authenticated address
    $from_addr = (empty($my_prefs["email_address"]) ? $sender_addr : $my_prefs["email_address"] );
    $from      = $from_name . " <" . $from_addr . ">";
    $reply_to  = "";
}
else
{
    // Set "From" to authenticated user address
    // Set "Reply-To" to user specified address (if any)
	$from_addr = $sender_addr;
    $from = $from_name . " <" . $sender_addr . ">";
    if(!empty($my_prefs["email_address"])) 
	{
		$reply_to = $from_name . " <" . $my_prefs["email_address"] . ">";
	}
    else
	{
		$reply_to = "";
	}
}
$original_from = $from;



// Resolve groups added from contacts selector
$to_has_group =  $cc_has_group  = $bcc_has_group = false;
if(!empty($to))  $to_has_group  = ereg("@contacts.group", $to);
if(!empty($cc))  $cc_has_group  = ereg("@contacts.group", $cc);
if(!empty($bcc)) $bcc_has_group = ereg("@contacts.group", $bcc);

if($to_has_group || $cc_has_group || $bcc_has_group)
{
	$dm = new DataManager_obj;
	if($dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $DB_TYPE))
	{
		if(empty($sort_field)) $sort_field = "grp,name";
		if(empty($sort_order)) $sort_order = "ASC";
		$contacts = $dm->sort($sort_field, $sort_order);
		
		if($to_has_group)  $to  = ResolveContactsGroup($to);
		if($cc_has_group)  $cc  = ResolveContactsGroup($cc);
		if($bcc_has_group) $bcc = ResolveContactsGroup($bcc);
	}
}



// CHECK UPLOADS DIR
$uploadDir = $UPLOAD_DIR . ereg_replace("[\\/]", "", $loginID . "." . $host);
if(!file_exists(realpath($uploadDir))) 
{
	$error .= 'Uploads dir not found: ' . $uploadDir . '<br>';
}


// SEND
function cmp_send(){}
if(isset($send) || isset($savedraft))
{
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if(!$conn)
	{
		echo "failed";
	}
	else
	{
		$error = "";
		
		// Check for subject
        $no_subject = false;
		if((strlen($subject) == 0) && (!$confirm_no_subject))
		{
            $error .= "The subject field is empty. Please enter a subject or click \"Send Message\" again to send.<br>\n";
            $no_subject = true;
        }
		
		// Alternate identity?
		if($sender_identity_id > 0)
		{
			// Format sender name
			$from_name = $alt_identities[$sender_identity_id]["name"];
			$from_name = LangEncodeSubject($from_name, $CHARSET);
			if((!empty($from_name)) && (count(explode(" ", $from_name)) > 1))
			{
				$from_name = '"' . $from_name . '"';
			}
			
			// Format "From:" header
			$from_addr = $alt_identities[$sender_identity_id]["email"];
			$from      = $from_name . " <" . $from_addr . ">";
			
			// Format "Reply-To:" header
			if(!empty($alt_identities[$sender_identity_id]["replyto"]))
			{
				$reply_to = $from_name . " <" . $alt_identities[$sender_identity_id]["replyto"] . ">";
			}
			else
			{
				$reply_to = "";
			}
		}
		
		// Check "from"
		if(strlen($from) < 7)
		{
			$error .= 'The "from" field is empty.  Please enter your email address.<br>';
		}
		
		// Check for recepient
		$to = stripslashes($to);
		$to = str_replace(";", ",", $to);
		if((strcasecmp($to, "self") == 0) || (strcasecmp($to, "me") == 0)) 
		{
			$to = $my_prefs["email_address"];
		}

		if((strlen($to) < 7) || (strpos($to, "@")===false))
		{
			$error .= 'The "To" field is empty.  Please specify the destination address.<br>';
		}
			
		// Anti-Spam
		$as_ok = true;
		if( (isset($max_rcpt_message)) && ((isset($max_rcpt_session))) && (isset($min_send_interval)) )
		{
			$num_recepients = 0;
			$num_recepients = substr_count($to . $cc . $bcc, "@");
			if($num_recepients > $max_rcpt_message)
			{
				$as_ok = false;
			}

			if(($num_recepients + $numSent) > $max_rcpt_session)
			{
				$as_ok = false;
			}

			if((time() - $lastSend) < $min_send_interval)
			{
				$as_ok = false;
			}
		}
		else
		{
			echo "Bypassing anti-spam<br>\n";
		}

		if(!$as_ok)
		{
			$as_error = "For spam prevention reasons, you may only send to %1 people (%2 total per session) every %3 seconds.";
			$as_error = str_replace("%1", $max_rcpt_message, $as_error);
			$as_error = str_replace("%2", $max_rcpt_session, $as_error);
			$as_error = str_replace("%3", $min_send_interval, $as_error);
			$error .= $as_error;
		}


		if(!$error)
		{
			?>
			<p>Building Message....<br>

			<?
			
			flush();
			
			$num_parts = 0;
	
			// Initialize header
			$headerx    = "Date: " . TZDate($my_prefs["timezone"]) . "\r\n";
			$headerx   .= "X-Mailer: BlueMamba.org/$version (On: " . $_SERVER["SERVER_NAME"] . ")\r\n";
			$mt_str     = microtime();
			$space_pos  = strpos($mt_str, " ");
			$message_id = GenerateRandomString(8,"") . "." . substr($mt_str, $space_pos+1) . substr($mt_str, 1, $space_pos - 2) . "." . $sender_addr;
			$headerx   .= "Message-ID: <" . $message_id . ">\r\n";
			if(!empty($replyto_messageID))
			{
				$headerx .= "In-Reply-To: <".$replyto_messageID.">\r\n";
			}
		
			// Attach Sig
			if($attach_sig == 1)
			{
				if($sender_identity_id > 0) 
				{
					$message .= "\n\n" . $alt_identities[$sender_identity_id]["sig"];
				}
				else
				{
					$message .= "\n\n" . $my_prefs["signature1"];
				}
			}	

			// Attach Tag-line
			if($TAG_LINE)
			{
				$message .= "\n\n" . $TAG_LINE;
			}

			//  Smart Wrap
			$message = LangSmartWrap($message, $WORD_WRAP);

			//  Encode
			$subject = stripslashes($subject);
			$subject = LangEncodeSubject($subject, $CHARSET);
			
			$message = stripslashes($message);
			$part[0] = LangEncodeMessage($message, $CHARSET);

				
			//  Pre-process addresses
			$from = stripslashes($from);
			$to = stripslashes($to);
			$to = RemoveDoubleAddresses($to);
			
			echo "To: " . htmlspecialchars($to) . " <br>\n"; flush();
				
			$to = LangEncodeAddressList($to, $CHARSET);
			$from = LangEncodeAddressList($from, $CHARSET);
					
			if(!empty($cc))
			{
				$cc = stripslashes($cc);
				$cc = str_replace(";", ",",$cc);
				$cc = RemoveDoubleAddresses($cc);
				$cc = LangEncodeAddressList($cc, $CHARSET);

				echo "CC: " . htmlspecialchars($cc) . " <br>\n"; flush();
			}

			if(!empty($bcc))
			{
				$bcc = stripslashes($bcc);
				$bcc = str_replace(";", ",", $bcc);
				$bcc = RemoveDoubleAddresses($bcc);
				$bcc = LangEncodeAddressList($bcc, $CHARSET);

				echo "BCC: " . htmlspecialchars($bcc) . " <br>\n"; flush();
			}


			// Add Recipients
			$headerx .= "From: ".$from."\r\n";
			$headerx .= "Bounce-To: ".$from."\r\n";
			$headerx .= "Errors-To: ".$from."\r\n";
			if(!empty($reply_to)) 
			{
				$headerx .= "Reply-To: " . stripslashes($reply_to) . "\r\n";
			}
			if($cc)
			{
				$headerx .= "CC: " . stripslashes($cc) . "\r\n";
			}

				
			// Prepare attachments
			$attach_cur = 0;
			echo "<br> Attachments: " . count($attach) . " <br>\n"; flush();
			if(file_exists(realpath($uploadDir)))
			{
				if(is_array($attach))
				{
					while(list($file, $v) = each($attach))
					{
						if($v == 1)
						{
							// Split up file name
							$file_parts = explode(".", $file);
							
							// Put together full path
							$a_path = $uploadDir . "/" . $file;

							// Get name and type
							$a_name = mod_base64_decode($file_parts[1]);
							$a_type = strtolower(mod_base64_decode($file_parts[2]));
							if($a_type == "") $a_type = "application/octet-stream";								

							// If data is good...
							if( ($file_parts[0] == $user) && ( file_exists(realpath($a_path)) ) )
							{
								$num_parts++;
								$attach_cur++;
								echo "Attachment $attach_cur is good <br>\n"; flush();
								
								// Stick it in conent array
								$part[$num_parts]["type"] 		 = "Content-Type: $a_type; name=\"$a_name\"\r\n";
								$part[$num_parts]["disposition"] = "Content-Disposition: attachment; filename=\"$a_name\"\r\n";
								$part[$num_parts]["encoding"] 	 = "Content-Transfer-Encoding: base64\r\n";
								$part[$num_parts]["size"]        = filesize($a_path);
								$attachment_size                += $part[$num_parts]["size"];
								$part[$num_parts]["path"]        = $a_path;
							}
							elseif(strpos($file_parts[0], "fwd-") !== false)
							{
								// Forward an attachment
								// Extract specs of attachment
								$fwd_specs 	= explode("-", $file_parts[0]);
								$fwd_folder = mod_base64_decode($fwd_specs[1]);
								$fwd_id 	= $fwd_specs[2];
								$fwd_part 	= mod_base64_decode($fwd_specs[3]);
								
								// Get attachment content
								$fwd_content = iil_C_FetchPartBody($conn, $fwd_folder, $fwd_id, $fwd_part);

								// Get attachment header
								$fwd_header = iil_C_FetchPartHeader($conn, $fwd_folder, $fwd_id, $fwd_part);
								
								// Extract "content-transfer-encoding field
								$head_a = explode("\n", $fwd_header);
								if(is_array($head_a))
								{
									while(list($k, $head_line) = each($head_a))
									{
										$head_line = chop($head_line);
										if(strlen($head_line) > 15)
										{
											list($head_field,$head_val)=explode(":", $head_line);
											if(strcasecmp($head_field, "content-transfer-encoding")==0)
											{
												$fwd_encoding = trim($head_val);
												echo $head_field.": ".$head_val."<br>\n"; flush();
											}
										}
									}
								}
									
								// Create file in uploads dir
								$file   = $user .".". $file_parts[1] .".". $file_parts[2] .".". $file_parts[3];
								$a_path = $uploadDir . "/" . $file;
								$fp     = fopen($a_path, "w");
								if($fp)
								{
									fputs($fp, $fwd_content);
									fclose($fp);
								}
								else
								{
									echo "Error when saving fwd att to $a_path <br>\n"; flush();
								}
								$fwd_content = "";
									
								echo "Attachment $i is a forward <br>\n"; flush();
								$num_parts++;

								// Stick it in conent array
								$part[$num_parts]["type"]		  = "Content-Type: ".$a_type."; name=\"".$a_name."\"\r\n";
								$part[$num_parts]["disposition"]  = "Content-Disposition: attachment; filename=\"".$a_name."\"\r\n";
								if(!empty($fwd_encoding)) 
								{
									$part[$num_parts]["encoding"] = "Content-Transfer-Encoding: $fwd_encoding\r\n";
								}
								$part[$num_parts]["size"] 		  = filesize($a_path);
								$attachment_size                 += $part[$num_parts]["size"];
								$part[$num_parts]["path"] 		  = $a_path;
								$part[$num_parts]["encoded"] 	  = true;
							}
						}
					}
				}
			}

			
			// Put together MIME message
			echo "Num parts: $num_parts <br>\n"; flush();
			
			$received_header  = "Received: from ".$_SERVER["REMOTE_ADDR"]." (auth. user $loginID@$host)\r\n";
			$received_header .= "          by ".$_SERVER["SERVER_NAME"]." with HTTP; ".TZDate($my_prefs["timezone"])."\r\n";
			$headerx = $received_header."To: ".$to."\r\n".(!empty($subject)?"Subject: ".$subject."\r\n":"").$headerx;
			
			if($num_parts == 0)
			{
				// Simple message, just store as string
				$headerx .= "MIME-Version: 1.0 \r\n";
				$headerx .= $part[0]["type"];
				if(!empty($part[0]["encoding"])) 
				{
					$headerx .= $part[0]["encoding"];
				}
				$body = $part[0]["data"];
				/*
				// HTML EMAIL			
				if($my_prefs["compose_html"] == 1)
				{*/
					// Add HTML tags for HMTL base email
					//$body = "<html><head></head><body>" . $body . "</body></html>";
				/*}
				*/
				$message = $headerx."\r\n".$body;
				$is_file = false;
			}
			else
			{
				// For multipart message, we'll assemble it and dump it into a file
				
				echo "Uploads directory: $uploadDir <br>\n"; flush();
				if(file_exists(realpath($uploadDir)))
				{
					$tempID = ereg_replace("[/]","",$loginID) . time();
					$boundary = "RWP_PART_" . $tempID;
					
					$temp_file = $uploadDir ."/". $tempID;
					echo "Temp file: $temp_file <br>\n";
					$temp_fp = fopen($temp_file, "w");
					if($temp_fp)
					{
						// Setup header
						$headerx .= "MIME-Version: 1.0 \r\n";
						$headerx .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"; 

						// Write header to temp file
						fputs($temp_fp, $headerx."\r\n");
					
						// Write main body
						fputs($temp_fp, "This message is in MIME format.\r\n");
			
						// Loop through attachments
						for($i = 0; $i <= $num_parts; $i++)
						{
							// Write boundary
							fputs($temp_fp, "\r\n--".$boundary."\r\n");
							
							// Form part header
							$part_header = "";
							if($part[$i]["type"]!="")        $part_header .= $part[$i]["type"];
							if($part[$i]["encoding"]!="")    $part_header .= $part[$i]["encoding"];
							if($part[$i]["disposition"]!="") $part_header .= $part[$i]["disposition"];
							
							// Write part header
							fputs($temp_fp, $part_header."\r\n");
								
							// Open uploaded attachment
							$ul_fp = false;
							if( (!empty($part[$i]["path"])) && (file_exists(realpath($part[$i]["path"]))) )
							{
								$ul_fp = fopen($part[$i]["path"], "rb");
							}

							if($ul_fp)
							{
								// Transfer data in uploaded file to MIME message
								if($part[$i]["encoded"])
								{
									// Straight transfer if already encoded
									while(!feof($ul_fp))
									{
										$line = chop(fgets($ul_fp, 1024));
										fputs($temp_fp, $line."\r\n");
									}
								}
								else
								{
									// Otherwisee, base64 encode
									while(!feof($ul_fp))
									{
										// Read 57 bytes at a time
										$buffer = fread($ul_fp, 57);
										// Base 64 encode and write (line len becomes 76 bytes)
										fputs($temp_fp, base64_encode($buffer)."\r\n");
									}
								}
								fclose($ul_fp);
								unlink($part[$i]["path"]);
							}
							elseif(!empty($part[$i]["data"]))
							{
								// Write message (part is not an attachment)
								$message_lines = explode("\n", $part[$i]["data"]);
								while(list($line_num, $line)=each($message_lines))
								{
									$line = chop($line)."\r\n";
									fputs($temp_fp, $line);
								}
							}
						}
						
						// Write closing boundary
						fputs($temp_fp, "\r\n--".$boundary."--\r\n");
						
						// Close temp file
						fclose($temp_fp);
						
						$message = $temp_file;
						$is_file = true;
					}
					else
					{
						$error .= "Temp file could not be opened: $temp_file <br>\n"; flush();
					}
				}
				else
				{
					$error .= "Invlalid uploads directory<br>\n"; flush();
				}
			}
			
			// Clean up uploads directory
			if( file_exists(realpath($uploadDir)) )
			{
				// Open directory
				if($handle = opendir($uploadDir))
				{
					// Loop through files
					while(false !== ($file = readdir($handle)))
					{
						if($file != "." && $file != "..")
						{
							// Split up file name
							$file_parts = explode(".", $file);
				
							if( (count($file_parts)==4) && (strpos($file_parts[0], "fwd-") !== false) )
							{
								$path = $uploadDir ."/". $file;
								unlink($path);
							}
						} 
					}
					closedir($handle); 
				}
			}	
			


			// Send message
			if(!empty($error))
			{
				echo $error;
				echo "</body></html>"; flush();
				exit;
			}
			
			echo "<p>";


			$sent = false;
			if(empty($savedraft) && isset($send))
			{

				// Save temp file before we send the message
				echo "Saving temporary message in Unsent folder . . . "; flush();
				if($is_file) 
				{
					$saved = iil_C_AppendFromFile($conn, "INBOX.Unsent", $message);
				}
				else 
				{
					$saved = iil_C_Append($conn, "INBOX.Unsent", $message);
				}
	
				if(!$saved) 
				{
					$error .= "Failed: " . $conn->error . "\n";
					$temp_message = false;
				}
				else 
				{
					echo "Success<br>\n"; flush();
					$temp_message = $saved;
				}

				echo "<p>Sending Message . . . <p>"; flush();

				if(isset($SMTP_SERVER))
				{
					// Send thru SMTP server using cusotm SMTP library
					include("../include/smtp.php");
					
					// Connect to SMTP server
					$smtp_conn = smtp_connect($SMTP_SERVER, "25", $loginID, $password);
					
					if($smtp_conn)
					{
						// Generate list of recipients
						$recipients = $to . ", " . $cc . ", " . $bcc;
						$recipient_list = smtp_expand($recipients);
						echo "Sending to: " . htmlspecialchars(implode(",", $recipient_list)) . "<br>\n"; flush();
					
						// Send message 
						$sent = smtp_mail($smtp_conn, $from_addr, $recipient_list, $message, $is_file);
						// $sent is validated on line 810

					}
					else
					{
						echo "SMTP connection failed: $smtp_error \n"; flush();
					}
				}
				else
				{
					// Send using PHP's mail() function
					include_once("../include/smtp.php");
					$to = implode(",", smtp_expand($to));
					$to = ereg_replace("[<>]", "", $to);
					echo "Adjusted to: ".htmlspecialchars($to)."<br>"; flush();
					
					
					if($is_file)
					{
						// Open file
						$fp = fopen($message, "r");
						if($fp)
						{
							// Read header
							$header = "";
							do
							{
								$line = chop(iil_ReadLine($fp, 1024));
								if( (!empty($line)) && (!iil_StartsWith($line, "Subject:")) && (!iil_StartsWith($line, "To:")) )
								{
									$header .= $line."\n";
								}							
							}
							while((!feof($fp)) && (!empty($line)));
							
							echo nl2br($header);
							
							// Read body
							$body = "";
							while(!feof($fp))
							{
								$body .= chop(fgets($fp, 8192))."\n";
							}
							fclose($fp);
							
							echo "<br>From: $from_addr <br>\n"; flush();
							
							// Send
							if(ini_get("safe_mode") == "1")
							{
								$sent = mail($to, $subject, $body, $header);
							}
							else
							{
								$sent = mail($to, $subject, $body, $header, "-f $from_addr");
							}
						}
						else
						{
							$error .= "Couldn't open temp file $message for reading<br>\n";
						}
					}
					else
					{
						// Take out unnecessary header fields
						$header_a = explode("\n", $headerx);
						$header_a[2] = "User-Agent: BlueMamba.org";
	
						reset($header_a);
						while(list($k, $line) = each($header_a))
						{
							$header_a[$k] = chop($line);
						}
	
						$headerx = implode("\n", $header_a);
						$body = str_replace("\r", "", $body);
						
						echo "<br>From: $from_addr <br>\n"; flush();
	
						// Send
						if(ini_get("safe_mode") == "1")
						{
							$sent = mail($to, $subject, $body, $headerx);
						}
						else
						{
							$sent = mail($to, $subject, $body, $headerx, "-f $from_addr");
						}
					}
				}
			}


			echo "<p>";


			// Is Sent
			if($sent)
			{
				echo "Message successfully sent!<p>"; flush();
				$error = "";
				
				// Save in sent folder
				echo "Moving message to Sent folder . . . "; flush();
				if($is_file) 
				{
					$saved = iil_C_AppendFromFile($conn, "INBOX.Sent", $message);
				}
				else 
				{
					$saved = iil_C_Append($conn, "INBOX.Sent", $message);
				}

				if(!$saved) 
				{
					$error .= "Failed: " . $conn->error . "<br>\n";
				}
				else 
				{
					echo "Success<br>\n"; flush();
				}
				
				// Remove temporary message in Unsent Folder
				if($temp_message != false)
				{
					// Get ID's for temporary message
					list($tmp_mid, $tmp_uid) = explode("-", $temp_message);

					// Get normal IMAP id for message
					$UID2ID = iil_C_UID2ID($conn, "INBOX.Unsent", $tmp_uid);

					echo "<p>Removing temporary message in Unsent folder . . . "; flush();
					
					if(iil_C_Delete($conn, "INBOX.Unsent", $UID2ID) > 0)	// Delete message from Unsent
					{
						$return = iil_C_Expunge($conn, "INBOX.Unsent");		// Cleanup removed message
						echo "Success";
					}
					else
					{
						echo "FAILED: $UID2ID";
					}
					echo "</p>\n"; flush();
		
				}
				
				// Delete temp file, if necessary
				if($is_file) unlink($message);
				
				// If replying, flag original message
				if(isset($in_reply_to))
				{
					$reply_id = $in_reply_to;
				}
				elseif(isset($forward_of)) 
				{
					$reply_id = $forward_of;
				}

				if(isset($reply_id))
				{
					$pos          = strrpos($reply_id, ":");
					$reply_uid    = substr($reply_id, $pos+1);
					$reply_folder = substr($reply_id, 0, $pos);
					$reply_num    = iil_C_UID2ID($conn, $reply_folder, $reply_uid);
					
					if($reply_num !== false)
					{
						if(iil_C_Flag($conn, $reply_folder, $reply_num, "ANSWERED") < 1)
						{
							echo "Flagging failed: ".$conn->error." ()<br>\n"; flush();
						}
					}
					else
					{
						echo "UID -> ID conversion failed.<br>\n"; flush();
					}
				}
				
				// Update spam-prevention related records
				include("../include/antispam_update.php");

				if( empty($error) )
				{
					// Clean up uploads dir
					$uploadDir = $UPLOAD_DIR . ereg_replace("[\\/]", "", $loginID.".".$host);

					if(file_exists(realpath($uploadDir)))
					{
						if($handle = opendir($uploadDir))
						{
							while(false !== ($file = readdir($handle)))
							{
								if($file != "." && $file != "..")
								{
									$file_path = $uploadDir."/".$file;
									unlink($file_path);
								} 
							}
							closedir($handle); 
						}
					}	
					
					// Close window
					if( ($my_prefs["preview_window"] != 1) && ($my_prefs["closeAfterSend"] == 1) )
					{
						echo "\n<script type=\"text/javascript\">\n";
						echo "   DoCloseWindow(\"main.php?user=$user&folder=".(empty($folder)?"INBOX":urlencode($folder))."\");\n";
						echo "</script>\n";
					}

					echo "<br><br>"; flush();
				}
				else
				{
					echo $error;
				}
			}
			else	// Message Did not Send
			{
				if($savedraft)	// Save in drafts folder
				{
					echo "Saving message in Drafts folder . . . "; flush();
					if($is_file)
					{
						$saved = iil_C_AppendFromFile($conn, "INBOX.Drafts", $message);
					}
					else 
					{
						$saved = iil_C_Append($conn, "INBOX.Drafts", $message);
					}
	
					if(!$saved)
					{
						$error .= "Failed: " . $conn->error . "<br>\n"; flush();
					}
					else 
					{
						echo "Success<br>\n"; flush();
					}
				
				}
				else
				{
					echo "<p><font color=\"red\">Send FAILED</font><br>";
					echo $smtp_errornum . " : " . nl2br($smtp_error);
					$error = "";
					
					// Message is saved in unsent folder. Notify user
					echo "<p>Your message is located in your Unsent folder"; flush();

				}
				// Delete temp file, if necessary
				if($is_file) { unlink($message); }
				
			}

			iil_Close($conn); 
			exit;
		}

		iil_Close($conn);
	}
}


// HANDLE UPLOADED FILE
function upload(){}
if(isset($upload))
{
	if( ($userfile) && ($userfile!="none") )
	{
		$i = $attachments;
		$newfile = $user .".". mod_base64_encode($userfile_name) .".". mod_base64_encode($userfile_type) .".". mod_base64_encode($userfile_size);
		$newpath = $uploadDir ."/". $newfile;
		if(move_uploaded_file($userfile, $newpath))
		{
			$attach[$newfile] = 1;
		}
		else
		{
			echo $userfile_name . " : Upload failed";
		}
	}
	else
	{
		echo "No files received.";
	}
}


// Fetch list of uploaded files
function fetchUploads(){}
if(file_exists(realpath($uploadDir)))
{
	// Open directory
	if($handle = opendir($uploadDir))
	{
		// Loop through files
		while(false !== ($file = readdir($handle)))
		{
			if($file != "." && $file != "..")
			{
				// Split up file name
				$file_parts = explode(".", $file);
				
				// Make sure first part is session ID, and add to list
				if((strcmp($file_parts[0], $user)==0)||(strpos($file_parts[0], "fwd-")!==false))
				{
					$uploaded_files[] = $file;
				}
			} 
		}
		closedir($handle); 
	}
}

if(is_array($fwd_att_list))
{
	reset($fwd_att_list);
	while(list($file, $v) = each($fwd_att_list))
	{
		$uploaded_files[] = $file;
	}
}


// Replying or Forwarding
function replyOrForward(){}
if((isset($replyto)) || (isset($forward)) || (isset($draft)) || (isset($unsent)))
{
    // If REPLY, or FORWARD
	if((isset($folder)) && (isset($id)))
	{		
        include_once("../include/mime.php");
        
		// Connect
		$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);

		// Get message
		$header = iil_C_FetchHeader($conn, $folder, $id);

		// Check IMAP UID, if set
		if(($uid > 0) && ($header->uid!=$uid))
		{
			$temp_id = iil_C_UID2ID($conn, $folder, $uid);
			if($temp_id)
			{
				$header = iil_C_FetchHeader($conn, $folder, $temp_id);
			}
			else
			{
				echo "UID - MID mismatch:  UID $uid not found.  Original message no longer exists in $folder <br>\n";
				exit;
			}
		}


        $structure_str = iil_C_FetchStructureString($conn, $folder, $id);
        $structure = iml_GetRawStructureArray($structure_str);
		
		$subject = LangDecodeSubject($header->subject, $CHARSET);

		$lookfor = (isset($replyto)?"Re: ":(isset($forward)?"Fwd:":" "));

		$pos = strpos ($subject, $lookfor);
        if($pos === false)
		{
			$pos = strpos ($subject, strtoupper($lookfor));
        	if($pos === false)
			{
				$subject = $lookfor.$subject;
			}
        }
		

		// Get messageID
		$replyto_messageID = $header->messageID;
		
		// Get "from";
		$from = $header->from;

		// Replace to "reply-to" if specified
		if($replyto)
		{
			$to = $from;
			if(!empty($header->replyto)) 
			{
				$to = $header->replyto;
			}
		}

		if($replyto_all || $draft || $unsent)
		{
			if(!empty($header->to)) $to .= (empty($to)?"":", ").$header->to;
			if(!empty($header->cc)) $cc .= (empty($cc)?"":", ").$header->cc;
		}

		// Mime decode "to," "cc," and "from" fields
		if(isset($to))
		{
			$to_a = LangParseAddressList($to);
			if(count($to_a)>1)
			{
				$to = "";
				while(list($k, $v) = each($to_a))
				{
					// Remove user's own address from "to" list
              	  	if((stristr($to_a[$k]["address"], $from_addr) === false) &&
 					    (stristr($to_a[$k]["address"], $loginID."@".$host) === false) &&
						($my_prefs["email_address"] != $to_a[$k]["address"]))
					{
						$to .= (empty($to)?"":", ")."\"".LangDecodeSubject($to_a[$k]["name"], $CHARSET)."\" <".$to_a[$k]["address"].">";
               	 	}
            	}
				$to = RemoveDoubleAddresses($to);
			}
			elseif(count($to_a) == 1)
			{
				$to = "\"".LangDecodeSubject($to_a[0]["name"], $CHARSET)."\" <".$to_a[0]["address"].">";
			}
		}
		if(isset($cc))
		{
			$cc_a = LangParseAddressList($cc);
			$cc = "";
			while(list($k, $v) = each($cc_a))
			{
				echo "<!-- CC: ".$cc_a[$k]["address"]." //-->\n";
                // Remove user's own address from "cc" list
                if((stristr($cc_a[$k]["address"], $from_addr) === false) &&
 				    (stristr($cc_a[$k]["address"], $loginID."@".$host) === false) &&
					($my_prefs["email_address"] != $cc_a[$k]["address"]))
				{
                    $cc .= (empty($cc)?"":", ") . "\"" . LangDecodeSubject($cc_a[$k]["name"], $CHARSET)."\" <".$cc_a[$k]["address"].">";
                }
            }
		}
		
		$from_a = LangParseAddressList($from);
		$from = "\"".LangDecodeSubject($from_a[0]["name"], $CHARSET)."\" <".$from_a[0]["address"].">";
		
		// Format headers for reply/forward
		if(isset($replyto))
		{
			$message_head = "On %d, %s wrote:\n";
			$message_head = str_replace("%d", LangFormatDate($header->timestamp, "%m/%d/%y"), $message_head);
			$message_head = str_replace("%s", $from, $message_head);
		}
		elseif(isset($forward))
		{
			if($show_header)
			{
				$message_head = iil_C_FetchPartHeader($conn, $folder, $id, 0);
			}
			else
			{
				$message_head = "--- Original Message ---\n";
				$message_head .= "Date: "   . ShowDate2($header->date,"","short")          ."\n";
				$message_head .= "From: "   . LangDecodeSubject($from, $CHARSET)           ."\n";
				$message_head .= "Subject: ". LangDecodeSubject($header->subject, $CHARSET)."\n\n";
			}
		}

		if(!empty($message_head)) 
		{
			$message_head = "\n$message_head\n";
		}
		
		// Get message attachments
		if($forward)
		{
			$att_list = iml_GetPartList($structure, "");
			while(list($i,$v) = each($att_list))
			{
				if((strcasecmp($att_list[$i]["disposition"], "inline") == 0)
					|| (strcasecmp($att_list[$i]["disposition"], "attachment") == 0)
					|| (!empty($att_list[$i]["name"])))
				{
					$file = "fwd-".mod_base64_encode($folder)."-$id-".base64_encode($i);
					$file .= ".".mod_base64_encode($att_list[$i]["name"]);
					$file .= ".".mod_base64_encode($att_list[$i]["typestring"]);
					$file .= ".".mod_base64_encode($att_list[$i]["size"]);

					if(!$fwd_att_list[$file])
					{
						$uploaded_files[]    = $file;
						$fwd_att_list[$file] = 1;
						$attach[$file]       = 1;
					}
				}
			}
		}

		// Get message
        if(!empty($part))
		{
			$part .= ".1";
		}
        else
		{
            $part = iml_GetFirstTextPart($structure, "");
        }

		$message = iil_C_FetchPartBody($conn, $folder, $id, $part);

		// Decode message if necessary
        $encoding = iml_GetPartEncodingCode($structure, $part);        
		if($encoding == 3) 
		{
			$message = base64_decode($message);
		}
		elseif($encoding == 4)
		{
            $message = str_replace("=\n", "", $message);
            $message = quoted_printable_decode(str_replace("=\r\n", "", $message));
        }
		
		// Add quote marks
		$message = str_replace("\r", "", $message);
		$charset = iml_GetPartCharset($structure, $part);


		$message = LangConvert($message, $CHARSET, $charset);
		if(isset($replyto)) 
		{
			$message = ">".str_replace("\n","\n>",$message);
		}
		$message = "\n".LangConvert($message_head, $CHARSET, $charset) . $message;
		
		iil_Close($conn);			
	}
}
else
{
	$message = stripslashes($message);
}


?>
