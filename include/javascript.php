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
              
    Document: include/javascript.php
              
    Function: Display JavaScript
              
*********************************************************************/

$current_page = $_SERVER["PHP_SELF"];
$pos = strrpos($current_page, "/");
if($pos !== false)
{
	$current_page = substr($current_page, $pos+1);
}

echo "\n<!-- $current_page //-->\n";


if( (strstr($current_page, "main.php") !== false) || (strstr($current_page, "main_pending.php") !== false) || (strstr($current_page, "search_post.php") !== false) )
{
?>
		<SCRIPT type="text/javascript" language="JavaScript1.2">
		function SelectAllMessages(val) {
		    for (var i = 0; i < document.messages.elements.length; i++) {
				if(document.messages.elements[i].type == 'checkbox') {
					document.messages.elements[i].checked = !(document.messages.elements[i].checked);
					document.messages.elements[i].checked = val;
				}
		    }
		}
		</SCRIPT>

<?
}
elseif(strpos($current_page, "compose.php")!==false)
{
?>
		<script type="text/javascript" language="JavaScript1.2">
		var contacts_popup_visible=false;
		var contacts_popup;
		function CopyAdresses() {
			switch (document.forms[0].to_a_field.selectedIndex) {
			case 1:
				var target = document.forms[0].cc;
				break;
			case 2:
				var target = document.forms[0].bcc;
				break;
			default:
				var target = document.forms[0].to;
			}
			var selbox=document.forms[0].elements['to_a[]'];
			for (var i=0; selbox.length>i; i++) {
				if((selbox.options[i].selected == true) &&
		 		 (target.value.indexOf(selbox.options[i].text, 0)==-1)) { //A check to prevent adresses from getting listed twice.
					if(target.value != '') 
						target.value += ', ';
					target.value += selbox.options[i].text;
				}
			}
		}
		
		function DeselectAdresses() {
			var selbox = document.forms[0].elements['to_a[]'];
			if(selbox) {
				for (var i=0; selbox.length>i; i++)
					selbox.options[i].selected = false;
			}
		}
		
		function DoCloseWindow(redirect_url){
			if(parent.frames.length!=0)
				parent.list2.location=redirect_url;
			else
				window.close();
		}		
		
		function fixtitle(title_str)
		{
			if(document.forms[0].subject.value=='')
				document.title = title+" : <? echo $SITE_TITLE; ?>";
			else
				document.title = title_str+": "+document.forms[0].subject.value+" : <? echo $SITE_TITLE; ?>";
		}
		
		function open_popup(comp_uri) {
			if(comp_uri) {
				if(contacts_popup_visible==false) {
					if(document.forms[0].cc) comp_uri += "&cc=1";
					if(document.forms[0].bcc) comp_uri += "&bcc=1";
					contacts_popup = window.open(comp_uri, "_blank","width=500,height=500,scrollbars=yes,resizable=yes");
					if(contacts_popup.opener == null)
					contacts_popup.opener = window;
				}
				contacts_popup.focus();
			}
			return;
		}
		
  		function close_popup(){
			if(contacts_popup_visible)
  				contacts_popup.close();
  		}

		</SCRIPT>
<?
}
elseif(strpos($current_page, "contacts_popup.php")!==false)
{
?>
		<script type="text/javascript" language="JavaScript1.2">
		var contacts;
		function gettarget() {
			switch (document.contactsopts.to_a_field.selectedIndex) {
			case 1:
				var target = opener.document.forms[0].cc;
				break;
			case 2:
				var target = opener.document.forms[0].bcc;
				break;
			default:
				var target = opener.document.forms[0].to;
			}
			return target;
		}

		function addcontact(address) {
			var target = gettarget();
			if(target.value.indexOf(address, 0)==-1) { //A check to prevent adresses from getting listed twice.
				if(target.value != '') target.value += ', ';
				target.value += address;
			}
		}
		
		function addcontact2(id) {
			for (var i=0; i<contacts.length; i++) {
				if(id==contacts[i][0])
					addcontact("\""+contacts[i][1]+"\" <"+contacts[i][2]+">");
			}
		}
		
		function addgroup(group) {
			for (var i=0; i<contacts.length; i++) {
				if(group==contacts[i][3])
					addcontact("\""+contacts[i][1]+"\" <"+contacts[i][2]+">");
			}
		}

		function acknowledge_popup() {
			opener.contacts_popup_visible=true;
		}
		
		function alert_close() {
			opener.contacts_popup_visible=false;
		}
		</script>
<?
}
elseif(strpos($current_page, "prefs.php")!==false)
{
?>
		<script type="text/javascript" language="JavaScript1.2">
		var colprefs_popup_visible=false;
		var colprefs_popup;

		
		function open_popup(comp_uri) {
			if(comp_uri) {
				if(colprefs_popup_visible==false) {
					colprefs_popup = window.open(comp_uri, "_blank","width=350,height=350,scrollbars=yes,resizable=yes");
					if(colprefs_popup.opener == null)
					colprefs_popup.opener = window;
				}
				colprefs_popup.focus();
			}
			return;
		}
		
  		function close_popup(){
			if(colprefs_popup_visible)
  				colprefs_popup.close();
  		}

		</SCRIPT>
<?
}
?>