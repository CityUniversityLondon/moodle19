<?php // $Id: assignment.class.php,v 1.46.2.10 2010/09/16 05:45:42 samhemelryk Exp $
require_once($CFG->libdir.'/formslib.php');

/**
 * Extend the base assignment class for assignments where you upload a single file
 *
 */
class assignment_online extends assignment_base {

    function assignment_online($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'online';
    }

    function view() {

        global $USER;

        $edit  = optional_param('edit', 0, PARAM_BOOL);
        $saved = optional_param('saved', 0, PARAM_BOOL);

        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        require_capability('mod/assignment:view', $context);

        $submission = $this->get_submission();

        //Guest can not submit nor edit an assignment (bug: 4604)
        if (!has_capability('mod/assignment:submit', $context)) {
            $editable = null;
        } else {
            $editable = $this->isopen() && (!$submission || $this->assignment->resubmit || !$submission->timemarked);
        }
        $editmode = ($editable and $edit);

        if ($editmode) {
            //guest can not edit or submit assignment
            if (!has_capability('mod/assignment:submit', $context)) {
                print_error('guestnosubmit', 'assignment');
            }
        }

        add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

/// prepare form and process submitted data
        $mform = new mod_assignment_online_edit_form();

        $defaults = new object();
        $defaults->id = $this->cm->id;
        if (!empty($submission)) {
            if ($this->usehtmleditor) {
                $options = new object();
                $options->smiley = false;
                $options->filter = false;

                $defaults->text   = format_text($submission->data1, $submission->data2, $options);
                $defaults->format = FORMAT_HTML;
            } else {
                $defaults->text   = clean_text($submission->data1, $submission->data2);
                $defaults->format = $submission->data2;
            }
        }
        $mform->set_data($defaults);

        if ($mform->is_cancelled()) {
            redirect('view.php?id='.$this->cm->id);
        }

        if ($data = $mform->get_data()) {      // No incoming data?
            if ($editable && $this->update_submission($data)) {
                //TODO fix log actions - needs db upgrade
                $submission = $this->get_submission();
                add_to_log($this->course->id, 'assignment', 'upload',
                        'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                $this->email_teachers($submission);
                //redirect to get updated submission date and word count
                redirect('view.php?id='.$this->cm->id.'&saved=1');
            } else {
                // TODO: add better error message
                notify(get_string("error")); //submitting not allowed!
            }
        }

/// print header, etc. and display form if needed
        if ($editmode) {
            $this->view_header(get_string('editmysubmission', 'assignment'));
        } else {
            $this->view_header(get_string('viewsubmissions', 'assignment'));
        }

        $this->view_intro();

        $this->view_dates();

        if ($saved) {
            notify(get_string('submissionsaved', 'assignment'), 'notifysuccess');
        }

        if ($editmode) {
            print_box_start('generalbox', 'online');
            $mform->display();
            print_box_end();
        } else {
            print_box_start('generalbox boxwidthwide boxaligncenter', 'online');
            if ($submission) {
                echo format_text($submission->data1, $submission->data2);
            } else if (!has_capability('mod/assignment:submit', $context)) { //fix for #4604
                if (isguest()) {
                    echo '<div style="text-align:center">'. get_string('guestnosubmit', 'assignment').'</div>';
                } else {
                    echo '<div style="text-align:center">'. get_string('usernosubmit', 'assignment').'</div>';
                }
            } else if ($this->isopen()){    //fix for #4206
                echo '<div style="text-align:center">'.get_string('emptysubmission', 'assignment').'</div>';
            }
            print_box_end();
            if ($editable) {
                echo "<div style='text-align:center'>";
                print_single_button('view.php', array('id'=>$this->cm->id,'edit'=>'1'),
                        get_string('editmysubmission', 'assignment'));
                echo "</div>";
            }
        }

        $this->view_feedback();

        $this->view_footer();
    }

    /*
     * Display the assignment dates
     */
    function view_dates() {
        global $USER, $CFG;

        if (!$this->assignment->timeavailable && !$this->assignment->timedue) {
            return;
        }

        print_simple_box_start('center', '', '', 0, 'generalbox', 'dates');
        echo '<table>';
        if ($this->assignment->timeavailable) {
            echo '<tr><td class="c0">'.get_string('availabledate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timeavailable).'</td></tr>';
        }
        if ($this->assignment->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timedue).'</td></tr>';
        }
        $submission = $this->get_submission($USER->id);
        if ($submission) {
            echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
            echo '    <td class="c1">'.userdate($submission->timemodified);
        /// Decide what to count
            if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')</td></tr>';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')</td></tr>';
            }
        }
        echo '</table>';
        print_simple_box_end();
    }

    function update_submission($data) {
        global $CFG, $USER;

        $submission = $this->get_submission($USER->id, true);

        $update = new object();
        $update->id           = $submission->id;
        $update->data1        = $data->text;
        $update->data2        = $data->format;
        $update->timemodified = time();

        if (!update_record('assignment_submissions', $update)) {
            return false;
        }

        $submission = $this->get_submission($USER->id);
        $this->update_grade($submission);
        return true;
    }


