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

    Modified: 09/19/2008
              
    Document: source/themes/[domain]/welcome/tips.php
              
    Function: Did You Know (tips)

*********************************************************************/

// Random
function random($max)
{
	$x = rand(); 
	$y = getrandmax(); 
	$r = $x / $y * ($max); 
	$r = round($r++); 
	if ($r=='0')
	{
		$r='1';
	} 
	return $r; 
} 


$user = $_GET['user'];

$TIPS = array
(
	// Color Themes
	"
	Web Mail has more than one color theme to select from?
	<br> 
	You can change this on the bottom left corner of your options page.
	<br> <br>
	<a href=\"../../../options.php?user=". $user ."\" target=\"list2\">Click here for your options page</a>
	",

	// Notify Sounds
	"
	You can select a different new mail sound?
	<br> 
	You can change this on the bottom left corner of your options page.
	<br> <br>
	<a href=\"../../../options.php?user=". $user ."\" target=\"list2\">Click here for your options page</a>
	",

	// Folders and Filters	
	"
	Web Mail has filter options, which let you search and organize your contacts and email into separate folders! 
	You can sort each family member's email into their own folder, or separate work contacts from family contacts!
	<br> <br>
	<a href=\"../../../edit_folders.php?user=". $user ."\" target=\"list2\">Add some folders</a>
	then
	<a href=\"../../../filters.php?user=". $user ."\" target=\"list2\">add filters for your contacts</a>
	",
	
	// Drafts & Unsent folders
	"
	You can save drafts of your messages, to help save time when sending multiple similar emails. No more retyping 
	that same email over and over again. If you don't have time to finish that lengthy email, just save it in the 
	Unsent Folder! It will be waiting for you when you get back!	
	",
	
	// World Wide Access
	"
	No matter where you are in the world, you will have access to your email. You can access your email 
	from any computer with an Internet connection.
	",
	
	// Search
	"
	We have added powerful sorting and search features, and easy to use folders for storing important emails.
	<br> <br>
	<a href=\"../../../search_form.php?user=". $user ."\" target=\"list2\">Search for an email</a>
	",
	
	// Auto Save Sent
	"
	Every time you send a piece of email, a copy will be stored in the Sent folder.
	<br> <br>
	<a href=\"../../../main.php?folder=INBOX.Sent&user=". $user ."\" target=\"list2\">Go to your Sent folder</a>
	",
	
	// Trash Folder
	"
	Accidentally deleted emails are not gone yet! Check the Trash folder to recover items that you've recently 
	deleted. It will not be permanently deleted until you press the Empty Trash link.
	<br> <br>
	<a href=\"../../../main.php?folder=INBOX.Trash&user=". $user ."\" target=\"list2\">Go to your Trash folder</a>
	",
	
	// Calendar
	"
	A powerful new appointment calendar will keep track of important dates, meetings, birthdays and special occasions for you.
	<br> <br>
	<a href=\"../../../calendar.php?user=". $user ."\" target=\"list2\">Go to your Calendar</a>
	",
	
	// Contacts
	"
	With our Contacts feature, your friends and associates addresses, phone numbers and email addresses are as close as 
	the nearest computer with an Internet connection.
	<br> <br>
	<a href=\"../../../contacts.php?user=". $user ."\" target=\"list2\">Go to your Contacts</a>
	",
	
	// Options
	"
	You can customize the behavior of your Web Mail with even more options than before.
	<br> <br>
	<a href=\"../../../options.php?user=". $user ."\" target=\"list2\">Click here for your Options page</a>
	",
	
	// Bookmarks
	"
	Keep your favorite web sites with your wherever you go, with the new bookmarks feature. At home or
    abroad; simply log into your Web Mail and you can access your favorite sites without searching the web.
	<br> <br>
	<a href=\"../../../bookmarks.php?user=". $user ."\" target=\"list2\">Click here for your Bookmarks page</a>
	",
	
	// Filters
	"
	Block unwanted email from anywhere or just move important email to a different folder.
	<br> <br>
	<a href=\"../../../filters.php?user=". $user ."\" target=\"list2\">Click here for your Filters page</a>
	",
	
	// Identities
	"
	Create your Identity! Send outgoing email with personalized signatures, nicknames, or what ever you want.
	<br> <br>
	<a href=\"../../../options_identities.php?user=". $user ."\" target=\"list2\">Click here for your Identities page</a>
	",
	
	// from/to Folder 
	"
	You can create a folder for sent messages and recieved messages. Select the 'Type' when creating a new folder. 
	'To' are messages sent and 'From' are messages recieved. This feature makes it easy for archiving email.
	<br> <br>
	<a href=\"../../../edit_folders.php?user=". $user ."\" target=\"list2\">Click here to Manage Folders</a>
	",
	
	// CC & BCC
	"
	CC stands for Carbon Copy. This is so you can send a copy of the email to another person. 
	BCC is Blind Carbon Copy. BCC is similar to CC, accept all other recipients do not see it.
	",
	
	// Contact Compose
	"
	Instead of clicking contacts on the compose page you can go to your Contacts list and select who you
	want to send a message to. Click the check boxes next to the desired contacts then click Compose.
	<br> <br>
	<a href=\"../../../contacts.php?user=". $user ."\" target=\"list2\">Go to your Contacts</a>
	"
);

// Select
$TIP_OF_THE_DAY = $TIPS[ random(count($TIPS)-1) ];

?>
