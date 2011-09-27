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
              
    Document: include/css.php
              
    Function: Build Cascading Style Sheet
              
*********************************************************************/

$font_family = ($my_colors["font_family"]?"font-family: ".$my_colors["font_family"].";\n":"");

if ($raw_css == false)
{
	echo '
		<STYLE type="text/css">
			<!--
		';
}
?>

	a
	{
		text-decoration: none
	}
	a:link
	{
		color: #<?=$my_colors["main_link"]?>;
	}
	a:visited
	{
		color: #<?=$my_colors["main_link"]?>;
	}
	a:hover
	{
		color: #<?=$my_colors["main_link"]?>;
		text-decoration: underline
	}

	body
	{
		<?=$font_family?>
		font-size: <?=$font_size?>px;
		background-color: <?=$bgc?>;
	}
	
	td, input
	{
		<?=$font_family?>
		font-size: <?=$my_colors["font_size"]?>px;
	}
	.textbox, select, textarea
	{
		font-family: Courier New, Courier, mono;
		color: <?=$my_colors["main_light_txt"]?>;
		font-size: <?=$my_colors["font_size"]?>px;
		border: thin solid <?=$my_colors["main_hilite"]?>;
	}
	
	
	h2
	{
		<?=$font_family?>
		font-size: <?=($my_colors["font_size"]+6)?>px;
		font-weight: bold;
		padding: 0px;
	}
	
	table
	{
		font-size: <?=$my_colors["font_size"]?>px;
		color: <?=$my_colors["main_text"]?>;
	}
	
	.bigTitle
	{
		<?=$font_family?>
		font-size: <?=$my_colors["font_size"]?>px;
		color: <?=$my_colors["main_head_txt"]?>;
		font-weight: bold;
	}
	
	.small
	{
		<?=$font_family?>
		font-size: <?=$my_colors["small_font_size"]?>px;
		color: <?=$my_colors["main_text"]?>;
	}
	
	a.tblheader:link, 
	a.tblheader:active, 
	a.tblheader:visited
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
	}
	a.tblheader:hover
	{
		text-decoration: underline;
	}
	.tblheader
	{
		<?=$font_family?>
		font-size: <?=$my_colors["font_size"]?>px;
		font-weight: bold;
		color: <?=$my_colors["main_head_txt"]?>;
	}
	
	.hilite
	{
		background-color: <?=$hilitec?>;
	}
	
	.menuText
	{
		<?=$font_family?>
		font-size: <?=$my_colors["menu_font_size"]?>px;
		color: <?=$my_colors["tool_link"]?>;
		font-weight: bold;
	}
	a.menuText:link, 
	a.menuText:active, 
	a.menuText:visited
	{
		text-decoration: none;
		<?=$font_family?>
		font-size: <?=$my_colors["menu_font_size"]?>px;
		color: <?=$my_colors["tool_link"]?>;
		font-weight: bold;
	}
	a.menuText:hover
	{
		text-decoration: underline;
	}
					
	.folderList
	{
		<?=$font_family?>
		font-size: <?=$my_colors["font_size"]?>px;
		color: <?=$my_colors["folder_link"]?>;
	}
	
	.mainHeading
	{
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
	}
	a.mainHeading:link, 
	a.mainHeading:active, 
	a.mainHeading:visited
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
	}
	a.mainHeading:hover
	{
		text-decoration: underline;
	}
	
	.mainHeadingSmall
	{
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
		border: 1px solid <?=$my_colors["main_head_txt"]?>;
		padding: 0px 3px;
	}
	a.mainHeadingSmall:link,
	a.mainHeadingSmall:visited,
	a.mainHeadingSmall:active
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
	}
	a.mainHeadingSmall:hover
	{
		text-decoration: none;
		border: 2px solid <?=$my_colors["main_head_txt"]?>;
		padding: 0px 2px;
	}
	
	.mainLight
	{
		<?=$font_family?>
		color: <?=$my_colors["main_light_txt"]?>;
	}
	a.mainLight:link, 
	a.mainLight:active, 
	a.mainLight:visited,
	a.mainLight:hover
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_light_txt"]?>;
	}
	
	.mainLightSmall
	{
		<?=$font_family?>
		color: <?=$my_colors["main_light_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
		overflow: visible;
	}
	a.mainLightSmall:link,
	a.mainLightSmall:visited,
	a.mainLightSmall:active,
	a.mainLightSmall:hover
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_light_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
	}
	
	.mainToolBar
	{
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
	}
	a.mainToolBar:link,
	a.mainToolBar:visited,
	a.mainToolBar:active
	{
		text-decoration: none;
		<?=$font_family?>
		color: <?=$my_colors["main_head_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
	}
	a.mainToolBar:hover
	{
		text-decoration: none;
	}

		<?=$font_family?>
		color: <?=$my_colors["main_light_txt"]?>;
		font-size: <?=$my_colors["small_font_size"]?>;
	
	a.txlink        {text-decoration:none}
    a.txlink:link   {color:<?=$my_colors["main_head_txt"]?>; font-size:<?=$my_colors["small_font_size"]?>; padding: 1px 5px;}
    a.txlink:visited{color:<?=$my_colors["main_head_txt"]?>; font-size:<?=$my_colors["small_font_size"]?>; padding: 1px 5px;}
    a.txlink:hover  {color:#000000; font-size:<?=$my_colors["small_font_size"]?>; background-color:<?=$my_colors["main_head_txt"]?>; padding: 1px 5px; text-decoration:none;}



<?
if ($raw_css == false)
{
	echo '
			//-->
		</STYLE>
		';
}
?>