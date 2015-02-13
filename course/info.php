<?php // $Id: info.php,v 1.17.2.7 2008/09/01 09:30:07 mudrd8mz Exp $

/// Displays external information about a course

    require_once("../config.php");
    require_once("lib.php");

    $id   = optional_param('id', false, PARAM_INT); // Course id
    $name = optional_param('name', false, PARAM_RAW); // Course short name

    if (!$id and !$name) {
        error("Must specify course id or short name");
    }

    if ($name) {
        if (! $course = get_record("course", "shortname", $name) ) {
            error("That's an invalid short course name");
        }
    } else {
        if (! $course = get_record("course", "id", $id) ) {
            error("That's an invalid course id");
        }
    }

    $site = get_site();

    if ($CFG->forcelogin) {
        require_login();
    }
    
    $context = get_context_instance(CONTEXT_COURSE, $course->id); 
    if ((!course_parent_visible($course) || (! $course->visible)) && !has_capability('moodle/course:viewhiddencourses', $context)) {
        print_error('coursehidden', '', $CFG->wwwroot .'/'); 
    }  
    
    print_header(get_string("summaryof", "", $course->fullname));

    print_heading(format_string($course->fullname) . '<br />(' . format_string($course->shortname) . ')');

    if ($course->guest || $course->password) {
        print_box_start('generalbox icons');
        if ($course->guest) {
            $strallowguests = get_string('allowguests');
            echo "<div><img alt=\"\" class=\"icon guest\" src=\"$CFG->pixpath/i/guest.gif\" />&nbsp;$strallowguests</div>";
        }
        if ($course->password) {
            $strrequireskey = get_string('requireskey');
            echo "<div><img alt=\"\" class=\"icon key\" src=\"$CFG->pixpath/i/key.gif\" />&nbsp;$strrequireskey</div>";
        }
        print_box_end();
    }


    print_box_start('generalbox info');

    echo filter_text(text_to_html($course->summary),$course->id);

    // ALAN Display number of places left on the course (Martin Mandausch suggested adding this here)
    if (!empty($course->enrolmax)) {
        $availableenrolments = $course->enrolmax - count_course_students($course);
        if ($availableenrolments > 0) {
            print_string('availableenrolments');
            echo ': ';
            echo $availableenrolments;
        }
        else {
            print_string('availableenrolmentsnone');
        }
    }
	else { // BRUCE/ALAN
		print_string('availableenrolments');
		echo ': ';
		print_string('availableenrolmentsunlimited');
	}
	echo '<br />';
	// END ALAN
    
    if ($managerroles = get_config('', 'coursemanager')) {
        $coursemanagerroles = split(',', $managerroles);
        foreach ($coursemanagerroles as $roleid) {
            $role = get_record('role','id',$roleid);
            $canseehidden = has_capability('moodle/role:viewhiddenassigns', $context);
            $roleid = (int) $roleid;
            // CMDL-1111 fix participant sorts to be case insensitive
            if ($users = get_role_users($roleid, $context, true, '', 'LOWER(u.lastname) ASC', $canseehidden)) {
            // end CMDL-1111
                foreach ($users as $teacher) {
                    $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $context)); 
                    $namesarray[] = format_string(role_get_name($role, $context)).': <a href="'.$CFG->wwwroot.'/user/view.php?id='.
                                    $teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
                }
            }          
        }
        
        if (!empty($namesarray)) {
            echo "<ul class=\"teachers\">\n<li>";
            echo implode('</li><li>', $namesarray);
            echo "</li></ul>";
        }
    }

    require_once("$CFG->dirroot/enrol/enrol.class.php");
    $enrol = enrolment_factory::factory($course->enrol);
    echo $enrol->get_access_icons($course);

    print_box_end();

    echo "<br />";

    close_window_button();

    print_footer();

?>
