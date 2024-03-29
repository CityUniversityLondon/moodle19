<?php

class block_quickcourselist extends block_base {

    function init() {
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2009010601;
        $this->title = get_string('quickcourselist','block_quickcourselist');
        $this->content->footer = '';
    }


    function applicable_formats() {
        if (has_capability('block/quickcourselist:use', get_context_instance(CONTEXT_SYSTEM))) {
            return (array('all' => false, 'site'=>true, 'my'=>true));
        } else {
            return (array('all' => false, 'site'=>true, 'my'=>false));
        }
    }

    function get_content() {
        global $CFG;
        
        $context_system = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('block/quickcourselist:use', $context_system)) {
            $this->content->text="<input type='text' onkeyup='quickcoursesearch()' id='quickcourselistsearch'><br><p id='quickcourselist'>";
            
            
            $query= 'SELECT id,
                    shortname,
                    fullname,
                    CASE WHEN length(shortname) > 20 THEN concat(substr(shortname, 1, 20), \'...\')
                    ELSE shortname
                    END AS trunc_shortname,
                    CASE WHEN length(fullname) > 20 THEN concat(substr(fullname, 1, 20), \'...\')
                    ELSE fullname
                    END AS trunc_fullname
                    FROM ' .$CFG->prefix. 'course WHERE id <>'.SITEID;
            if(!has_capability('moodle/course:viewhiddencourses',$context_system)){$query.=' AND visible=1';}
            

            if(!$courses=get_records_sql($query)){
                $this->content->text=get_string('nocourses','block_quickcourselist');
            }else{
                foreach ($courses as $course) {
                    $this->content->text .= "<a style='display:none' href='$CFG->wwwroot/course/view.php?id=$course->id' title='$course->shortname: $course->fullname'>$course->shortname: $course->trunc_fullname</a>"; // EDITED AD
                }

               $this->content->text .="</p>";
            }
            require_js($CFG->wwwroot.'/blocks/quickcourselist/quickcourselist.js');
            $this->content->text.='<script type="text/javascript">quickcoursesearch();</script>';
        }
        $this->content->footer='';
        return $this->content;
        
    }
}
?>
