<?php  // $Id: index.php,v 1.32.2.6 2008/02/26 23:19:05 skodak Exp $

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id',PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_course_login($course);

    add_to_log($course->id, "groupchoice", "view all", "index?id=$course->id", "");

    $strchoice = get_string("modulename", "groupchoice");
    $strchoices = get_string("modulenameplural", "groupchoice");
    $navlinks = array();
    $navlinks[] = array('name' => $strchoices, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple("$strchoices", "", $navigation, "", "", true, "", navmenu($course));


    if (! $choices = get_all_instances_in_course("groupchoice", $course)) {
        notice(get_string('thereareno', 'moodle', $strchoices), "../../course/view.php?id=$course->id");
    }

    $sql = "SELECT cho.id, cho.choiceid, cho.text
              FROM {$CFG->prefix}groupchoice ch, {$CFG->prefix}groupchoice_options cho
             WHERE cho.choiceid = ch.id AND
                   ch.course = $course->id";

    $answers = array () ;
    if (isloggedin() and !isguestuser() and $allanswers = get_records_sql($sql)) {
        foreach ($allanswers as $aa) {
			if(groups_is_member((int)$aa->text))
	            $answers[$aa->choiceid] = groups_get_group_name((int)$aa->text);
        }
        unset($allanswers);
    }


    $timenow = time();

    if ($course->format == "weeks") {
        $table->head  = array (get_string("week"), get_string("question"), get_string("answer"));
        $table->align = array ("center", "left", "left");
    } else if ($course->format == "topics") {
        $table->head  = array (get_string("topic"), get_string("question"), get_string("answer"));
        $table->align = array ("center", "left", "left");
    } else {
        $table->head  = array (get_string("question"), get_string("answer"));
        $table->align = array ("left", "left");
    }

    $currentsection = "";

    foreach ($choices as $choice) {
        if (!empty($answers[$choice->id])) {
            $answer = $answers[$choice->id];
        } else {
            $answer = "";
        }
        $printsection = "";
        if ($choice->section !== $currentsection) {
            if ($choice->section) {
                $printsection = $choice->section;
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $choice->section;
        }
        
        //Calculate the href
        if (!$choice->visible) {
            //Show dimmed if the mod is hidden
            $tt_href = "<a class=\"dimmed\" href=\"view.php?id=$choice->coursemodule\">".format_string($choice->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $tt_href = "<a href=\"view.php?id=$choice->coursemodule\">".format_string($choice->name,true)."</a>";
        }
        if ($course->format == "weeks" || $course->format == "topics") {
            $table->data[] = array ($printsection, $tt_href, $answer);
        } else {
            $table->data[] = array ($tt_href, $answer);
        }
    }
    echo "<br />";
    print_table($table);

    print_footer($course);

?>
