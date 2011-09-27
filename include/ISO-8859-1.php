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
              
    Document: include/ISO-8859-1.php
              
    Function: Encoding library for the ISO-8859-1 charset.

*********************************************************************/

include_once("../include/qp_enc.php");

function LangIs8Bit($string)
{
	$len = strlen($string);
	for ($i=0; $i < $len; $i++)
	{
		if( ord($string[$i]) >= 128 ) {return true;}
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
	if(count($words) > 0)
	{
		while(list($k, $word) = each($words))
		{
			if(LangIs8Bit($word)) $words[$k] = "=?".$charset."?Q?".qp_enc($word, 76)."?=";
		}
		$input = implode(" ", $words);
	}
	return $input;
}


function LangEncodeMessage($input, $charset)
{
	$message = $input;
	
	$result["type"]     = "Content-Type: text/plain; charset=" . $charset . "\r\n";
	$result["data"]     = qp_enc($message, 78);
		
	return $result;
}


function LangWrap($str)
{
	return wordwrap($str);
}


include_once("../include/common.php");

?>
