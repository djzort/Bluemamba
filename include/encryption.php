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
              
    Document: include/encryption.php
              
    Function: Provide basic encryption related functionality

*********************************************************************/

function GenerateRandomString($messLen, $seed)
{
	srand ((double) microtime() * 1000000);
	if(empty($seed)) $seed="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	$seedLen = strlen($seed);
	if($messLen == 0) $messLen = rand(10, 20);
	for ($i = 0; $i < $messLen; $i++)
	{
		$point=rand(0, $seedLen-1);
		$message .= $seed[$point];
	}
	return $message;
}

function GenerateMessage($messLen)
{
	$seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	return GenerateRandomString($messLen, $seed);
}

function EncryptMessage($key,$message)
{
	$messLen = strlen($message);
	$keylen  = strlen($key);
	$enc_message = "";
	
	for ($i = 0; $i < $messLen; $i++)
	{
		$j    = $i % $keylen;
		$code = chr((ord($message[$i]) + ord($key[$j])) % 128);
		$enc_message .= $code;
	}

	return base64_encode($enc_message);
}

function DecodeMessage($pass, $message)
{
	$message = base64_decode($message);
	$messLen = strlen($message);
	$passLen = strlen($pass);
	
	$decMessage = "";
	for ($i = 0; $i < $messLen; $i++)
	{
		$j      = $i % $passLen;
		$num    = ord($message[$i]);
		$decNum = (($num + 128) - ord($pass[$j])) % 128;
		$decMessage .= chr($decNum);
	}
	
	return $decMessage;
}


function GenerateKeyFromIP()
{
	$ip    = $_SERVER["REMOTE_ADDR"];
	$ipkey = "";
	$ip_a  = explode(".", $ip);
	for ($i = 3; $i >= 0; $i--)
	{
		$ipkey .= $ip_a[$i];
	}
	return $ipkey;
}


function GetSessionEncKey($sid)
{
	global $MAX_SESSION_TIME, $STAY_LOGGED_IN;
	$cookie_name = "BLUEMAMBA_SESS_KEY_".$sid;
	if(empty($_COOKIE[$cookie_name]))
	{
		// No cookies, turn IP into encryption key
		$ipkey = GenerateKeyFromIP();		
	}
	else
	{
		// Use cookie
		$ipkey = $_COOKIE[$cookie_name];
		if($STAY_LOGGED_IN)
		{
			setcookie ($cookie_name, $ipkey, time()+$MAX_SESSION_TIME, "/", $_SERVER[SERVER_NAME]);
		}
	}
	return $ipkey;
}


function InitSessionEncKey($sid)
{
	global $MAX_SESSION_TIME;
	
	if(empty($_COOKIE['BLUEMAMBA_TEST_COOKIE']))
	{
		// Cookies disabled
		$key = GenerateKeyFromIP();
	}
	else
	{
		// Cookies enabled
		$cookie_name = "BLUEMAMBA_SESS_KEY_".$sid;
		$key = GenerateRandomString(16, "");
		$_COOKIE[$cookie_name] = $key;
		setcookie ($cookie_name, $key, time()+$MAX_SESSION_TIME, "/", $_SERVER[SERVER_NAME]);
	}
	return $key;
}

?>