<?PHP //$Id$

/**
 * Block course_contents_extended - generates a course contents based on the section descriptions
 * 
 * @uses block_base
 * @package block_course_contents_extended
 * @version $Id$
 * @copyright 2009
 * @author David Mudrak <david.mudrak@gmail.com> 
 * @license GNU Public License {@link http://www.gnu.org/copyleft/gpl.html}
 */
class block_course_contents_extended extends block_base {

    function init() {
        $this->title = get_string('blockname', 'block_course_contents_extended');
        $this->version = 2010080200;
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
            $this->config->title = $this->title = get_string('blockcontentstitle','block_course_contents_extended');
        } else {
            $this->title = $this->config->title;
        }

        if (empty($this->config->blockstyle)) {
            $this->config->blockstyle = 0;
        }

        if (empty ($this->config->courseid)) {
            $this->config->courseid = $this->instance->pageid; 
        } else if (!$course = get_record('course', 'id', $this->config->courseid)) {
            notify(get_string('invalidid','block_course_contents_extended'), 'errorbox');
        }        

    }

    function applicable_formats() {
        return array('course' => true);
    }

    function get_content() {
        global $CFG, $USER, $COURSE;

        if(isset($this->config)){
            $config = $this->config;
            } else{
                $config = get_config('blocks/block_course_contents_extended');
        }

        $this->courseid = $this->config->courseid? $config->courseid : ($this->instance->pageid? $this->instance->pageid : $COURSE->id);

        $context = get_context_instance(CONTEXT_COURSE, $this->courseid);

        if (has_capability('moodle/course:view', $context)) {            

            $this->highlight = 0;

            if ($this->content !== NULL) {
                return $this->content;
            }

            $this->content = new stdClass;
            $this->content->footer = '';
            $this->content->text   = '';

            if (empty($this->instance)) {
                return $this->content;
            }

            if ($this->courseid == $COURSE->id) {
                $course = $COURSE;
            } else {
                $course = get_record('course', 'id', $this->courseid);
            }
            $this->context = get_context_instance(CONTEXT_COURSE, $course->id);

            if ($course->format == 'weeks' or $course->format == 'weekscss') {
                $this->highlight = ceil((time()-$course->startdate)/604800);
                $this->linktext = get_string('jumptocurrentweek', 'block_course_contents_extended');
                $sectionname = 'week';
            }
            else if ($course->format == 'topics') {
                $this->highlight = $course->marker;
                $this->linktext = get_string('jumptocurrenttopic', 'block_course_contents_extended');
                $sectionname = 'topic';
            }

            if (!empty($USER->id)) {
                $display = get_field('course_display', 'display', 'course', $this->courseid, 'userid', $USER->id);
            }
            if (!empty($display)) {
                $this->link = $CFG->wwwroot.'/course/view.php?id='.$this->courseid.'&amp;'.$sectionname.'=';
            } else {
                $this->link = $CFG->wwwroot.'/course/view.php?id='.$this->courseid.'#section-';
            }

            $sql = "SELECT section, summary, visible
                      FROM {$CFG->prefix}course_sections
                     WHERE course = $course->id AND
                           section < ".($course->numsections+1)."
                  ORDER BY section";

            if ($this->sections = get_records_sql($sql)) {

                if ($config->blockstyle == 1) {
                    $text = $this->get_content_dropdown();
                } else {
                    $text = $this->get_content_list();
                }
            }

            $this->content->text = $text;
            return $this->content;
        } //if
    }


    /**
     * Create the content in list format
     *
     * @return string $text
     */
    function get_content_list() {

        $text = '<ul class="section-list">';

        foreach ($this->sections as $section) {
            $i = $section->section;
            if (!isset($this->sections[$i]) or ($i == 0)) {
                continue;
            }
            $isvisible = $this->sections[$i]->visible;
            if (!$isvisible and !has_capability('moodle/course:update', $this->context)) {
                continue;
            }
            $title = $this->extract_title($section->summary);
            if (empty($title)) {
                $title = get_string('emptysummary', 'block_course_contents_extended', $i);
            }
            $style = ($isvisible) ? '' : ' class="dimmed"';
            $odd = $i % 2;
            if ($i == $this->highlight) {
                $text .= "<li class=\"section-item current r$odd\">";
            } else {
                $text .= "<li class=\"section-item r$odd\">";
            }
            $text .= "<a href=\"$this->link$i\"$style>";
            $text .= "<span class=\"section-number\">$i </span>";
            $text .= "<span class=\"section-title\">$title</span>";
            $text .= "</a>";
            $text .= "</li>\n";
        }
        $text .= '</ul>';

        if ($this->highlight and isset($this->sections[$this->highlight])) {
            $isvisible = $this->sections[$this->highlight]->visible;
            if ($isvisible or has_capability('moodle/course:update', $this->context)) {
                $style = ($isvisible) ? '' : ' class="dimmed"';
                $this->content->footer = "<a href=\"$this->link$this->highlight\"$style>$this->linktext</a>";
            }
        }
        return $text;

    }


 /**
     * Create the content in dropdown format
     *
     * @return string $text
     */
    function get_content_dropdown() {

        global $CFG, $USER, $COURSE;
        
        $text = "<form action=\"{$CFG->wwwroot}/course/jumpto.php\" method=\"get\" id=\"content_extended_form{$this->instance->id}\">
                    <div>
                        <select onchange=\"self.location=document.getElementById('content_extended_form{$this->instance->id}').jump.options[document.getElementById('content_extended_form{$this->instance->id}').jump.selectedIndex].value;\" id=\"extended_select\" name=\"jump\">
                            <option class=\"section-item value=\"javascript:void(0)\">Jump to...</option>\n";

        foreach ($this->sections as $section) {
            $i = $section->section;
            if (!isset($this->sections[$i]) or ($i == 0)) {
                continue;
            }
            $isvisible = $this->sections[$i]->visible;
            if (!$isvisible and !has_capability('moodle/course:update', $this->context)) {
                continue;
            }
            $title = $this->extract_title($section->summary);
            if (empty($title)) {
                $title = get_string('emptysummary', 'block_course_contents_extended', $i);
            }
            $style = ($isvisible) ? '' : ' dimmed';
            $odd = $i % 2;
            if ($i == $this->highlight) {
                $text .= "<option name =\"id\" class=\"section-item current r$odd $style\" value=\"$this->link$i\">";
            } else {
                $text .= "<option name =\"id\" class=\"section-item r$odd $style\" value=\"$this->link$i\">";
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
                            <div id=\"noscript_content_extended_form{$this->instance->id}\" style=\"display: inline;\">
                                <input value=\"Go\" type=\"submit\">
                            </div>
                        <script type=\"text/javascript\">
                            //<![CDATA[
                                document.getElementById(\"noscript_content_extended_form{$this->instance->id}\").style.display = \"none\";
                            //]]>
                        </script>
                   </div>

               </form>\n";

        if ($this->highlight and isset($this->sections[$this->highlight])) {
            $isvisible = $this->sections[$this->highlight]->visible;
            if ($isvisible or has_capability('moodle/course:update', $this->context)) {
                $style = ($isvisible) ? '' : ' class="dimmed"';
                $this->content->footer = "<a href=\"$this->link$this->highlight\"$style>$this->linktext</a>";
            }
        }
        return $text;

    }



    
    /**
     * Given a section summary, exctract a text suitable as a section title
     * 
     * @param string $summary Section summary as returned from database (no slashes)
     * @return string Section title
     */
    function extract_title($summary) {
        global $CFG;
        require_once(dirname(__FILE__).'/lib/simple_html_dom.php');

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

    function backuprestore_instancedata_used() {
        return true;
    }

 
    function instance_restore ($restore, $data) {
        global $CFG;
        
        $sql = "SELECT bi.*
                    FROM {$CFG->prefix}block_instance bi
                    JOIN {$CFG->prefix}block b ON b.id = bi.blockid
                    WHERE b.name = 'course_contents_extended' 
                    AND bi.id = $data->new_id";

        if ($instance = get_record_sql($sql)) {
            $blockobject = block_instance('course_contents_extended', $instance);

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
