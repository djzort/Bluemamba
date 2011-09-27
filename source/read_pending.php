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
              
    Document: source/read_pending.php
              
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
include("../include/ftp.php");


	// Connect to mail server
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if(!$conn)
	{
		echo "<p>Failed to connect to mail server: $iil_error<br></body</html>";
		exit;
	}

	// Show toolbar
	include("../include/read_pending_tools.php");
	
	// Use FTP to change permissions so "webmaster" is able
	// to read the message. Reset permissions once finished.
	if($ftp_conn = xftp_conn())
	{
		$message;
		$message_file = ".tmda/pending/" . $id;
	
		xftp_chmod($message_file, "0666");			// Make Readable
	
		$fp = fopen($USER_BASE_DIR . $message_file, "r");
		while(!feof($fp))
		{
			$msg_line = fgets($fp);
			$message .= $msg_line;

			if(ereg("^From: (.*)\n", $msg_line, $regs))
			{
				$msg->from = $regs[1];
				unset($regs);
			}

			if(ereg("^To: (.*)\n", $msg_line, $regs))
			{
				$msg->to = $regs[1];
				unset($regs);
			}

			if(ereg("^Subject: (.*)\n", $msg_line, $regs))
			{
				$msg->subject = $regs[1];
				unset($regs);
			}

			if(ereg("^Date: (.*)\n", $msg_line, $regs))
			{
				$msg->date = $regs[1];
				unset($regs);
			}

		}
		fclose($fp);
	
		xftp_chmod($message_file, "0600");			// Make Unreadable
	}
	ftp_close($ftp_conn);
	
	if($message)
	{
		$msg->start = strpos($message, chr(10).chr(10)) + 2;
		$msg->body  = substr($message, $msg->start);
		$msg->size  = strlen($message);
	}
	else
	{
		echo "Unable to open message!</body></html>";
		exit;
	}


	?>
	
	<br>
	
	<table width="100%" bgcolor="<?=$my_colors["main_hilite"]?>">
		<tr>
			<td>
			
				<table width="100%" height="25" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_head_bg"] ?>">
					<tr>
						<td valign="middle" class="tblheader">
							<b> &nbsp; <? echo encodeUTFSafeHTML($msg->subject); ?></b>
						</td>

					</tr>
				</table>
			
				<br>
				
				<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="<?=$my_colors["main_hilite"]?>">
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>Date:</b> &nbsp; <? echo encodeUTFSafeHTML($msg->date); ?><br>
							&nbsp; &nbsp; &nbsp; 
							&nbsp; <b>Size:</b> &nbsp; <? echo ShowBytes($msg->size); ?><br>
						</td>
					</tr>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>From:</b> &nbsp; <? echo LangDecodeAddressList($msg->from,  $CHARSET, $user); ?><br>
						</td>
					</tr>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>To:</b> &nbsp; <? echo LangDecodeAddressList($msg->to, $CHARSET, $user); ?><br>
						</td>
					</tr>
					<?
			
				if(!empty($header->cc))
				{
					?>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>CC:</b> &nbsp; <? echo LangDecodeAddressList($msg->cc,  $CHARSET, $user); ?><br>
						</td>
					</tr>
					<?
				}
			
				if(!empty($header->replyto))
				{
					?>
					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td valign="top">
							&nbsp; <b>Reply To:</b> &nbsp; <? echo LangDecodeAddressList($msg->replyto,  $CHARSET, $user); ?><br>
						</td>
					</tr>
					<?
				}
			
					?>

					<tr bgcolor="<?=$my_colors["main_hilite"]?>">
						<td valign="top" align="center">&nbsp;
														
						</td>
					</tr>

					<tr bgcolor="<?=$my_colors["main_bg"]?>">
						<td>
					
							<table width="90%" align="left" border="0" cellpadding="5">
								<tr>
									<td>
				
										<?
										echo nl2br(encodeHTML($msg->body));
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
	include("../include/read_pending_tools.php");
	iil_Close($conn);
	?>
	
	<br> <br>
	
	<table align="center" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
			
				For security and saftey reasons, pending messages are limited in what will be display.
			
			</td>
		</tr>
	</table>
	
	<br>

</body>
</html>