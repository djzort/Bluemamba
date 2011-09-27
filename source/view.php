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
              
    Document: source/view.php
              
    Function: Show Message

*********************************************************************/

include_once("../include/super2global.php");
include_once("../include/nocache.php");

if((isset($user))&&(isset($folder)))
{
	include_once("../include/session_auth.php");
	include_once("../include/icl.php");

	include_once("../include/global_func.php");
	include_once("../include/common.php");


	$view_conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if($iil_errornum==-11)
	{
		for ($i=0; (($i<10)&&(!$view_conn)); $i++)
	{
			sleep(1);
			$view_conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
		}
	}
	if(!$view_conn)
	{
		echo "failed\n".$iil_error;
		flush();
	}
	else
	{

		// Let's look for MSIE as it needs special treatment
		if(strpos(getenv('HTTP_USER_AGENT'), "MSIE"))
		{
			$DISPOSITION_MODE = "inline";
		}
		else
		{
			$DISPOSITION_MODE = "attachment";
		}

		// Get basic info
		include_once("../include/mime.php");
		$header        = iil_C_FetchHeader($view_conn, $folder, $id);
		$structure_str = iil_C_FetchStructureString($view_conn, $folder, $id);
		$structure     = iml_GetRawStructureArray($structure_str);

		// If part id not specified but content-id is, 
		// Find corresponding part id
		if(!isset($part) && $cid)
		{
			if(!ereg("^<", $cid)) $cid = "<".$cid;
			if(!ereg(">$", $cid)) $cid.= ">";
			
			//fetch parts list
			$parts_list = iml_GetPartList($structure, "");
			
			//search for cid
			if(is_array($parts_list))
			{
				reset($parts_list);
				while(list($part_id,$part_a)=each($parts_list))
				{
					if($part_a["id"]==$cid){
						$part = $part_id;
					}
				}
			}
			
			//we couldn't find part with cid, die
			if(!isset($part)) exit;
		}
		
		if(isset($source))
		{
			//show source
			header("Content-type: text/plain");
			iil_C_PrintSource($view_conn, $folder, $id, $part);
		}
		elseif($show_header)
		{
			//show header
			header("Content-Type: text/plain");
			$header = iil_C_FetchPartHeader($view_conn, $folder, $id, $part);
			//$header = str_replace("\r", "", $header);
			//$header = str_replace("\n", "\r\n", $header);
			echo $header;
		}
		elseif($printer_friendly)
		{
			//get message info
			$conn = $view_conn;
			
			$num_parts = iml_GetNumParts($structure, $part);
			$parent_type = iml_GetPartTypeCode($structure, $part);
			$uid = $header->uid;

			//get basic header fields
			$subject = encodeHTML(LangDecodeSubject($header->subject, $my_prefs["charset"]));
			$from = LangShowAddresses($header->from,  $my_prefs["charset"], $user);
			$to = LangShowAddresses($header->to,  $my_prefs["charset"], $user);
			if(!empty($header->cc)) $cc = LangShowAddresses($header->cc,  $my_prefs["charset"], $user);
			else $cc = "";

			header("Content-type: text/html");

			//output
			?>
			<html>
			<head><title><? echo $subject ?></title></head>
			<body>
			<?
			echo "<b>Subject:&nbsp;</b>$subject<br>\n";
			echo "<b>Date:&nbsp;</b>".htmlspecialchars($header->date)."<br>\n";
			echo "<b>From:&nbsp;</b>".$from."<br>\n";
			echo "<b>To:&nbsp;</b>".$to."<br>\n";
			if(!empty($cc)) echo "<b>CC:&nbsp;</b>".$cc."<br>\n";
			//20094
			include("../include/read_message_handler.php");
			?>
			</body>
			</html>
			<?

		}
		elseif(isset($tneffid))
		{
			//show ms-tnef

			include("../include/tnef_decoder.php");			
			$type=iml_GetPartTypeCode($structure, $part);
			$typestring=iml_GetPartTypeString($structure, $part);
			list($type, $subtype) = explode("/", $typestring);
			$body=iil_C_FetchPartBody($view_conn, $folder, $id, $part);
			$encoding=iml_GetPartEncodingCode($structure, $part);
			if($encoding == 3 ) $body=base64_decode($body);
			elseif($encoding == 4) $body=quoted_printable_decode($body);
			$charset=iml_GetPartCharset($structure, $part);
			if(strcasecmp($charset, "utf-8")==0)
			{
				include_once("../include/utf8.php");
				$is_unicode = true;
				$body = utf8ToUnicodeEntities($body);
			}
			else
			{
				$is_unicode = false;
			}
			//$body=LangConvert($body, $my_charset, $charset);
			$tnef_files=tnef_decode($body);
			header("Content-type: ".$tnef_files[$tneffid]['type0']."/".$tnef_files[$tneffid]['type1']."; name=\"".$tnef_files[$tneffid]['name']."\"");
			header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$tnef_files[$tneffid]['name']."\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");
			echo($tnef_files[$tneffid]['stream']);
		}
		else
		{			
			$header_obj = $header;
			$type=iml_GetPartTypeCode($structure, $part);
			if($is_html) $typestr = "text/html";
			elseif(empty($part) || $part==0) $typestr = $header_obj->ctype;
			else $typestr = iml_GetPartTypeString($structure, $part);
			list($majortype, $subtype) = explode("/", $typestr);

			// structure string
			if($show_struct)
			{
				echo $structure_str;
					exit;
			}

			// format and send HTTP header
			if($type==$MIME_APPLICATION)
			{
				$name = str_replace("/",".",iml_GetPartName($structure, $part));
				header("Content-type: $typestr; name=\"".$name."\"");
				header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$name."\"");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Pragma: public");
			}
			elseif($type==$MIME_MESSAGE)
			{
				$name=str_replace("/",".", iml_GetPartName($structure, $part));
				header("Content-Type: text/plain; name=\"".$name."\"");
			}
			elseif($type != $MIME_INVALID)
			{
				$charset=iml_GetPartCharset($structure, $part);
				$name=str_replace("/",".", iml_GetPartName($structure, $part));
				$header="Content-type: $typestr";
				if(!empty($charset)) $header.="; charset=\"".$charset."\"";
				if(!empty($name)) $header.="; name=\"".$name."\"";
				header($header);
				if($type!=$MIME_TEXT && $type!=$MIME_IMAGE)
				{
					header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$name."\"");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Pragma: public");
				}
				elseif(!empty($name))
				{
					header("Content-Disposition: inline; filename=\"".$name."\"");
				}
			}
			else
			{
				if($debug) echo "Invalid type code!\n";
			}
			if($debug) echo "Type code = $type ;\n";
			
			//check if text/html
			if($type==$MIME_TEXT && strcasecmp($subtype, "html")==0)
			{
				$is_html = true;
				$img_url = "view.php?user=$user&folder=$folder&id=$id&cid=";
				//echo "IS HTML<br>\n";
			}
			else
			{
				$is_html = false;
				//echo "IS NOT HTML $type $subtype <br>\n";
			}

			// send actual output
			if($print)
			{
				// straight output, no processing
				iil_C_PrintPartBody($view_conn, $folder, $id, $part);
				if($debug) echo $view_conn->error;
			}
			else
			{
				// process as necessary, based on encoding
				$encoding=iml_GetPartEncodingCode($structure, $part);
				if($debug) echo "Part code = $encoding;\n";

				if($raw)
				{
					iil_C_PrintPartBody($view_conn, $folder, $id, $part);
				}
				elseif($encoding==3)
				{
					// base 64
					if($debug) echo "Calling iil_C_PrintBase64Body\n"; flush();
					if($is_html)
					{
						$body = iil_C_FetchPartBody($view_conn, $folder, $id, $part);
						$body = ereg_replace("[^a-zA-Z0-9\/\+]", "", $body);
						$body = base64_decode($body);
						$body = eregi_replace("src=\"cid:", "src=\"".$img_url, $body);
						echo $body;
					}
					else
					{
						iil_C_PrintBase64Body($view_conn, $folder, $id, $part);
					}
				}
				elseif($encoding == 4)
				{
					// quoted printable
					$body = iil_C_FetchPartBody($view_conn, $folder, $id, $part);
					if($debug)
					{
						echo "Read ".strlen($body)." bytes\n";
					}
					$body    = quoted_printable_decode(str_replace("=\r\n","",$body));
					$charset = iml_GetPartCharset($structure, $part);
					if(strcasecmp($charset, "utf-8") == 0)
					{
						include_once("../include/utf8.php");
						$body = utf8ToUnicodeEntities($body);
					}
					if($is_html)
					{
						$body = eregi_replace("src=\"cid:", "src=\"".$img_url, $body);
					}
					echo $body;
				}
				else
				{
					// otherwise, just dump it out
					if($is_html)
					{
						$body = iil_C_FetchPartBody($view_conn, $folder, $id, $part);
						$body = eregi_replace("src=\"cid:", "src=\"".$img_url, $body);
						echo $body;
					}
					else
					{
						iil_C_PrintPartBody($view_conn, $folder, $id, $part);
					}
				}
				if($debug) echo $view_conn->error;
			}
		}
		iil_Close($view_conn);
	}
}
?>
