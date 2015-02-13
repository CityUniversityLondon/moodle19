-----------------------------
Respondus QTI Importer 1.4.4
-----------------------------

All programmers stand on the shoulders of giants...
  
Updated by Respondus, Inc. Oct2008
Updated by Respondus, Inc. Mar2008
Updated by Respondus, Inc. Feb2008
Updated by Respondus, Inc. Oct2007
Developed by Respondus, Inc. Sep2007

based on work by:   Greg Mushial (gmushial@gmdr.com) May2007
based on work by Elijah Atkinson based on pre-existing
Blackboard importer, etc.

Supported version(s) of Moodle: 1.5 - 1.9

Supported version(s) of Respondus: 3.5+
(3.5.4a+ required for import of ampersands and angle brackets in plain-text fields)

- Imports export packages from the "IMS QTI 1.1+" personality in Respondus.
- File format must be "QTI XML zip file using IMS Content Packaging 1.1.3".
- Points preferences must be "Points as decimal numbers".
- Any existing version of the tool should be uninstalled prior to installing a different version.

To import Respondus QTI packages in Moodle:

- In any Moodle course, go to "Questions" and choose the "Import" tab
- Choose the Respondus QTI format
- Select the category that questions will be imported to
- Click "Browse..." and select the Respondus QTI package file to import


----------------------------
Installation/Uninstallation:
----------------------------

To Install (Moodle 1.6 - 1.9):

		* Copy the folder 'respondusqti'
		  into the question/format/ directory.

		* Add the line:

		           $string['respondusqti'] = 'Respondus QTI format';

		  to  "your Moodle install path"/lang/"your default language folder"/quiz.php.

		  BACKUP YOUR QUIZ.PHP FILE FIRST!!!!

			
To Uninstall (Moodle 1.6 - 1.9):

		* Remove the question/format/respondusqti directory and all files it may contain
		* Replace the quiz.php file in
		  "your Moodle install path"/lang/"your default language folder"
		  with your backup of the quiz.php file.


To Install (Moodle 1.5):

		* Copy the folder 'respondusqti'
		  into the mod/quiz/format/ directory.

		* Add the line:

		           $string['respondusqti'] = 'Respondus QTI format';

		  to  "your Moodle install path"/lang/"your default language folder"/quiz.php.

		  BACKUP YOUR QUIZ.PHP FILE FIRST!!!!

		* Add the line:

			   $CFG->allowobjectembed = true;

		  to "your Moodle install path"/config.php.

		  BACKUP YOUR CONFIG.PHP FILE FIRST!!!!

			
To Uninstall (Moodle 1.5):

		* Same as uninstalling with Moodle 1.6 - 1.9.	


-----------------------
Version information:
-----------------------

v 1.4.4 [Changes by Respondus Oct2008]

  * fixed a bug that was preventing the import of True-False questions in some Moodle versions

v 1.4.3 [Changes by Respondus Mar2008]

  * modified import process for images and a/v files to support Moodle 1.9

v 1.4.2 [Changes by Respondus Feb2008]

  * fixed a bug in importing Multiple Choice questions containing image-only answers
    (thanks to Barry Bookout for finding the problem and suggesting the solution!)

v 1.4.1 [Changes by Respondus Oct2007]

  * fixed a bug that was preventing the import of True-False questions in some Moodle versions
  * fixed a bug in the parsing of correct answers for Matching questions
  * fixed a bug in the handling of unused right-column items in matching questions
  * fixed import of plain-text fields to handle accented characters
  * added default handling for most image and a/v file formats

v 1.4 [Changes by Respondus Sep2007]

  * fixed import of feedback for all question types
  * fixed import of points for all question types
  * fixed import of answer weights for Multiple Response questions
  * fixed answer import bugs with Multiple Choice, True-False, Fill-in-the-Blank, and Algorithmic questions
  * fixed import of ampersands and angle brackets in plain-text fields (also requires QTI output from Respondus 3.5.4a or later)
  * added support for Matching questions with more right-column items than left-column items
  * set shuffling of answers to false for Multiple Choice, Multiple Response, and Algorithmic questions, and true for Matching questions
  * added display notice for missing point values
  * added display notice that Respondus QTI output does not include point values for Essay questions
  * added display notice about MR answer weights being limited to available scoring options in Moodle
  * added display notices for Matching questions dropped because they include less than 3 matching pairs as required by Moodle
  * added display notice about Matching right-column items being imported without formatting, inline images, etc. as required by Moodle
  * changed tool name to Respondus QTI Importer to avoid confusion with other QTI flavors

v 1.3.1 [Changes by Elijah Atkinson (elijahatkinson@gmail.com) August2007]
  
  * Fixed true / false import problem in Moodle 1.8.
  * Fixed errors with embedded .swf files in algorithmic questions.

v 1.3 
                * fixed problem of image file collisions by uploading images into
                  separate folders per question bank/import.
             
                * fixed x^4 performance problem where question content was being uploaded
                  into the DB x^4 times (where x is the number of question + answers in a
                  question bank, ie, why users of v1.2 had to break question banks into
                  small groups.

                * fixed 


-----------------------
Known Issues:
-----------------------

None currently.

Respondus will work with Elijah and Greg to maintain this script going forward.

