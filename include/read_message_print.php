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
              
    Document: include/read_message_print.php
              
    Function: Actual code that displays message body part in 
	          "source/read_message.php"
              
*********************************************************************/

// figure out the body part's type
if( empty($typestring) || empty($type) || empty($subtype) )
{
	$typestring = iml_GetPartTypeString($structure, $part);
	list($type, $subtype) = explode("/", $typestring);
}
	
// Fetch body part
$body = iil_C_FetchPartBody($conn, $folder, $id, $part);

// Decode body part
$encoding = iml_GetPartEncodingCode($structure, $part);
if($encoding == 3)
{
	$body = base64_decode($body);
}
elseif($encoding == 4)
{
	$body = quoted_printable_decode($body);
}

// Detect HTML
if(eregi("<html>", $body))
{
	if($my_prefs["html_in_frame"])
	{
		$subtype = "html";
	}
	else
	{
		$body = "";
		$view_url = "view.php?user=$user&folder=$folder_url&id=$id&part=$part&is_html=1";
		echo '<p>This is an HTML message. <a href="'.$view_url.'" target="_blank">Click here to read it.</a></p>';
	}
}

// Check if UTF-8
$charset = iml_GetPartCharset($structure, $part);
if(strcasecmp($charset, "utf-8") == 0)
{
	include_once("../include/utf8.php");
	$is_unicode = true;
}
elseif( preg_match("/#[0-9]{5};/", $body) )
{
	// Look for unicode that look like #12345; (without '&')
	$body = preg_replace("/(?<!&)(#[0-9]{5};)/", "&$1", $body);
	$is_unicode = false;
}
else
{
	$is_unicode = false;
}

// Run through character encoding engine
$body = LangConvert($body, $my_charset, $charset);

// Dump!
echo "<pre>";
if(strcasecmp($subtype, "html") == 0)
{
	if(!$my_prefs["html_in_frame"])
	{
		$body = strip_tags($body, '<a><b><i><u><p><br><font><div>');
	}
	$body = eregi_replace("src=\"cid:", "src=\"view.php?user=$user&folder=$folder&id=$id&cid=", $body);
	echo $body;
}
else
{
	// Quote colorization
	$color = $my_colors["quotes"];
	if(empty($color)) $color = "blue";
	
	$lines = explode("\n", $body);
	while(list($key, $line) = each($lines))
	{
		$line = chop($line);
		
		// Color quotes
		if($my_prefs["colorize_quotes"] == 1)
		{
			// Colorize quotes
			if(($line[0] == ">") && (!$quoteLN))
			{
				$quoteLN = true;
				echo "<font color=\"$color\">";
			}
			if(($line[0] != ">") && ($quoteLN))
			{
				$quoteLN = false;
				echo "</font>";
			}
		}
		
		// Detect links
		$html_encoded = false;
		if($my_prefs["detect_links"] == 1)
		{
			// Detect URL
			$pattern = "/(.*)([fh]+[t]*tp[s]*:\/\/[a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)(.*)/";
			if(preg_match($pattern, $line, $match))
			{
				$line = encodeHTML($match[1]);
				$line.= "<a href=\"".$match[2]."\" target=\"_blank\">".$match[2]."</a>";
				$line.= encodeHTML($match[3]);
				$html_encoded = true;
			}
		}
		
		// Encode and spit out
		if(!$html_encoded)
		{
			if($is_unicode)
			{
				$line = utf8ToUnicodeEntities($line);
			}
			else
			{
				$line = encodeUTFSafeHTML($line);
			}
		}
	
		// Convert leading spaces to &nbsp;
		$indent = "";
		for($c_pos=0; $line[$c_pos]==' ' || $line[$c_pos]=='\t'; $c_pos++)
		{
			if($line[$c_pos] == ' ')
			{
				$indent .= "&nbsp;";
			}
			elseif($line[$c_pos] == '\t')
			{
				$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		$line = $indent . trim($line) . "\n";		// Word wrap needs the \n 

		// Start Word Wrap
		echo LangWrapLine($line, $WORD_WRAP);

	}
}
echo "</pre>";

flush();
	
?>