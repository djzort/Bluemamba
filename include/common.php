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
              
    Document: include/common.php
              
    Function: Common Text Functions

*********************************************************************/


include_once("../include/qp_enc.php");

function LangIs8Bit($string)
{
	$len = strlen($string);
	for ($i = 0; $i < $len; $i++)
	{
		if(ord($string[$i]) >= 128)
		{
			return true;
		}
	}
	
	return false;
}


function LangConvert($string, $charset)
{
	return $string;
}


function LangEncodeSubject($input, $charset)
{
	$words = explode(" ", $input);
	if( count($words) > 0)
	{
		while(list($k, $word) = each($words))
		{
			if(LangIs8Bit($word)) $words[$k] = "=?".$charset."?Q?".qp_enc($word, 76)."?=";
		}
		$input = implode(" ", $words);
	}
	return $input;
}


function LangEncodeMessage($message, $charset)
{
	$result["type"]		= "Content-Type: text/plain; charset=".$charset."\r\n";
	$result["encoding"]	= "Content-Transfer-Encoding: quoted-printable\r\n";
	$result["data"] 	= qp_enc($message, 78);
	return $result;
}


function LangWrap($str)
{
	return wordwrap($str);
}


function LangFormatIntTime($time, $system, $ampm, $format)
{
	// Purpose: take "930" and format as "9:30am" or "0930" as necessary
	$min_pos = strlen($time) - 2;
	$hours 	 = substr($time, 0, $min_pos);
	$minutes = substr($time, $min_pos);
	if($system == 12)
	{
		if($hours >= 12)
		{
			if($hours>12) $hours-=12;
			$a = "pm";
		}
		else
		{
			$a = "am";
		}
	}
	$result = $format;
	if(!$hours)   $hours = "00";
	if(!$minutes) $minutes = "00";
	$result = str_replace("%h", $hours,    $result);
	$result = str_replace("%m", $minutes,  $result);
	$result = str_replace("%a", $ampm[$a], $result);
	
	return $result;
}


function LangInsertStringsFromAK($dest, $source_a)
{
	if(!is_array($source_a)) 
	{
		return $dest;
	}
	else
	{
		while(list($key, $val) = each($source_a))
		{
			$place_holder = "%".$key;
			$dest = str_replace($place_holder, $val, $dest);
		}
	}
	return $dest;
}


function LangDecodeMimeString($str, $charset)
{
	$a = explode("?", $str);
	$count = count($a);
	if($count >= 3)			// Should be in format "charset?encoding?base64_string"
	{
		for($i = 2; $i < $count; $i++) $rest .= $a[$i];
		
		if( ($a[1] == "B") || ($a[1] == "b") )
		{
			$rest = base64_decode($rest);
		}
		elseif( ($a[1] == "Q") || ($a[1] == "q") )
		{
			$rest = str_replace("_", " ", $rest);
			$rest = quoted_printable_decode($rest);
		}

		if(strcasecmp($a[0], "utf-8")==0)
		{
			include_once("../include/utf8.php");
			return utf8ToUnicodeEntities($rest);
		}
		else
		{
			return LangConvert($rest, $charset, $a[0]);
		}
	}
	else
	{
		return $str;		// We dont know what to do with this
	}
}


function LangDecodeSubject($input, $charset)
{
	$out = "";

	$pos = strpos($input, "=?");
	if($pos !== false)
	{
		$out = substr($input, 0, $pos);

		$end_cs_pos = strpos($input, "?", $pos+2);
		$end_en_pos = strpos($input, "?", $end_cs_pos+1);
		$end_pos    = strpos($input, "?=", $end_en_pos+1);

		$encstr		= substr($input, $pos+2, ($end_pos-$pos-2));
		$rest  		= substr($input, $end_pos+2);
		$out       .= LangDecodeMimeString($encstr, $charset);
		$out   	   .= LangDecodeSubject($rest, $charset);

		return $out;
	}
	else
	{
		return LangConvert($input, $charset, $charset);
	}
}


function LangDisableHTML($str)
{
	$result = $str;
	$result = str_replace("<", "&lt;", $result);
	$result = str_replace(">", "&gt;", $result);
	
	return $result;
}


function LangFormAddressHTML($user, $name, $address, $charset)
{
	include('../conf/conf.php');
	global $my_prefs;
	global $THEME;
	
	$target = "_blank";
	if($my_prefs["preview_window"] == 1)		// Build Targets
	{
		$target = 'preview';
	}
	else
	{
		if($my_prefs["compose_inside"] == 1)
		{
			$target = 'list2';
		}
	}
	
	if(empty($name)) $name=$address;
	$decoded_name = LangDecodeSubject($name, $charset);
	if(strpos($decoded_name, " ")!==false) $q_decoded_name = "\"".$decoded_name."\"";
	else $q_decoded_name = $decoded_name;
	
	$url = "compose.php?user=" . $user . "&to=" . urlencode($q_decoded_name." <".$address.">");
	
	$res  = "";
	$res .= "<a href=\"$url\" target=\"$target\">" . LangDisableHTML($decoded_name) . "</a>&nbsp;";
	$res .= "<a href=\"edit_contact.php?user=$user&name=" . urlencode($decoded_name) . "&email=" . urlencode($address) . "&edit=-1\">";
	$res .= "<img src='" . $THEME . "plus.gif' border='0'></a>";
	return $res;
}


