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
              
    Document: source/tool.php
              
    Function: Tool Bar

*********************************************************************/

include("../include/super2global.php");
include("../include/nocache.php");

if (isset($user))
{
	include_once("../include/global_func.php");
	include_once("../include/encryption.php");
	include_once("../include/session_auth.php");
	include_once("../include/icl.php");
	
	$linkc	    = $my_colors["tool_link"];
	$bgc	    = $my_colors["tool_bg"];
	$font_size  = $my_colors["menu_font_size"];
	$bodyString = '<BODY LEFTMARGIN=0 RIGHTMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0 TOPMARGIN=0 background="' . $THEME . 'menuback.gif" BGCOLOR="'.$bgc.'" TEXT="'.$linkc.'" LINK="'.$linkc.'" ALINK="'.$linkc.'" VLINK="'.$linkc.'">';
}
else
{
	echo "User not specified.";
	exit;
}

function showLink($a)
{
	echo $a[3]."<a href=\"".$a[0]."\" target=\"".$a[1]."\" class=\"menuText\">".$a[2]."</a>\n";
}

?><html><head><?

	include("../include/css.php");

?></head><?

echo $bodyString; 

$div = "<span class='menuText'>&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</span>";


$target = "_blank";
if($my_prefs["compose_inside"])
{
	if($my_prefs["preview_window"] == 1)		// Build Targets
	{
		$target = "preview";
	}
	else
	{
		$target = "list2";
	}
}



$links[] = array("main.php?folder=INBOX&user=$user", "list2",  "Inbox", 	 "&nbsp;&nbsp;&nbsp;&nbsp;");
$links[] = array("compose.php?user=$user",     		 $target,  "Compose",    $div);
$links[] = array("calendar.php?user=$user",    		 "list2",  "Calendar",   $div);
$links[] = array("contacts.php?user=$user",    		 "list2",  "Contacts",   $div);
$links[] = array("bookmarks.php?user=$user",   		 "list2",  "Bookmarks",  $div);
$links[] = array("search_form.php?user=$user", 		 "list2",  "Search",     $div);
$links[] = array("filters.php?user=$user",     		 "list2",  "Filters",    $div);
$links[] = array("options.php?user=$user",     		 "list2",  "Options",    $div);


?>
<table width="100%">
	<tr class="menuText">
		<td valign="bottom">
			<?
			while(list($k,$v) = each($links))
			{
				showLink($links[$k]);
			}
			?>
		</td>
		<td align="right" valign="bottom">
			<a href="logout.php?logout=1&user=<?=$user?>" target="_parent" class="menuText">Logout</a>
		</td>
	</tr>
</table>
</BODY>
</HTML>
