<?php

///
///Written by Elijah Atkinson <elijahatkinson@gmail.com> 7/25/06,
//    based on \moodle\questioformat\blackboard\format.php
//  re-written so as to work, by greg mushial gmushial@gmdr.com 7may2007
//                                                             10Jun2007
//  re-write using Delphi for PHP
//
//////////////////////////////////////////
//  Updated by Respondus, Inc. Oct2008
//  Updated by Respondus, Inc. Mar2008
//  Updated by Respondus, Inc. Feb2008
//  Updated by Respondus, Inc. Sep2007
//  Updated by Respondus, Inc. Oct2007
//////////////////////////////////////////
//
//Script version: 1.4.4
//Moodle version: 1.5 - 1.9
//Respondus version: 3.5+
//PHP version: 4.x, 5.x
//

//////////////////////////////////////////////////////////////////////
///RESPONDUS QTI FORMAT IMPORTER
///
/////////////////////////////////////////////////////////
/// Based on format.php, located in the quiz/question directory.
/////////////////////////////////////////////////////////
///
///This moodle class contains all the functions necessary to import
///QTI .xml files from Respondus to the moodle quiz module.
///
////////////////////////////////////////////////////////
///              Basic DESCRIPTION                   ///
////////////////////////////////////////////////////////
///
///looks at .zip file:
///     *NOTE: to have this class work properly, files
///                submitted for upload must be in the .zip
///                format.
///     Unzip it:
///                     Ignore files named "imsmanifest"
///          process other files:
///                             respondus qti .xml files
///                                     Processing entails:
///                                             1) The file is read and converted into an array of lines
///                                                with each index as a line in the file.
///                                             2) The array of lines is converted into an array of question strings
///                                                             (Question strings begin with the tag containing item and end
///                                                              with the </item> tag.)
///                                             3) The array of question strings is processed:
///                                                             a) The questiontype of each index is determined
///                                                             b) The question string is then converted into an
///                                                                appropriate Moodle question object.
///                                                             c) The Moodle question object is appended to an array
///                                                                of question objects.
///                                             4) The array of question objects is returned to the calling function.
///                             images: JPG, GIF, BMP files.
///                                     (More extentions could be added.)
///                                             Images are inserted into the appropriate question field.
///                                                     This depends on how they were inserted into the Respondus question
///                                                     editing environment.
///              .swf files
///                                             Swf files are embedded into questions of the Algorithmic questiontype.
///                                             The Algorithmic questiontype exported from Respondus is essentially a
///                                             modified Multichoice questiontype,  therefore,  All algorithmic questions
///                                             found in the Respondus QTI .xml file are imported as mulitple choice questions
///                                             with the embedded .swf file for its questiontext.
///
///////////////////////////////////////////////////////
///Imports the following question types from Respondus
/////////////////////////////////////////////////////////
///Multiple choice
///
///True/false
///
///Essay
///
///Matching
///
///Fill in the blank
///
///Multiple response
///
///Algorithmic
///
//////////////////////////////////////////////////////////////////////

require_once($CFG->dirroot.'/lib/uploadlib.php');

//create a wrapper class depending on which version of moodle is being used.
if($CFG->version < 2006050501) {	//<< If using anything before moodle 1.6
   	include_once($CFG->dirroot.'/mod/quiz/format/respondusqti/quizDefaultFormatWrapper.php');
}
else {	//<< If using moodle 1.6+
  	include_once($CFG->dirroot.'/question/format/respondusqti/qformatDefaultWrapper.php');
} 


//
//  functions and variables for use within the class, ie, previously defined, hence usable
//

//  if htmlspecialchars_decode as a function isn't already defined, then define one
if (!function_exists('htmlspecialchars_decode')) {
 function htmlspecialchars_decode($str, $quote_style = ENT_COMPAT) {
   return strtr(   $str, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style))   );
 }
}


//====================================
//Variables used by class methods
//====================================
//  swf img and audio names set by ReadData - basically a list of such files found in the zip subdir
$swfNames =                         array();                                   //  used to stow short fn of .swf's found in distrib zip subdir
$imgNames =                         array();                                   //  names of images found in distrib zip subdir
$audioNames =                       array();                                   //  names of audio files found in distrib zip subdir
$otherNames =                       array();                                   //  names of other files found in distrib zip subdir

//
$temp_dir =                         "";                                        //  stores the temp directory path.
$zip_dir=                           "";                                        //  stores the directory of the .zip contents.

$crAssessmentSafeDir =              "respondusqti_import";                     //  used to store current assessment title translated
                                                                               //  into a filesystem safe subdir name         //  <<<< sep dir fix   gjm
                                                                               //  use elijah's subdir as dflt if we don't find
                                                                               //  an assessment title w.i the ims


//   -----------------------------------------------------------------------------------------------------------------------------------
//
//
//   B E G I N      C L A S S
//
//
//Declare the respondusqti importer class. This will be re-wrapped.
//
class respondus_qti_class extends wrapper {

	var $points_notice_displayed; // flag for displaying missing points notice

  //======================================================
  //Class Functions
  //======================================================

  //class qformat_default reqires this to be overloaded.
  //provides import functionality
  function provide_import() {
    return true;
  }

  //========================================================
  //Directory Management Functions
  //**Taken from ../question/blackbord_6/format.php**
  //========================================================

  //Function to check and create the needed dir to unzip file to
  function check_and_create_import_dir($unique_code) {
    global $CFG;
    $succ =                              $this->check_dir_exists($CFG->dataroot."/temp", true);
    if ($succ) {$succ =                  $this->check_dir_exists($CFG->dataroot."/temp/respondusqtiquiz_import", true);}
    if ($succ) {$succ =                  $this->check_dir_exists($CFG->dataroot."/temp/respondusqtiquiz_import/".$unique_code, true);}
    return $succ;
  }

  //Function to check if a directory exists and, optionally, create it
  function check_dir_exists($dir,$create=false) {
    global $CFG;
    $status =                            true;
    if(!is_dir($dir))
        {
        if (!$create)
            {$status =                   false;}
        else
            {umask(0000);
             $status =                   mkdir ($dir,$CFG->directorypermissions);
            }
    }
    return $status;
  }

  //Copies a file to the appropriate course in the moodledata folder.
  function copy_file_to_course($filename) {
    global $CFG;
    global $course; // prior to Moodle 1.9?

	if($CFG->version >= 2007101509) {      //<< If using moodle 1.9 or later
		$course_id = $this->course->id;
	}
	else {
		$course_id = $course->id;
	}

    $filename =                          str_replace('\\','/',$filename);
    $fullpath =                          $this->zip_dir.'/'.$filename;
    $basename =                          basename($filename);

    global $crAssessmentSafeDir;                                                // <<<<<<<<<<<<  sep dirs fix gjm

    $copy_to =                           $CFG->dataroot.'/'.$course_id.'/'.$crAssessmentSafeDir;         // <<<<<<<<<<<<

    if ($this->check_dir_exists($copy_to,true))
        {
        if(is_readable($fullpath))
            {$copy_to.= '/'.$basename;
             if (!copy($fullpath, $copy_to)) {return false;} else {return $copy_to;}
            }
        }
    else
        {return false;}
  }

  //Function to delete dirs/files in temp directory
  //older than $delete_from (measured in seconds)
  function delete_old_temp_dirs($delete_from) {
          global $CFG;

          $status = true;

          //Get files and directories in the respondusqtiquiz_import dir witout descend
          $list = get_directory_list($CFG->dataroot."/temp/respondusqtiquiz_import", "", false, true, true);

          foreach ($list as $file) {
                  $file_path = $CFG->dataroot."/temp/respondusqtiquiz_import/".$file;
                  $moddate = filemtime($file_path);
                  if ($status && $moddate < $delete_from) {
                          fulldelete($file_path);
                  }
          }

          return $status;
  }

  //==================================================================
  //End of Directory Functions
  //==================================================================

