<?PHP //$Id: block_courses_dropdown.php,v 1.46.2.6 2010/06/09 04:23:38 mikehughes Exp $

include_once($CFG->dirroot . '/course/lib.php');

class block_courses_dropdown extends block_base {
    function init() {
        $this->title = 'Dropdown Course List';
        $this->version = 2010060900;
    }
    
    function has_config() {
        return false;
    }

    function get_content() {
        global $THEME, $CFG, $USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->icons = array();
        $this->content->footer = '';
        $this->content->text = '';

        if ($courses = get_my_courses($USER->id, 'visible DESC, fullname ASC')) {
            $this->content->text = "<form action=\"{$CFG->wwwroot}/course/jumpto.php\" method=\"get\" id=\"dropdown_form\">
              <div><select onchange=\"self.location=document.getElementById('dropdown_form').jump.options[document.getElementById('dropdown_form').jump.selectedIndex].value;\" id=\"dropdown_select\" name=\"jump\"><option value=\"javascript:void(0)\">Jump to...</option>\n";
            $n = 20; // max length of course name in dropdown block
            foreach ($courses as $course) {
                if ($course->id == SITEID) {
                    continue;
                }
            // CMDL-1507 Sorting the My Courses block drop-down list by academic years
                if (preg_match('/\d{4}-\d{2}/', $course->shortname, &$matches, 0, -7)) {
                    $years[$matches[0]][] = $course;
                } else {
                    $years['other'][] = $course;
                }
            }
            krsort(&$years, SORT_NUMERIC);

            foreach ($years as $year=>$courses) {
                $this->content->text = $this->content->text . '<optgroup label="' . $year . '">';
                foreach ($courses as $course) {
                    // end CMDL-1507
                    if (strlen($course->fullname) > $n)   {
                      $this->content->text = $this->content->text . "<option name=\"id\" value=\"{$CFG->wwwroot}/course/view.php?sesskey={$USER->sesskey}&amp;id={$course->id}\">" . substr($course->fullname,0,$n-3) . "...</option>\n";
                    } else {
                      $this->content->text = $this->content->text . "<option name=\"id\" value=\"{$CFG->wwwroot}/course/view.php?sesskey={$USER->sesskey}&amp;id={$course->id}\">" . $course->fullname . "</option>\n";
                    }
                }
                $this->content->text = $this->content->text . '<\optgroup>';
            }
            // end CMDL-1507

            $this->content->text = $this->content->text . "</select>\n<input type=\"hidden\" name=\"sesskey\" value=\"{$USER->sesskey}\" /><br />\n
            <div id=\"noscriptdropdown_form\" style=\"display: inline;\"><input value=\"Go\" type=\"submit\"></div>
            <script type=\"text/javascript\">
//<![CDATA[
document.getElementById(\"noscriptdropdown_form\").style.display = \"none\";
//]]>
</script></div></div>
</form>\n";
            $this->title = get_string('mycourses');
        }
        
        return $this->content;
    }
}

?>