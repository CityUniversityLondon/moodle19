/******************************************************************************\
 *
 * Filename:   README.txt
 *
 *				Installation notes for the iPodcast module 
 *
 * History:     01/03/06 Tom Dolsky     - Added this copyright and info
 *              02/28/06 Tom Dolsky     - Updated to reflect current install
 *              10/18/06 Tom Dolsky     - Added text to reflect Moodle 1.6 language file
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
For Apache:
Make sure to AllowOverride All(or enough to get "AddType" to function)  on /mod/ipodcast folder.  
The folder contains a .htaccess file which allows files with media extentions to execute as php files.
This fools iTunes into downloading the media file

For IIS:
Go to  IIS Manager and fan down to the moodle/mod/ipodcast directory in the left pane.  
Right click on the ipodcast directory and open properties.  
Click on the Directory tab and in the bottom section click on the Create button then click on the Configuration button.  
It should inherit the setting from the root setup.  
Find the .php line and write down the executable path to the CGI.  
Then click the Add button and add the same executable path to the media extensions one add for each " .mp3, .mp4, .mov , .m4a, .m4b, .m4v" Apply then click ok all the way out.

For Mac OSX server:

*********************************************************************************************************************************

Some quick directions on use.
*********************************************************************************************************************************
* Language File
Moodle < 1.6
As of ipodcast .37b3 the language folder is copied automatically to the proper folders.  
If the language is changed after the update the language file will need to be manually moved.

Copy the ipodcast folder from moodle/mod/ipodcast/lang/en/help to moodle/lang/en/help/ .

Moodle > 1.6
Moodle 1.6 has renamed the language folder from en to en_utf8 please take note when placing language files.

As of ipodcast .37b3 the language folder is copied automatically to the proper folders.  
If the language is changed after the update the language file will need to be manually moved.
Copy the ipodcast folder from moodle/mod/ipodcast/lang/en_utf8/help to moodle/lang/en_utf8/help/ .

*********************************************************************************************************************************
* Media extensions
Go to your administration and install module and make sure all succeeds.  
Apache will need to have .htaccess override allowed for the ipodcast module directory.  
The module directory contains a premade .htaccess to allow scripts to run with media extensions ie. .mp3 , .mov, .mp4, .m4v .m4a .m4b .mpg .pdf   
itunes will only download files with these extensions.  

to add the m4v,m4b  and m4a extensions they need to be added to the function mimeinfo() at the top of the /lib/filelib.php
around line 54 find the line

         'mp3'  => array ('type'=>'audio/mp3', 'icon'=>'audio.gif'),

and replace with the following 4 line snippit

//**snip**
        'mp3'  => array ('type'=>'audio/mpeg', 'icon'=>'audio.gif'),
        'm4a'  => array ('type'=>'audio/x-m4a', 'icon'=>'audio.gif'),
        'm4b'  => array ('type'=>'audio/x-m4b', 'icon'=>'audio.gif'),
        'm4v'  => array ('type'=>'video/x-m4v', 'icon'=>'video.gif'),
//**end snip**

The mp3 mimetype is incorrect in the filelib.php this will also fix that issue.
Now go to the end of the filelib.php and check for a newline after the ?> line and delete if it is there.  
This newline character conflicts with the xml building on some systems.
*********************************************************************************************************************************
* Darwin streaming server.
There are two ways to utilize the Darwin Streaming Server(DSS).  Both are very insecure at this time because the entire moodledata folder is exposes through DSS.  If security is an issue don't use it at this point.

The first is to install DSS on the moodle server.  After you have a working DSS set the DSS media directory to the moodledata directory.
In the podcast module settings set the Darwin base URL to the root of your DSS "rtsp://192.168.1.240/" iPodcast will add the remaining path to the root.
Each iPodcast course settings can override the Darwin URL so make sure it is also set properly.  Enable Darwin support in the iPodcast course settings.  

The second is to manually copy the media files to another server.  The same directory structure of the moodledata must be maintained inorder for it to function.
Make sure your Darwin base URL's are set properly and Darwin support is enabled.  iPodcast darwin link will stream from the other DSS.

At this point any files that are uploaded 

*********************************************************************************************************************************
* File Hinting 
If you want to be able to hint files for Darwin Streaming server purposes download the mpeg4ip package for your particular platform.  
Binaries are available around the net.  Install in a php executable directory.  I installed mine in c:/php/ on windows and the standard /usr/local/bin in linux.
Go to the module settings and set the paths including the executable name.  Example "C:/php/mp4creator.exe" for windows.
At that point if you upload an mp4 in the attachment tab a hint button will appear.  Pushing this button will add streaming hint tracks.

*********************************************************************************************************************************
* Testing
You should have a working setup here.  To this point has only been tested with Windows and mysql other OS's are untested.  
Also English is the only language file since its the only one I can understand.

The next step is to hit the ipodcast module settings and enable ipodcast_enablerssfeeds and set ipodcast_enablerrssitunes if you want itunes tags.  
enablerssfeeds needs to be enabled for your site to have that option. 
Now go into a course you are a teacher or admin for and turn editing on.  
Add an Activity and Podcast should now be in the list. When you select it you should get the message 
"iPodcast settings have not been set for this Course. Set here before continuing"

This is the first time per course setting for the podcast.  It will load some default settings from the course.  
Check over and hit "Save Course Settings" .  The header should now read "Updating iPodcast settings for Course {coursename}" .  
Select done to get back to the course outline.  Now add a podcast activity again for whatever section you want.  
Update the name summary and notes to your preference and hit the save button.  You should see a tabbed window with a few options. 

View Podcast tab views it as a student would see the page.  
Comments tab views the student comments on the podcast
Views tab shows who has viewed the media file and when.
Edit Podcast tab edits the Name, Summary, and notes of the podcast
iTunes Tags tab edits all the iTunes specific information
Attachment  tab loads and selects the media file.
Visibility tab selects both the current or scheduled visibility for the podcast.

By default the iTunes tags are set from the Course settings for the podcast.  No Attachment and visibility is set to hidden.
To select a media file hit the Attachment tag and select the Change... button then select Upload a file button.  
You should get another window with a Browse... button select it and an open window should appear.  
Find the media file and select it.  Remember only "mp3, mov, m4a, m4b, m4v, and mp4" files are supported. 
After selecting the file click Upload this file you should have a directory listing with that file listed.  
Choose select from the action column and the line should go grey and the action will display "Currently Selected" .  
Choose Close this window button to take you back to the Podcast.  
Now change the current visibility to "Podcast visible to students"  click save and the podcast entry is ready.
To subscribe to the podcast click on "Podcasts" under activities for the course.  
Its on the upper left side on my moodle.  This should bring up a list of published podcasts,  probably just one if you were following this from scratch.  
In the upper right corner should be a rss iPodcast button right click and copy shortcut or link.  
Now paste that shortcut into the iTunes subscribe to ipodcast window.
If all is well you should have the podcast.  

The rss xml feed is built when the link is called so you dont have to wait for cron after adding a podcast activity.
*********************************************************************************************************************************
* Other notes
Media files with spaces in the name can be selected but iTunes for Mac can't download them for whatever reason.


*********************************************************************************************************************************

* Version info

Tom

*********************************
****Release notes***
*********************************
*********************************
*****v.37b3
*********************************
01/02/07 Tom Dolsky     - Changed stripslashes to stripslashes_safe
01/02/07 Tom Dolsky     - Fixed some addslashes bugs
01/02/07 Tom Dolsky     - Added pcast:// uri for Macintosh only Not 100% positive on Windows yet
01/02/07 Tom Dolsky     - Added ability to support pdf files
01/02/07 Tom Dolsky     - Changed stripslashes to stripslashes_safe
01/02/07 Tom Dolsky     - Fixed & character in categories
01/04/07 Tom Dolsky     - Fixed some stripslashes bugs
01/02/07 Tom Dolsky     - Updated support for roles in library


*********************************
*****v.37b2
*********************************
12/23/06 Tom Dolsky     - Extended the rsslink flags to include global per module and per course
12/23/06 Tom Dolsky     - Removed fields causing notices
12/23/06 Tom Dolsky     - added the enablerssfeed to form
12/23/06 Tom Dolsky     - Fixed several bugs in getting teacher list
12/23/06 Tom Dolsky     - Changed teacher list to be the role of legacy:editingteacher

*********************************
*****v.37b
*********************************
12/22/06 Tom Dolsky     - Added roles based Teacher list for setupcourse

*********************************
*****v.36
*********************************
This version will quite likely mess up your category selections.  Apple made changes to the category lists some time ago and not all map one to one.
The update script makes a small effort in reassigning some of the obvious ones but make sure you double sheck your categories.
 
10/28/06 Tom Dolsky     - Updated category list to current from apple
11/06/06 Tom Dolsky     - Teacher list for course broke in 1.7 beta added version checking to get by for now
11/06/06 Tom Dolsky     - Added None to nested category to select only top category

*********************************
*****v.35
*********************************
10/25/06 Tom Dolsky     - made sure file_get_extension only returns lower case.
10/25/06 Tom Dolsky     - Variable fixes for moodle 1.7
10/25/06 Tom Dolsky     - Added some variables to prepare for student posting
10/25/06 Tom Dolsky     - fixed get attachent log to support hash.
10/25/06 Tom Dolsky     - Remove hinting button if no attachment or an mp3 file
10/25/06 Tom Dolsky     - Remove streaming link if mp3 file attached

*********************************
*****v.34
*********************************
10/23/06 Tom Dolsky     - Fixed media viewing
10/24/06 Tom Dolsky     - Added image size settings in image tab.
10/24/06 Tom Dolsky     - Attachment windows create ipodcast directory if one doesn't exist.
10/24/06 Tom Dolsky     - Removed some old debugging echo output.
10/24/06 Tom Dolsky     - Fixed new course setup with new tab view
10/24/06 Tom Dolsky     - Fixed empty podcast continue button link
10/24/06 Tom Dolsky     - Rebuilt extension parsing.
10/24/06 Tom Dolsky     - Added image size variables to rss header

*********************************
*****v.33
*********************************
10/20/06 Tom Dolsky     - Fixed apostrophe display in name and notes
10/20/06 Tom Dolsky     - Put lost text back in ipodcast.php
10/20/06 Tom Dolsky     - Added hinting for Darwin files during auto posting.
10/23/06 Tom Dolsky     - Added path configs for mpeg4ip executables.
10/23/06 Tom Dolsky     - Added file and path checking for mpeg4ip executables.
10/23/06 Tom Dolsky     - Added hinting button to attachment page.
10/23/06 Tom Dolsky     - Created imagepopup.php file
10/23/06 Tom Dolsky     - Created hintpopup file
10/20/06 Tom Dolsky     - Added per iPodcast specific darwinurl
10/23/06 Tom Dolsky     - Converted setupcourse.php to tabbed format
10/23/06 Tom Dolsky     - Added image selection tab
10/20/06 Tom Dolsky     - Added darwin enable flag to ipodcast course settings
10/20/06 Tom Dolsky     - Adds a root slash if one doesnt exist on attachment
10/23/06 Tom Dolsky     - Changed config for darwin base url from streambaseURL to ipodcast_darwinurl
10/20/06 Tom Dolsky     - changed darwin config name.
10/23/06 Tom Dolsky     - Added path configs for mpeg4ip executables.
10/23/06 Tom Dolsky     - Fixed bug when configs were empty.
10/23/06 Tom Dolsky     - Fixed isteacher bug in attachment link.
10/23/06 Tom Dolsky     - Fixed enclosure length error

*********************************
*****v.31
*********************************
 *              10/17/06 Tom Dolsky     - Added text
*********************************
*****v.30
*********************************
10/17/06 Tom Dolsky     - Repaired incorrect URL in upload popup for playing files
10/17/06 Tom Dolsky     - Can now select file with spaces - uploading replaces spaces with underscores so I'm not sure when this is needed
10/18/06 Tom Dolsky     - Fields now properly escaped for Database unfriendly characters
10/17/06 Tom Dolsky     - Added ability to Sort XML enclosures - iTunes still sorts media files by creation date 
10/18/06 Tom Dolsky     - Teacher now has ability to view file when podcast is invisible.
10/18/06 Tom Dolsky     - Fixed comments not properly being displayed  
10/18/06 Tom Dolsky     - Added text to reflect Moodle 1.6 language file 
10/17/06 Tom Dolsky     - Added help files from Stephen Bourget
 *
*********************************
*****v.29
*********************************
07/07/06 Tom Dolsky     - Directories now function properly
07/13/06 Tom Dolsky     - Repaired delete confirmation
07/07/06 Tom Dolsky     - Made file.php media extension independant
07/07/06 Tom Dolsky     - Moved some functions to filelib.php
07/07/06 Tom Dolsky     - Added more specific error messages
07/07/06 Tom Dolsky     - Added file checking with appropriate error
07/07/06 Tom Dolsky     - Tested and added this copyright and info
07/07/06 Tom Dolsky     - Added a key hash encoded with an authorization key
07/07/06 Tom Dolsky     - Added initial Darwin Support

*********************************
*****v.28
*********************************

*********************************
*****v.27
*********************************

03/24/06 Tom Dolsky     - Podcast delete now deletes matching views and comments
03/24/06 Tom Dolsky     - Fixed course delete bug.  Now course delete also deletes podcasts
03/24/06 Tom Dolsky     - backuplib.php First implementation and testing
03/24/06 Tom Dolsky     - restorelib.php First implementation and testing

*********************************
*****v.26
*********************************
02/28/06 Tom Dolsky     - Added m4a and m4b file types
02/28/06 Tom Dolsky     - Moved remainder functions out of lib/rsslib.php dependancy

*********************************
*****v.25
*********************************
02/19/06 Tom Dolsky     - Fixed quotes causing error on module settings page.
File wasn't properly included in .24

*********************************
*****v.24
*********************************
02/19/06 Tom Dolsky     - Repaired make directory function
02/19/06 Tom Dolsky     - Added embedded media player
02/19/06 Tom Dolsky     - Added usemediafilter module setting.
02/19/06 Tom Dolsky     - Fixed quotes causing error on module settings page.
02/19/06 Tom Dolsky     - Added media filter settings text.
02/19/06 Tom Dolsky     - Moved all needed rsslib functions to this file ipodcast should now work without replacing lib/rsslib.php

*********************************
*****v.23
*********************************
02/07/06 Tom Dolsky     - Ran the feed through feed validator and made several necassary changes
02/07/06 Tom Dolsky     - Keywords now separated by commas - it will replace all the spaces with commas on upgrade
			  Also make sure to copy the new language file over it has the space comma verbage changed
02/06/06 Tom Dolsky     - Added a subscribe link for a on click itunes subscribe

*********************************
*****v.22
*********************************
02/05/06 Tom Dolsky     - Fixed Edit button from course view causing error


*********************************
*****v.21
*********************************

Darren Smith's Database fix hopefully
Blank upload popup fix
delete podcast if its an external reference would fail - fixed now

*********************************
*****v.20
*********************************
uploadpopup now refreshes parent window
fixed duration tag not being included in rss feed
backuplib.php now backs up podcasts,podcast course settings, podcast views, and podcast comments
deleting podcast deletes the media file
The attachment edit box can have an external url typed in it.  only links that start with "http://" or "https://" currently, and the length field will be empty

*********************************
*****v.19
*********************************
fixed the deleting and reinstalling module bug 


*********************************
****KNOWN BUGS***
*********************************
escaped text is listed with slashes throughout moodle
uploading a file with apostrophies truncates filename

*********************************
****TODO***
*********************************
Finish Darwin Streaming Server Support
rewrite mp3lib.php to support id3v2.x with pictures
Add hours to calculate_length() in lib/mp3lib.php
Write id3tag information into mp3
write mpeg4lib.php for video podcasting need to use ffmpeg for this
need to have external url support - started needs a bunch of cleanup 
Finish help buttons and help text
convert setup course to tabbed layout
Finish Postrgres support
PDF file support
it

*********************************

Please report any bugs or feature requests to tomd@cytekmedia.com

