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
              
    Document: include/display_monthly_calendar.php
              
    Function: Display monthly calendar

*********************************************************************/

include_once("../conf/db_conf.php");
include_once("../include/idba.php");

// Full english months, for generating date string
$eng_month_a = array("January", "February", "March", "April", "May", "June", "July",
                     "August", "September", "October", "November", "December");


$month_str 	 = $eng_month_a[$month-1];		// Generate string for first day of month
$date_str 	 = "1 $month_str $year";
$time 		 = strtotime($date_str);		// Convert string to timestamp
$dow 		 = date("w", $time);			// Day of week month starts on
$week_offset = $dow;	
$num_days  	 = date("t", $time);			// Number of days in month

$heading = $lang_datetime["monthyear"];		// Format heading
$heading = str_replace("%m", $lang_months[$month], $heading);
$heading = str_replace("%y", $year, $heading);

if( ($month < 10) && (strlen($month) == 1) ) 
{
	$mon_str = "0" . $month;
}
else
{
	$mon_str = $month;
}
$wom = 1;

?>
<center>

<span class="mainLight"><b><? echo $heading; ?></b></span>


<table border="0" cellspacing="1" cellpadding="3" bgcolor="<?=$my_colors["main_hilite"]?>">
	<?
	echo '<tr height="25">';					// Display Days row (Characters)
	for($x = 0; $x < 7; $x++)
	{
		echo '<td height="25" valign="middle" align="center" bgcolor="' . $my_colors["main_head_bg"] . '" width="20" class="tblheader">';
		echo $lang_datetime["dsow_short"][$x];
		echo '</td>';
	}
	echo '</tr>';

	if($dow > 0)
	{
		echo '<tr>';
		for($x = 0; $x < $dow; $x++)			// Display Place Holders for previouse month's dates
		{
			echo '<td height="25" valign="middle" bgcolor="' . $my_colors["main_hilite"] . '" width="20"></td>';
		}
	}
	
	for($x = 1; $x <= $num_days; $x++)			// Display Every Day in the month
	{
		if(($x < 10) && (strlen($x)==1))
		{
			$day_str = "0" . $x;
		}
		else
		{
			$day_str = $x;
		}

		$disp_date = $year.$mon_str.$day_str;
		$run_span  = false;

		// Modify Week of Month
		if($dow == 0)
		{
			if($wom >= 5)
			{
				$wom = 0;
			}
			$wom++;
		}

		if($backend != "FS")
		{
			// Check DB for schedual on current day
			$sql = "SELECT * FROM $DB_CALENDAR_TABLE WHERE userID='$session_dataID' ";
			$sql.= "and ((beginDate <= '$disp_date' and endDate >= '$disp_date'))";
		
			if(!isset($db)) $db = new idba_obj;
			if($db->connect())
			{
				$backend_result = $db->query($sql);
			}
			else
			{
				$backend_result = false;
			}
		}
		
		if($backend_result)
		{
			if( $db->num_rows($backend_result) > 0 )
			{
				while( $a = $db->fetch_row($backend_result) )
				{

					$day_beginTime  = $a["beginTime"];
					$day_endTime    = $a["endTime"];
					$day_beginDate  = $a["beginDate"];
					$day_endDate    = $a["endDate"];
					$day_color      = $a["color"];

					$pattern_xday   = $a["pattern_day"];
					$pattern_xweek  = $a["pattern_week"];
					$pattern_xmonth = $a["pattern_month"];
					$pattern_xyear  = $a["pattern_year"];


					// Get Start and Current Year/Month/Day
					$start_year = substr($day_beginDate, 0,4);
					$end_year   = substr($day_endDate, 0,4);
					$cur_year   = substr($disp_date, 0,4);
					
					$start_month = substr($day_beginDate, 4,2);
					$end_month   = substr($day_endDate, 4,2);
					$cur_month   = substr($disp_date, 4,2);

					$start_day   = substr($day_beginDate, 6,2);
					$end_day     = substr($day_endDate, 6,2);
					$cur_day     = substr($disp_date, 6,2);

					// Yearly Displays
					$year_flag = false;
					if($pattern_xyear)							// Display for Every Year
					{
						$year_flag = true;
					}
					else										// Has a year elapsed since creation
					{
						if($start_year <= $cur_year)
						{
							$year_flag = true;
						}
					}
	
					if($year_flag)
					{
						if($start_month == $cur_month)		// Is Month current month?
						{
							if($start_day == $cur_day)		// Is day current day?
							{
								$run_span = true;
							}
						}
					}
	
					// Monthly Displays
					if($pattern_xmonth)						// Run For Every Month
					{
						if($pattern_xmonth == $day_str)		// Is day current day?
						{
							$run_span = true;
						}
					}
	
					// Days Display
					if($pattern_xday)					// Display for every day, continue
					{
						$dows = '';
						$dows = explode(",", $pattern_xday);
						while( list($key, $disp_day) = each($dows) )
						{
							// Is today the current day? Make sure the disp_day is not null
							if( ($disp_day == $dow) && ($disp_day != "") ) 
							{
								// Week Displays
								if($pattern_xweek == "all" || $pattern_xweek == "") 			// Display for every week, continue
								{
									$run_span = true;
								}
								else
								{
									$woms = explode(",", $pattern_xweek);
									while( list($key, $disp_week) = each($woms) )
									{
										if( ($disp_week == $wom) )		// Is this the current week?
										{
											$run_span = true;
										}
									}
								}
							}
						}
					}


					// If there is no pattern then display all days
					if(!$pattern_xday && !$pattern_xweek && !$pattern_xmonth && !$pattern_xyear)
					{
						$run_span = true;
					}

					if($run_span)
					{
						if($prevday_color == '') { $prevday_color = $day_color; }
						if($prevday_color)       { $day_color = $prevday_color; }
					}

				}
			}
		}

				
		$url = "calendar.php?user=$user&date=$disp_date";

		// Display Actual Day
		if($dow == 0)
		{
			echo '<tr>';
		}


		echo '<td valign="middle" align="center" bgcolor="';
			if($run_span && ($disp_date == $date) )
			{
				$prevday_color = '';
				echo $day_color;
			}
			elseif($disp_date == $date)
			{
				echo "#000000";
			}
			else
			{
				echo $my_colors["main_bg"];
			}
		echo '" height="25" width="20"><a href="' . $url . '">';

			// Display Numerical Day (with bolded color if event)
			if($disp_date == $date)
			{
				echo '<font color="#FFFFFF"><b>' . $x . '</b></font>';
			}
			elseif($run_span)
			{
				$prevday_color = '';
				echo '<font color="' . $day_color . '"><b>' . $x . '</b></font>';
			}
			else
			{
				echo $x;
			}


		echo '</a></td>';

		if($dow==6) echo '</tr>';		// End row
		$dow = ($dow + 1) % 7;
	}



	if($dow > 0)
	{
		for($x = $dow; $x < 7; $x++)			// Display Place Holders for next month's dates
		{
			echo '<td valign="middle" bgcolor="' . $my_colors["main_hilite"] . '" height="25" width="20"></td>';
		}
		echo '</tr>';
	}

	?>
</table>