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
              
    Document: source/filters_tmda.php
              
    Function: Filter Editor for Tagged Message Delivery Agent (TMDA)
              While and Blacklist filtering

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../include/data_manager.php");
include("../include/filters.php");

include("../include/filters_menu.php");


// Authenticate
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if(!$conn)
{
	echo "Authentication failed.";
	echo "</body></html>\n";
	exit;
}
iil_Close($conn);


// Activate / Disable
if($enable == 1)
{
	$my_prefs['tmda'] = "1";
}
elseif($enable == -1)
{
	$my_prefs['tmda'] = "0";
}
if(isset($enable))
{
	include_once("../include/save_prefs.php");
	include_once("../include/tmda_datafunc.php");
	// Refresh Folders List
	echo "\n<script language=\"JavaScript\">\n";
	echo "parent.list1.location=\"folders.php?user=" . $user . "\";\n";
	echo "</script>\n";
}


?>
<br>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="95%">
	<tr>
		<td>
			<?
			if($my_prefs["tmda"] == 1)	// Use TMDA
			{
			
				include_once("../include/filters_tmda.php");
				
				// List filters
				if(empty($edit))
				{
					?>
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
						<tr>
							<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
								&nbsp; <span class="tblheader">Advanced Filters</span>&nbsp;&nbsp;
								<a href="?user=<?=$user?>&edit=-1" class="mainHeadingSmall">Add Sender</a>
								<a href="?user=<?=$user?>&edit=-2" class="mainHeadingSmall">Import Contacts</a>
								<a href="?user=<?=$user?>&delete_all=1" class="mainHeadingSmall">Delete All</a>
								<a href="?user=<?=$user?>&enable=-1" class="mainHeadingSmall">Disable</a>
							</td>
						</tr>
					</table>
					
					<?
					if($error)
					{
						echo '<br> <font color="#FF0000">' . $error .'</font> <br> ';
					}
					?>
					<br>

					<?
					// Are there senders in the lists
					if( (filesize("$tmda_dir/lists/blacklist") > 0)   || (filesize("$tmda_dir/lists/whitelist") > 0) ||
						(filesize("$tmda_dir/lists/wcblacklist") > 0) || (filesize("$tmda_dir/lists/wcwhitelist") > 0) )
					{
						?>
						<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" align="center">
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						
						
									<table align="center" border="0" cellspacing="0" cellpadding="10">
										<tr>
											<?
											if((filesize("$tmda_dir/lists/whitelist") > 0) || (filesize("$tmda_dir/lists/wcwhitelist") > 0))
											{
												?>
												<td align="left" valign="top">
													Whitelist<br> <br>
					
													<form method="post" action="<? echo "?user=".$user; ?>" style="display:inline">
														<input type="hidden" name="user" value="<?=$user?>">
														<input type="hidden" name="session" value="<?=$user?>">
														<input type="hidden" name="list" value="whitelist">
														<input type="hidden" name="edit" value="1">
														
														<select name="email" size="10">
															<?
															$counter = 0;
															$cf = fopen("$tmda_dir/lists/whitelist", "r");
																while(!feof($cf))
																{
																	$line = chop(fgets($cf));
																	if($line)
																	{
																		$counter++;
																		echo '<option value="' . $counter . '^' . $line . '">' . $line . '</option>';
																	}
																}
															fclose($cf);
															
															$counter = 0;
															$cf = fopen("$tmda_dir/lists/wcwhitelist", "r");
																while(!feof($cf))
																{
																	$line = chop(fgets($cf));
																	if($line)
																	{
																		$counter++;
																		echo '<option value="wc' . $counter . '^' . $line . '">' . $line . '</option>';
																	}
																}
															fclose($cf);
															?>
														</select>
								
														<br>
														<input type="submit" name="edit" value="Edit">
													</form>
					
												</td>
												<?
											}
					
											if((filesize("$tmda_dir/lists/blacklist") > 0) || (filesize("$tmda_dir/lists/wcblacklist") > 0))
											{
												?>
												<td align="left" valign="top">
													
													Blacklist<br> <br>
					
													<form method="post" action="<? echo "?user=".$user; ?>" style="display:inline">
														<input type="hidden" name="user" value="<?=$user?>">
														<input type="hidden" name="session" value="<?=$user?>">
														<input type="hidden" name="list" value="blacklist">
														<input type="hidden" name="edit" value="1">
														
														<select name="email" size="10">
															<?
															$counter = 0;
															$cf = fopen("$tmda_dir/lists/blacklist", "r");
																while(!feof($cf))
																{
																	$line = fgets($cf);
																	if($line)
																	{
																		$counter++;
																		echo '<option value="' . $counter . '^' . $line . '">' . $line . '</option>';
																	}
																}
															fclose($cf);
															
															$counter = 0;
															$cf = fopen("$tmda_dir/lists/wcblacklist", "r");
																while(!feof($cf))
																{
																	$line = fgets($cf);
																	if($line)
																	{
																		$counter++;
																		echo '<option value="wc' . $counter . '^' . $line . '">' . $line . '</option>';
																	}
																}
															fclose($cf);
															?>
														</select>
								
														<br>
														<input type="submit" name="edit" value="Edit">
													</form>
					
												</td>
												<?
											}
											?>
										</tr>
									</table>
					
								</td>
							</tr>
						</table>
						<?
					}
					else
					{
						?>
						<table width="100%" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>">
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
									Your Whitelist and Blacklist is empty. <a href="?user=<?=$sid?>&edit=-1" class="mainLight">Add Sender</a>
								</td>
							</tr>
						</table>
						<?
					}
				}

				
				// Show Edit Form
				if($edit && ($edit != -1) && ($edit != -2))
				{
					?>
					<form method="post" action="<? echo "?user=".$user; ?>" style="display:inline">
						<input type="hidden" name="user" value="<?=$user?>">
						<input type="hidden" name="session" value="<?=$user?>">
						<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>">
							<tr>
								<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left">
									&nbsp; <span class="tblheader">Edit Sender</span>&nbsp;&nbsp;
									<a href="?user=<?=$user?>" class="mainHeadingSmall">List Senders</a>
								</td>
							</tr>
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">

									<?
									if($email)
									{
										?>
										<table border="0" cellspacing="0" cellpadding="5">
											<tr>
												<td>
													Edit email:
												</td>
												<td>
													<? 
													// Seperate Row count from email address
													$email = explode("^", $email, 2); 
													?>
													<input type="text" name="email" size="30" class="textbox" value="<? echo $email[1]; ?>">
													<input type="hidden" name="edit_id" value="<? echo $email[0]; ?>">
													<input type="hidden" name="prevlist" value="<? echo $list; ?>">
												</td>
												<td>
													move to
												</td>
												<td>
													<select name="list" class="textbox">
														<option value="whitelist"<? if($list=="whitelist") {echo " selected";} ?>>Whitelist</option>
														<option value="blacklist"<? if($list=="blacklist") {echo " selected";} ?>>Blacklist</option>
													</select>
												</td>
												<td>
													<input type="submit" name="save" value="Save">
													<input type="submit" name="delete" value="Delete">
												</td>
											</tr>
										</table>
										<?
									}
									else
									{
										?>
										Nothing selected. <a href="?user=<?=$sid?>" class="mainLight">List Senders</a>
										<?
									}
									?>

								</td>
							</tr>
						</table>
					</form>
					<?
				}
			
			
				// Show Add Form
				if($edit == -1)
				{
					?>					
					<form method="post" action="<? echo "?user=".$user; ?>" style="display:inline">
						<input type="hidden" name="user" value="<?=$user?>">
						<input type="hidden" name="session" value="<?=$user?>">
						<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>">
							<tr>
								<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left">
									&nbsp; <span class="tblheader">Add Sender</span>&nbsp;&nbsp;
									<a href="?user=<?=$user?>" class="mainHeadingSmall">List Senders</a>
									<a href="?user=<?=$user?>&edit=-2" class="mainHeadingSmall">Import Contacts</a>
								</td>
							</tr>
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
				
									<table border="0" cellspacing="0" cellpadding="5">
										<tr>
											<td>
												Add email:
											</td>
											<td>
												<input type="text" name="email" size="30" class="textbox">
											</td>
											<td>
												into
											</td>
											<td>
												<select name="list" class="textbox">
													<option value="whitelist">Whitelist</option>
													<option value="blacklist">Blacklist</option>
												</select>
											</td>
											<td>
												<input type="submit" name="add" value="Add">
											</td>
										</tr>
									</table>
				
								</td>
							</tr>
						</table>
					</form>
				
					<?
				}
			
			
				// Show Import Form
				if($edit == -2)
				{
					?>					
					<form method="post" action="<? echo "?user=".$user; ?>" style="display:inline">
						<input type="hidden" name="user" value="<?=$user?>">
						<input type="hidden" name="session" value="<?=$user?>">
						<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>">
							<tr>
								<td bgcolor="<?=$my_colors["main_head_bg"] ?>" align="left">
									&nbsp; <span class="tblheader">Import Contacts</span>&nbsp;&nbsp;
									<a href="?user=<?=$user?>" class="mainHeadingSmall">List Senders</a>
									<a href="?user=<?=$user?>&edit=-1" class="mainHeadingSmall">Add Sender</a>
								</td>
							</tr>
							<tr>
								<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
				
									<table border="0" cellspacing="0" cellpadding="5">
										<tr>
											<td>
												Add my contacts to the
											</td>
											<td>
												<select name="list" class="textbox">
													<option value="whitelist">Whitelist</option>
													<option value="blacklist">Blacklist</option>
												</select>
											</td>
											<td>
												<input type="submit" name="import" value="Import">
											</td>
										</tr>
									</table>
				
								</td>
							</tr>
						</table>
					</form>
				
					<?
				}
				?>
			
				<br> <br>
				
				<table align="center" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
					<tr>
						<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						
							<p>
								<a href="help/filtering_tmda.html" target="_blank">Click here</a> of Advanced Filtering help.
							</p>
						
						</td>
					</tr>
				</table>
				<?
			}
			else	// No TMDA
			{
				?>
				<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
					<tr>
						<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
							&nbsp; <span class="tblheader">Advanced Filters</span>&nbsp;&nbsp;
							<a href="?user=<?=$user?>&enable=1" class="mainHeadingSmall">Enable</a>
						</td>
					</tr>
				</table>
			
				<br>

				<table align="center" border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="100%">
					<tr>
						<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
						
							Advanced Filters are not enabled. <a href="?user=<?=$user?>&enable=1" class="mainLight">Click here to enable.</a>
						
						</td>
					</tr>
				</table>
				<?
			}
			?>
		</td>
	</tr>
</table>	
<br>
</body>
</html>