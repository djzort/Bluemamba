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

    Modified: 09/19/2008
              
    Document: themes/[domain]/default/login/splash.php
              
    Function: Login Splash

*********************************************************************/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>BlueMamba - Webmail</title>
	<META NAME="robot" CONTENT="index,follow">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta http-equiv="imagetoolbar" content="no">
    <style type="text/css">
	<!--
	td,table
	{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
		color: #777777;
	}
	-->
    </style>
</head>
<body bgcolor="#FFFFFF" text="#000000" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="pageback">

<table width="100%" height="80%" border="0" cellpadding="5" cellspacing="0">
	<tr>
		<td align="center" valign="middle">

			<table border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td>
						<img src="themes/bluemamba.org/colors/bluemamba/folder_logo.gif" width="150" height="130">
					</td>
					<td>
					
						<table summary="" width="100%" border="0" cellspacing="0" cellpadding="5">
							<tr>
								<td valign="top" align="center">
								&nbsp;<br>
								
									<form method="post" action="http://www.yourdomain.com/webmail/index.php">
										<input type="hidden" name="host" value="mail.yourdomain.com">
										<input type="hidden" name="port" value="143">
								
										<table summary="" border="0" cellspacing="0" cellpadding="0" class="tx">
											<tr> 
												<td colspan="2">Email:</td>
											</tr>
											<tr> 
												<td colspan="2"><input name="user" type="text" size="25"></td>
											</tr>
											<tr> 
												<td height="5" colspan="2"></td>
											</tr>
											<tr> 
												<td colspan="2">Password:</td>
											</tr>
											<tr> 
												<td colspan="2"><input name="password" type="password" size="25"></td>
											</tr>
											<tr> 
												<td height="5" colspan="2"></td>
											</tr>
											<tr> 
												<td align="left">&nbsp;</td>
												<td align="right"><input name="submit" type="submit" value="Log In"></td>
											</tr>
										</table>
						
									</form>
								
								</td>
							</tr>
						</table>		
					
					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>

</body>
</html>