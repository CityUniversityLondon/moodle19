<?php


define("AJAX_SECURITY", 1000); // for ajax content
define("AJAJ_SECURITY", 1001); // for json content

/**
 * final ajax security check result object
 */
class ajax_security_result{
    var $mode;
    var $success;
    var $messages;
    var $checktype;
    var $checkval;
    protected $output; // (ajax or json string)
    
    /**
     * constructor
     * @param integer $mode
     * @param boolean $success
     * @param null|array $messages
     * @param string checktype - string identifying type of check (e.g. require_login)
     * @param string checkval - value that was checked
     */
    function __construct($mode=AJAX_SECURITY, $success=false, $messages=null, $checktype='', $checkval=''){
        // init variables
        $this->mode=$mode;
        $this->success=$success;
        $this->messages=$messages;
        $this->checktype=$checktype;
        $this->checkval=$checkval;
        $this->output='';
        $this->set_output();
    }
    
    /**
     * factory method
     * @param integer $mode
     * @param boolean $success
     * @param null|array $messages
     */
    public static function factory ($mode=AJAX_SECURITY, $success=false, $messages=null, $checktype=''){
        return new ajax_security_result($mode=AJAX_SECURITY, $success=false, $messages=null, $checktype='');
    }

    /**
     * accessor method for output property
     * @return string
     */
    public function output(){
        return ($this->output);
    }

    /**
     * set method for output property
     * @return void
     */
    protected function set_output(){
        if ($this->mode==AJAX_SECURITY){
            $this->output='<security_check>';
            $this->output.='<type>'.$this->checktype.'</type>';
            if ($this->success){
                $this->output.='<passed>true</passed>';
            } else {
                $this->output.='<passed>false</passed>';
            }
            if (is_array($this->messages) && !empty($this->messages)){
                $this->output.='<messages>';
                foreach ($this->messages as $key=>$message){
                    $this->output.='<'.strval($key).'>'.strval($message).'</'.strval($key).'>';
                }
                $this->output.='</messages>';
            }
            $this->output.='</security_check>';
        } else {
            $outobj=(object) array('success'=>$this->success, 'messages'=>$this->messages, 'type'=>$this->checktype);
            $this->output=json_encode($outobj);
        }
    }
    
}

/**
 * ajax security
 */
class ajax_security{

    var $mode;

    function __construct($mode=AJAX_SECURITY){
        $this->mode=$mode;
    }

    /**
     * return final ajax security check result object
     * @param boolean $success
     * @param null|array $messages
     */
    protected function result($success=false, $messages=null, $type='', $val=''){
        $retobj=new ajax_security_result($this->mode, $success, $messages, $type, $val);
        return ($retobj);
    }

    /**
     * Addresses security issues reported by Peter Skodr for enhanced file module
     * AJAX version of moodlelib function require_login
     * @uses $CFG
     * @uses $SESSION
     * @uses $USER
     * @uses $FULLME
     * @uses SITEID
     * @uses $COURSE
     * @param mixed $courseorid id of the course or course object
     * @param bool $autologinguest
     * @param object $cm course module object
     * @param bool $setwantsurltome Define if we want to set $SESSION->wantsurl, defaults to
     *             true. Used to avoid (=false) some scripts (file.php...) to set that variable,
     *             in order to keep redirects working properly. MDL-14495
     */
    public function valid_login($courseorid=0, $autologinguest=true, $cm=null, $setwantsurltome=true) {

        global $CFG, $SESSION, $USER, $COURSE, $FULLME;

        // setup global $COURSE, themes, language and locale
        course_setup($courseorid);

        // check logged in
        if (!isloggedin()) {
            return ($this->result(false, array('critical_error'=>get_string('loggedinnot')), 'valid_login'));
        }

        //  warn user to terminate logged in as session if logged in as another user
        if ($COURSE->id != SITEID and !empty($USER->realuser)) {
            if ($USER->loginascontext->contextlevel == CONTEXT_COURSE) {
                if ($USER->loginascontext->instanceid != $COURSE->id) {
                    return ($this->result(false, array('critical_error'=>get_string('loginasonecourse','error')), 'valid_login'));
                }
            }
        }

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);


        // make sure user can view course
        if (has_capability('moodle/course:view', $context)) {
            if (!empty($USER->realuser)) {   // Make sure the REAL person can also access this course
                if (!has_capability('moodle/course:view', $context, $USER->realuser)) {
                    return ($this->result(false, array('critical_error'=>get_string('studentnotallowed', '', fullname($USER, true))), 'valid_login'));
                }
            }
        }

        // Check that the user account is properly set up
        if (user_not_fully_set_up($USER)) {
            return ($this->result(false, array('critical_error'=>get_string('useraccountnotsetup', 'enhancedfile', fullname($USER, true))), 'valid_login'));
        }

        // Make sure current IP matches the one for this session (if required)
        if (!empty($CFG->tracksessionip)) {
            if ($USER->sessionIP != md5(getremoteaddr())) {
                 return ($this->result(false, array('critical_error'=>get_string('sessionipnomatch', 'error')), 'valid_login'));
            }
        }

        // Check that the user has agreed to a site policy if there is one
        if (!empty($CFG->sitepolicy)) {
            if (!$USER->policyagreed) {
                return ($this->result(false, array('critical_error'=>get_string('userpolicynotagreed', 'enhancedfile', fullname($USER, true))), 'valid_login'));
            }
        }

        // Fetch the system context
        $sysctx = get_context_instance(CONTEXT_SYSTEM);
        // If the site is currently under maintenance, then print a message
        if (!has_capability('moodle/site:config', $sysctx)) {
            if (file_exists($CFG->dataroot.'/'.SITEID.'/maintenance.html')) {
                return ($this->result(false, array('critical_error'=>get_string('sitemaintenancemode', 'enhancedfile', fullname($USER, true))), 'valid_login'));
            }
        }

        // Check user has capability to view course
        if (has_capability('moodle/course:view', $context)) {
            if (!empty($USER->realuser)) {   // Make sure the REAL person can also access this course
                if (!has_capability('moodle/course:view', $context, $USER->realuser)) {
                     return ($this->result(false, array('critical_error'=>get_string('studentnotallowed', '', fullname($USER, true))), 'valid_login'));
                }
            }
        }

        return ($this->result(true, null, 'valid_login'));
    }

   /**
    * Check user has capbaility
    * @param string $capability - name of the capability
    * @param object $context - a context object (record from context table)
    * @param integer $userid - a userid number
    * @param bool $doanything - if false, ignore do anything
    */
    public function has_capability($capability, $context, $userid=NULL, $doanything=true){
        if (!has_capability($capability, $context, $userid, $doanything)){
            return ($this->result(false, array('critical_error'=>get_capability_string($capability)), 'has_capability', $capability));
        }

        return ($this->result(true, null, 'has_capability', $capability));
    }
}

?>
