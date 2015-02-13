<?php
function get_course_data(){
    global $CFG, $USER;

    $courses = get_my_courses($USER->id, 'visible DESC,sortorder ASC', '*', false, $courses_limit);

    return $courses;

}
?>
