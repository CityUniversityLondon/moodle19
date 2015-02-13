<?php // $Id: assignment.class.php,v 1.33.2.6 2009/11/20 08:25:32 skodak Exp $

/**
 * Extend the base assignment class for assignments where you upload a single file
 *
 */
class assignment_uploadsingle extends assignment_base {


    function print_student_answer($userid, $return=false){
           global $CFG, $USER;

        $filearea = $this->file_area_name($userid);

        $output = '';

        if ($basedir = $this->file_area($userid)) {
            if ($files = get_directory_list($basedir)) {
                require_once($CFG->libdir.'/filelib.php');
                foreach ($files as $key => $file) {

                    $icon = mimeinfo('icon', $file);
                    $ffurl = get_file_url("$filearea/$file");

                    //died right here
                    //require_once($ffurl);
                    $output = '<img src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                            '<a href="'.$ffurl.'" >'.$file.'</a><br />';
                }
            }
        }

        $output = '<div class="files">'.$output.'</div>';
        return $output;
    }

    function assignment_uploadsingle($cmid='staticonly', $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'uploadsingle';
    }

    function view() {

        global $USER;

        $context = get_context_instance(CONTEXT_MODULE,$this->cm->id);
        require_capability('mod/assignment:view', $context);

        add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}", $this->assignment->id, $this->cm->id);

        $this->view_header();

        $this->view_intro();

        $this->view_dates();

        $filecount = $this->count_user_files($USER->id);

        if ($submission = $this->get_submission()) {
            if ($submission->timemarked) {
                $this->view_feedback();
            }
            if ($filecount) {
                print_simple_box($this->print_user_files($USER->id, true), 'center');
            }
        }

        if (has_capability('mod/assignment:submit', $context)  && $this->isopen() && (!$filecount || $this->assignment->resubmit || !$submission->timemarked)) {
            $this->view_upload_form();
        }