  // Returns complete file as an array, one item per line
  // with all html characters converted into srting equivilents by
  // htmlspecialchars($string);
  //
  //*NOTE: For this script to work properly,
  //               all files should be exported from Respondus in .zip format.
  function readdata($filename) {
          global $CFG;

          //Set up the temp directory, and the zipcontents directory.
          //All information from the respondusqti quiz import process is stored in
          //
          //  ../../moodledata/temp/respondusqtiquiz_import
          //
          //This directory is cleared of temporary data at the end of this script.
          //
          //Files associated with each upload attempt are placed in their own unique dir.
          //In each unique directory, there is a zipcontents folder, that stores the results
          //of unzipping the uploaded file.
          $unique_code = time();
          $temp_dir = $CFG->dataroot."/temp/respondusqtiquiz_import/".$unique_code;
          $zip_dir = $temp_dir."/zipcontents";
          $this->zip_dir = $zip_dir;              //<< store the zip dir so the whole class can use it.
          $this->temp_dir = $temp_dir;    //<< store the temp dir so the whole class can use it.

          //Delete all old content in the respondusqtiquiz_import directory.
          //              This will be any content not accessed in the last hour since this script ran.
          //              This will only delete the unique directorys that are created when a non .zip
          //              file is uploaded (as normal fulldelete($this->temp_dir) functions do not work
          //              after unzip_file has failed to unzip a non .zip file).
          $hour = 1;
          $seconds = $hour * 60 * 60;
          $delete_from = time() - $seconds;
          $status = $this->delete_old_temp_dirs($delete_from);

          if ($this->check_and_create_import_dir($unique_code)) { //<< create the import dir, or check to see if it is already created.
                  if (is_readable($filename)) {
                          //The file is readable, so process it.

                           //make the destination folder if it does not already exist.
                           check_dir_exists("$temp_dir/zipcontents", true);

                           //Copy the file with a .zip extention so it can be unzipped.
                           if (!copy($filename, "$temp_dir/respondusqti.zip")) {

                                  //clear temp dir.
                                  fulldelete($this->temp_dir);

                                  error("Could not copy backup file");
                                  return false;           //<< terminate script if a backup cannot be created.

                           }

                           //Unzip the file $temp_dir/respondusqti.zip to the path: $temp_dir/zipcontents.
						   $unzipResults = unzip_file("$temp_dir/respondusqti.zip", "$temp_dir/zipcontents", false);

                           //unzip the file and check
                           //if unzipping the was successfull.
                           if($unzipResults) {

                                  //open the zipcontents directory.
                                  $dh = opendir($zip_dir);

                                  //get the first file from the directory
                                  $currentFile = readdir($dh);

                                  //loop through all the files in the directory
                                  while($currentFile != false) {

                                          //get the extention of the file.
                                          $fileExt = substr($currentFile, -3);

                                          //if $currentFile == "imsmanifest.xml", skip it.
                                          if($currentFile != "imsmanifest.xml") {

                                                  if($fileExt == "xml") {

                                                          //process the respondus qti .xml file.
                                                          $fp = fopen("$zip_dir/$currentFile", "r");

                                                            //Build an array of lines from the file:
                                                            //    Each index of $filearray contains
                                                            //    a line from the file.
                                                            while(!feof($fp))
                                                            {
                                                                  $buff = fgets($fp);     //<< Get a line from the file.
                                                                  $buff = htmlspecialchars($buff);
                                                                             //<< Convert HTML chars <, >, ", ', and & to string equivilents.
                                                                  $filearray[] = $buff;   //<< Add the converted line to the array.
                                                            }

                                                          fclose($fp);

                                                  }//end respondus qti .xml file processing.

                                                  else {

                                                          if($fileExt == "swf") {
                                                                  //Store the name of the swf file in the class array for .swf files.
                                                                  $this->swfNames[] = $currentFile;
                                                          }
                                                          else {
                                                                  //more file extentions may have to be added here.  This depends on
                                                                  //how many filetypes Respondus allows.
                                                                  if( $fileExt == "jpg" ||
																      $fileExt == "gif" ||
																      $fileExt == "png" ||
																	  $fileExt == "bmp" ){

                                                                          //This file must be an image.
                                                                          //Therefore, store the name of the img file in the global array for
                                                                          //img files.
                                                                          //(These names will be used later on to move the image
                                                                          // to the correct direcory).
                                                                          $this->imgNames[] = $currentFile;
                                                                  }
                                                                  else {
                                                                          //More audio extentions may be added here.
                                                                          //These are the most common ones however.
                                                                          if( $fileExt == ".au" ||
                                                                              $fileExt == "aac" ||
                                                                              $fileExt == "mp3" ||
                                                                              $fileExt == "mp2" ||
                                                                              $fileExt == "mp1" ||
                                                                              $fileExt == "wav" ||
                                                                              $fileExt == "aif" ||
                                                                              $fileExt == "iff" ||
                                                                              $fileExt == "mov" ||
                                                                              $fileExt == "avi" ||
                                                                              $fileExt == "wmv" ){

                                                                                  //Files containing any of these extentions are audio files.
                                                                                  //Add them to the list of audio files found in the .zip folder.
                                                                                  //These names are used in the same way image, and swf names are used.
                                                                                  $this->audioNames[] = $currentFile;
                                                                          }
																		  else {
																		          //Files containing any other extension
																				  $this->otherNames[] = $currentFile;
																		  }

                                                                  }

                                                          }

                                                  }

                                          }

                                          $currentFile = readdir($dh); //get the next file

                                  }//end loop through the directory.

                                  closedir($dh);

                           }//end unzip file block

                           else {                                  //<< couldn't unzip the file.

                                  //**NOTE: currently this block never executes because if any file besides a .zip is passed to
                                  //                unzip_file(), the script is terminated during that function call.
                                  //                The error message generated by unzip_file() is cryptic, but I have not found a way
                                  //                to circumvent it yet.

								  // more info: library function unzip_file() calls exit() when an error occurs, but not before
								  //            completing the output page (error message, continue button, page footer, etc.),
								  //            so even registering a shutdown function wouldn't allow us to display our own error
								  //            in the proper page location.
								  
                                  //clear temp dir
                                  fulldelete($this->temp_dir);

                                  print "filename: $filename<br />tempdir: $temp_dir <br>";
                                  error("Could not unzip file.<br>File may be corrupt or a non .zip file");
                                  return false;
                           }

                          //return the array containing the file.
                          return $filearray;

                  }//end readability check.

                  else {  //<< The file is unreadable.

                          //clear temp dir
                          fulldelete($this->temp_dir);

                          print "filename: $filename<br />tempdir: $temp_dir <br />";
                          error("File is Unreadable.");
                          return false; //file could not be read
                  }

          }//end of make/check temp dir

          else {  //<< Could not make a new temp direcotry.

                  //clear temp dir
                  fulldelete($this->temp_dir);

                  return false; //could not make new temp directory.
          }

  }//end function.



  //
  //           r e a d q u e s t i o n s
  //
  //  Importing qti format questions is a two part process: 1) load the question text
  //  into memory and then, 2) process that text, representing in xml, the questions into
  //  moodle db questions. Part of the processing of each question involves moving a copy
  //  of the necessary multimedia files to the question bank folder.
  //  Input to this function is the entire text of the question bank (read into memory
  //  by "readdata"); output is an array of questions to be added to the db by the caller.
  //
  function readquestions($lines)
  {
    $questionStrings =                 parse_as_question_strings($lines);      //  repackage xml lines into questions - one per (array) line
    $questions =                       array();                                //  make an array to store the resulting question objects into
    set_assessment_safe_subdir($lines);                                        //  generate subdir to stow this set of questions into   gjm seg dir fix

    //  now walk through the $questions array - typing and processing each question
	$this->points_notice_displayed = false;
    $crQlnn =                          0;

    while ($crQlnn < count($questionStrings)) {

      //print "-----------  question begin  lnn = $crQlnn <br>";

      $crQln =                         $questionStrings[$crQlnn];              //  extract next compacted question (in the form of a single line)
      $crQlnType =                     $this->determine_type($crQln);          //  get type of question

      // per type call an appropriate processing routine
      switch ($crQlnType) {
      case "true-false":               $this->process_truefalse($crQln, $questions); break;
      case "multichoice":              $this->process_multichoice($crQln, $questions); break;
      case "multianswer":              $this->process_multianswer($crQln, $questions); break;
      case "essay":                    $this->process_essay($crQln, $questions); break;
      case "fillinblank":              $this->process_fillblank($crQln, $questions); break;
      case "matching":                 $this->process_matchingQuestions($crQln, $questions); break;
      case "algorithmic":              $this->process_algorithmic($crQln, $questions); break;
      }

      $crQlnn++;                                                 //  onto next question/"line"

    }    //  while

    //after all processing is complete, clear out the respondusqtiquiz_import directory.
    fulldelete($this->temp_dir);

    return $questions;

  }  // readquestions



  ///=============================================================================
  ///-------------------------------------------------------------------------
  ///                             PROCESSING FUNCTIONS
  ///-------------------------------------------------------------------------
  ///Processing functions take an array of questions to be processed,
  ///and then process each question that falls into the particular function's
  ///question type.
  ///
  ///Processing functions fill the by-reference $question object with appropriate
  ///parameters from the Respondus QTI file.
  ///
  ///The question objects are then appended to an array of questions, which is
  ///then used by the calling function.
  ///=============================================================================

