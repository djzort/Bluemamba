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
              
    Document: source/calendar.php
              
    Function: Calendar

*********************************************************************/

include("../include/super2global.php");
include("../include/header_main.php");
include("../include/icl.php");
include("../include/version.php");
include("../conf/defaults.php");
include("../include/calendar.php");


// Authenticate
$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
if($conn)
{
	iil_Close($conn);
}
else
{
	echo "Authentication failed.";
	echo "</body></html>";
	exit;
}

if($edit_cal || $delete_cal)
{
	include("../include/edit_calendar.php");
}

echo "<center>";

if(($go_month > 0) && ($go_year > 0)) $date = formatCalDate($go_month, 1, $go_year);

if(empty($date)) $date = date("Ymd", time());
if(strlen($date)==8)
{
	$current_year = substr($date, 0, 4);
	$current_month = substr($date, 4, 2);
	$current_day = substr($date, 6, 2);
}
if(!isset($current_ts)) $current_ts = mktime(0, 0, 0, $current_month, $current_day, $current_year);

// What's today's day of week?
$dow = date("w", $current_ts);

if(!isset($disp_mode)) $disp_mode = 0;
// 0: begin on sunday
// 1: begin on monday
// 2: begin 3 days ago
// 3: begin today
if($disp_mode==0) $offset = $dow;
else if($disp_mode==1) $offset = $dow-1;
else if($disp_mode==2) $offset = 3;
else if($disp_mode==3) $offset = 0;

// Timestamp for last sunday (beginning of week)
$start_ts = $current_ts - ($offset * (60 * 60 * 24));

// Timestamp for next saturday (end of week)
$end_ts = $start_ts + (60 * 60 * 24 * 6);

// Which week of the month?
$end_day = date("d", $end_ts);
$wom = (int)($end_day / 7);
if(($end_day % 7) != 0) $wom++;

// Starting date
$start_date = date("Ymd", $start_ts);
$end_date = date("Ymd", $end_ts);

// Create hash tables for day-of-week <-> date
for($i = 0; $i < 7; $i++)
{
	$temp_ts = $start_ts + ($i * (60 * 60 * 24));
	$temp_date = date("Ymd", $temp_ts);
	$temp_dow = date("w", $temp_ts);
	$date_dow[$temp_date] = "d".$temp_dow;		// date to day-of-week lookup
	$dow_date["d".$temp_dow] = $temp_date;		// day-of-week to date lookup
	$schedule2[$temp_date]["dow"] = $temp_dow;
}

$backend_result = false;	// Initialize

$sql  = "SELECT * FROM $DB_CALENDAR_TABLE WHERE userID='$session_dataID' ";
$sql .= "and ((beginDate <= '$end_date' and endDate >= '$start_date') ";
$sql .= "or   (endDate   <= '$end_date' and endDate >= '$start_date'))";

include_once("../conf/db_conf.php");
include_once("../include/idba.php");

if(!isset($db)) $db = new idba_obj;
if($db->connect())
{
	$backend_result = $db->query($sql);
}
else
{
	$backend_result = false;
}


