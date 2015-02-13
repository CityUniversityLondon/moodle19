<?php 

class block_related_courses extends block_list {
    
    function init() {
        $this->title = get_string('relatedcourses', 'block_related_courses');
        $this->version = 2004111200;
    }

    function get_content() {
        global $CFG, $course;
        
        //print_object($course);
        
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        
        // Following the explanation in http://docs.moodle.org/en/Metacourses
        if ($course->metacourse) {
        // you are in a metacourse, you are looking for the parent
            if($children = get_courses_in_metacourse($course->id)) {
                $courseA = array();
                foreach ($children as $courseA) {
                    $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/course.gif" height="16" width="16" alt="'.get_string('notHiddenCourses', "block_counters").'" />';
                    $this->content->items[] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseA->id.'">'. format_string($courseA->fullname).'</a><br />';
                }
            }
        } else {
        // you are in a course, you are looking for the children
            $sql = "SELECT c.id,c.fullname
                        FROM {$CFG->prefix}course c, {$CFG->prefix}course_meta m
                        WHERE c.id = m.parent_course
                        AND m.child_course = $course->id
                        ORDER BY c.fullname ASC";
            $courseslist = get_records_sql($sql);
            foreach ($courseslist as $thiscourse) {
                $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/course.gif" height="16" width="16" alt="'.get_string('notHiddenCourses', "block_counters").'" />';
                $this->content->items[] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$thiscourse->id.'">'. format_string($thiscourse->fullname).'</a><br />';
            }
        }
        $this->content->footer = '';
    }
}

?>