<?php

 function assignment_download_course_submissions($course, $id) {
    global $CFG;

    require_once($CFG->libdir.'/filelib.php');
    // Setup temp directory
    $desttemp = $CFG->dataroot . '/' . $course->id . '/moddata/assignment/temp/';
    $courseshortnamecleaned = clean_filename($course->shortname);
    $destfile = $courseshortnamecleaned.date('_Y-m-d\THi').'.zip'; //name of new zip file.
    if (!file_exists($desttemp)) { //create temp dir if it doesn't already exist.
        mkdir($desttemp);
    }

    // Get all of the course's assignment details
    $modinfo = get_fast_modinfo($course);
    $assignments = array();
    foreach($modinfo->cms as $cmid => $info) {
        if($info->modname == 'assignment') {
            $assignment = get_record("assignment", "id", $info->instance);
            require_once ($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
            $assignmentclass = 'assignment_'.$assignment->assignmenttype;
            $assignmentinstance = new $assignmentclass($cmid, $assignment, $info, $course);

            $assignmentinstance->copy_submissions($desttemp.$courseshortnamecleaned);
        }
    }

    //zip files
    $fullzipfilename = $desttemp.$destfile;
    if (file_exists($desttemp)) {
        zip_directory($desttemp, $courseshortnamecleaned, $destfile);
    }

    //send file to user
    if (file_exists($fullzipfilename)) {
        $path_parts = pathinfo(cleardoubleslashes($fullzipfilename));
        header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header ("Content-Type: application/octet-stream");
        header ("Content-Length: " . filesize($fullzipfilename));
        header ("Content-Disposition: attachment; filename=$destfile");
        readfile($fullzipfilename);
    }

    //delete old temp files
    fulldelete($desttemp);

}





?>
