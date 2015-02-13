<?php  // $Id: view.php,v 1.102.2.9 2009/02/09 09:52:59 danmarsden Exp $

    require_once("../../config.php");
    require_once("lib.php");
    require_once("../../group/lib.php");

    $id         = required_param('id', PARAM_INT);                 // Course Module ID
    $action     = optional_param('action', '', PARAM_ALPHA);
    $attemptids = optional_param('attemptid', array(), PARAM_INT); // array of attempt ids for delete action
    $groupid    = optional_param('groupid', PARAM_INT);

    if (! $cm = get_coursemodule_from_id('groupchoice', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }

    require_course_login($course, false, $cm);

    if (!$choice = groupchoice_get_choice($cm->instance)) {
        error("Course module is incorrect");
    }
    
    $strchoice = get_string('modulename', 'groupchoice');
    $strchoices = get_string('modulenameplural', 'groupchoice');

    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        print_error('badcontext');
    }

    if ($action == 'delchoice') {
        if ($answer = get_record('groups_members', 'groupid', $groupid, 'userid', $USER->id)) {
            groups_remove_member($groupid, $USER->id);
        }
    }
    $navigation = build_navigation('', $cm);
    print_header_simple(format_string($choice->name), "", $navigation, "", "", true,
    update_module_button($cm->id, $course->id, $strchoice), navmenu($course, $cm));

/// Submit any new data if there is any
    if ($form = data_submitted() && has_capability('mod/groupchoice:choose', $context)) {
        $timenow = time();

        if (has_capability('mod/groupchoice:deleteresponses', $context)) {
            if ($action == 'delete') { //some responses need to be deleted
               groupchoice_delete_responses($attemptids, $choice); //delete responses.
               redirect("view.php?id={$cm->id}");
            }
        }
        $answer = optional_param('answer', '', PARAM_INT); 

        if (empty($answer)) {
            redirect("{$CFG->wwwroot}/mod/groupchoice/view.php?id=$cm->id", get_string('mustchooseone',"groupchoice"));
        } else {
            groupchoice_user_submit_response($answer, $choice, $USER->id, $course->id, $cm);
        }
        notify(get_string('choicesaved',"groupchoice"),'notifysuccess');
    }


/// Display the choice and possibly results
    add_to_log($course->id, "groupchoice", "view", "{$CFG->wwwroot}/mod/groupchoice/view.php?id=$cm->id", $choice->id, $cm->id);

    /// Check to see if groups are being used in this choice
    $groupmode = groups_get_activity_groupmode($cm);
    
    if ($groupmode) {
        groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, "{$CFG->wwwroot}/mod/groupchoice/view.php?id=$id");
    }

	$allresponses = groupchoice_get_response_data($choice, $cm, $groupmode);   // Big function, approx 6 SQL calls per user

    if (has_capability('mod/groupchoice:readresponses', $context)) {
        groupchoice_show_reportlink($cm);
    }    

    echo '<div class="clearer"></div>';

    if ($choice->text) {
        print_box(format_text($choice->text, $choice->format), 'generalbox', 'intro');
    }
//
    $current = false;  // Initialise for later

    $usergroups = groupchoice_get_membership($choice);
    
    if (!empty($usergroups)) {
        $current = true;
    }
////
//	foreach($choice->option as $key => $val)
//	{
//		if(groups_is_member((int)$val))
//		{
//			$current = (int)$val;
//			break;
//		}
//	}
    
    //if user has already made a selection, and they are not allowed to update it, show their selected answer.
//    if (!empty($USER->id) && $current &&
//        empty($choice->allowupdate) ) {
//
//        // code below needs changing
//        print_simple_box(get_string("yourselection","groupchoice", userdate($choice->timeopen)).": ".format_string(groupchoice_get_option_text($choice, $current->optionid)), "center");
//    }

/// Print the form
    $choiceopen = true;
    $timenow = time();
    if ($choice->timeclose !=0) {
        if ($choice->timeopen > $timenow ) {
            print_simple_box(get_string("notopenyet","groupchoice", userdate($choice->timeopen)), "center");
            print_footer($course);
            exit;
        } else if ($timenow > $choice->timeclose) {
            print_simple_box(get_string("expired","groupchoice", userdate($choice->timeclose)), "center");
            $choiceopen = false;
        }
    }


    // They haven't made their choice yet or updates allowed and choice is open
    if ($choiceopen and has_capability('mod/choice:choose', $context) ) {

        echo '<form id="form" method="post" action="view.php">';        

        groupchoice_show_form($choice, $USER, $cm, $usergroups);

        echo '</form>';

        $choiceformshown = true;
    } else {
        $choiceformshown = false;
    }



    if (!$choiceformshown) {

        $sitecontext = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('moodle/legacy:guest', $sitecontext, NULL, false)) {      // Guest on whole site
            $wwwroot = $CFG->wwwroot.'/login/index.php';
            if (!empty($CFG->loginhttps)) {
                $wwwroot = str_replace('http:','https:', $wwwroot);
            }
            notice_yesno(get_string('noguestchoose',"groupchoice").'<br /><br />'.get_string('liketologin'),
                         $wwwroot, $_SERVER['HTTP_REFERER']);

        } else if (has_capability('moodle/legacy:guest', $context, NULL, false)) {   // Guest in this course only
            $SESSION->wantsurl = $FULLME;
            $SESSION->enrolcancel = $_SERVER['HTTP_REFERER'];

            print_simple_box_start('center', '60%', '', 5, 'generalbox', 'notice');
            echo '<p align="center">'. get_string('noguestchoose',"groupchoice") .'</p>';
            echo '<div class="continuebutton">';
            print_single_button($CFG->wwwroot.'/course/enrol.php?id='.$course->id, NULL, 
                                get_string('enrolme', '', format_string($course->shortname)), 'post', $CFG->framename);
            echo '</div>'."\n";
            print_simple_box_end();

        }
    }

    // print the results at the bottom of the screen

    if ( $choice->showresults == GROUPCHOICE_SHOWRESULTS_ALWAYS or
        ($choice->showresults == GROUPCHOICE_SHOWRESULTS_AFTER_ANSWER and $current ) or
        ($choice->showresults == GROUPCHOICE_SHOWRESULTS_AFTER_CLOSE and !$choiceopen ) )  {

        groupchoice_show_results($choice, $course, $cm, $allresponses); //show table with students responses.

    } else if (!$choiceformshown) {
        print_simple_box(get_string('noresultsviewable',"groupchoice"), 'center');
    } 


    print_footer($course);


?>
