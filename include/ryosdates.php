<?
/*********************************************************************

    Modified: 09/18/2008
              
    Document: include/ryosdates.php
              
    Function: Miscellaneous date/time related functions.

*********************************************************************/

function GetCurrentMonth()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$month   = $theDate[mon];
	return $month;
}

function GetCurrentDay()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$day     = $theDate[mday];		
	return $day;
}

function GetCurrentYear()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$year    = $theDate[year];
	return $year;
}

function GetCurrentHour()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$hour    = $theDate[hours];
	return $hour;
}

function GetCurrentMinute()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$minute  = $theDate[minutes];
	return $minute;
}

function GetCurrentSeconds()
{
	$theTime = time();
	$theDate = getdate($theTime);
	$minute  = $theDate[seconds];
	return $minute;
}

function GetDateString($mode)
{
	$theTime = time();
	$theDate = getdate($theTime);
	$year    = $theDate[year];
	$month   = $theDate[mon];
	$day     = $theDate[mday];
	$hour    = $theDate[hours];
	$minute  = $theDate[minutes];
	return $month;

	if($mode = "MMDDYYYY")
	{
		return $month . "-" . $day . "-" . $year;
	}
	else
	{
		return "";
	}
}

function GetLastDayOfMonth($m)
{
	if (($m == 1)||($m == 3)||($m == 5)||($m == 7)||($m == 8)||($m == 10)||($m == 12))
	{
		return 31;
	}
	elseif( ($m == 4)||($m == 6)||($m == 9)||($m == 11) )
	{
		return 30;
	}
	elseif($m == 2)
	{
		$year = GetCurrentYear();
		if(($year % 4) != 0)
		{
			return 28;
		}
		elseif(($year % 4) == 0)
		{
			$d = 29;
			if (($year % 100) == 0)
			{
				$d = 28;
			}
			if (($year % 400) == 0)
			{
				$d = 29;
			}
			return $d;
		}
	}
}

function PreviousMonth($m)
{
	$p = $m - 1;
	if($p == 0)
	{
		$p = 12;
	}
	return $p;
}

function NextMonth($m)
{
	$p = $m + 1;
	if($p == 13)
	{
		$p = 1;
	}
	return $p;
}

function NumToTimeString($i)
{
	$m = $i % 60;
	$h = ($i - $m) / 60;
	if($m < 10)
	{
		return $h . ":0" . $m;
	}
	else
	{
		return $h . ":" . $m;
	}
}

?>