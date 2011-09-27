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
              
    Document: include/log.syslog.php
              
    Function: System Logging
              
*********************************************************************/

$log_entry = $log_template;
$log_entry = str_replace("date", $log_time, $log_entry);
$log_entry = str_replace("acct", $log_account, $log_entry);
$log_entry = str_replace("ip", $log_ip, $log_entry);
$log_entry = str_replace("action", $log_action, $log_entry);
if (! empty($log_comment))
  $log_entry = str_replace("comment", "($log_comment)", $log_entry);
else
  $log_entry = str_replace("comment", "", $log_entry);

$log_priority = 6; // 7 = debug, 6 = info, 5 = notice, 4 = warning, 3 = err, 2 = crit, 1 = alert, 0 = emerg

syslog($log_priority, $log_entry);

?>
