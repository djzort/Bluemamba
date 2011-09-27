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
              
    Document: source/contacts_popup.php
              
    Function: Contacts selection popup

*********************************************************************/


function FormatHeaderLink($user, $label, $color, $new_sort_field, $sort_field, $sort_order)
{
	if(strcasecmp($new_sort_field, $sort_field) == 0)
	{
		if(strcasecmp($sort_order, "ASC") == 0)
		{
			$sort_order = "DESC";
		}
		else
		{
			$sort_order = "ASC";
		}
	}
	$link  = "<a href=\"contacts_popup.php?user=$user&sort_field=$new_sort_field&sort_order=$sort_order\" class=\"mainHeading\">";
	$link .= "<b>".$label."</b></a>";
	return $link;
}


function ShowRow($a, $id)
{
	global $my_colors, $grp_sort;

	echo "<tr>\n";
	if(empty($a["name"])) $a["name"]="--";
	echo "<td bgcolor=\"".$my_colors["main_bg"]."\"><a href=\"javascript:addcontact2('$id');\">".$a["name"]."</a></td>";
	echo "<td bgcolor=\"".$my_colors["main_bg"]."\">".$a["email"]."</td>";
	if(!$grp_sort) echo "<td>".$a["grp"]."</td>";
	echo "</tr>\n";
}

include("../include/super2global.php");
include("../include/contacts_commons.php");
include_once("../include/data_manager.php");