if($backend_result)	// If query returned
{
	if($db->num_rows($backend_result) > 0)
	{
		//initialize schedule array
		//$schedule = array();
		
		// Loop through records
		while($a = $db->fetch_row($backend_result))
		{
			// Create & initialize new scheduleItem object
			$item 				= new scheduleItem;
			$item->id 			= $a["id"];
			$item->title 		= $a["title"];
			$item->place 		= $a["place"];
			$item->description  = $a["description"];
			$item->participants = $a["participants"];
			$item->beginTime 	= $a["beginTime"];
			$item->endTime 		= $a["endTime"];
			$item->color 		= $a["color"];
			
			$pattern_day        = $a["pattern_day"];
			$pattern_week       = $a["pattern_week"];
			$pattern_month      = $a["pattern_month"];
			$pattern_year       = $a["pattern_year"];

			$beginDate          = $a["beginDate"];
			$endDate            = $a["endDate"];


			if($beginDate == $endDate)								// Single day event
			{
				$schedule2[$beginDate][$item->beginTime][] = $item;
			}
			else													// Multi-day event
			{
				/*
				if(($beginDate >= $start_date) && ($beginDate <= $end_date))
				{
					// insert event for first day (event ends at midnight)
					$temp_item = $item;
					$temp_item->endTime = 2400;
					$schedule2[$beginDate][$item->beginTime][]=$temp_item;
				}
					
				if(($endDate <= $end_date) && ($endDate >= $start_date))
				{
					// insert event for last day (event beings at midnight)
					$temp_item = $item;
					$temp_item->beginTime = 0;
					$schedule2[$endDate][0][]=$temp_item;
				}
				*/

				if(($endDate - $beginDate) > 1)
				{
					// If event spans more than two days, insert events for all days inbetween
					$run_span = false;

					// If any of the fallowing are false then set a donot run flag.

					if(!empty($date_dow[$beginDate]))
					{
						$fromD = $date_dow[$beginDate][1]+1;
					}
					else
					{
						$fromD = 0;
					}
					if(!empty($date_dow[$endDate]))
					{
						$untilD = $date_dow[$endDate][1];
					}
					else
					{
						$untilD = 7;
					}
					
					reset($date_dow);
					// Loop for every day in the current week

					while(list($cur_date, $cur_dayofweek) = each($date_dow))
					{
						if( ($cur_date >= $beginDate) && ($cur_date <= $endDate) )
						{
							$run_span = false;

							// Get Start and Current Year/Month/Day
							$start_year  = substr($beginDate, 0, 4);
							$end_year    = substr($endDate,   0, 4);
							$cur_year    = substr($cur_date,  0, 4);
							
							$start_month = substr($beginDate, 4, 2);
							$end_month   = substr($endDate,   4, 2);
							$cur_month   = substr($cur_date,  4, 2);
	
							$start_day   = substr($beginDate, 6, 2);
							$end_day     = substr($endDate,   6, 2);
							$cur_day     = substr($cur_date,  6, 2);
							
							$cur_week = $wom; // Week Of Month
							$cur_weekday = substr($cur_dayofweek, 1, 1); // Day of Week

							//echo 'Y -> S: '.$start_year.' | E: '.$end_year.' | C: '.$cur_year.' - -:- - ';
							//echo 'M -> S: '.$start_month.' | E: '.$end_month.' | C: '.$cur_month.' - -:- - ';
							//echo 'D -> S: '.$start_day.' | E: '.$end_day.' | C: '.$cur_day.' <br> ';

							// Yearly Displays
							$year_flag = false;
							if($pattern_year) 				// Display for Every Year
							{
								$year_flag = true;
							}
							else							// Has a year elapsed since creation?
							{
								if($start_year <= $cur_year)
								{
									$year_flag = true;
								}
							}

							if($year_flag)
							{
								if($start_month == $cur_month) 		// Is Month current month?
								{
									if($start_day == $cur_day) 		// Is day current day?
									{
										$run_span = true;
									}
								}
							}


							// Monthly Displays
							if($pattern_month)							// Run For Every Month
							{
								if($pattern_month == $cur_day)			// Is day current day?
								{
									$run_span = true;
								}
							}


							// Weekly Days Display
							$day_flag = false;
							if($pattern_day) 					// Display for every day, continue
							{

								$dows = explode(",", $pattern_day);
								while(list($key, $disp_day) = each($dows))
								{

									if( ($disp_day == substr($cur_dayofweek,1,1)) && ($disp_day != "") ) 
									{
										// Weekly Displays
										if($pattern_week == "all" || $pattern_week == "")			// Display for every week, continue
										{
											$run_span = true;
										}
										else 
										{
											$woms = explode(",", $pattern_week);
											while(list($key, $disp_week) = each($woms)) 
											{
												if($disp_week == $wom)		// Is this the current week?
												{
													$run_span = true;
												}
											}
										} 

									}
								}

	
							}
							
							// If there is no pattern then display all days
							if(!$pattern_day && !$pattern_week && !$pattern_month && !$pattern_year)
							{
								$run_span = true;
							}
		
							if($run_span)
							{
								$schedule2[$cur_date][0][] = $item;
							}
						}
					}
			
				}
			}

		} // While Loop

	}

	if($error)
	{
		echo "<b><font color='#CC0000'>$error</font></b> <br>";
	}
	

	if(is_array($schedule2))
	{
		$start_date_a = getdate($start_ts);
		$end_date_a   = getdate($end_ts);
		
		$last_ts = $current_ts - (60 * 60 * 24 * 7);
		$next_ts = $current_ts + (60 * 60 * 24 * 7);
		
		// Top Bar
		?>
	<table border="0" cellspacing="1" cellpadding="3" width="100%">
		<tr>
			<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
	
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="bigTitle">
					<tr>
						<td width="100">
							&nbsp; Calendar 
						</td>
						<td align="center" class="mainHeading">
							<a href="calendar.php?user=<? echo $user ."&date=".date("Ymd",$last_ts)."&mode=$mode";?>">
							<img src="<? echo $THEME; ?>leftarrow.gif" border="0" align="texttop"></a>
							&nbsp; &nbsp; 
							<span class="bigTitle">
								<?
								$wdate_a   = array("m"=>$lang_datetime["short_mon"][$start_date_a["mon"]], "d"=>$start_date_a["mday"], "y"=>$start_date_a["year"]);
								$wdate_str = LangInsertStringsFromAK($lang_datetime["verbal"], $wdate_a);
								echo str_replace("%d", $wdate_str, "Week of: %d");
								?>
							</span>
							&nbsp; &nbsp; 
							<a href="calendar.php?user=<? echo $user ."&date=".date("Ymd",$next_ts)."&mode=$mode"; ?>">
							<img src="<? echo $THEME; ?>rightarrow.gif" border="0" align="texttop"></a>
						</td>
						<td width="100">&nbsp;
							 
						</td>
					</tr>
				</table>
	
			</td>
		</tr>
	</table>
		<?
		
		$dsow = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
		
		// Display weekly schedule
		echo "<p>";
		echo '<table width="95%" border="0" cellspacing="1" cellpadding="3" bgcolor="'.$my_colors["main_hilite"].'">';
		
		// Show tabl header -days of week and dates
		echo "<tr>";
		reset($date_dow);
		while(list($k,$d) = each($date_dow))
		{
			$tbh_month = (int)substr($k, 4, 2);
			$tbh_day   = (int)substr($k, 6, 2);
			$tbh_a     = array("m" => $lang_datetime["short_mon"][$tbh_month], "d" => $tbh_day);
			$tbh_str   = LangInsertStringsFromAK($lang_datetime["verbal_short"], $tbh_a);
			echo "<td align=\"center\" bgcolor=\"".$my_colors["main_head_bg"]."\" class=\"tblheader\" width=\"14%\">";
			echo ($k==$date?"<b>":"").$lang_datetime["dsow"][$d[1]].", ".$tbh_str.($k==$date?"</b>":"");
			echo "</td>";
		}
		echo "</tr>";
		
		// Show schedule
		echo "<tr>";
		reset($schedule2);
		while(list($k,$schedules) = each($schedule2))
		{
			if(!empty($k))
			{
				echo "<td align=\"left\" valign=\"top\" bgcolor=\"".$my_colors["main_bg"]."\" width=\"14%\"><span class=\"small\">";
				echo "[<a href=\"edit_calendar.php?user=$user&date=".$k."edit=\">Add Schedule</a>]";
				
				$schedules = $schedule2[$k];
				
				//echo count($schedules);
				
				ksort($schedules);
				reset($schedules);
				while(list($start, $blah) = each($schedules))
				{
					if(strcmp($start,"date")!=0)
					{
						if(is_array($schedules[$start]))
						{
							while(list($k2, $item) = each($schedules[$start]))
							{
								echo "<p>";
						
								if(!empty($item->color))
								{
									$style = "style=\"color: " . $item->color . "\"";
								}
								else
								{
									$style = "";
								}
						
								echo "<a href=\"edit_calendar.php?user=$user&edit=".$item->id."\" $style>";
								echo $item->title;
								echo "</a>";
								if(($item->beginTime + $item->endTime) != 0)
								{
									echo "<br>";
									echo LangFormatIntTime($item->beginTime, $my_prefs["clock_system"], $lang_datetime["ampm"], $lang_datetime["time_format"]);
									echo "-";
									echo LangFormatIntTime($item->endTime, $my_prefs["clock_system"], $lang_datetime["ampm"], $lang_datetime["time_format"]);
									echo ":";
								}
								if($item->place)
								{
									echo "<br><span $style>" . $item->place . "</span>";
								}
							}
						}
					}
				}
				?>
				</span></td>
				<?
			}
		}
	}
	?>
	</tr>
	</table>
	<?
}