function LangExplodeQuotedString($delimiter, $string)
{
	$quotes = explode("\"", $string);
	while(list($key, $val) = each($quotes))
	{
		if(($key % 2) == 1)
		{
			$quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
		}
	}
	$string = implode("\"", $quotes);
	
	$result = explode($delimiter, $string);
	while(list($key, $val) = each($result))
	{
		$result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);
	}
	
	return $result;
}


function LangParseAddressList($str)
{
	$a = LangExplodeQuotedString(",", $str);
	$result = array();
	reset($a);
	while(list($key, $val) = each($a))
	{
		$val   = str_replace("\"<", "\" <", $val);
		$sub_a = LangExplodeQuotedString(" ", $val);
		reset($sub_a);
		while(list($k, $v) = each($sub_a))
		{
			if((strpos($v, "@") > 0) && (strpos($v, ".") > 0))
			{
				$result[$key]["address"] = str_replace("<", "", str_replace(">", "", $v));
			}
			else
			{
				$result[$key]["name"] .= (empty($result[$key]["name"])?"":" ").str_replace("\"","",stripslashes($v));
			}
		}

		if(empty($result[$key]["name"]))
		{
			$result[$key]["name"] = $result[$key]["address"];
		}
	}
	
	return $result;
}


function LangEncodeAddressList($str, $charset)
{
	$str = str_replace(", ", ",",  $str);
	$str = str_replace("," , ", ", $str);
	$str = str_replace("; ", ";",  $str);
	$str = str_replace(";",  "; ", $str);
	
	$a = LangExplodeQuotedString(" ", $str);
	if(is_array($a))
	{
		$c = count($a);
		for($i = 0; $i < $c; $i++)
		{
			if((strpos($a[$i],"@") > 0) && (strpos($a[$i], ".") > 0))
			{
				// Probably an email address, leave it alone
			}
			else
			{
				// Some string, encode
				$word = stripslashes($a[$i]);
				$len  = strlen($word);
				$enc  = LangEncodeSubject(str_replace("\"", "", $word), $charset);
				if( ($word[0] == "\"") && ($word[$len-1] == "\"") )
				{
					$enc = "\"".$enc."\"";
				}
				$a[$i] = $enc;
			}
		}
		return implode(" ", $a);
	}
	else
	{
		return $str;
	}
}


function LangDecodeAddressList($str, $charset, $user)
{
	$a = LangParseAddressList($str);
	if(is_array($a))
	{
		$c = count($a);
        $j = 0;
		reset($a);
		while(list($i, $val) = each($a))
		{
            $j++;
			$address = $a[$i]["address"];
			$name    = str_replace("\"", "", $a[$i]["name"]);
			$res    .= LangFormAddressHTML($user, $name, $address, $charset);
			if( (($j % 3) == 0) && (($c - $j) > 1) ) 
			{
				$res .= ",<br>&nbsp;&nbsp;&nbsp;";
			}
			else if($c>$j) 
			{
				$res .= ", &nbsp; ";
			}
		}
	}
	
	return $res;
}


function LangShowAddresses($str, $charset)
{
	$a = LangParseAddressList($str);
	if(is_array($a))
	{
		$c = count($a);
        $j = 0;
		reset($a);
		while(list($i, $val) = each($a))
		{
            $j++;
			$address = $a[$i]["address"];
			$name    = str_replace("\"", "", $a[$i]["name"]);
			
			$res    .= htmlspecialchars("\"$name\" <$address>");
			if( (($j % 3) == 0) && (($c - $j) > 1) )
			{
				$res .= ",<br>&nbsp;&nbsp;&nbsp;";
			}
			elseif($c > $j)
			{
				$res .= ",&nbsp;";
			}
		}
	}
	
	return $res;
}


function LangFormatDate($timestamp, $format)
{
	$date = getdate($timestamp);
	
	$result = $format;
	$result = str_replace("%d", $date["mday"], $result);
	$result = str_replace("%m", $date["mon"],  $result);
	$result = str_replace("%y", $date["year"], $result);
	$result = str_replace("%t", $date["hour"].":".$date["minutes"], $result);
	$result = str_replace("%S", date('S', $timestamp), $result);

	return $result;
}


function LangWrapLine($line, $width)
{
	$line_len = strlen($line);
	$i = 0;
	
	// If line is less than width, we're good
	if($line_len <= $width) return $line;
	
	for($prev_i = 0, $i = $width;  $i < $line_len;  $prev_i = $i, $i += $width)
	{
		// Extract last segment that is $width wide
		$chunk = substr($line, $prev_i, ($i-$prev_i))."\n";
		
		// Find last space in this chunk
		$last_space = strrpos($chunk, " ");
		$last_space = $prev_i + $last_space;
		
		if($last_space == $prev_i)
		{
			// No space found in this chunk
			$next_space = strpos($line, " ", $i);
			if($next_space !== false)
			{
				$i = $next_space;
				$line[$next_space] = "\n";
			}
		}
		else
		{
			// Replace last space before width with newline
			$line[$last_space] = "\n";
			$i = $last_space;
		}
	}
	
	return $line;
}


function LangSmartWrap($text, $len)
{
	$lines = explode("\n", $text);
	
	if(!is_array($lines)) return "";
	
	while(list($i, $line) = each($lines))
	{
		if(!ereg("^>", $line))
		{
			$lines[$i] = LangWrapLine(chop($line), $len);
		}
	}
	
	return implode("\n", $lines);
}


?>