if(isset($user))
{
	include("../include/header_main.php");

	// Authenticate
	include_once("../include/icl.php");
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if($conn)
	{
		iil_Close($conn);
	}
	else
	{
		echo "Authentication failed.";
		echo "</html>\n";
		exit;
	}
	
	// Open data manager connection
	$dm = new DataManager_obj;
	if(!$dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $backend))
	{
		echo "Data Manager initialization failed:<br>\n";
		$dm->showError();
	}
		
	// Initialize sort fields and order
	if(empty($sort_field)) $sort_field = "grp,name";
	if(empty($sort_order)) $sort_order = "ASC";
	if(ereg("^grp", $sort_field))
	{
		$grp_sort = true;
	}
	else
	{
		$grp_sort = false;
	}
	
	// Fetch and Sort
	$contacts    = $dm->sort($sort_field, $sort_order);
	$numContacts = count($contacts);
	$groups      = explode(",", base64_decode(GetGroups($contacts)));

	// Show error, if any
	if(!empty($error)) echo "<p>".$error."<br>\n";
	
	
	// Show title heading
	echo "\n<table width=\"100%\" cellpadding=\"2\" cellspacing=\"2\"><tr>\n";
	echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\" align=left valign=bottom>\n";
	echo "&nbsp;<span class=\"bigTitle\">Contacts</span>\n";
	echo "&nbsp;&nbsp;&nbsp;";
	echo '<span>';
	echo ' <a href="javascript:close();" onClick="window.close();" class="mainHeadingSmall">Close Window</a> ';
	echo '</span>';
	echo "</td></tr></table>\n";


	// Show instructions
	?>
	<br>
	<span class=mainLight>Click on names to add. You may also click on group labels to add everyone in the group.</span>
	<?
	
	// Show controls
	echo "<p><form method=\"POST\" name=\"contactsopts\" action=\"contacts_popup.php\">\n";
	echo "<input type=\"hidden\" name=\"user\" value=\"$user\">\n";
	echo "<input type=\"hidden\" name=\"cc\" value=\"$cc\">\n";
	echo "<input type=\"hidden\" name=\"bcc\" value=\"$bcc\">\n";
	echo "<input type=\"hidden\" name=\"sort_order\" value=\"$sort_order\">\n";
	echo "<input type=\"hidden\" name=\"sort_field\" value=\"$sort_field\">\n";
	echo "<table width=\"100%\" cellpadding=\"2\" cellspacing=\"1\"><tr>\n";
	echo "<td valign=\"top\"><span class=mainLight>\n";
		$select_str = "<select name=\"to_a_field\">\n";
		$select_str.= "<option value=\"to\">To:\n";
		if($cc) $select_str.= "<option value=\"cc\">CC:\n";
		if($bcc) $select_str.= "<option value=\"bcc\">BCC:\n";
		$select_str.= "</select>\n";
		echo str_replace("%s", $select_str, "Add contacts to %s");
	echo "</span></td>\n";
	echo "<td valign=\"top\"><span class=mainLight>\n";
		$select_str = "<select name=\"show_grp\" onChange=\"contactsopts.submit()\">\n";
		$select_str.= "<option value=\"\" ".(empty($show_grp)?"SELECTED":"").">All\n";
		while ( list($k,$val)=each($groups) ) $select_str.= "<option value=\"$val\" ".($show_grp==$val?"SELECTED":"").">$val\n";
		$select_str.= "</select>\n";
		echo str_replace("%s", $select_str, "Show %s");
	echo "</span></td>\n";
	echo "</tr></table>\n";
	echo "</form>\n";
	flush();

	// Show contacts
	if(is_array($contacts) && count($contacts) > 0)
	{
		reset($contacts);
		$num_c=0;
		echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">\n";
		echo "contacts = new Array(";
		while(list($k1, $foobar) = each($contacts))
		{
			$a = $contacts[$k1];
			if($a["email"])
			{
				if($num_c>0) echo ",\n";
				$name = (!empty($a["name"])?"\"".$a["name"]."\" ":"\"".$a["email"]."\"");
				echo "new Array($num_c,$name,\"".$a["email"]."\",\"".$a["grp"]."\")";
				$num_c++;
			}
			if($a["email2"])
			{
				$name = (!empty($a["name"])?"\"".$a["name"]."\" ":"\"".$a["email2"]."\"");
				echo ",\nnew Array($num_c,$name,\"".$a["email2"]."\",\"".$a["grp"]."\")";
				$num_c++;
			}
		}
		echo ");\n</script>";

		reset($contacts);
		$num_c = 0;
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"2\" bgcolor=\"".$my_colors["main_hilite"]."\">\n";
		echo "<tr bgcolor=\"".$my_colors["tool_bg"]."\">";
		echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\">&nbsp;".FormatHeaderLink($user, "Name", $textc, "name", $sort_field, $sort_order)."</td>";
		echo "<td bgcolor=\"".$my_colors["main_head_bg"]."\">&nbsp;".FormatHeaderLink($user, "Email", $textc, "email", $sort_field, $sort_order)."</td>";
		if(!$grp_sort)
		{
			echo "<td>".FormatHeaderLink($user, "Group", $textc, "grp,name", $sort_field, $sort_order)."</td>";
		}
		echo "</tr>";
		$prev_grp = "";
		$num_c = 0;
		while(list($k1, $foobar) = each($contacts))
		{
			$a = $contacts[$k1];
			if(empty($show_grp) || $show_grp == $a["grp"])
			{
				if($grp_sort && $a["grp"] != $prev_grp)
				{
					//$grp = str_replace(" ", "_", $a["grp"]);
					$toString = htmlspecialchars($a["grp"]);
					echo "<tr bgcolor=\"".$my_colors["main_bg"]."\"><td colspan=2 align=center><br><b>";
					echo "<a href=\"javascript:addgroup('$toString');\">".$a["grp"]."</a>";
					echo "</b></td></tr>";
					$prev_grp = $a["grp"];
				}
				if($a["email"])
				{
					ShowRow($a, $num_c); $num_c++;
				}
				if($a["email2"])
				{
					$a["email"] = $a["email2"];
					ShowRow($a, $num_c); $num_c++;
				}
			}
		}
		echo "</table>\n";
	}
	else
	{
		echo "<p>Contacts list is empty";
	}
}
?>
</body>
</html>