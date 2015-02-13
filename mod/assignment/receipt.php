<?php  // $Id$

    require_once("../../config.php");
    require_once("lib.php");
 
    $id  = required_param('id', PARAM_INT);
    $submissionid  = required_param('submission', PARAM_INT);   // Assignment Submission Id
    $delete = optional_param('delete', 0, PARAM_INT);
    
    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $assignment = get_record("assignment", "id", $cm->instance)) {
        error("assignment ID was incorrect");
    }

    if (! $course = get_record("course", "id", $assignment->course)) {
        error("Course is misconfigured");
    }
    
    if (!$submission = get_record('assignment_submissions', 'id', $submissionid)) {
        error('Submission not found');
    }

    require_login($course, true, $cm);
    
    if ($submission->userid != $USER->id && !has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        error('Permission Denied');
    }

    require ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");
    $assignmentclass = "assignment_$assignment->assignmenttype";
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);
    $assignmentinstance->view_header();

    // CMDL-1251 add deletion titles to receipt
    if (!$delete) {
        echo ''.get_string('assignmentsubmissionreceipt', 'assignment').'';
    } else {
        echo ''.get_string('assignmentdeletionreceipt', 'assignment').'';
    }
    // end CMDL-1251

    print_box_start();
    echo $assignmentinstance->get_coversheet_html($submission, $delete);
    print_box_end();
    print_continue($CFG->wwwroot.'/mod/assignment/view.php?id='.$cm->id);
    $assignmentinstance->view_footer();
    
    add_to_log($course->id, 'assignment', 'view receipt', 'receipt.php?id='.$cm->id.'&amp;submission='.$submission->id, $cm->instance, $cm->id);
?>