        $this->view_footer();
    }


    function view_upload_form() {
        global $CFG;
        $struploadafile = get_string("uploadafile");

        $maxbytes = $this->assignment->maxbytes == 0 ? $this->course->maxbytes : $this->assignment->maxbytes;
        $strmaxsize = get_string('maxsize', '', display_size($maxbytes));

        echo '<div style="text-align:center">';
        echo '<form enctype="multipart/form-data" method="post" '.
             "action=\"$CFG->wwwroot/mod/assignment/upload.php\">";
        echo '<fieldset class="invisiblefieldset">';
        echo "<p>$struploadafile ($strmaxsize)</p>";
        echo '<input type="hidden" name="id" value="'.$this->cm->id.'" />';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        require_once($CFG->libdir.'/uploadlib.php');
        upload_print_form_fragment(1,array('newfile'),false,null,0,$this->assignment->maxbytes,false);
        echo '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';
    }


    function upload() {

        global $CFG, $USER;

        require_capability('mod/assignment:submit', get_context_instance(CONTEXT_MODULE, $this->cm->id));

        $this->view_header(get_string('upload'));

        $filecount = $this->count_user_files($USER->id);
        $submission = $this->get_submission($USER->id);
        if ($this->isopen() && (!$filecount || $this->assignment->resubmit || !$submission->timemarked)) {
            if ($submission = $this->get_submission($USER->id)) {
                //TODO: change later to ">= 0", to prevent resubmission when graded 0
                if (($submission->grade > 0) and !$this->assignment->resubmit) {
                    notify(get_string('alreadygraded', 'assignment'));
                }
            }

            $dir = $this->file_area_name($USER->id);

            require_once($CFG->dirroot.'/lib/uploadlib.php');
            $um = new upload_manager('newfile',true,false,$this->course,false,$this->assignment->maxbytes);
            if ($um->process_file_uploads($dir) and confirm_sesskey()) {
                $newfile_name = $um->get_new_filename();
                if ($submission) {
                    $submission->timemodified = time();
                    $submission->numfiles     = 1;
                    $submission->submissioncomment = addslashes($submission->submissioncomment);
                    unset($submission->data1);  // Don't need to update this.
                    unset($submission->data2);  // Don't need to update this.
                    if (update_record("assignment_submissions", $submission)) {
                        add_to_log($this->course->id, 'assignment', 'upload',
                                'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                        $submission = $this->get_submission($USER->id);
                        $this->update_grade($submission);
                        $this->email_teachers($submission);
                        print_heading(get_string('uploadedfile'));
                    } else {
                        notify(get_string("uploadfailnoupdate", "assignment"));
                    }
                } else {
                    $newsubmission = $this->prepare_new_submission($USER->id);
                    $newsubmission->timemodified = time();
                    $newsubmission->numfiles = 1;
                    if (insert_record('assignment_submissions', $newsubmission)) {
                        add_to_log($this->course->id, 'assignment', 'upload',
                                'view.php?a='.$this->assignment->id, $this->assignment->id, $this->cm->id);
                        $submission = $this->get_submission($USER->id);
                        $this->update_grade($submission);
                        $this->email_teachers($newsubmission);
                        print_heading(get_string('uploadedfile'));
                    } else {
                        notify(get_string("uploadnotregistered", "assignment", $newfile_name) );
                    }
                }
            }
        } else {
            notify(get_string("uploaderror", "assignment")); //submitting not allowed!
        }

        print_continue('view.php?id='.$this->cm->id);

        $this->view_footer();
    }

    function setup_elements(&$mform) {
        global $CFG, $COURSE;

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'resubmit', get_string("allowresubmit", "assignment"), $ynoptions);
        $mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));
        $mform->setDefault('resubmit', 0);

        $mform->addElement('select', 'emailteachers', get_string("emailteachers", "assignment"), $ynoptions);
        $mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
        // CMDL-1141 fix emails sent to roles above teacher
        $mform->setAdvanced('emailteachers', 0);
        // end CMDL-1141

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit') . ' ('.display_size($COURSE->maxbytes).')';
        $mform->addElement('select', 'maxbytes', get_string('maximumsize', 'assignment'), $choices);
        $mform->setDefault('maxbytes', $CFG->assignment_maxbytes);

    }

  // CMDL-1175 add bulk upload of feedback
  function download_submissions() {
	        global $CFG;

	        $submissions = $this->get_submissions('','');

	        $filesforzipping = array();
	        $filesnewname = array();
	        $desttemp = "";

	        //create prefix of new filename
		$filenewname = clean_filename($this->assignment->name. "_");
		$course     = $this->course;
		$assignment = $this->assignment;
		$cm         = $this->cm;
		$context    = get_context_instance(CONTEXT_MODULE, $cm->id);
		$groupmode = groupmode($course,$cm);
		$groupid = 0;	// All users
		if($groupmode) $groupid = get_current_group($course->id, $full = false);
		$count = 0;

	        foreach ($submissions as $submission) {
		$a_userid = $submission->userid; //get userid
		if ( (groups_is_member( $groupid,$a_userid)or !$groupmode or !$groupid)) {
		$count = $count + 1;

			    $a_assignid = $submission->assignment; //get name of this assignment for use in the file names.

			    $a_user = get_complete_user_data("id", $a_userid); //get user

	            $filearea = $this->file_area_name($a_userid);

			    $desttemp = $CFG->dataroot . "/" . substr($filearea, 0, strrpos($filearea, "/")). "/temp/"; //get temp directory name

			    if (!file_exists($desttemp)) { //create temp dir if it doesn't already exist.
			        mkdir($desttemp);
			    }

	            if ($basedir = $this->file_area($a_userid)) {
	                if ($files = get_directory_list($basedir)) {
	                    foreach ($files as $key => $file) {
	                        require_once($CFG->libdir.'/filelib.php');

	                        //get files new name.
	                        $filesforzip = $desttemp . $a_user->firstname ."_". $a_user->lastname  . "_" . $a_user->username . "_" . $filenewname . $file;

	                        //get files old name
	                        $fileold = $CFG->dataroot . "/" . $filearea . "/" . $file;

	                        if (!copy($fileold, $filesforzip)) {
						        error ("failed to copy file<br>" . $filesforzip . "<br>" .$fileold);
						    }

	                        //save file name to array for zipping.
	                        $filesforzipping[] = $filesforzip;
	                    }
	                }
	            }
	        }   }     // End of foreach

	        //zip files
	        $filename = "assignment.zip"; //name of new zip file.
	        if ($count) zip_files($filesforzipping, $desttemp.$filename);
		// skip if no files zipped
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

?>
