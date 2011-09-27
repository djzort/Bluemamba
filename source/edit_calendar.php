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
              
    Document: source/edit_calendar.php
              
    Function: Interface for viewing/adding/updating calendar items.

*********************************************************************/

function ShowTimeWiget($hour_name, $hour, $minute_name, $minute)
{
	global $my_prefs, $lang_datetime;
	
	$system = $my_prefs["clock_system"];
	$ampm = $lang_datetime["ampm"];
	$format = $lang_datetime["hour_format"];
	
	echo "<select name=\"$hour_name\">\n";
	for ($i=0; $i<24; $i++)
	{
		echo "<option value=\"$i\" ".($i==$hour?"SELECTED":"").">";
		echo LangFormatIntTime($i."00", $system, $ampm, $format)."\n";
	}
	echo "</select>\n";
	echo " : <select name=\"$minute_name\">\n";
	for ($i=0; $i<60; $i=$i+5)
	{
		echo "<option ".($i==$minute?"SELECTED":"").">".($i<10?"0":"")."$i\n";
	}
	echo "</select>\n";
}

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/calendar.php");
include_once("../include/common.php");
include_once("../include/icl.php");

// Authenticate
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if ($conn)
{
	iil_Close($conn);
}
else
{
	echo "Authentication failed.";
	echo "</html>\n";
	exit;
}


//open backend connection
include_once("../conf/db_conf.php");
include_once("../include/idba.php");
include_once("../include/array2sql.php");

$db = new idba_obj;
if (!$db->connect())
{
	echo "DB connection failed.";
	exit;
}


// Calendar stuff
if (isset($date))
{
	$start_year = substr($date, 0, 4);
	$start_month = substr($date, 4, 2);
	$start_day = substr($date, 6, 2);
			
	$end_year = $start_year;
	$end_month = $start_month;
	$end_day = $start_day;
}

	if ($edit>0)
	{
		$backend_result = false;
    	$backend_query = "SELECT * FROM $DB_CALENDAR_TABLE WHERE userID='$session_dataID' and id='$edit'";;
		$backend_result = $db->query($backend_query);
				
		if(($backend_result) && ($db->num_rows($backend_result)>0))
		{
			//$data = $db->fetch_row($backend_result); 
			//extract($data);
			while( $a = $db->fetch_row($backend_result) )
			{
				//create & initialize new scheduleItem object
				$title         = $a["title"];
				$place         = $a["place"];
				$description   = $a["description"];
				$participants  = $a["participants"];
				$beginTime     = $a["beginTime"];
				$endTime       = $a["endTime"];
				$color         = $a["color"];

				$pattern_day   = $a["pattern_day"];
				$pattern_week  = $a["pattern_week"];
				$pattern_month = $a["pattern_month"];
				$pattern_year  = $a["pattern_year"];
	
				$beginDate      = $a["beginDate"];
				$endDate        = $a["endDate"];
			}
				
			$start_hour = (int)($beginTime / 100);
			$start_minute = $beginTime % 100;

			$end_hour = (int)($endTime / 100);
			$end_minute = $endTime % 100;
			
			$start_year = substr($beginDate, 0, 4);
			$start_month = substr($beginDate, 4, 2);
			$start_day = substr($beginDate, 6, 2);

			$end_year = substr($endDate, 0, 4);
			$end_month = substr($endDate, 4, 2);
			$end_day = substr($endDate, 6, 2);


			//days of week
			$dows = explode(",", $pattern_day);
			while( list($k, $d)=each($dows) ) $repeat_d[$d]=1;

			//weeks in month
			$woms = explode(",", $pattern_week);
			while( list($k, $d)=each($woms) ) $repeat_w[$d]=1;

			if ($pattern_month) $repeat_monthly = 1;
			if ($pattern_year) $repeat_yearly = 1;

		}
		else
		{
			echo $error;
			if (empty($error)) "Invalid item, or access denied";
			$edit="";
		}
	}

$cal_colors = array("#990000"=>"Dark Red",   "#FF0000"=>"Red",    "#000099"=>"Deep Blue", "#0000FF"=>"Blue", 
					"#006600"=>"Dark Green", "#00FF00"=>"Green",  "#9900FF"=>"Purple",    "#00FFFF"=>"Cyan",
					"#FF6600"=>"Orange",     "#FFFF00"=>"Yellow", "#FF00FF"=>"Magenta",   "#000000"=>"No Color");

