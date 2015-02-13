<?php  // $Id: mod.php,v 1.9.2.5 2010/06/12 10:10:49 stronk7 Exp $

    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }

    require_once($CFG->dirroot.'/course/lib.php');
    require_once($CFG->dirroot.'/backup/restorelib.php');

    $syscontext = get_context_instance(CONTEXT_SYSTEM);

    // if we're not a course creator , we can only import from our own courses.
    if (has_capability('moodle/course:create', $syscontext)) {
        $creator = true;
    }

    $strimport = get_string("importdata");

    $tcourseids = '';

    if ($teachers = get_user_capability_course('moodle/course:update')) {
        foreach ($teachers as $teacher) {
            // CMDL-1109 fix for ORA-01795
            if ($teacher->id != $course->id && $teacher->id != SITEID && $teacher->id > 0){
            // end CMDL-1109
                $tcourseids .= $teacher->id.',';
            }
        }
    }

    // CMDL-1109 fix for ORA-01795
    // quick fix by Mike to solve ORA-01795 issue where list exceeds 1000 
    // convert list to array and count itmes
    $tc_array = explode(',',$tcourseids);
    $tc_count = count($tc_array);
    
    if (!empty($tcourseids)) {
      $taught_courses = array();
      if ($tc_count < 1000) { // the usual way
        $tcourseids = substr($tcourseids,0,-1); // removes final ','
        // CMDL-1491 Import from dropdown list is currently long and messy
        $taught_courses = get_records_list('course', 'id', $tcourseids, 'fullname','id,fullname,shortname');
        // end CMDL-1491
      } else { // must be > 1000 items therefore can't use get_records_list
        // going to use the array of ID instead
        foreach ($tc_array as $tc) {
          if ($tc > 0) {
            // CMDL-1491 Import from dropdown list is currently long and messy
            $temp = get_records_list('course', 'id', $tc, 'fullname','id,fullname,shortname'); // an array of objects
            // end CMDL-1491
            $taught_courses[$tc] = $temp[$tc];  
          }
        }        
      }        
    }

    // end fix by mike
    // end CMDL-1109

    if (!empty($creator)) {
        // CMDL-1491 Import from dropdown list is currently long and messy
        $cat_courses = get_courses($course->category, $sort="c.fullname ASC", $fields="c.id, c.fullname, c.shortname");
        // end CMDL-1491
    } else {
        $cat_courses = array();
    }

    print_heading(get_string("importactivities"));

    $options = array();
    foreach ($taught_courses as $tcourse) {
    // CMDL-1491 Import from dropdown list is currently long and messy
        if ($tcourse->id != $course->id && $tcourse->id != SITEID){
            if (preg_match('/\d{4}-\d{2}/', $tcourse->shortname, &$matches, 0, -7)) {
            $options[$matches[0]][$tcourse->id] = format_string($tcourse->fullname);
            } else {
                $options['other'][$tcourse->id] = format_string($tcourse->fullname);
            }
        }
    }
    
    krsort(&$options, SORT_NUMERIC);
    // end CMDL-1491
    if (empty($options) && empty($creator)) {
        notify(get_string('courseimportnotaught'));
        return; // yay , this will pass control back to the file that included or required us.
    }

    // quick forms
    include_once('import_form.php');

    $mform_post = new course_import_activities_form_1($CFG->wwwroot.'/course/import/activities/index.php', array('options'=>$options, 'courseid' => $course->id, 'text'=> get_string('coursestaught')));
    $mform_post ->display();

    unset($options);
    $options = array();

    foreach ($cat_courses as $ccourse) {
    // CMDL-1491 Import from dropdown list is currently long and messy
        if ($ccourse->id != $course->id && $ccourse->id != SITEID){
            if (preg_match('/\d{4}-\d{2}/', $ccourse->shortname, &$matches, 0, -7)) {
            $options[$matches[0]][$ccourse->id] = format_string($ccourse->fullname);
            } else {
                $options['other'][$ccourse->id] = format_string($ccourse->fullname);
            }
        }
    }

    krsort(&$options, SORT_NUMERIC);
    // end CMDl-1491
    $cat = get_record("course_categories","id",$course->category);

    if (count($options) > 0) {
        $mform_post = new course_import_activities_form_1($CFG->wwwroot.'/course/import/activities/index.php', array('options'=>$options, 'courseid' => $course->id, 'text' => get_string('coursescategory')));
        $mform_post ->display();
    }

    if (!empty($creator)) {
        $mform_post = new course_import_activities_form_2($CFG->wwwroot.'/course/import/activities/index.php', array('courseid' => $course->id));
        $mform_post ->display();
    }

    if (!empty($fromcoursesearch) && !empty($creator)) {
        $totalcount = 0;
        $courses = get_courses_search(explode(" ",$fromcoursesearch),"fullname ASC",$page,50,$totalcount);
        if (is_array($courses) and count($courses) > 0) {
            $table->data[] = array('<b>'.get_string('searchresults').'</b>','','');
            foreach ($courses as $scourse) {
                if ($course->id != $scourse->id) {
                    $table->data[] = array('',format_string($scourse->fullname),
                                           '<a href="'.$CFG->wwwroot.'/course/import/activities/index.php?id='.$course->id.'&amp;fromcourse='.$scourse->id.'">'.get_string('usethiscourse').'</a>');
                }
            }
        }
        else {
            $table->data[] = array('',get_string('noresults'),'');
        }
    }
    if (!empty($table)) {
        print_table($table);
    }
?>
