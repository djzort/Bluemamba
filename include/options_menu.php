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
              
    Document: include/options_menu.php
              
    Function: Options Menu

*********************************************************************/
?>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr class="bigTitle">
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
			&nbsp; Options
		</td>
	</tr>
	<tr>
		<td class="mainLight">&nbsp;
			<a href="options.php?user=<?=$sid?>"            class="mainLight">General</a>    &nbsp; | &nbsp; 
			<a href="options_identities.php?user=<?=$sid?>" class="mainLight">Identities</a>
		</td>
	</tr>
</table>