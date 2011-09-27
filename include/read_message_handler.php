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
              
    Document: include/read_message_handler.php
              
    Function: Decides which part to display in read message window 
	          and printer friendly view

*********************************************************************/

	$typeCode = iml_GetPartTypeCode($structure, $part);
	list($dummy,$subtype) = explode("/", iml_GetPartTypeString($structure,$part));
			
	if( ($typeCode == 3) && (strcasecmp($subtype, "ms-tnef") == 0) )
	{
		// Ms-tnef
		$type = $dummy;
		include_once("../include/tnef_decoder.php");
		include("../include/read_tnef_print.php");
	}
	elseif($typeCode == 0)
	{
		// Major type is "TEXT"
		$typestring = iml_GetPartTypeString($structure, $part);
		
		// If part=0, and there's a conflict in content-type, use what's specified in header
		if(empty($part) && !empty($header->ctype) && strcmp($typestring, $header->ctype)!=0)
		{
			$typestring = $header->ctype;
		}

		list($type, $subtype) = explode("/", $typestring);
		
		
		if(strcasecmp($subtype, "HTML") == 0)
		{
			// Type is "TEXT/HTML"
			if($my_prefs["html_in_frame"])
			{
				include("../include/read_message_print.php");
			}
			else
			{
				$view_url = "view.php?user=$user&folder=$folder_url&id=$id&part=$part&is_html=1";
				echo '<p>This is an HTML message. <a href="'.$view_url.'" target="_blank">Click here to read it.</a>';
			}
		}
		else
		{
			// Type "TEXT/PLAIN"
			include("../include/read_message_print.php");
		}

	}
	elseif( ($typeCode == 1) && empty($part) && ($structure[0][0] == "message") )
	{
		// Message content type is message/rfc822
		$part = "1.1";
		$typestring  = iml_GetPartTypeString($structure, $part);
		list($type, $subtype) = explode("/", $typestring);
		$typeCode    = iml_GetPartTypeCode($structure, $part);
		$disposition = iml_GetPartDisposition($structure, $part);
		include("../include/read_message_print.php");
	}
	elseif( ($typeCode == 1) || ($typeCode == 2) )
	{
		// Multipart message
		$typestring = iml_GetPartTypeString($structure, $part);
		list($type, $subtype) = explode("/", $typestring);
		
		$mode = 0;
		$subtypes = array("mixed"=>1, "signed"=>1, "related"=>1, "array"=>2, "alternative"=>2);
		$subtype  = strtolower($subtype);
		if($subtypes[$subtype] > 0)
		{
			$mode = $subtypes[$subtype];
		}
		elseif(strcasecmp($subtype, "rfc822") == 0)
		{
			$temp_num = iml_GetNumParts($structure, $part);
			if($temp_num > 0) $mode = 2;
		}
		elseif(strcasecmp($subtype, "encrypted") == 0)
		{
			//check for RFC2015
			$first_part     = $part . (empty($part)?"":".")."2";
			$encrypted_type = iml_GetPartTypeString($structure, $part.".1");
			if(stristr($encrypted_type, "pgp-encrypted") !== false)
			{
				$mode = -1;
			}
		}
		
		if($mode == -1)
		{
			// Handle RFC2015 message
			$part        = $part . (empty($part)?"":".") . "2";
			$typestring  = iml_GetPartTypeString($structure, $part);
			list($type, $subtype) = explode("/", $typestring);
			$typeCode    = iml_GetPartTypeCode($structure, $part);
			$disposition = iml_GetPartDisposition($structure, $part);
			include("../include/read_message_print.php");
		}
		elseif($mode > 0)
		{
			$originalPart = $part;
			for($i = 1; $i <= $num_parts; $i++)
			{
				// Get part info
				$part        = $originalPart.(empty($originalPart)?"":".").$i;
				$typestring  = iml_GetPartTypeString($structure, $part);
				list($type, $subtype) = explode("/", $typestring);
				$typeCode    = iml_GetPartTypeCode($structure, $part);
				$disposition = iml_GetPartDisposition($structure, $part);
				
				// If NOT attachemnt...
				if(strcasecmp($disposition, "attachment") != 0)
				{
					if(($mode == 1) && ($typeCode == 0))
					{
						//if "mixed" and type is "text" then show
						include("../include/read_message_print.php");
					}
					elseif($mode == 2)
					{
						// If "alternative" and type is "text/plain" then show
						if($my_prefs["html_in_frame"] && strcasecmp($subtype, "html") == 0)
						{
							// "Show HTML" and is html, then show
							include("../include/read_message_print.php");
						}
						elseif(!$my_prefs["html_in_frame"] && strcasecmp($subtype, "plain") == 0)
						{
							// Not "Show HTML" and not html, then show
							include("../include/read_message_print.php");
						}
					}
					elseif(($typeCode == 5) && (strcasecmp($disposition, "inline") == 0 && $my_prefs["show_images_inline"]))
					{
						// If type is image and disposition is "inline" show
						echo "<img src=\"view.php?user=$user&folder=$folder_url&id=$id&part=$part\">";
					}
					elseif($typeCode == 1)
					{
						// Multipart part
						$part = iml_GetFirstTextPart($structure, $part);
						if($my_prefs["html_in_frame"])
						{
							// If HTML preferred, see if next part is HTML
							$next_part = iml_GetNextPart($part);
							$next_type = iml_GetPartTypeString($structure, $next_part);
							// If it is HTML, use it instead of text part
							if(stristr($next_type,"html") !== false)
							{
								$typestring = "text/html";
								$type       = "text";
								$subtype    = "html";
								$part       = $next_part;
							}
							$i++;
						}
						include("../include/read_message_print.php");
					}
				}
				else
				{
					if( ($typeCode == 5) && ($my_prefs["show_images_inline"]) )
					{
						echo "<img src=\"view.php?user=$user&folder=$folder_url&id=$id&part=".$part."\"><br>\n";
					}
				}
			}
		}
		else
		{
			if(strcasecmp($subtype, "rfc822") != 0)
			{
				$part = iml_GetFirstTextPart($structure, "");
				if($my_prefs["html_in_frame"])
				{
					// If HTML preferred, see if next part is HTML
					$next_part = iml_GetNextPart($part);
					$next_type = iml_GetPartTypeString($structure, $next_part);
					// If it is HTML, use it instead of text part
					if(stristr($next_type,"html")!==false)
					{
						$typestring = "text/html";
						$type       = "text";
						$subtype    = "html";
						$part       = $next_part;
					}
				}
			}
			include("../include/read_message_print.php");
		}
	}
	else
	{
		// Not text or multipart, i.e. it's a file
			
		$type        = iml_GetPartTypeCode($structure, $part);
		$name        = iml_GetPartName($structure, $part);
		$typestring  = iml_GetPartTypeString($structure,$part);
		$bytes       = iml_GetPartSize($structure,$part);
		$encoding    = iml_GetPartEncodingCode($structure, $part);
		$disposition = iml_GetPartDisposition($structure, $part);
		$icons_a     = array("text.gif", "multi.gif", "multi.gif", "application.gif", "music.gif", "image.gif", "movie.gif", "unknown.gif");
		$href        = "view.php?user=$user&folder=$folder_url&id=$id&part=".$part;
		echo '
		<table>
			<tr>
				<td align="center">
					<a href="' . $href . '" target="_blank">
						<img src="' . $THEME . $icons_a[$type] . '" border="0">
						<font size="-1">' . LangDecodeSubject($name, $my_charset) . ' [' . ShowBytes($bytes) . ']</font>
					</a>
				</td>
			</tr>
		</table>
		';
	}
