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
              
    Document: source/themes/[domain]/welcome/welcome.php
              
    Function: Preview Window Welcome 

*********************************************************************/

$theme = $_GET['theme']?$_GET['theme']:"default";

include("../../../../include/version.php");
include("../colors/$theme/theme.php");
include("../conf.php");
include("tips.php");


?>
<html>
<head>
<title>Welcome</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?
	echo '<style type="text/css"><!--';
?>
.top
{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 24px;
	color: <? echo $my_colors['tool_link']; ?>;
	font-weight: bold;
}
.version
{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #999999;
}
.text
{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
}
.back
{
	background-image: url(fadeback.gif);
	background-position: right;
	background-repeat: repeat-y;
}
.feature
{
	background-image: url(feature.gif);
	background-repeat: no-repeat;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #777777;
	letter-spacing: 2px;
}
<?
	echo '--></style>';
?>
</head>
<body bgcolor="#FFFFFF" text="#333333" link="#999999" vlink="#999999" alink="#999999" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">



<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="40">

			<table width="100%" height="40" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					<td width="25" bgcolor="<? echo $my_colors['tool_bg']; ?>" align="center" valign="middle" class="top">&nbsp;
						
					</td>
					<td bgcolor="<? echo $my_colors['tool_bg']; ?>" valign="middle" class="top">
						Welcome to Webmail 
					</td>
				</tr>
			</table>

		</td>
	</tr>
	<tr>
		<td>

			<table width="100%" height="100%" border="0" cellpadding="25" cellspacing="0" class="back">
				<tr>
					<td valign="top">
			
						<table width="100%" height="100%" border="0" cellpadding="2" cellspacing="0" class="text">
							<tr> 
								<td colspan="2" height="26" class="feature"><b>&nbsp;Did You Know?</b></td>
							</tr>
			
							<tr><td colspan="2">&nbsp;</td></tr>
							
							<tr> 
								<td colspan="2">
									<? echo $TIP_OF_THE_DAY; ?>
								</td>
							</tr>
			
							<tr><td colspan="2">&nbsp;</td></tr>
							
							<tr> 
								<td class="version">
									<table width="266" height="1" border="0" cellspacing="0" cellpadding="0"><tr><td bgcolor="#E5E7EC"></td></tr></table>
									WebMail Version <? echo $version; ?>
								</td>
								<td class="version" width="20">
									<a href="?user=<? echo $_GET['user']; ?>">Next_Tip</a>
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