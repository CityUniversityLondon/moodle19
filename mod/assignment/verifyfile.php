<?php  // $Id$

    require_once('../../config.php');
    require_once("lib.php");
    require_once('../../lib/uow-lib.php');
    require_once('verifyfile-form.php');
    
    $id = required_param('id', PARAM_INT);  // Course Module ID

    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $assignment = get_record("assignment", "id", $cm->instance)) {
        error("assignment ID was incorrect");
    }

    if (! $course = get_record("course", "id", $assignment->course)) {
        error("Course is misconfigured");
    }

    require_login($course, true, $cm);
    
    require_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

    require ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");
    $assignmentclass = "assignment_$assignment->assignmenttype";
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);
    $assignmentinstance->view_header();
    
    $mform = new assignment_verifyfile_form(null, array('id' => $id));
    
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot.'/mod/assignment/view.php?id='.$cm->id);
    } elseif ($data = $mform->get_data(false)) {
        $parts = explode('-',$data->receipt);
        $date = '';
        foreach ($parts as $part) {
            $date = $part[4].$date;
        }

        if (strtoupper($data->receipt) == uow_assignment_reciept($_FILES['assignment']['tmp_name'], strtotime($date))) {
            notice_yesno(get_string('validationsuccess', 'assignment'), 'verifyfile.php?id='.$cm->id, $CFG->wwwroot.'/mod/assignment/view.php?id='.$cm->id);
        } else {
            notice_yesno(get_string('validationfailed', 'assignment'), 'verifyfile.php?id='.$cm->id, $CFG->wwwroot.'/mod/assignment/view.php?id='.$cm->id);
        }

    } else {
        $mform->display();
    }
    $assignmentinstance->view_footer();

?>