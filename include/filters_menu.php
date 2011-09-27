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
              
    Document: include/filters_menu.php
              
    Function: Filters Menu

*********************************************************************/

?>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr class="bigTitle">
		<td bgcolor="<?=$my_colors["main_head_bg"] ?>">
			&nbsp; Filters
		</td>
	</tr>
	<tr>
		<td class="mainLight">&nbsp;
			<a href="filters.php?user=<?=$sid?>" class="mainLight">Basic</a>
			<?
			
			if($TMDA_ENABLED)
			{
			?>
			&nbsp; | &nbsp; <a href="filters_tmda.php?user=<?=$sid?>" class="mainLight">Advanced</a>
			<?
			}
			?>
		</td>
	</tr>
</table>
