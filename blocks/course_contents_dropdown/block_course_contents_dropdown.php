<?PHP //$Id$

/**
 * Block course_contents_dropdown - generates a course contents based on the section descriptions
 *
 * @uses block_base
 * @package block_course_contents_dropdown
 * @version $Id$
 * @copyright 2009
 * @author David Mudrak <david.mudrak@gmail.com> with edits by Amanda Doughty
 * @license GNU Public License {@link http://www.gnu.org/copyleft/gpl.html}
 */
class block_course_contents_dropdown extends block_base {

    function init() {
        $this->title = get_string('blockname', 'block_course_contents_dropdown');
        $this->version = 2010072800;
    }

    function applicable_formats() {
        return array('course' => true);
    }

    function get_content() {
        global $CFG, $USER, $COURSE;

        $highlight = 0;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text   = '';

        if (empty($this->instance->pageid)) { // sticky
            if (!empty($COURSE)) {
                $this->instance->pageid = $COURSE->id;
            }
        }

        if (empty($this->instance)) {
            return $this->content;
        }

        if ($this->instance->pageid == $COURSE->id) {
            $course = $COURSE;
        } else {
            $course = get_record('course', 'id', $this->instance->pageid);
        }
        $context = get_context_instance(CONTEXT_COURSE, $course->id);

        if ($course->format == 'weeks' or $course->format == 'weekscss') {
            $highlight = ceil((time()-$course->startdate)/604800);
            $linktext = get_string('jumptocurrentweek', 'block_course_contents_dropdown');
            $sectionname = 'week';
        }
        else if ($course->format == 'topics') {
            $highlight = $course->marker;
            $linktext = get_string('jumptocurrenttopic', 'block_course_contents_dropdown');
            $sectionname = 'topic';
        }

        if (!empty($USER->id)) {
            $display = get_field('course_display', 'display', 'course', $this->instance->pageid, 'userid', $USER->id);
        }
        if (!empty($display)) {
            $link = $CFG->wwwroot.'/course/view.php?id='.$this->instance->pageid.'&amp;'.$sectionname.'=';
        } else {
            $link = $CFG->wwwroot.'/course/view.php?id='.$this->instance->pageid.'#section-';
        }

        $sql = "SELECT section, summary, visible
                  FROM {$CFG->prefix}course_sections
                 WHERE course = $course->id AND
                       section < ".($course->numsections+1)."
              ORDER BY section";

        if ($sections = get_records_sql($sql)) {
            $text = "<form action=\"{$CFG->wwwroot}/course/jumpto.php\" method=\"get\" id=\"content_dropdown_form\">
                        <div>
                            <select onchange=\"self.location=document.getElementById('content_dropdown_form').jump.options[document.getElementById('content_dropdown_form').jump.selectedIndex].value;\" id=\"dropdown_select\" name=\"jump\">
                                <option class=\"section-item value=\"javascript:void(0)\">Jump to...</option>\n";

            foreach ($sections as $section) {
                $i = $section->section;
                if (!isset($sections[$i]) or ($i == 0)) {
                    continue;
                }
                $isvisible = $sections[$i]->visible;
                if (!$isvisible and !has_capability('moodle/course:update', $context)) {
                    continue;
                }
                $title = $this->extract_title($section->summary);
                if (empty($title)) {
                    $title = get_string('emptysummary', 'block_course_contents_dropdown', $i);
                }
                $style = ($isvisible) ? '' : ' dimmed';
                $odd = $i % 2;
                if ($i == $highlight) {
                    $text .= "<option name =\"id\" class=\"section-item current r$odd $style\" value=\"$link$i\">";
                } else {
                    $text .= "<option name =\"id\" class=\"section-item r$odd $style\" value=\"$link$i\">";
                }

                $n = 20;
                if (strlen($title) > $n)   {
                    $title = substr($title, 0, $n-3) . "...";
                }

                $text .= "<span class=\"section-number\">$i </span>";
                $text .= "<span class=\"section-title\">$title</span>";

                $text .= "</option>\n";
            }
            $text .= "
                           </select>\n<input type=\"hidden\" name=\"sesskey\" value=\"{$USER->sesskey}\" /><br />\n
                                <div id=\"noscript_content_dropdown_form\" style=\"display: inline;\">
                                    <input value=\"Go\" type=\"submit\">
                                </div>
                            <script type=\"text/javascript\">
                                //<![CDATA[
                                    document.getElementById(\"noscript_content_dropdown_form\").style.display = \"none\";
                                //]]>
                            </script>
                       </div>
                      
                   </form>\n";

            if ($highlight and isset($sections[$highlight])) {
                $isvisible = $sections[$highlight]->visible;
                if ($isvisible or has_capability('moodle/course:update', $context)) {
                    $style = ($isvisible) ? '' : ' class="dimmed"';
                    $this->content->footer = "<a href=\"$link$highlight\"$style>$linktext</a>";
                }
            }
        }

        $this->content->text = $text;
        return $this->content;
    }



    /**
     * Given a section summary, exctract a text suitable as a section title
     *
     * @param string $summary Section summary as returned from database (no slashes)
     * @return string Section title
     */
    function extract_title($summary) {
        global $CFG;
        require_once($CFG->dirroot.'/blocks/course_contents/lib/simple_html_dom.php');
        
        $node = new simple_html_dom;
        $node->load($summary);
        return $this->_node_plain_text($node);
    }


    /**
     * Recursively find the first suitable plaintext from the HTML DOM.
     *
     * Internal private function called only from {@link extract_title()}
     *
     * @param mixed $node Current root node
     * @access private
     * @return void str
     */
    private function _node_plain_text($node) {
        if ($node->nodetype == HDOM_TYPE_TEXT) {
            $t = trim($node->plaintext);
            if (!empty($t)) {
                return $t;
            }
        }
        $t = '';
        foreach ($node->nodes as $n) {
            $t = $this->_node_plain_text($n);
            if (!empty($t)) {
                break;
            }
        }
        return $t;
    }



}

?>