  ///---------------------------------------------------------------
  ///  processes passed multiple choice question
  ///---------------------------------------------------------------
  function process_multichoice($questionString, &$questions) {
    global $CFG;

    $question =                        $this->defaultquestion();               //  buy a question object into which we build the question
    $question->qtype =                 MULTICHOICE;
    $question->single =                1;                                      // Only one answer is allowed

	// Respondus does not specify, so default to preserving answer order
	if (isset($question->shuffleanswers)) {
		$question->shuffleanswers = 0;
	}

    $question->name =                  get_question_name($questionString);     //  fill question name
    $question->questiontext =          $this->get_question_text($questionString);    //  fill question text

    //get an array of the choice IDs from the file.
    //index 0 is the first choice, 1 is the second, etc.
    $ids =                             get_choice_ids($questionString);

    //get an array of choices
    //index 0 is the first choice, index 1 is the second,
    //etc.
    //NOTE: this array corresponds with the $ids array,
    //            i.e. the choice at index 0 of $choices has
    //                     the same ID as the index 0 of $ids.
    $choices =                         $this->get_choices($questionString);

    //Add the answer choices to the object
    for ($i = 0; $i < sizeof($choices); $i++) {$question->answer[$i] = $choices[$i];}

//    for ($i = 0; $i < sizeof($choices); $i++) {    print "choice " . $i . " = " . $choices[$i] . " <br>" ;    }

    //get the id of the correct answer.
    $correct_id =                      get_correct_id($questionString, $ids);

    //get the index of $ids that $correct_id occurs at
    $correct_index =                   get_correct_index($correct_id, $ids);

	// get points
	$correct_points = get_correct_points($questionString);
	if ($correct_points != false) {
		$question->defaultgrade = round($correct_points);
	}
	else { // no points found
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

    //get an array of the feedback
    $feedbackArray =                   $this->get_feedback($questionString, true);

    //if feedback is disabled:
    if($feedbackArray == false) {
            //set feedback for each choice to a blank string.
            for ($i = 0; $i < sizeof($choices)+1; $i++) {$feedbackArray[$i] = "";}
    }

	// general feedback
	$feedbackgeneral = $feedbackArray[sizeof($choices)];
	if (isset($question->generalfeedback) && !empty($feedbackgeneral)) {
		$question->generalfeedback = $feedbackgeneral;
	}

    //Loop through the answer ids to find the correct answer,
    //assign feedback to it, as well as the proper score,
    //and to assign proper feedback to the incorrect answers.
    for ($i = 0; $i < sizeof($ids); $i++) {

            //if the id in the correct index matches the $correct_id.
            if($ids[$i] == $correct_id) { //this is the correct answer

                    //Set the correct answer.
                    $question->fraction[$i] = 1;

                    //Set the feedback to feedback corresponding with the current id.
                    $question->feedback[$i] = $feedbackArray[$i]; //<< Sets feedback when correct.
            }
            else {        //<< This is an incorrect answer

                    //Set the incorrect answer
                    $question->fraction[$i] = 0;

                    //Set the feedback to feedback corresponding with the current id.
                    $question->feedback[$i] = $feedbackArray[$i]; //<< Sets feedback when incorrect.
            }

			// add general feedback to choice feedback if appropriate
			if (!isset($question->generalfeedback) && !empty($feedbackgeneral)) {
				if (!empty($question->feedback[$i])) {
					$question->feedback[$i] .= "<p>";
				}
				$question->feedback[$i] .= $feedbackgeneral;
			}
    }

    //Add the question object to the array.
    $questions[] = $question;

  }//end function



  ///---------------------------------------------------------------
  ///  Processes passed true-false question - return a question object
  ///---------------------------------------------------------------
  function process_truefalse($questionString, &$questions) {
    global $CFG;

    //  make a new object to hold question information
    $question =                        $this->defaultquestion();

    //  start stuffing question object
    $question->qtype =                 TRUEFALSE;
    $question->single =                1;                                      //  Only one answer is allowed
    $question->name =                  get_question_name($questionString);     //  add question name to question object
    $question->questiontext =          $this->get_question_text($questionString);    //  add in text of question
    $ids =                             get_choice_ids($questionString);        //  have built array of IDs of choices for this q - choice 1 is ndx 0, 2 ndx 1
    $choices =                         $this->get_choices($questionString);    //  have buit matching array of text/content of choices, again: choice 1 = ndx 0
    $correct_id =                      get_correct_id($questionString, $ids);  //  get ID of correct answer for this question
    $correct_index =                   get_correct_index($correct_id, $ids);   //  find ndx of that ID in choices list
	$incorrect_index = 1 - $correct_index;

	// get points
	$correct_points = get_correct_points($questionString);
	if ($correct_points != false) {
		$question->defaultgrade = round($correct_points);
	}
	else { // no points found
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

    //  process feedback for question
    $feedbackArray =                   $this->get_feedback($questionString, true);   //  have built array containing feedback items for question
    
	//if feedback is disabled:
    if($feedbackArray == false) {
            //set feedback for each choice to a blank string.
            for ($i = 0; $i < sizeof($choices)+1; $i++) {$feedbackArray[$i] = "";}
    }

	// general feedback
	$feedbackgeneral = $feedbackArray[sizeof($choices)];
	if (isset($question->generalfeedback) && !empty($feedbackgeneral)) {
		$question->generalfeedback = $feedbackgeneral;
	}

    //this is the correct answer
    if($choices[$correct_index] === "True")       {        //true is correct
            
            $question->answer = 1;
            
            if($CFG->version >= 2007021503) {      //<< If using moodle 1.8 or later
                $question->correctanswer = 1;
            }
            
			//apply feedback
			$question->feedbacktrue = $feedbackArray[$correct_index];
			$question->feedbackfalse = $feedbackArray[$incorrect_index];
    }
    else if($choices[$correct_index] === "False") {       //false is correct

            $question->answer = 0;
            
            if($CFG->version >= 2007021503) {      //<< If using moodle 1.8 or later
                $question->correctanswer = 0;
            }

			//apply feedback
			$question->feedbacktrue = $feedbackArray[$incorrect_index];
			$question->feedbackfalse = $feedbackArray[$correct_index];
    }

	// add general feedback to choice feedback if appropriate
	if (!isset($question->generalfeedback) && !empty($feedbackgeneral)) {
		if (!empty($question->feedbacktrue)) {
			$question->feedbacktrue .= "<p>";
		}
		if (!empty($question->feedbackfalse)) {
			$question->feedbackfalse .= "<p>";
		}
		$question->feedbacktrue .= $feedbackgeneral;
		$question->feedbackfalse .= $feedbackgeneral;
	}

     //add the question object to the array of questions.
     $questions[] = $question;

  }//end function



  ///-----------------------------------------------------------------
  ///  Processes passed multiple answer question
  ///-----------------------------------------------------------------
  function process_multianswer($questionString, &$questions) {

    //This $questionString contains a multiple choice question

    global $CFG;
    static $noticedisplayed = false;             //<<marks if the multianswer weight notice has been displayed.

    //make a new object to hold question information
    if($CFG->version < 2006050501) {      //<< If using anything before moodle 1.6
            $question = NULL;
    }
    else {        //<< If using moodle 1.6 or up.
            $question = $this->defaultquestion();
    }

    //get question name from the file
    $question->name = get_question_name($questionString);
	
	// Respondus does not specify, so default to preserving answer order
	if (isset($question->shuffleanswers)) {
		$question->shuffleanswers = 0;
	}

    $correctanswercount = 0;
    $incorrectanswercount = 0;
    $allornone = false;

    //get an array of choices
    //index 0 is the first choice, index 1 is the second,
    //etc.
    //NOTE: this array corresponds with the $answerScores array,
    //            i.e. the choice at index 0 of $choices has
    //                     the same ID as the index 0 of $ids.
    $choices = $this->get_choices($questionString);

    //get an array of the scores associated with the choices in $choices.
    $answerScores = get_answer_scores($questionString);

    $question->qtype = MULTICHOICE;
    $question->single = 0;        // Multiple answers are allowed.
    $question->image = "";  // No images are allowed with this format.
    $maxValue =   $this->get_maxvalue($questionString);

	// get points
	if (strpos($maxValue, ".") !== false) {
		$points_as_decimal = true;
		$question->defaultgrade = round($maxValue);
	}
	else { // no points found
		$points_as_percent = true;
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

	if (!$noticedisplayed) {
		echo "<br><p><center><b>Multi-response Notice:</b><br><br>
		  All multi-response answer values are limited to the scoring options
		  available in Moodle. As a result, some values may be converted to
		  percentages and/or re-weighted to the nearest available option.
		  </center><br><hr>";
		$noticedisplayed = true;
	}

    //get an array of the feedback
    $feedbackArray = $this->get_feedback($questionString, true);

    //get the body of the question from the file
    $question->questiontext = $this->get_question_text($questionString);

    //add the answer choices to the object
    for ($i = 0; $i < sizeof($choices); $i++) {
            $question->answer[$i] = $choices[$i];
    }

    //count the number of correct and incorrect answers
    for ($i = 0; $i < sizeof($answerScores); $i++) {
            if($answerScores[$i] > 0) {
                    $correctanswercount++;
            }
            else if($answerScores[$i] < 0) {
                    $incorrectanswercount++;
            }
    }

    //check to see if the multianswer is all or none type;
    for ($i = 0; $i < sizeof($answerScores) && !$allornone; $i++) {
            if($answerScores[$i] == -$maxValue) {
                    $allornone = true;
            }
    }

    //if feedback is disabled:
    if($feedbackArray == false) {
            //set feedback for each choice to a blank string.
            for ($i = 0; $i < sizeof($choices)+1; $i++) {
                    $feedbackArray[$i] = "";
            }
    }

	// general feedback
	$feedbackgeneral = $feedbackArray[sizeof($choices)];
	if (isset($question->generalfeedback) && !empty($feedbackgeneral)) {
		$question->generalfeedback = $feedbackgeneral;
	}

    // assign per-answer scores and feedback
    for ($i = 0; $i < sizeof($answerScores); $i++) {

		// answer score
		if ($points_as_percent) {
			$score = $answerScores[$i];
		}
		else { // $points_as_decimal
			$score = ($answerScores[$i] / $maxValue) * 100;
		}
		$question->fraction[$i] = fraction_from_grade_option(
		  nearest_grade_option($score));
        
		// answer choice feedback
		$question->feedback[$i] = $feedbackArray[$i];

		// add general feedback to choice feedback if appropriate
		if (!isset($question->generalfeedback) && !empty($feedbackgeneral)) {
			if (!empty($question->feedback[$i])) {
				$question->feedback[$i] .= "<p>";
			}
			$question->feedback[$i] .= $feedbackgeneral;
		}
    }

    //add the question object to the array.
    $questions[] = $question;

  }//end function



  ///---------------------------------------------------------------
  ///  processes passed essay question
  ///---------------------------------------------------------------
  function process_essay($questionString, &$questions) {
    global $CFG;
    static $noticedisplayed = false;             //<<marks if the essay points notice has been displayed.

    //get question name from the file:
    //    this will be displayed if "ESSAY" is undefined.
    $errorDisplayName = get_question_name($questionString);

    //this comparison is borrowed from  ../blackbord_6/format.php
    if (defined("ESSAY")) {

        //make a new object to hold question information
        if($CFG->version < 2006050501) {  //<< If using anything before moodle 1.6
                $question = NULL;
        }
        else {            //<< If using moodle 1.6 or later.
                $question = $this->defaultquestion();
        }

        $question->qtype = ESSAY;
        $question->usecase = 0;   // Ignore case
        $question->image = "";    // No images with this format

        //the question text is HTML, so set the questiontextformat to reflect this
        $question->questiontextformat = 1;

        //get the bocy text of the question from the file
        $question->questiontext = $this->get_question_text($questionString);

        //get question name from the file
        $question->name = get_question_name($questionString);

		// get points
		$maxValue = $this->get_maxvalue($questionString);
		if (strpos($maxValue, ".") !== false) {
			$question->defaultgrade = round($maxValue);
		}
		else { // no points found
			$question->defaultgrade = 1;
			if (!$noticedisplayed) {
				echo "<br><p><center><b>Essay Notice:</b><br><br>
				  Respondus does not currently export the points
				  associated with Essay questions. Default grade
				  for Essay questions will be set to 1.</center><br><hr>";
				$noticedisplayed = true;
			}
		}

        //essays have no score untill graded by the teacher.
        $question->fraction = 0;

        //set the answer to a blank array that can be filled by the user input.
        $question->answer = array();

        //get the feedback for the question
        $feedbackArray = $this->get_feedback($questionString, false);

        //NOTE: essay questions can only have 1 feedback, so we only need to look
        //      at the first index of $feedbackArray.
        if($feedbackArray == false) {
                $feedbackArray[0] = "";           //only 1 feedback possible, no feedback, therefore set to blank.
        }

        $question->feedback = $feedbackArray[0];  //set feedback.

        //question object passed into a by reference array to store it for callback.
        $questions[]=$question;

    }//end essay defined check

    else {        //<< Essay is not a defined question type in this version of Moodle.
      print "Essay question types are not handled because the quiz question type 'Essay' does not exist in this installation of Moodle<br/>";
      print "Omitted Question: ".$errorDisplayName.'<br/><br/>';
    }

  }//end function



  ///--------------------------------------------------------------------------
  ///  Processes passed fill-in-the-blank question
  ///--------------------------------------------------------------------------
  function process_fillblank($questionString, &$questions) {
    global $CFG;

    //make a new object to hold question information
    if($CFG->version < 2006050501) {      //<< If using anything before moodle 1.6
            $question = NULL;
    }
    else {        //<< If using moodle 1.6 or up.
            $question = $this->defaultquestion();
    }

    $question->name = get_question_name($questionString);
    $question->qtype = SHORTANSWER;
    
	// get points
	$maxValue = $this->get_maxvalue($questionString);
	if (strpos($maxValue, ".") !== false) {
		$question->defaultgrade = round($maxValue);
	}
	else { // no points found
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

    $question->single = 1;
    $question->usecase = 0;
    $question->questiontext = $this->get_question_text($questionString);

    $answers = array();
    $answerScoreArray = array();

    $fractionArray = array();
    $feedbackArray = array();

    //get an array of the correct answers to the question.
    $answers = get_fillblank_Answers($questionString);

    //get an array of scores for the choices
    $answerScoreArray = get_answer_scores($questionString);

    //get feedback options for the question.
    $feedbackArray = $this->get_feedback($questionString, false);

    //Determine if feedback is enabled.
    //NOTE: only one feedback for this questiontype
    if($feedbackArray == false) {
            //no feedback, so make the feedback blank.
            $feedbackArray[0] = "";
    }

    //convert percents to decimals.
    for($i = 0; $i < sizeof($answerScoreArray); $i++) {
            //Cover decimals and values that are higher than 1.
            //Each answer gives a 100% correct to the question, so
            //there is no need to have anything but 1 here.
            if($answerScoreArray[$i] > 1 || $answerScoreArray[$i] < 1) {
                    $answerScoreArray[$i] = 1;
            }
    }

    //set remaining question parameters.
    $question->answer = $answers;
    $question->fraction = $answerScoreArray;

	// answer feedback
	for ($i = 0; $i < sizeof($answers); $i++) {
		if (!isset($question->generalfeedback) && !empty($feedbackArray[0])) {
			$question->feedback[$i] = $feedbackArray[0];
		}
		else {
			$question->feedback[$i] = ""; // answer feedback not supported by Respondus
		}
	}

	// general feedback
	if (isset($question->generalfeedback) && !empty($feedbackArray[0])) {
		$question->generalfeedback = $feedbackArray[0];
	}

    //append the question to the question array.
    $questions[] = $question;

  }//end of function.



  //
  //
  //    p r o c e s s _ m a t c h i n g Q u e s t i o n s
  //
  //
  //    replacement routine gjm 15jun07
  function process_matchingQuestions($questionString, &$questions) {
    global                             $CFG;
    static $noticedisplayed = false;             //<<marks if the matching right-column notice has been displayed.

    //  buy a question object into which we can build this matching question
    $question =                        $this->defaultquestion();

    //  init as matching question
    $question->valid =                 true;
    $question->qtype =                 MATCH;
    $question->name =                  get_question_name($questionString);

	// get points
	$maxValue = $this->get_maxvalue($questionString);
	if (strpos($maxValue, ".") !== false) {
		$question->defaultgrade = round($maxValue);
	}
	else { // no points found
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

	$question->shuffleanswers = 1;
    $question->image =                 '';                                     //  we don't have a url to an image (yet) - make null.
    $question->questiontext =          $this->get_question_text($questionString);

	if (!$noticedisplayed) {
		echo "<br><p><center><b>Matching Notice:</b><br><br>
		  Moodle restricts all matching answer (right column) items to text
		  only, so any formatting, images, etc. for those items will not be
		  imported.</center><br><hr>";
		$noticedisplayed = true;
	}

    //get information about the question subquestions
    $subquestionArray =                $this->get_matching_subquestions($questionString);
    $subquestionIds =                  get_matching_subquestion_ids($questionString);

    //get information about the question subanswers.
    $subanswerArray =                  $this->get_matching_subanswers($questionString);
    $subanswerIds =                    get_matching_subanswer_ids($questionString);

	if (count($subquestionArray) < 3 || count($subanswerArray) < 3) {
		echo "<br><p><center><b>Matching question \"$question->name\":
		  </b><br><br>This question was not imported because it did not
		  include at least 3 matching pairs as Moodle requires.
		  </center><br><hr>";
		return;
	}

    //get the subanswers in order such that, the subanswer @ index 0 is the correct answer to
    //the subquestion @ index 0 of $subquestionArray;
    $orderedSubanswers =               get_matching_correct_subanswers(  $questionString,  $subanswerArray,  $subanswerIds,  $subquestionIds  );

    //Add the subquestionArray to the question object.
    foreach($subquestionArray as $currentSub) {
      $question->subquestions[] =      $currentSub;
    }

	// add empty dummy questions for unused answers
	// if we don't do this, the extra answers won't be imported
	if (count($orderedSubanswers) > count($subquestionArray)) {
		$question->subquestions = array_pad(
		  $question->subquestions, count($orderedSubanswers), "");
	}

    //Add the subanswers to the question object.
    foreach($orderedSubanswers as $answers) {
      $question->subanswers[] =        $answers;
    }
	
	// general feedback
	$feedbackArray = $this->get_feedback($questionString, false);
    if($feedbackArray == false) {
		$feedbackArray[0] = "";
    }
	if (isset($question->generalfeedback) && !empty($feedbackArray[0])) {
		$question->generalfeedback = $feedbackArray[0];
	}

    //append question object to questions array.
    $questions[] =                     $question;

  }//end of function



  ///---------------------------------------------------------------
  ///  processes passed algorithmic question
  ///---------------------------------------------------------------
  function process_algorithmic($questionString, &$questions) {
    global $CFG;

    //make a new object to hold question information
    $question = $this->defaultquestion();

    //The algorithmic questiontype from Respondus is a multiple choice question
    //with an embedded .swf file for the question text.
    //this .swf file takes an input from the user and displays what option to choose.

    $question->qtype = MULTICHOICE;
    $question->single = 1;        //<<Only one answer is allowed

	// Respondus assumes answer order will be preserved
	if (isset($question->shuffleanswers)) {
		$question->shuffleanswers = 0;
	}

    //get question name from the file
    $question->name = get_question_name($questionString);

    //get the body of the question from the file (a swf embed tag will go here).
    $question->questiontext = $this->get_question_text($questionString);

    //get an array of the choice IDs from the file.
    //index 0 is the first choice, 1 is the second, etc.
    $ids = get_choice_ids($questionString);

    //get an array of choices
    //index 0 is the first choice, index 1 is the second,
    //etc.
    //NOTE: this array corresponds with the $ids array,
    //            i.e. the choice at index 0 of $choices has
    //                     the same ID as the index 0 of $ids.
    $choices = $this->get_choices($questionString);

    //add the answer choices to the object
    for ($i = 0; $i < sizeof($choices); $i++) {
            $question->answer[$i] = $choices[$i];
    }

    //get the id of the correct answer.
    $correct_id = get_correct_id($questionString, $ids);

    //get the index of $ids that $correct_id occurs at
    $correct_index = get_correct_index($correct_id, $ids);

	// get points
	$correct_points = get_correct_points($questionString);
	if ($correct_points != false) {
		$question->defaultgrade = round($correct_points);
	}
	else { // no points found
		$question->defaultgrade = 1;
		if (!$this->points_notice_displayed) {
			echo "<br><p><center><b>Missing Points Notice:</b><br><br>
			  No points found for one or more questions (points may have been
			  published as percentages). Default grade will be set to 1 for the
			  affected questions.</center><br><hr>";
			$this->points_notice_displayed = true;
		}
	}

    //Algorithmic question types do not allow include feedback outside of the
	//Flash object. Therefore, set all feedback to blank strings.
    for ($i = 0; $i < sizeof($choices)+1; $i++) {
            $feedbackArray[$i] = "";
    }

	// general feedback
	$feedbackgeneral = $feedbackArray[sizeof($choices)];
	if (isset($question->generalfeedback) && !empty($feedbackgeneral)) {
		$question->generalfeedback = $feedbackgeneral;
	}

    //loop through the answer ids to find the correct answer,
    //assign feedback to it,
    //and to assign proper feedback to the incorrect answers.
    for ($i = 0; $i < sizeof($ids); $i++) {

            //set the feedback, all feedback is blank.
            $question->feedback[$i] = $feedbackArray[$i];

            //if the id in the correct index matches the $correct_id.
            if($ids[$i] == $correct_id) { //<< This is the correct answer
                    //set the correct answer
                    $question->fraction[$i] = 1;
            }
            else {        //<< This is an incorrect answer
                    //set the incorrect answer
                    $question->fraction[$i] = 0;
            }

			// add general feedback to choice feedback if appropriate
			if (!isset($question->generalfeedback) && !empty($feedbackgeneral)) {
				if (!empty($question->feedback[$i])) {
					$question->feedback[$i] .= "<p>";
				}
				$question->feedback[$i] .= $feedbackgeneral;
			}
    }

    //add the question object to the array.
    $questions[] = $question;

  }//end function



  //--------------------------------------------------------
  //Class tools |
  //-------------
  //****************************************
  //** These are tool functions that must **
  //** be part of the class in order to   **
  //** function properly.                 **
  //****************************************
  //--------------------------------------------------------

  //Determines what type of question is contained
  //in $questionString.
  //Returns and appropriate question type identifier string.
  function determine_type($questionString) {
          //-----------------------
          //determine if true-false
          //-----------------------
          //check the question string for '/response_label' string:
          //      if there are more than 2 or less than 2 of these strings, the question cannot be a true false.
          //      If there are 2 of these strings exactly, the question COULD be true false (further testing):
          //              Check if the possible options are ONLY true and false.  (do a non case sensitive compairison).
          //              If this is true, then the question type is True-False.
          //                      return "true-false";
          //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
          //Counts how many times '/response_label' occurs in the $questionString.
          //              '/response_lable' is the end of the XML tag that contains answer data.
          //              there is only 1 '/response_label' tag per answer, so scanning the $questionString
          //              for '/response_label' reveals how many answers are possible for the question in $questionString.
          $choiceCount = substr_count($questionString, '/response_label');

          //Get an array of the possible answer choices for the question.
          $choicearray = $this->get_choices($questionString);

          //------------------------------------------------------------------
          //Check to make sure the answer values are ONLY 'true' and 'false'.
          //------------------------------------------------------------------
          //Get a substring of the $questionString that contains both of the
          //tags containing answers.

          //if there are only 2 choices and if answer 1 is true or false and if answer 2 is true or false
          if($choiceCount == 2 && ($choicearray[0] == "True" || $choicearray[0] == "False" && $choicearray[1] == "True" || $choicearray[1] == "False")) {
                  //The question has a True-False questiontype.
                  return 'true-false';
          }
          else {
                  //--------------------------
                  //Determine if multi-choice
                  //--------------------------
                  //check to see if the '/response_label' srting occurs 2 or more times.
                  //check to see if there is a "Single" string in the question
                  //      (only questions with single answers have this, this exludes all multi answer questions)
                  //Check to see that the question does not contain a "/qmd_itemtype" tag.
                  //      (only matching questions have this)
                  //(i.e. there are 2 or more possible answers)
                  //              if true, check to see the answer values do not contain

                  //Get the block that the number of allowable answers is declaired in.
                  $answerNumberBlock = extractstr($questionString, "<response_lid", "<render_choice>");

                  $singleAnswerCheck = substr_count($answerNumberBlock, "Single");
                  $matchingtagcount = substr_count($questionString, '/qmd_itemtype');

                  //check for occurances of ".swf" in the file.
                  $swfCount = substr_count($questionString, '.swf');

                  if($choiceCount >= 2 && $singleAnswerCheck == 1 && $matchingtagcount < 1 && $swfCount < 1){
                          //This question is a algorithmic questiontype.
                          return 'multichoice';
                  }
                  else{
                          //--------------------------------------------------
                          //Determine if algorithmic with multiple answers
                          //--------------------------------------------------
                          //check to see if the question allows multiple answers
                          $multiAnswerCheck = substr_count($answerNumberBlock, "Multiple");

                          if($choiceCount >= 2 && $multiAnswerCheck == 1 && $matchingtagcount < 1) {
                                  //This question is a multiple answer questiontype.
                                  return 'multianswer';
                          }
                          else {
                                  //----------------------------------------------
                                  //Determine if Essay type
                                  //----------------------------------------------
                                  //Check to see how many times the string "/render_fib" appears in the question.
                                  $render_fibCheck = substr_count($questionString, '/render_fib');

                                  //Check to see how many times the string "/outcomes" appears in the question.
                                  $outcomesCheck = substr_count($questionString, '/outcomes');

                                  if($render_fibCheck == 1 && $outcomesCheck == 0) {
                                          return 'essay';
                                  }
                                  else {
                                          //------------------------------------
                                          //Determine if Fill in the blank type
                                          //------------------------------------
                                          if($render_fibCheck == 1 && $outcomesCheck > 0){
                                                  return 'fillinblank';
                                          }
                                          else {
                                                  //----------------------------------
                                                  //Determine if matching type
                                                  //----------------------------------
                                                  //check the itemtype of the question.
                                                  $itemtype = extractstr($questionString, "<qmd_itemtype>", "</qmd_itemtype>");

                                                  if(strtolower(trim($itemtype)) == "matching") {
                                                          return 'matching';
                                                  }
                                                  else {
                                                          //----------------------------------
                                                          //Determine  if algorithimic  type
                                                          //----------------------------------
                                                          //count the occurances of ".swf" string in the file.
                                                          $swfCount = substr_count($questionString, '.swf');

                                                          if($swfCount > 0) {
                                                                  return 'algorithmic';
                                                          }
                                                          else {
                                                                  //Print some kind of notification here, saying that the question is of no
                                                                  //format that this Respondus QTI importer can recognize.
                                                                  echo "<font color=\"red\">Error: Unfamiliar questiontype</font><hr><br>";
                                                                  return false;
                                                          }

                                                  }

                                          }

                                  }

                          }

                  }

          }


  }  //  function determine_type



  //
  //    g e t _ q u e s t i o n _ t e x t
  //
  // Given a question def in a string, return question text (vs choices; db safe) to caller.
  // note: in searching through question will upload encountered images/flash/sound etc files into course material folder
  //
  function get_question_text($questionString) {
    $textBlock =                       extractstr($questionString, "<material>", "</material>");  //  extract full question part of passed question def
    $text =                            $this->get_option_text($textBlock);                        //  extract textual part of question

    //  if we have question text to work with - make db safe before returning to caller
    return                             $text == false ? $text : addslashes($text) ;               //  fix for cannot insert bug gjm 31may07

  }  // get_question_text



  //given a string containing a question,
  //return an array of choice strings
  //(possible answers).
  //NOTE: the array returned corresponds
  //              to the array returned by get_choice_ids()
  //              provided that both functions are called with the
  //              same $questionString.
  function get_choices($questionString) {

          //get the portion of the questionString that the choices are in
          $choicesBlock = extractstr($questionString, "<render_choice>", "</render_choice>");

          //determines how many choices there are
          $choiceCount = substr_count($choicesBlock, '/response_label');

          //loop through the $choicesBlock and add all the choice strings to the array.
          for($i = 1; $i <= $choiceCount; $i++) {

                  //Get the string containg ith choice from the $choicesBlock.
                  $choiceBlock = extractstr($choicesBlock, "<response_label", "</response_label>", $i, $i);
                  $choiceTextBlock =   extractstr($choiceBlock, "<material>", "</material>");

                  //Get the choice text (will contain images, or swfs, if they are present).
                  $choice = $this->get_option_text($choiceTextBlock);

                  //add the $choice to the array  $choices.
                  $choices[] = addslashes($choice);
          }

          return $choices;

  }//end of function.



  //Assumes a question with a matching questiontype has been passed.
  //gets all of the subquestions for a matching choice question, returns
  //an array of them in the order in which they are listed in the Respondus QTI file.
  function get_matching_subquestions($questionString) {                                                           //<< Test this to make sure images are not in it!!!!!!!!!

          //determines how many subquestions there are.
          $subquestionCount = substr_count($questionString, '/response_lid');

          //get the block that the subquestions occur in.
          $subquestionBlock = extractstr($questionString, '<presentation>', '</presentation>');

          //loop through the $subquestionBlock
          for($i = 1; $i <= $subquestionCount; $i++) {

                  //get the ith set of lines for the ith subquestion.
                  $subquestionSubBlock = extractstr($subquestionBlock, '<response_lid ident=', '</response_lid>', $i, $i);

                  //get the line the subquestion is on.
                  $subquestionLine = extractstr($subquestionSubBlock, "<material>", "</material>");

                  $subquestion = $this->get_option_text($subquestionLine);

                  //append the subquestion to the $subquestions array;
                  $subquestions[] = addslashes($subquestion);
          }

          return $subquestions;
  }



  //Assumes a question with a matching questiontype has been passed.
  //gets all of the subanswer posibilities in the order in which they are
  //listed in the Respondus QTI file.
  //Returns an array containing these subanswers.
  function get_matching_subanswers($questionString) {

          //get the block of the questionString containing all the subanswers.
          $subanswerBlock = extractstr($questionString, '<render_choice', '</render_choice>');

          //count the number of subanswers in the subanswerBlock
          $subanswerCount = substr_count($subanswerBlock, '/response_label');

          //loop through the $subanswerBlock.
          for($i = 1; $i <= $subanswerCount; $i++) {

                  //get the ith line containing the ith subanswer in the $subanswerBlock.
                  $subanswerLine = extractstr($subanswerBlock, "<material>", "</material>", $i, $i);

                  //get all the text for the subanswer.
                  $subanswer = $this->get_option_text($subanswerLine);

                  //append the subanswer to the $subanswers array.
                  $subanswers[] = addslashes($subanswer);
          }

          return $subanswers;
  }



  //returns an array of feedback that shares the same
  //key as the $answerIds array (if $mapChoices is true).
  //Each feedback item corresponds to an answer choice.
  //General feedback is last array element.
  //
  function get_feedback($questionString, $mapChoices) {

          //determines how many feedback entries there are
          $feedbackCount = substr_count($questionString, '/itemfeedback');

          //check if feedback is enabled (i.e $feedbackCount > 0)
          if ($feedbackCount > 0) {

                  //init feedback array
				  $choiceCount = 0;
				  $maxFeedCount = 1;
				  if ($mapChoices) {
				          $choiceCount = substr_count($questionString, '/response_label');
				  }
				  $maxFeedCount = $choiceCount + 1; // for general feedback
				  for ($i = 0; $i < $maxFeedCount; $i++) {
				          $feedbackArray[$i] = "";
				  }

                  
				  //loop to process each feedback item
                  for ($i = 1; $i <= $feedbackCount && $i <= $maxFeedCount; $i++) {

                          $currentFeedbackBlock = extractstr($questionString, '<itemfeedback ident=', '</itemfeedback>', $i, $i);
                          $feedBackData = extractstr($currentFeedbackBlock, "<material>", "</material>");
						  $feedbackId = extractstr($currentFeedbackBlock, "\"", "\"", 1, 2);
						  $feedbackView = extractstr($currentFeedbackBlock, "view=\"", "\">");

                          //Get a string containing the feedback info for the ith feedback item.
                          //This feedback string will contain all appropriate files from the Respondus QTI file being imported.
                          $feedback = $this->get_option_text($feedBackData);

                          //populate the feedback array
                          if ($feedbackView == "All") {
						          // general feedback
						          $feedbackArray[$maxFeedCount-1] = addslashes($feedback);
						  }
						  else if ($mapChoices) {
						          // choice feedback
						          $j = $this->get_feedback_index($questionString, $feedbackId);
						          if ($j !== false) {
								          $feedbackArray[$j] = addslashes($feedback);
								  }
						  }

                  }

                  //return the array of feedback when all feedback has been processed
                  return $feedbackArray;
          }

          //if feedback is not enabled, return false
          else {
                  return false;
          }
  }



  // returns the answer choice index associated with the given feedback id
  // returns false if $feedbackId is not associated with any answer choices
  // returned index is zero-based, so use === or !== to test return value
  function get_feedback_index($questionString, $feedbackId) {

        $choiceCount = substr_count($questionString, '/response_label');

		for ($i = 0; $i < $choiceCount; $i++) {
		        $respcondition = extractstr($questionString, "<respcondition>", "</respcondition>", $i+1, $i+1);
				if (strpos($respcondition, $feedbackId) !== false) {
				        return $i;
				}
		}
		
		return false;
  }




  //
  //
  //      g e t _ o p t i o n _ t e x t
  //
  //
  //  called with material btwn xml <material and </material> tags,
  //  returns dbsafe actual text + html necessary to invoke any image or sound content
  //  will upload swf, sound or image files into course content folders
  // gjm replacment routine 20jun07

  function get_option_text($materialBlock) {

    global                             $CFG;
    global                             $course; // prior to Moodle 1.9?
    global                             $crAssessmentSafeDir;

	if($CFG->version >= 2007101509) {      //<< If using moodle 1.9 or later
		$course_id = $this->course->id;
	}
	else {
		$course_id = $course->id;
	}

    if  (  trim($materialBlock) ==  ""  )    {
      return                           false;
    }
    else    {

      $ltMATtagHSAS =                    htmlspecialchars(addslashes("<mat"));   //  what we need to look for at the beginning of a <material packet
      $ltMATTEXTblankHSAS =              htmlspecialchars(addslashes("<mattext "));    //  full mat text tag
      $ltSlashMATTEXTtagHSAS =           htmlspecialchars(addslashes("</mattext>"));    //  tag at end of mattext packet
      $ltMATIMAGEblankHSAS =             htmlspecialchars(addslashes("<matimage "));    //  tag for image packet
      $ltMATAUDIOblankHSAS =             htmlspecialchars(addslashes("<mataudio "));    //  tag for audio packet
      $spaceSlashGTtagHSAS =             htmlspecialchars(addslashes(" />"));    //  end of packet tag for image and audio packets

      //  walk through the <matxxx packets within $material collecting up resulting text as we go
      $materialBlock =                   " " . $materialBlock;                 //  kludge so that strpos will work

      $outputString =                    "";                                   //  nothing yet
      $ix =                              0;                                    //  start at the beginning..

      while  (  $ix < strlen($materialBlock)  )    {

        //  parse off next packet
        if  ( strlen($materialBlock) - $ix < strlen($ltMATtagHSAS) )    {
          $ix =                          strlen($materialBlock);
        }
        else  {
          $ix =                          strpos($materialBlock, $ltMATtagHSAS, $ix);  //  find us the next <matxxx tag to the right... assume skipped chars to be cr lf etc
          if      ( $ix == false )    {    $ix =    strlen($materialBlock);    }
          elseif  ( $ix < 1 )         {    $ix =    strlen($materialBlock);    }
        }

        //  if we didn't find a <matxxx tag LE...  then flush what's left
        if ( $ix >= strlen($materialBlock) )    {                                                   //  if we didn't find another <mat... tag...
        }
        else    {                                                              //  otherwise... we did find a <mat... tag

          //  if a <mattext packet...
          //                              simply collect text from cdata (though make sure a .swf isn't hiding there)
          $argHSAS =                     substr($materialBlock, $ix, strlen($ltMATTEXTblankHSAS));

          if ( $argHSAS == $ltMATTEXTblankHSAS)    {
            $ixx =                       $ix;                                  //  remember where we found <mattext
            $ix =                        strpos($materialBlock, $ltSlashMATTEXTtagHSAS, $ix);    //  find other end of mattext packet

            if  (  $ix == false  )    {    $ix =    strlen($materialBlock);    }      //  if we didn't find an end, fake one

            $currentTextBlock =          substr($materialBlock, $ixx, $ix - $ixx);    //  make copy of text block
            $currentText =               extractstr($currentTextBlock, "<![CDATA[", "]]>");    //  extract embedded text
            $swfSearchText =             $currentText; // make a copy of currentText for later searching before we de-encode it.
            $currentText =               htmlspecialchars_decode($currentText);  //  de-encode text

            //  funny place to put 'em... but our cdata might actually be a url to a .swf file
            if  (  strpos($swfSearchText, '.swf') != false  )    {

              $swfName =                 extractstr($swfSearchText, "\"", "\"", 1, 2);    //  try for the .swf file name
              $found =                   false;
              $zipSwfNamesNdx =          0;

              while (    (! $found)  &&  ($zipSwfNamesNdx < count($this->swfNames))    )     {
                $crZipSwfName =          $this->swfNames[$zipSwfNamesNdx];
                if ( $swfName == $crZipSwfName )    {    $found =  true;    }    else    {    $zipSwfNamesNdx++;    }

              }    //  while

              //  if the file was a .swf file, part of the original .zip... upload it into course content folder
              if ( $found ) {
                $this->copy_file_to_course($swfName);                          //  copy the image to the course content folder
                $swfPath =               "$CFG->wwwroot/file.php/$course_id/$crAssessmentSafeDir/$swfName";    //  full path to saved swf
                $swfEmbedCode =          "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"625\" height=\"485\"><param name=\"movie\" value=\"$swfPath\"><param name=\"quality\" value=\"high\"><embed src=\"$swfPath\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"625\" height=\"485\"></embed></object>";
                $outputString =          $outputString . "" . $swfEmbedCode;
              }
              else    {
                $outputString =          $outputString . $currentText;         //  add gleaned text to output string
              }

            }

            //  if not a .swf, then append to output text

            else    {
              $outputString =            $outputString . $currentText;         //  add gleaned text to output string
            }

            $ix =                        $ix + strlen($ltSlashMATTEXTtagHSAS);   //  pt past </mattext tag

          }

          //  if not <mattext, how about a <matimage packet...
          //                                                     copy image to courseware and add <img to output text
          else    {

            $argHSAS =                 substr($materialBlock, $ix, strlen($ltMATIMAGEblankHSAS));

            if (  $argHSAS == $ltMATIMAGEblankHSAS )    {
              $ixx =                   $ix;                                    //  remember beginning of packet
              $ix =                    strpos($materialBlock, $spaceSlashGTtagHSAS, $ix);    //  find other end of packet

              if ( $ix <= 0  )    {    $ix =   strlen($materialBlock);    }         //  if we didn't find an end, fake one

              $currentImageBlock =     substr($materialBlock, $ixx, $ix + strlen($spaceSlashGTtagHSAS) - $ixx);    //  extract content of matimage tag
              $imageName =             extractstr($currentImageBlock, "uri=\"", "\" />");    //  extract image name

              //  see if this file image was seen in the just imported .zip
              $found =                 false;
              $zipImageNamesNdx =      0;

              while (    (! $found)  &&  ($zipImageNamesNdx < count($this->imgNames))    )     {
                $crZipImageName =      $this->imgNames[$zipImageNamesNdx];
                if ( $imageName == $crZipImageName )    {    $found =  true;    }    else    {    $zipImageNamesNdx++;    }

              }    //  while

			  if (!$found) {
				$zipOtherNamesNdx =      0;
				while (    (! $found)  &&  ($zipOtherNamesNdx < count($this->otherNames))    )     {
					$crZipOtherName =      $this->otherNames[$zipOtherNamesNdx];
					if ( $imageName == $crZipOtherName )    {    $found =  true;    }    else    {    $zipOtherNamesNdx++;    }
				}    //  while
			  }

              //  if the file was part of the .zip... upload it into course content folder
              if ( $found ) {
                $this->copy_file_to_course($imageName);                            //  copy the image to the course content folder
                $imagePath =              "$CFG->wwwroot/file.php/$course_id/$crAssessmentSafeDir/$imageName";    //  full path to saved image
                $outputString =        $outputString . "<img src=\"$imagePath\">";    //  add reference to image to output string / html
              }

              //  if we didn't find the file in the image or other lists...  try in the .swf list

              else     {

                $found =               false;
                $zipSwfNamesNdx =      0;

                while (    (! $found)  &&  ($zipSwfNamesNdx < count($this->swfNames))    )     {
                  $crZipSwfName =      $this->swfNames[$zipSwfNamesNdx];
                  if ( $imageName == $crZipSwfName )    {    $found =  true;    }    else    {    $zipSwfNamesNdx++;    }
                }    //  while

                //  if the file was a .swf file, part of the original .zip... upload it into course content folder
                if ( $found ) {
                  $this->copy_file_to_course($imageName);                            //  copy the image to the course content folder
                  $swfPath =           "$CFG->wwwroot/file.php/$course_id/$crAssessmentSafeDir/$imageName";    //  full path to saved swf
                  $swfEmbedCode =      "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"625\" height=\"485\"><param name=\"movie\" value=\"$swfPath\"><param name=\"quality\" value=\"high\"><embed src=\"$swfPath\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"625\" height=\"485\"></embed></object>";
                  $outputString =      $outputString . "" . $swfEmbedCode;
                }

              }

              $ix =                    $ix + strlen($spaceSlashGTtagHSAS);

            }  //  if image packet

            //    if not text nor image, how about audio...
            //
            else    {

              $argHSAS =               substr($materialBlock, $ix, strlen($ltMATAUDIOblankHSAS));

              if ( $argHSAS == $ltMATAUDIOblankHSAS)    {

                $ixx =                 $ix;                                    //  remember beginning of packet
                $ix =                  strpos($materialBlock, $spaceSlashGTtagHSAS);    //  find other end of packet

                if ( $ix <= 0  )    {    $ix =   strlen($materialBlock);    }          //  if we didn't find an end, fake one

                $currentAudioBlock =   substr($materialBlock, $ixx, $ix + strlen($spaceSlashGTtagHSAS) - $ixx);    //  extract content of matimage tag
                $audioName =           extractstr($currentAudioBlock, "uri=\"", "\" />");    //  extract image name

                //  see if this file audio was seen in the just imported .zip
                $found =               false;
                $zipAudioNamesNdx =    0;

                while (    (! $found)  &&  ($zipAudioNamesNdx < count($this->audioNames))    )     {
                  $crZipAudioName =    $this->audioNames[$zipAudioNamesNdx];
                  if ( $audioName == $crZipAudioName )    {    $found =  true;    }    else    {    $zipAudioNamesNdx++;    }
                }    //  while

				if (!$found) {
					$zipOtherNamesNdx =      0;
					while (    (! $found)  &&  ($zipOtherNamesNdx < count($this->otherNames))    )     {
						$crZipOtherName =      $this->otherNames[$zipOtherNamesNdx];
						if ( $audioName == $crZipOtherName )    {    $found =  true;    }    else    {    $zipOtherNamesNdx++;    }
					}    //  while
				}

                //  if the file was an audio file, part of the original .zip... upload it into course content folder
                if ( $found ) {
                  $this->copy_file_to_course($audioName);                            //  copy the image to the course content folder
                  $audioPath =         "$CFG->wwwroot/file.php/$course_id/$crAssessmentSafeDir/$audioName";    //  full path to saved image
                  $outputString =      $outputString . "<a href=\"" . $audioPath . "\">$audioName</a>";    //  add reference to audio to output string / html
                }

                //  if we didn't find the file in the audio or other lists...  try in the .swf list
                else     {

                  $found =             false;
                  $zipSwfNamesNdx =    0;

                  while (    (! $found)  &&  ($zipSwfNamesNdx < count($this->swfNames))    )     {
                    $crZipSwfName =    $this->swfNames[$zipSwfNamesNdx];
                    if ( $audioName == $crZipSwfName )    {    $found =  true;    }    else    {    $zipSwfNamesNdx++;    }
                  }    //  while

                  //  if the file was a .swf file, part of the original .zip... upload it into course content folder
                  if ( $found ) {
                    $this->copy_file_to_course($audioName);                          //  copy the image to the course content folder
                    $swfPath =         "$CFG->wwwroot/file.php/$course_id/$crAssessmentSafeDir/$audioName";    //  full path to saved swf
                    $swfEmbedCode =    "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"625\" height=\"485\"><param name=\"movie\" value=\"$swfPath\"><param name=\"quality\" value=\"high\"><embed src=\"$swfPath\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\" width=\"625\" height=\"485\"></embed></object>";
                    $outputString =    $outputString . "" . $swfEmbedCode;
                  }

                }

                $ix =                  $ix + strlen($spaceSlashGTtagHSAS);

              }

              else    {
                $ix =                  strlen($materialBlock);                 //  skip over packet... better luck with next one
              }

            }  //

          }  //  <matimage packet

        }  //  if  <mat found

      }  //  while $ix < strlen...

      //  if we generated no text to return - return a FALSE instead
      if  (  $outputString == "" )    {    $outputString =    false;    }

      //  done here...
      return                           $outputString;

    }

  }  //  get_option_text



	//NOTE: Assumes that a multianswer, essay, fillblank, or matching question
	//      is given as a parameter.
	//
	//returns the maximum points possible for the question in $questionString.
	function get_maxvalue($questionString) {

		$questionType = $this->determine_type($questionString);

		switch ($questionType) {

		case "multianswer":
		case "fillinblank":
		case "essay":
			return extractstr($questionString, "maxvalue=\"", "\" minvalue");

		case "matching":
			$decvar = extractstr($questionString, "<decvar ", "</outcomes>", 3, 1);
			return extractstr($decvar, "maxvalue=\"", "\"/>");

		default:
			return false;

		}

	}


  //----------------------------------------------------
  //End of class tools
  //----------------------------------------------------

}  //  CLASS END

//
//
//    E N D     O f     C L A S S
//
//
//   ------------------------------------------------------------------------------------------------------------------------------------------------


//wrap the class respondus_qti_class based on which moodle version is being used.
if($CFG->version < '2006050501') {		//<< If using anything before moodle 1.6
  include_once($CFG->dirroot.'/mod/quiz/format/respondusqti/quizFormatRespondusQTIWrapper.php');
}
else {	//<< If using moodle 1.6+
  include_once($CFG->dirroot.'/question/format/respondusqti/qformatRespondusQTIWrapper.php');
}


///======================================================================
///                                     LOCAL FUNCTIONS
///======================================================================

//                                                                               //  <<<<<  sep dir fix gjm  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//  Set Assessment Safe Folder global variable from xml text                     //  <<<<<  sep dir fix gjm
//                                                                               //  <<<<<  sep dir fix gjm  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
//                                                                               new routine by gjm
function set_assessment_safe_subdir($lines)
{
  global $crAssessmentSafeDir;

  //  search $questionStrings for a line with <assessment title> tag
  //  build up that search arg so we can build it once, use it many times
  $looknfor =                          "assessment title=\"";                     //  assessment title="
  $looknforHS =                        htmlspecialchars($looknfor);               //  generate same but in html safe format - the format of $lines
  $looknforHSuc =                      strtoupper($looknforHS);                   //  gen uppercase ver of same - match case of $line

  //  now search
  $tagfound = false;
  $crlnn = 0;

  //  search until we find the tag, or run out of strings to search
  while (  (! $tagfound) && ($crlnn < count($lines))  )
  {
    //  extract current line from array, make uppercase copy for comparing against
    $line =                            $lines[$crlnn];
    $lineuc =                          strtoupper($line);

    //  see if our sought string is within the current line
    $i =                               strpos($lineuc, $looknforHSuc);

    //  if we found
    if ( $i > 0)  {

      //  if we found our tag...  trim the line by deleting everying thing thru the end of the tag (leaving the argument value etc)
      $i =                             $i + strlen($looknforHSuc);
      $line =                          substr($line, $i, 4096);
      $lineuc =                        substr($lineuc, $i, 4096);

      //  next look for the closing " at the end of the parm
      $looknfor =                      "\" ident=";
      $looknforHS =                    htmlspecialchars($looknfor);
      $looknforHSuc =                  strtoupper($looknforHS);

      //  see if we can't find the other end of the tag/value
      $dlmbgn =                        strpos($lineuc, $looknforHSuc);

      if ($dlmbgn > 0) {

        //  walk across what's left of assessment name... and extract usable chars for a subdir name
        $i =                             0;
        $crAssessmentSafeDir =           "";

        while ($i < $dlmbgn)   {
          $wrkc =                        substr($line, $i, 1);
          $wrkcuc =                      strtoupper($wrkc);

          //  if this char is a usable one for assessment subdir... add it to the growing list
          if (  (($wrkcuc >= "A") && ($wrkcuc <= "Z"))  ||  (($wrkcuc >= "0") && ($wrkcuc <= "9"))  )     {
            $crAssessmentSafeDir =       $crAssessmentSafeDir . $wrkc;
          }

          //  onto next char within whats left of tag param
          $i =                           $i + 1;

        }   //  while

        //  if we ended up with a null subdir name - use dflt
        if ($crAssessmentSafeDir == "")      {
          $crAssessmentSafeDir =         "imported";
        }

        $tagfound =                      true;        //  loop no longer - we have out subdir name

      }  // if found parm dlm

    }  // if found tag left part

    //  onto next record... even if we're about to exit
    $crlnn =                             $crlnn + 1;

  }  // while  (not tagfound and crlnn < count)

  print("Assessment set subdirectory set to -  " . $crAssessmentSafeDir .  "<br>");

}                                                                               //  <<<<<  sep dir fix gjm  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>



//
//        p a r s e _ a s _ q u e s t i o n _ s t r i n g s
//
//                                                           replacement routine by gjm
//  a question is defined as being that text btwn <item title=...  and </item>
//
function parse_as_question_strings($lines) {

  $InQuestion =                        false;
  $crLnn =                             0;                                      //  start with the first line in the passed lines

  while ($crLnn < count($lines)) {

    $crLn =                            trim($lines[$crLnn]);

    //  if the tag is <item title =...  then it begins a question
    if (substr_count($crLn, "item title=") > 0) {
      $linesOfQuestionArray[] =        $crLn . " ";                            //  start array with item title line
      $InQuestion =                    true;
    }

    //  if the tag is </item> then that's end of a question - process accordingly
    elseif (substr_count($crLn, htmlspecialchars('</item>')) > 0) {
      $implodedQuestion =              implode("", $linesOfQuestionArray);
      $questions[] =                   $implodedQuestion;
      $linesOfQuestionArray =          NULL;
      $InQuestion =                    false;
    }

    //  otherwise, neither a begin nor end, but within a question ... just add text to accum array
    else {
      if ($InQuestion) {
        $linesOfQuestionArray[] =      $crLn;
      }
    }

    //  onto next input line
    $crLnn++;                                                                  //  onto next line

  }    //  while

  return $questions;

}



//====================================================================
//                                      Import tool functions
//====================================================================
//These are functions to be used by proccessing functions.
//These functions interpret the file to be imported and return
//information back to the calling function.
//
//All returned data that will be added to the Moodle
//database (feedback, question name, question text, choices,
//                  subquestions, correct subanswers, and fillblank answers)
//have addslashes() applied so characters from the file (i.e. ", ', $, etc)
//will be converted to escape characters when necessary.
//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

//given a string containing a question,
//returns the name of the question.
function get_question_name($questionString) {

        //gets the question name from the $questionString.
        $name = extractstr($questionString, "item title=\"", "\" ident=\"QUE");

		// we must call htmlspecialchars_decode() twice here, because of the
		// htmlspecialchars() call in function readdata()
		// ex.  xml file:            [&lt;]
		//      after readdata():    [&amp;lt;]
		//      after 1st decode:    [&lt;]
		//      after second decode: [>]
		// then we have to encode as utf8 to handle accented chars
        return addslashes(utf8_encode(htmlspecialchars_decode(
	      htmlspecialchars_decode($name))));
}


//Assumes a question with a matching questiontype has been passed.
//gets the ids of all of the possible subquestions for the matching question.
//returns an array of these ids where the first index contains the id
//of the first choice.
function get_matching_subquestion_ids($questionString) {

        //determines how many ids there are.
        $idCount = substr_count($questionString, '/response_lid');

        //get the block that the ids occur in.
        $idBlock = extractstr($questionString, '<presentation>', '</presentation>');

        //loop through the idBlock
        for($i = 1; $i <= $idCount; $i++) {

                //get the ith line that the ith id is on.
                $idLine = extractstr($idBlock, "<response_lid", "</response_lid>", $i, $i);

                //get the answer id from $id line.  It is beteen "ident=\"" and the second " on the line.
                $id = extractstr($idLine, "ident=\"", "\"", 1, 2);

                //append the id to the array of ids
                $ids[] = $id;
        }

        return $ids;
}


//Assumes a question with a matching questiontype has been passed.
//gets the ids of all of the possible subanswers for the matching question.
//returns an array of these ids where the first index contains the id
//of the first answer.
function get_matching_subanswer_ids($questionString) {

        //get the block that the ids occur in.
        $idString = extractstr($questionString, "<render_choice", "</render_choice>", 1, 1);

        //determines how many ids there are.
        $idCount = substr_count($idString, "/response_label");

        //loop through the idBlock
        for($i = 1; $i <= $idCount; $i++) {

                //get the ith line that the ith id is on.
                $idLine = extractstr($idString, "<response_label ident=", "</response_label>", $i, $i);

                //get the answer id from $id line.  It is beteen "ident=\"" and the second " on the line.
                $id = extractstr($idLine, "\"", "\"", 1, 2);

                //append the id to the array of ids
                $ids[] = $id;
        }

        return $ids;
}


//Assumes a question with a matching questiontype has been passed.
//Given a questionstring, an array of subanswers, an array of subanswer ids,
//and an array of subquestion ids,
//
//returns an array of the subanswers in the correct order so that the first
//index of the returned array is the correct answer to the first index of the
//subquestion array. unused answers are added to the end of the ordered array.
function get_matching_correct_subanswers($questionString, $subanswerArray, $subanswerIds, $subquestionIds) {

		//get the string containing all of the subanswer information from $questionString.
        $resprocessingBlock = extractstr($questionString, "<resprocessing>", "</resprocessing>");

        //loop through each matching choice possibility to find correct matching answers.
        //there are sizeof($subquestionIds) * sizeof($subanswerArray) answer posibilities.      
        for($k = 1; $k <= sizeof($subquestionIds) * sizeof($subanswerArray); $k++) {

                //get the kth string containing the information for the kth matching answer.
                $answersBlock = extractstr($resprocessingBlock, "title=\"", "</respcondition>", $k, $k);                                

                //get a string containing a means of determining if the kth answer  is correct.
                $checkString = extractstr($answersBlock, "varname=\"", "\" action=");

                //this is a correct answer! 
                if(trim($checkString) == "Respondus_Correct") {

                        //get the string containing the correct answer id for the kth matching answer.          
                        $subanswerLine = extractstr($answersBlock, "respident=\"", "</setvar>");

                        //get the correct answer id that is in the subanswerLine string.
                        //(This is the id of the answer we want to append to our array)
                        $correctId = extractstr($subanswerLine, "\">", "</varequal>");
						
                        $flag = false;

                        //loop through subanswers
                        for($j = 0; $j < sizeof($subanswerArray) && !$flag; $j++) {

								//if the id at index $j matches the correct id found in the $subanswerLine
                                if($subanswerIds[$j] == $correctId) {
                                
										// track used answer ids
										if (!in_array($correctId, $used_ids)) {
											$used_ids[] = $correctId;
										}
                                        
                                        //add the $subanswer @ index $j to our array, it is a correct answer.
                                        $correctSubanswers[] = $subanswerArray[$j];

                                        //terminate the loop, the answer has already been found.
                                        $flag = true;

                                }//end id comparison

                        }//end loop through subanswers

                }//end correct answer comparison

        }//end loop through all answer possibilities.

		// add unused answers to the end of the ordered array
		foreach($subanswerIds as $key => $value) {
			if (!in_array($value, $used_ids)) {
				$correctSubanswers[] = $subanswerArray[$key];
			}
		}
		
		return $correctSubanswers;

}//end function


//Assumes that a fill in the blank question is passed
//as a parameter.
//Returns an array of the correct responses for a fill in the blank
//question.
function get_fillblank_Answers($questionString) {

        //get the number of answers for the question.
        $answerCount = substr_count($questionString, '/setvar');

        //get the block of code that the answers are in.
        $answerBlock = extractstr($questionString, '<resprocessing>', '</resprocessing>');

        //loop through the $answerBlock and extract the answers.
        for($i = 1; $i <= $answerCount; $i++) {

                //extract the line that the answer is on from the $answerBlock.
                $answerLine = extractstr($answerBlock, "respident=\"","</conditionvar>", $i, $i);

                //get the answer from the $answerLine
                $answer = extractstr($answerLine, "\">","</varequal>");

                //append the $answer to the $answers array.
				// we must call htmlspecialchars_decode() twice here, because of the
				// htmlspecialchars() call in function readdata()
				// ex.  xml file:            [&lt;]
				//      after readdata():    [&amp;lt;]
				//      after 1st decode:    [&lt;]
				//      after second decode: [>]
				// then we have to encode as utf8 to handle accented chars
                $answers[] = addslashes(utf8_encode(htmlspecialchars_decode(
				  htmlspecialchars_decode($answer))));
        }

        return $answers;
}


//given a string containing a question,
//returns a string array of question IDs
//where index 0 is the ID of the first choice
//1 is the id of the second choice, etc.
function get_choice_ids($questionString) {

        //determines how many choices there are
        $choiceCount = substr_count($questionString, '/response_label');

        //get the portion of the questionString that the choices are in
        $choiceBlock = extractstr($questionString, "<render_choice>", "</render_choice>");

        //loop through the $choiceBlock and add all the choice IDs to the array.        
        for($i = 1; $i <= $choiceCount; $i++) {

                //get the ith questionID from the choiceBlock.
                //      i.e. the string between the ith occurence of "ident=\"" and the $ith occurance
                //               of "\" material" in the $choiceBlock
                $id = extractstr($choiceBlock, "ident=\"", "\"><material><mat", $i, $i);

                //add the $id to the array $ids.
                $ids[] = $id;
        }

        return $ids;
}


//given a string containing a question,
//and an array containing the answerIds
//for that question,
//Returns the ID (as a string) of the
//correct answer for the question.
//
//NOTE: later on (post 5/25/06), it might be
//              necessary to re-write this function
//              to process questions with multiple
//              correct answers.
function get_correct_id($questionString, $answerIds) {

        //determines how many choices there are
        $choiceCount = substr_count($questionString, '/response_label');

        //get the portion of the questionString that the answer info is in
        $answerBlock = extractstr($questionString, "<resprocessing>", "</resprocessing>");

        $flag = false;

        //loop through the $answerBlock and determine which ID corresponds to the correct
        //answer.
        //the $flag will be used for early exit of the loop if the correct ID is found before
        //compairing all of the answers.
        for($i = 0; $i < $choiceCount && !$flag; $i++) {

                //get the answer information for the ID at index $i of $answerIds
                $answerInfo = extractstr($answerBlock, "action=", "/setvar>", $i+1, $i+1);

                //get the action type from the answer info.
                $action = extractstr($answerInfo, "\"", "\"", 1, 2);

                //the answer is correct if the action is Set
                if($action == "Set") {
                        $correctIdIndex = $i;
                        $flag = true;
                }
        }

        //return the correct answer ID  
        return $answerIds[$correctIdIndex];
}


// returns the points value for multichoice, truefalse, and algorithmic questions
function get_correct_points($questionString) {

        //determines how many choices there are
        $choiceCount = substr_count($questionString, '/response_label');

        //get the portion of the questionString that the answer info is in
        $answerBlock = extractstr($questionString, "<resprocessing>", "</resprocessing>");

        //loop through the $answerBlock and find the correct answer.
        for($i = 0; $i < $choiceCount; $i++) {

                //get the answer information
                $answerInfo = extractstr($answerBlock, "action=", "/setvar>", $i+1, $i+1);

                //get the action type from the answer info.
                $action = extractstr($answerInfo, "\"", "\"", 1, 2);

                //the answer is correct if the action is Set
                if($action == "Set") {
                        
						$points = extractstr($answerInfo, ">", "<");
						return $points;
                }
        }

        return false;
}


//given the correct answer id for a question
//and an array of all answer ids for a question,
//returns the index in $answerIds where $correctId occurs.
//returns false if $correctId does not occur in $answerIds.
function get_correct_index($correctId, $answerIds) {

        foreach($answerIds as $key => $answer) {
                if($answer == $correctId) {
                        return $key;
                }
        }

        return false;
}


//NOTE: Assumes that a multianswer question is given as a parameter
//returns an array of scores that matches the array of choices and the
//array of ids.
//(I.E.  Index 0 of the array returned by get_answer_scores() contains
//       the score for the choice in index 0 of the array returned by
//               get_choices() (which has the same id as index 0 of the
//               array returned by get_choice_ids()).
//NOTE:  This function works properly if called with a fillintheblank question
//               as a parameter.
function get_answer_scores($questionString) {

        //determines how many scores there are
        $scoreCount = substr_count($questionString, '/setvar');

        //get the portion of the questionString that the scores are in
        $scoreBlock = extractstr($questionString, '<resprocessing>', '</resprocessing>');

        //loop through the $scoreBlock and add all the score values to the array.       
        for($i = 1; $i <= $scoreCount; $i++) {

                //get the ith score from the scoreBlock.
                //      i.e. the string between the ith occurence of "action=\"Add\"" and the $ith occurance
                //               of "/setvar" in the $scoreBlock.
                $score = extractstr($scoreBlock, "action=\"Add\">", "</setvar>", $i, $i);

                //add the $score to the array $scores.
                $scores[] = $score;
        }

        return $scores;
}


// returns the nearest grade option for the given score
// it's not clear how compatible this is with earlier versions of Moodle
function nearest_grade_option($score) {

	$gradeoptions = array("-100", "-90", "-80", "-75", "-70", "-66.666",
	  "-60", "-50", "-40", "-33.333", "-30", "-25", "-20", "-16.666", "-14.2857",
	  "-12.5", "-11.111", "-10", "-5", "0", "5", "10", "11.111", "12.5",
	  "14.2857", "16.666", "20", "25", "30", "33.333", "40", "50", "60",
	  "66.666", "70", "75", "80", "90", "100");

	$nearest = array();

	foreach($gradeoptions as $value) {
		if ($score == $value) {
			return $score;
		}
		$nearest[$value] = abs($score - $value);
	}

	// reverse sort list of deltas and grab the last (smallest)
	asort($nearest, SORT_NUMERIC);
	reset($nearest);
	return key($nearest);
}


// returns a fraction between 0 and 1 for the given grade option
// the value is rounded to 5 decimal places, which seems to be critical
function fraction_from_grade_option($option) {
	return round($option/100, 5);
}


//====================================================================
//                                      End of Import tool functions
//====================================================================


//--------------------------------------------------------------------
//                      Additional String Functions
//--------------------------------------------------------------------
//These are string functions that make use of previously existing string
//functions     and perform specific tasks necessary to this class.
//
//They are mainly here for code re-useability.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


//strbetween() returns a substring of the string str
//starting at the ith occurence of startstr in str
//and ending before the jth occurence of endstr in str.
//
//If called without parameters $i and $j,
//strbetween() will default both to 1
//and return the string between the first occurance
//of $startstr in $str and the first occurance of $end str
//in $str.
//
//-----------------
//      EXAMPLES
//-----------------
//$str = "The cat was mad at me";
//
//$result = strbetween($str, 'cat', ' me');
//
//echo $result;  //prints: 'cat was mad at me'
//
//------
//EX 2:
//------
//$str = "My cat is a fat cat on a mat";
//
//$result = strbetween($str, 'My', 'cat', 1, 2);
//
//echo $result; //prints 'My cat is a fat cat ';
//
function strbetween($str, $startstr, $endstr, $i=1, $j=1) {

        //in detail, returns a substring of $str where:
        //The start is at the ith occurence of $startstr in $str
        //and the length of the substring is the
        //distance between the ith occurence of the $startstr
        //and the jth occurence of the $endstr.

        //convert the occurences into array indexes;
        $i--;   
        $j--;

		//get an array of the positions of all occurences of $startstr in $str.
		$startStrPositions = strapos($str, $startstr);

		//get an array of the positions of all occurences of $endstr in $str.
		$endStrPositions = strapos($str, $endstr);

		//return the substring between the ith instance of $startstr in the string $str
		//and the jth instance of $endstr in the string $str.
        if (count($startStrPositions) > $i && count($endStrPositions) > $j) {
			$betweenstr = substr($str, $startStrPositions[$i], ($endStrPositions[$j] + strlen($endstr)) - $startStrPositions[$i]);
		}

		return $betweenstr;
}


//Returns an array of positions that correspond
//to all occurences of $needle in $haystack.
//The first index is the first occurence, and soforth
//The name strapos stands for:
//string array positions (for clarification purposes).
function strapos($haystack, $needle) {

        //get the len of $needle
        $needleLen = strlen($needle);
        $haystackLen = strlen($haystack);

        //get how many times $needle occures in $haystack
        $occurences = substr_count($haystack, $needle);

        //set the initial offset to 0.
        $offset = 0;

        //loop through all occurences
        for($i = 0; $i < $occurences; $i++)  {
                $pos = strpos($haystack, $needle, $offset);             //<-get the position of the first occurence of needle in haystack
                $positions[] = $pos;                                                            //<-store the pos
                $offset = $pos + $needleLen;                                            //<-change the offset so the occurence just found will be skipped.              
        }

        return $positions;
}


//given a string to search, a start string, and an endstring,
//Returns a substring that begins directly after the end
//of the ith occurence of startstring and ends before the
//beginning of the jth occurence of endstring.
//
////If called without parameters $i and $j,
//extractstr() will default both to 1
//and return a substring that begins directly after the end
//of the first occurence of startstring and ends before the
//beginning of the first occurence of endstring.//
//------------
//      EXAMPLE
//------------
//$str = 'I have a super coy pond.';
//$sub = extractstr($str, 'I have a ', ' pond');
//echo $sub;    //sub contains 'super coy'.
//------------
//      EX 2
//------------
//$str = "My coy get eaten by coy eating racoons.";
//$sub = extractstr($str, 'get ', ' coy', 1, 2);
//echo $sub;    //sub contains: 'eaten by';
//
function extractstr($str, $startstr, $endstr, $i=1, $j=1) {

        //convert occurences to array indexes
        $i--;
        $j--;

        //convert any html characters into their string corespondent.
        $startstr = htmlspecialchars($startstr);
        $endstr = htmlspecialchars($endstr);

        $startLen = strlen($startstr);          //get the length of the start string.

        //get positions of all occurances of $startstr in $str.
        $startPositions = strapos($str, $startstr);

        //get positions of all occurances of $endstr in $str.
        $endPositions = strapos($str, $endstr);

		if (count($startPositions) > $i && count($endPositions) > $j) {

			//get the length of the enclosed sub string.
			$subLength = $endPositions[$j] - ($startPositions[$i] + $startLen);

			//get the sub string enclosed by $startstr and $endstr.
			$enclosedstr = substr($str, $startPositions[$i]+$startLen, $subLength);
		}

        //  set return value correctly  -   gjm  major fix   20jun07
        return                         $enclosedstr;
}

// end of code

?>

