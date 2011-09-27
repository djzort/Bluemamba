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
              
    Document: include/edit_calendar.php
              
    Function: Handle calendar item edits and deletes

*********************************************************************/

include_once("../conf/db_conf.php");
include_once("../include/idba.php");
include_once("../include/array2sql.php");

$db = new idba_obj;
if(!$db->connect())
{
	echo "DB connection failed.";
	exit;
}

if(isset($edit_cal))
{
	$error = "";
	
	if(empty($title)) {$error="No Title Specified";}
	
	$beginDate = formatCalDate($start_month, $start_day, $start_year);
	if(!$beginDate) $error .= "Invalid beginning date\n";
	$endDate = formatCalDate($end_month, $end_day, $end_year);
	if(!$endDate) $error .= "Invalid ending date\n";
	
	$beginTime = ($start_hour * 100) + $start_minute;
	$endTime = ($end_hour * 100) + $end_minute;
	
	$pattern = "";
	if(count($repeat_d) > 0)
	{
		$pattern_day = "";
		while (list($k,$d) = each($repeat_d)) $pattern_day .= "$k,";
	}
	if(count($repeat_w) > 0)
	{
		$pattern_week = "";
		while (list($k,$d) = each($repeat_w)) $pattern_week .= "$k,";		
	}
	else if(count($repeat_d) > 0)
	{
		$pattern_week = "all";
	}
	if($repeat_monthly) $pattern_month = substr($beginDate, 6);
	if($repeat_yearly)  $pattern_year  = substr($beginDate, 4);
	
	$data["userID"]        = $session_dataID;
	$data["title"]         = $title;
	$data["place"]         = $place;
	$data["description"]   = $description;
	$data["participants"]  = $participants;
	$data["beginTime"]     = $beginTime;
	$data["endTime"]       = $endTime;
	$data["beginDate"]     = $beginDate;
	$data["endDate"]       = $endDate;
	$data["pattern_day"]   = $pattern_day;
	$data["pattern_week"]  = $pattern_week;
	$data["pattern_month"] = $pattern_month;
	$data["pattern_year"]  = $pattern_year;
	$data["color"] 		   = $color;
	
	if(!$error)
	{
		$sql = Array2SQL($DB_CALENDAR_TABLE, $data, ($edit > 0 ? "UPDATE":"INSERT"));
		if($edit>0) $sql.= " WHERE id=$edit and userID=$session_dataID";
	
		$backend_result = $db->query($sql);
	}
	
	$date = $beginDate;
}
else if(isset($delete_cal))
{
    $backend_query = "DELETE FROM $DB_CALENDAR_TABLE WHERE userID='$session_dataID' and id='$edit'";;
	$backend_result = $db->query($backend_query);	
}

?>