// Default values for month/year
$month 		= (int)$current_month;
$year 		= (int)$current_year;
$prev_month = ($month>1?$month-1:12);
$prev_year 	= ($month==1?$year-1:$year);
$next_month = ($month<12?$month+1:1);
$next_year 	= ($month==12?$year+1:$year);
?>

<br> <br>

<table cellspacing=0 cellpadding=2>
	<tr>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>" valign="middle">
			&nbsp;
			<a class="mainHeading" href="calendar.php?user=<?=$user?>&date=<? echo formatCalDate($current_month, $current_day, $current_year-1); ?>">&lt;&lt;</a>
			&nbsp; &nbsp;
			<a class="mainHeading" href="calendar.php?user=<?=$user?>&date=<? echo formatCalDate($prev_month, $current_day, $prev_year); ?>">&lt;</a>
			&nbsp;
		</td>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>" class="mainHeading" valign="bottom">
			<form method="POST" action="calendar.php" style="display:inline">
				<input type="hidden" name="user"    value="<?=$user?>">
				<input type="hidden" name="session" value="<?=$user?>">
				<select name="go_month">
					<?
					for ($i = 1; $i <= 12; $i++) 
					{
						echo '<option value="'. $i .'"' . ($i==$current_month?" SELECTED":"") . '>' . $lang_months[$i];
					}
					?>
				</select>
				<select name="go_year">
					<?
					for ($i = -5; $i <= 10; $i++)
					{
						$go_year = $current_year + $i;
						echo '<option value="' . $go_year . '"' . ($go_year==$current_year?" SELECTED":"") . '>' . $go_year;
					}
					?>
				</select>
				<input type="submit" name="go" value="Go">
			</form>
		</td>
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>" valign="middle">
			&nbsp;
			<a class="mainHeading" href="calendar.php?user=<?=$user?>&date=<? echo formatCalDate($next_month, $current_day, $next_year); ?>">&gt;</a>
			&nbsp; &nbsp;
			<a class="mainHeading" href="calendar.php?user=<?=$user?>&date=<? echo formatCalDate($current_month, $current_day, $current_year+1); ?>">&gt;&gt;</a>
			&nbsp;
		</td>
	</tr>
</table>

<br>

<table wdith="95%" cellspacing="10">
	<tr>
		<td valign="top">
			<?
			$month = $prev_month;
			$year = $prev_year;
			include("../include/display_monthly_calendar.php");
			?>
		</td>
		<td valign="top">
			<?
			$month = (int)$current_month;
			$year = $current_year;
			include("../include/display_monthly_calendar.php");
			?>
		</td>
		<td valign="top">
			<?
			$month = $next_month;
			$year = $next_year;
			include("../include/display_monthly_calendar.php");
			?>
		</td>
	</tr>
</table>

<br> <br>

<table border="0" cellspacing="1" cellpadding="10" width="95%" bgcolor="<?=$my_colors["main_hilite"]?>">
	<tr>
		<td bgcolor="<?=$my_colors["main_bg"]?>" align="center">
			Click on a schedules title to view or edit.
		</td>
	</tr>
</table>

<br>

</center>

</body>
</html>