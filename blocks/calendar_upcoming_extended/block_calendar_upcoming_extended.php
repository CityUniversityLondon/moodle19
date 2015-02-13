<?PHP //$Id: block_calendar_upcoming.php,v 1.27.2.3 2008/04/17 19:19:11 skodak Exp $

class block_calendar_upcoming_extended extends block_base {
    function init() {
        $this->title = get_string('allupcomingevents', 'block_calendar_upcoming_extended');
        $this->version = 2007101509;

    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }

    function specialization() {
        
        if (empty($this->config->pageid)) {
            $this->config->pageid = $this->instance->pageid;
        }
        
        if (empty($this->config->title)) {
            $this->config->title = $this->title = get_string('blockcontentstitle','block_calendar_upcoming_extended');
        } else {
            $this->title = $this->config->title;
        }

        if (empty ($this->config->courseid)) {
           $this->config->courseid = SITEID;
        } else if (! $course = get_record('course', 'id', $this->config->courseid)) {
           notify(get_string('invalidid','block_calendar_upcoming_extended'), 'errorbox');
        }
    }

    function get_content() {
        global $USER, $CFG, $SESSION, $COURSE;
        $cal_m = optional_param( 'cal_m', 0, PARAM_INT );
        $cal_y = optional_param( 'cal_y', 0, PARAM_INT );

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }
        // Reset the session variables
        calendar_session_vars($COURSE);
        $this->content = new stdClass;
        $this->content->text = '';

        if (empty($this->instance)) { // Overrides: use no course at all

            $courseshown = false;
            $filtercourse = array();
            $this->content->footer = '';

        } else {
            $courseshown = $this->config->courseid;
            $this->content->footer = '<br /><a href="'.$CFG->wwwroot.
                                     '/calendar/view.php?view=upcoming&amp;course='.$courseshown.'">'.
                                      get_string('gotocalendar', 'calendar').'</a>...';
            $context = get_context_instance(CONTEXT_COURSE, $courseshown);
            if (has_capability('moodle/calendar:manageentries', $context) ||
                has_capability('moodle/calendar:manageownentries', $context)) {
                $this->content->footer .= '<br /><a href="'.$CFG->wwwroot.
                                          '/calendar/event.php?action=new&amp;course='.$courseshown.'">'.
                                           get_string('newevent', 'calendar').'</a>...';
            }
            if ($courseshown == SITEID) {
                // Being displayed at site level. This will cause the filter to fall back to auto-detecting
                // the list of courses it will be grabbing events from.
                $filtercourse    = NULL;
                $groupeventsfrom = NULL;
                $SESSION->cal_courses_shown = calendar_get_default_courses(true);
                calendar_set_referring_course(0);
            } else {
                // Forcibly filter events to include only those from the particular course we are in.
                $filtercourse    = array($courseshown => $COURSE);
                $groupeventsfrom = array($courseshown => 1);
            }
        }

        // We 'll need this later
        calendar_set_referring_course($courseshown);

        // Be VERY careful with the format for default courses arguments!
        // Correct formatting is [courseid] => 1 to be concise with moodlelib.php functions.

        calendar_set_filters($courses, $group, $user, $filtercourse, $groupeventsfrom, false);

        // The upcoming events block for the course will include the global and
        // user events. We do not want to show these twice. We remove them if
        // the block has not been set to show 'All'. This means that if the regular
        // upcoming events block is used, then this block will not duplicate
        // events. However this block can be used to show 'All' events. In
        // which case the original upcoming events block may not be required.
        if ($courseshown != SITEID) {
            $site = array_search(SITEID, $courses);
            unset($courses[$site]);
            $user = null;
        }

        $events = calendar_get_upcoming($courses, $group, $user,
                                        get_user_preferences('calendar_lookahead', CALENDAR_UPCOMING_DAYS),
                                        get_user_preferences('calendar_maxevents', CALENDAR_UPCOMING_MAXEVENTS));

        if (!empty($this->instance)) {
            $this->content->text = calendar_get_sideblock_upcoming($events,
                                   'view.php?view=day&amp;course='.$courseshown.'&amp;');
        }

        if (empty($this->content->text) || ($courseshown != SITEID && !has_capability('moodle/course:view', $context))) {
            $this->content = null;
        }

        return $this->content;
    }
    
    function backuprestore_instancedata_used() {
        return true;
    }

 
    function instance_restore ($restore, $data) {
        global $CFG;
        
        $sql = "SELECT bi.*
                    FROM {$CFG->prefix}block_instance bi
                    JOIN {$CFG->prefix}block b ON b.id = bi.blockid
                    WHERE b.name = 'calendar_upcoming_extended' 
                    AND bi.id = $data->new_id";

        if ($instance = get_record_sql($sql)) {
            $blockobject = block_instance('calendar_upcoming_extended', $instance);

            if ($blockobject->config->courseid == $blockobject->config->pageid){
                $blockobject->config->courseid = $restore->course_id;
            }

            $blockobject->config->pageid = $restore->course_id;
            $blockobject->instance_config_commit($blockobject->pinned);
        }

        return true;
    }    
}

?>