    function print_student_answer($userid, $return=false){
        global $CFG;
        if (!$submission = $this->get_submission($userid)) {
            return '';
        }
        $output = '<div class="files">'.
                  '<img src="'.$CFG->pixpath.'/f/html.gif" class="icon" alt="html" />'.
                  link_to_popup_window ('/mod/assignment/type/online/file.php?id='.$this->cm->id.'&amp;userid='.
                  $submission->userid, 'file'.$userid, shorten_text(trim(strip_tags(format_text($submission->data1,$submission->data2))), 15), 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';
                  return $output;
    }

    function print_user_files($userid, $return=false) {
        global $CFG;

        if (!$submission = $this->get_submission($userid)) {
            return '';
        }

        $output = '<div class="files">'.
                  '<img align="middle" src="'.$CFG->pixpath.'/f/html.gif" height="16" width="16" alt="html" />'.
                  link_to_popup_window ('/mod/assignment/type/online/file.php?id='.$this->cm->id.'&amp;userid='.
                  $submission->userid, 'file'.$userid, shorten_text(trim(strip_tags(format_text($submission->data1,$submission->data2))), 15), 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';

        ///Stolen code from file.php

        print_simple_box_start('center', '', '', 0, 'generalbox', 'wordcount');
    /// Decide what to count
        if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
            echo ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')';
        } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
            echo ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')';
        }
        print_simple_box_end();
        print_simple_box(format_text($submission->data1, $submission->data2), 'center', '100%');

        ///End of stolen code from file.php

        if ($return) {
            //return $output;
        }
        //echo $output;
    }

    function preprocess_submission(&$submission) {
        if ($this->assignment->var1 && empty($submission->submissioncomment)) {  // comment inline
            if ($this->usehtmleditor) {
                // Convert to html, clean & copy student data to teacher
                $submission->submissioncomment = format_text($submission->data1, $submission->data2);
                $submission->format = FORMAT_HTML;
            } else {
                // Copy student data to teacher
                $submission->submissioncomment = $submission->data1;
                $submission->format = $submission->data2;
            }
        }
    }

    function setup_elements(&$mform) {
        global $CFG, $COURSE;

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'resubmit', get_string("allowresubmit", "assignment"), $ynoptions);
        $mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));
        $mform->setDefault('resubmit', 0);

        $mform->addElement('select', 'emailteachers', get_string("emailteachers", "assignment"), $ynoptions);
        $mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
        $mform->setDefault('emailteachers', 0);
        
        // CMDL-1141 fix emails sent to roles above teacher
        $mform->setAdvanced('emailteachers');
        // end CMDL-1141

        $mform->addElement('select', 'var1', get_string("commentinline", "assignment"), $ynoptions);
        $mform->setHelpButton('var1', array('commentinline', get_string('commentinline', 'assignment'), 'assignment'));
        $mform->setDefault('var1', 0);

    }

    // CMDL-1175 add bulk upload of feedback
    function download_submissions() {
 	      global $CFG;
 		    $submit = $this->get_submissions('','');
 
 	      $filesforzipping = array();
 	      $filesnewname = array();
 	      $desttemp = "";
        
        //create zip filename
 	      $filename = "online_assignment.zip";
 
 	      //online assignment can use html
 	      $filenewname = clean_filename($this->assignment->name);
 	      $file = '_' . $filenewname . ".html";
        $course     = $this->course;
        $assignment = $this->assignment;
        $cm         = $this->cm;
        $context    = get_context_instance(CONTEXT_MODULE, $cm->id);
 	      $groupmode = groupmode($course,$cm);
 	      $groupid = 0;	// All users
 	      if($groupmode) $groupid = get_current_group($course->id, $full = false);
 	      $count = 0;
 
      	foreach ($submit as $tp) {
 		      $a_userid = $tp->userid; //get userid
 		      if ( (groups_is_member( $groupid,$a_userid)or !$groupmode or !$groupid)) {
 		        $count = $count + 1;
 			      $a_assignid = $tp->assignment; //get name of this assignment for use in the file names.
            $a_user = get_complete_user_data("id", $a_userid); //get user
            $filearea = $this->file_area_name($a_userid);
          	$submission = $tp->data1;      //fetched from mysql database
            $desttemp = $CFG->dataroot . "/" . substr($filearea, 0, strripos($filearea, "/")). "/temp/";
 					  //get temp directory name
 
 			      if (!file_exists($desttemp)) { //create temp dir if it doesn't already exist.
 			        mkdir($desttemp,0777,true); 
 			      }
 
 	          require_once($CFG->libdir.'/filelib.php');
 
 	          //get file name.html
 	          $filesforzip = $desttemp . $a_user->firstname ."_". $a_user->lastname  . "_" . $a_user->username . $file;
            $fd = fopen($filesforzip,'wb');   //create if not exist, write binary
 	          fwrite( $fd, $submission);
 	          fclose( $fd );
            //save file name to array for zipping.
 	          $filesforzipping[] = $filesforzip;
 	        }    
        }      //end of foreach
 
 	      //zip files
        if ($count) zip_files($filesforzipping, $desttemp.$filename);  // check for no files
 
 	      //delete old temp files
 	      foreach ($filesforzipping as $filefor) {
 			    unlink($filefor);
 	       }
 
 	       //send file to user.
 	       if (file_exists($desttemp.$filename)) {
 	         header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
 	         header ("Content-Type: application/octet-stream");
 	         header ("Content-Length: " . filesize($desttemp.$filename));
 	         header ("Content-Disposition: attachment; filename=$filename");
 		       readfile($desttemp.$filename);
 	       }
    }
    // end CMDL-1175
}

class mod_assignment_online_edit_form extends moodleform {
    function definition() {
        $mform =& $this->_form;

        // visible elements
        $mform->addElement('htmleditor', 'text', get_string('submission', 'assignment'), array('cols'=>60, 'rows'=>30));
        $mform->setType('text', PARAM_RAW); // to be cleaned before display
        $mform->setHelpButton('text', array('reading', 'writing', 'richtext'), false, 'editorhelpbutton');
        $mform->addRule('text', get_string('required'), 'required', null, 'client');

        $mform->addElement('format', 'format', get_string('format'));
        $mform->setHelpButton('format', array('textformat', get_string('helpformatting')));

        // hidden params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // buttons
        $this->add_action_buttons();
    }
}

?>
