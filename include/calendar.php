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
              
    Document: include/calendar.php
              
    Function: Some common code for calendar feature
              
*********************************************************************/

class scheduleItem
{
	var $id;
	var $title;
	var $place;
	var $description;
	var $participants;
	var $beginTime;
	var $endTime;
	var $color;
}


function formatCalDate($month, $day, $year)
{
	$error = 0;
	$result = false;

	if(checkdate($month, $day, $year))
	{
		if (($day   < 10) && (strlen($day)   == 1)) $day   = "0" . $day;
		if (($month < 10) && (strlen($month) == 1)) $month = "0" . $month;
		$result = $year . $month . $day;
	}
	
	return $result;
}


// Remains of old Language Package
$lang_months = array
(
	"1"  => "January",
	"2"  => "February",
	"3"  => "March",
	"4"  => "April",
	"5"  => "May",
	"6"  => "June",
	"7"  => "July",
	"8"  => "August",
	"9"  => "September",
	"10" => "October",
	"11" => "November",
	"12" => "December"
);


$lang_datetime = array
(
	"short_mon" => array 
				(
					"1"  => "Jan",
					"2"  => "Feb",
					"3"  => "Mar",
					"4"  => "Apr",
					"5"  => "May",
					"6"  => "Jun",
					"7"  => "Jul",
					"8"  => "Aug",
					"9"  => "Sep",
					"10" => "Oct",
					"11" => "Nov",
					"12" => "Dec"
				),

	"dsow" => array
				(
					"0" => "Sun",
					"1" => "Mon",
					"2" => "Tue",
					"3" => "Wed",
					"4" => "Thu",
					"5" => "Fri",
					"6" => "Sat"
				),

	"dsowl" => array
				(
					"0" => "Sunday",
					"1" => "Monday",
					"2" => "Tuesday",
					"3" => "Wednesday",
					"4" => "Thursday",
					"5" => "Friday",
					"6" => "Saturday"
				),

	"dsow_short" 	=> array("S", "M", "T", "W", "T", "F", "S"),
	"today" 		=> "Today %t",		// e.g. "Today 12:02"
	"lastweek" 		=> "%w %t",			// e.g. "Wed 12:00"
	"thisyear" 		=> "%m/%d",			// e.g. "2/13"
	"prevyears"		=> "%m/%d/%y", 		// e.g. "1/1/2100"
	"monthyear" 	=> "%m %y",			// e.g. "January 2002"
	"verbal" 		=> "%m %d, %y",  	// e.g. "Jan 1, 1900"
	"verbal_short" 	=> "%m %d",  		// e.g. "Jan 1"
	"hour_system" 	=> 12,				// 12 or 24
	"ampm" 			=> array("am" => "am", "pm" => "pm"),
	"time_format" 	=> "%h:%m%a",		// %h=hour, %m=minutes, %a="am:pm"
	"hour_format" 	=> "%h%a"

);

?>