?>
<form action="calendar.php" method="post" style="display:inline">
	<input type="hidden" name="user" value="<?=$user?>">
	<input type="hidden" name="delete_item" value="<? echo $edit; ?>">	
	<input type="hidden" name="edit" value="<? echo $edit; ?>">

	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
				&nbsp; <span class="tblheader"><? echo ($edit>0?"Edit":"Add"); ?> Schedule</span>
				&nbsp;&nbsp;<a href="calendar.php?user=<?=$sid?>" class="mainHeadingSmall">Show Calendar</a>
			</td>
		</tr>
	</table>
	
	<br>
	
	<table border="0" cellspacing="1" cellpadding="10" bgcolor="<?=$my_colors["main_hilite"]?>" width="95%" align="center">
		<tr>
			<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
	
				<table border="0">
					<tr>
						<td align="right">Title:</td>
						<td><input type="text" name="title" class="textbox" value="<? echo $title ?>"></td>
						<td align="right">Color:</td>
						<td>
							<select name="color" class="textbox">
								<?
								while(list($value, $label) = each($cal_colors))
								{
									echo "<option value=\"$value\" ".($value==$color?"SELECTED":"").">$label\n";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right">Starts on:</td>
						<td align="left">
							<input type="text" name="start_month" class="textbox" value="<? echo $start_month ?>" size="2"> /
							<input type="text" name="start_day" class="textbox" value="<? echo $start_day ?>" size="2"> /
							<input type="text" name="start_year" class="textbox" value="<? echo $start_year ?>" size="4">
						</td>
						<td align="right">Ends on:</td>
						<td align="left">
							<input type="text" name="end_month" class="textbox" value="<? echo $end_month ?>" size="2"> / 
							<input type="text" name="end_day" class="textbox" value="<? echo $end_day ?>" size="2"> / 
							<input type="text" name="end_year" class="textbox" value="<? echo $end_year ?>" size="4">
						</td>		
					</tr>
					<tr>
						<td align="right">From:</td>
						<td align="left">
							<?
							ShowTimeWiget("start_hour", $start_hour, "start_minute", $start_minute);
							?>
						</td>
						<td align="right">Until:</td>
						<td align="left">
							<?
							ShowTimeWiget("end_hour", $end_hour, "end_minute", $end_minute);
							?>
						</td>		
					</tr>
					<tr>
						<td align="right">&nbsp;</td>
						<td align="left" colspan="3">
							<hr>
						</td>		
					</tr>
					<tr>
						<td align="right" valign="top">Repeat on</td>
						<td align="left" valign="top">
							<input type="checkbox" name="repeat_d[0]" value=1 <? echo ($repeat_d[0]?"CHECKED":"") ?>> Sunday<br>
							<input type="checkbox" name="repeat_d[1]" value=1 <? echo ($repeat_d[1]?"CHECKED":"") ?>> Monday<br>
							<input type="checkbox" name="repeat_d[2]" value=1 <? echo ($repeat_d[2]?"CHECKED":"") ?>> Tuesday<br>
							<input type="checkbox" name="repeat_d[3]" value=1 <? echo ($repeat_d[3]?"CHECKED":"") ?>> Wednesday<br>
							<input type="checkbox" name="repeat_d[4]" value=1 <? echo ($repeat_d[4]?"CHECKED":"") ?>> Thursday<br>
							<input type="checkbox" name="repeat_d[5]" value=1 <? echo ($repeat_d[5]?"CHECKED":"") ?>> Friday<br>
							<input type="checkbox" name="repeat_d[6]" value=1 <? echo ($repeat_d[6]?"CHECKED":"") ?>> Saturday
						</td>
						<td align="right" valign="top">of every</td>
						<td align="left" valign="top">
							<input type="checkbox" name="repeat_w[1]" value="1" <? echo ($repeat_w[1]?"CHECKED":"") ?>> first week<br>
							<input type="checkbox" name="repeat_w[2]" value="1" <? echo ($repeat_w[2]?"CHECKED":"") ?>> second week<br>
							<input type="checkbox" name="repeat_w[3]" value="1" <? echo ($repeat_w[3]?"CHECKED":"") ?>> third week<br>
							<input type="checkbox" name="repeat_w[4]" value="1" <? echo ($repeat_w[4]?"CHECKED":"") ?>> fourth week<br>
							...of every month.
							<br> <br>
							Will repeat for every week if none are selected.
						</td>
					</tr>
					<tr>
						<td align="right">&nbsp;</td>
						<td align="left" colspan="3">
							<hr>
						</td>		
					</tr>
					<tr>
						<td align="right"></td>
						<td align="left" colspan="3">
							<input type="checkbox" name="repeat_monthly" value="1" <? echo ($repeat_monthly?"CHECKED":""); ?>> Repeat Monthly<br>
							<input type="checkbox" name="repeat_yearly"  value="1" <? echo ($repeat_yearly?"CHECKED":"");  ?>> Repeat Annually
						</td>
					</tr>
					<tr>
						<td align="right">&nbsp;</td>
						<td align="left" colspan="3">
							<hr>
						</td>		
					</tr>
					<tr>
						<td align="right" valign="top">Place:</td>
						<td align="left" colspan="3">
							<textarea name="place" cols="50" rows="4"><? echo htmlspecialchars($place) ?></textarea>
						</td>
					</tr>
					<tr>
						<td align="right" valign="top">Description:</td>
						<td align="left" colspan="3">
							<textarea name="description" cols="50" rows="6"><? echo htmlspecialchars($description) ?></textarea>
						</td>
					</tr>
				</table>
				
				<table width="50%">
					<tr>
						<td align="left"><input type="submit" name="edit_cal" value="<? echo ($edit>0?"Save":"Add"); ?> Schedule"></td>
						<td align="right"><? if($edit>0){echo '<input type="submit" name="delete_cal" value="Delete Schedule">';} ?></td>
					</tr>
				</table>
	
			</td>
		</tr>
	</table>

</form>

<br>

</body>
</html>