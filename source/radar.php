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
              
    Document: source/radar.php
              
    Function: Check for new messages, notify user
              
*********************************************************************/

include("../include/super2global.php");
include("../include/nocache.php");
include("../conf/conf.php");

if(isset($user))
{
	include("../include/session_auth.php");
	include("../include/icl.php");


	$interval = ($my_prefs["radar_interval"] * 60);
	if($interval < $MIN_RADAR_REFRESH)
	{
		$interval = $MIN_RADAR_REFRESH;
	}
	
	$recent   = iil_CheckForRecent($host, $loginID, $password, $ROOTDIR);
	if($recent == 0)
	{
		$output  = "<script language=\"JavaScript\">\n";
		$output .= "setTimeout('location=\"radar.php?user=$user\"',".$interval."000);\n";
		$output .= "</script>\n";
	}
	elseif($recent > 0)
	{
		$output  = "<center><img src='" . $THEME . "newmail.gif'></center>";
		if(isset($my_prefs["notify"]))
		{
			$output .= "<EMBED SRC='themes/".$DOMAIN_THEME."/notify/".$my_prefs["notify"].".wav' autostart='true' hidden='true' loop='false'>\n";
		}
	}
	
	$linkc = $my_colors["tool_link"];
	$bgc   = $my_colors["tool_bg"];
	
	// Determine email address
	if(empty($my_prefs["email_address"]))
	{
		// Email Address Adjustment
		$title = $loginID;
		if(!strstr($loginID, "@"))
		{
			$domain_pieces = explode(".", $host);
			if( $domain_pieces[2] )			// Strip sub-domain "www." from hostname
			{
				$title = $loginID ."@". $domain_pieces[1] .".". $domain_pieces[2];
			}
		}
	}
	else
	{
		$title = $my_prefs["email_address"];
	}
	$title .= " : ". $SITE_TITLE;
	
	
	echo "<html>\n<head>\n";
	echo "<script type=text/javascript>\n";
	echo "function refresh(){ location=\"radar.php?user=$user\"; }\n";
	if($recent > 0) $title = "(!)".$title;
	?>
		var _p = this.parent;
		while(_p != this)
		{
			if(_p == _p.parent) { break; }
			_p = _p.parent;
		}
		_p.document.title = "<? echo $title; ?>";
	<?
	echo "</script>\n";
	echo "</head>\n";
	echo '<body background="' . $THEME . 'menuback.gif" bgcolor="' . $bgc . '">';
	
	echo $output;
}
?>
</body>
</html>