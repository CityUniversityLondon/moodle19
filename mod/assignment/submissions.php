<?php  // $Id: submissions.php,v 1.43 2006/08/28 08:42:30 toyomoyo Exp $

    require_once("../../config.php");
    require_once("lib.php");

    $id   = optional_param('id', 0, PARAM_INT);          // Course module ID
    $a    = optional_param('a', 0, PARAM_INT);           // Assignment ID
    $mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?
    // CMDL-1175 add bulk upload of feedback
    $download = optional_param('download', 'none', PARAM_ALPHA); // ZIP download asked for?
    $upload = optional_param('upload', 'none', PARAM_ALPHA); // ZIP upload asked for?
    // end CMDL-1175

    if ($id) {
        if (! $cm = get_coursemodule_from_id('assignment', $id)) {
            error("Course Module ID was incorrect");
        }

        if (! $assignment = get_record("assignment", "id", $cm->instance)) {
            error("assignment ID was incorrect");
        }

        if (! $course = get_record("course", "id", $assignment->course)) {
            error("Course is misconfigured");
        }
    } else {
        if (!$assignment = get_record("assignment", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $assignment->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id, false, $cm);

    require_capability('moodle/grade:viewall', get_context_instance(CONTEXT_MODULE, $cm->id));

/// Load up the required assignment code
    require($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
    $assignmentclass = 'assignment_'.$assignment->assignmenttype;
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

    // CMDL-1175 add bulk upload of feedback
    if ($download == "zip") {
        $assignmentinstance->download_submissions();
    } else if ($upload == "zip") {
        $assignmentinstance->upload_responses();
    // end CMDL-1175
    } else {
        $assignmentinstance->submissions($mode);   // Display or process the submissions
    }
?>
