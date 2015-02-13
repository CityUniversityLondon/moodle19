<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->libdir.'/uploadlib.php');
require_once($CFG->libdir.'/grade/constants.php');
require_once($CFG->libdir.'/grade/grade_category.php');
require_once($CFG->libdir.'/grade/grade_item.php');
require_once($CFG->dirroot.'/mod/enhancedfile/mod_form.php');
require_once($CFG->dirroot.'/mod/resource/lib.php');
require_once($CFG->dirroot.'/mod/enhancedfile/lib/db.php');
require_once($CFG->dirroot.'/mod/enhancedfile/lib/ajax_security.php');

$maxexec=ini_get('max_execution_time');
if ($maxexec<3600){
	/**
	set max input and execution time to 3600 seconds (1 hour)
	*/
	set_time_limit(3600); 
	ini_set('max_input_time', 3600);
}

class resource_file_upload {    
    
    /**
     * course object
     * @var stdObject
     */
    var $course;
    
    /**
     * section - topic section number NOT section instance id in database
     * @var integer
     */
    var $section;
    
    /**
     * course section
     * @var stdObject
     */
    var $cw;
    
    /**
     * module db row
     * @var stdObject
     */
    var $module;
    
    /**
     * file upload directory
     * @var string
     */
    var $directory;
    
    /**
     * submit mode
     * @var string - either 'form' or 'ajax'
     */
    var $submittype;
    
    /**
     * file array from $_FILES
     * @var array
     */
    var $file;
    
    /**
     * field in $_FILES array
     * @var string
     */
    var $filefield;

    /**
     * flash upload key
     * @var boolean|string
     */
    var $flashupkey=false;


    /**
     * session key
     * @var boolean|string
     */
    var $sesskey=false;

    /**
     * user id
     * @var boolean|string
     */
    var $userid=false;

    /**
     * resource type
     * @var string
     */
    var $resourcetype='files';

    /**
     * is resource visible
     * @var integer
     */
    var $visibility=1;

    /**
     * constructor
     * @return void
     */  
    function __construct(){    
        global $CFG, $SESSION, $USER, $COURSE, $FULLME;

        // get params
        $directory      = optional_param('directory', '~ROOT~', PARAM_TEXT);
        $add            = optional_param('add', 'file', PARAM_TEXT);        
        $submittype     = optional_param('_submittype', 'form', PARAM_ALPHA); // form or ajax
        $visibility     = optional_param('visible', 1, PARAM_INT); // enhancement by Amanda Doughty December 2010 (Changed from required param to optional by GThomas, better for AJAX)
        $this->resourcetype  = optional_param('resourcetype', 'files', PARAM_ALPHA);
        if ($submittype=='form'){
            $courseid       = required_param('courseid', PARAM_INT);
            $section        = required_param('section', PARAM_INT);
        } else {
            $courseid       = optional_param('courseid', false, PARAM_INT);
            $section        = optional_param('section', false, PARAM_INT);
            if ($courseid===false){
                $err='A required parameter (courseid) was missing';
                echo ('<critical_error>'.$err.'</critical_error>');
                echo ('<submitted>false</submitted>');
                exit;
            }
            if ($section===false){
                $err='A required parameter (sectionid) was missing';
                echo ('<critical_error>'.$err.'</critical_error>');
                echo ('<submitted>false</submitted>');
                exit;
            }
            if (stripos($_SERVER['HTTP_USER_AGENT'], 'flash')!==false){
                $this->flashupkey = optional_param('flashupkey', false, PARAM_TEXT);
                if ($this->flashupkey===false){
                    $err='A required parameter (flashupkey) was missing';
                    echo ('<critical_error>'.$err.'</critical_error>');
                    echo ('<submitted>false</submitted>');
                    exit;                    
                }
                $this->sesskey = optional_param('sesskey', false, PARAM_TEXT);
                if ($this->sesskey===false){
                    $err='A required parameter (sesskey) was missing';
                    echo ('<critical_error>'.$err.'</critical_error>');
                    echo ('<submitted>false</submitted>');
                    exit;
                }
                $this->userid = optional_param('userid', false, PARAM_TEXT);
                if ($this->userid===false){
                    $err='A required parameter (userid) was missing';
                    echo ('<critical_error>'.$err.'</critical_error>');
                    echo ('<submitted>false</submitted>');
                    exit;
                }                
            }
        }

         // get course object / report error if course does not exist
        if (! $course = get_record("course", "id", $courseid)) {
            if ($submittype=='form'){
                error("This course doesn't exist");
                exit;
            } else {
                echo ('<critical_error>This course does not exist</critical_error>');
                echo ('<submitted>false</submitted>');
                exit;
            }
        }
        $this->course=$course;

        /// setup global $COURSE, language and locale
        course_setup($courseid);

        if ($submittype=='form'){        
            $this->filefield='file';               
        } else {
            $this->filefield='Filedata';   
            // set http referer for FLASH submission or it will cause an error with moodle's upload manager
            if (stripos($_SERVER['HTTP_USER_AGENT'], 'flash')!==false){
                $_SERVER['HTTP_REFERER']=$CFG->wwwroot.'/course/modedit.php?add=file&type=&course='.$courseid.'&section='.$section.'&return=0';
            }
        }

        if (!isset($_FILES[$this->filefield])){
            echo ('<critical_error>Files not submitted</critical_error>');
            echo ('<submitted>false</submitted>');
            exit;
        }

        $this->file = $_FILES[$this->filefield];

        // force utf-8 encoding for filename when ajax (html5) used for file uploads
        if ($submittype=='ajax'){
            //$this->file['name'] = mb_convert_encoding($this->file['name'],'UTF-8', mb_detect_encoding($this->file['name'], "auto" ));
            //$this->file['name'] = mb_convert_encoding($this->file['name'],'UTF-8');            
            $this->file['name']=urldecode($this->file['name']);
            // also need to sort out filename in global $_FILES variable
            $_FILES[$this->filefield]['name']=urldecode($_FILES[$this->filefield]['name']);
        } 

        // security check
        // this has to be here, after $this->file has been set
        $this->security_check($course);
                        
        $this->section=$section;
        $this->directory=$directory;
        $this->visibility=$visibility;
        $this->submittype=$submittype;
                        
        // create moodle form - note - we never use this to edit so the instance param is an empty string and the
        // course module param is null 
        if ($submittype=='form'){ // can't create the form object in ajax(Flash) file send mode for some reason 
            $mform=new mod_enhancedfile_mod_form('',$section,null);
        }
    
        $fdata=(object) array (
            'add'=>'file', // allways in add mode
            'courseid'=>$courseid,
            'section'=>$section,
            'file'=>$this->file,
            'directory'=>$directory
        );
        
        // make sure module exists - note that its 'resource' and not 'file' for module type
        if (! $module = get_record("modules", "name", 'resource')) {
            error("This module type doesn't exist");
        }
        $this->module=$module;
        
        // get course section
        $cw = get_course_section($section, $course->id);
        $this->cw=$cw;
    
        // make sure module is enabled for this course
        if (!course_allowed_module($course, $module->id)) {
            error("This module has been disabled for this particular course");
        }    
        
        // get course section name
        $sectionname = get_section_name($course->format);
                
        // get module name
        $fullmodulename = get_string("modulename", $module->name);
        
        // get strings for editting module
        $streditinga = get_string("editinga", "moodle", $fullmodulename);
        $strmodulenameplural = get_string("modulenameplural", $module->name);    

        // set form data
        if ($submittype=='form'){ // can't create the form object in ajax(Flash) file send mode for some reason
            $mform->set_data($fdata);    
            $fdata=$mform->get_data();
                
            // redirect if cancelled
            if ($mform->is_cancelled()) {
                redirect($CFG->wwwroot.'/course/view.php?id='.$courseid.'#section-'.$section);
            }
            
            // add file and write to database
            if ($mform->is_validated()){
                $this->process($this->file, $this->filefield);

                // do other files too
                for ($fn=2; $fn<=5; $fn++){
                    $fname='file'.$fn;
                    if (isset($_FILES[$fname])){
                        $file = $_FILES[$fname];
                        $this->process($file, $fname);
                    }
                }

                if (isset($fdata->submitbutton)) {
                    redirect($CFG->wwwroot.'/course/modedit.php?add=enhancedfile&type=&course='.$courseid.'&section='.$section.'&return=0');
                } else {
                redirect($CFG->wwwroot.'/course/view.php?id='.$courseid.'#section-'.$section);
                }
                // always redirect back to form on successful submission
                //redirect($CFG->wwwroot.'/course/view.php?id='.$courseid.'#section-'.$section);
            }            
        } else {
            if ($this->process($this->file, $this->filefield)){
                echo ('<submitted>true</submitted>');
            } else {
                echo ('<submitted>false</submitted>');
            }
        }

        // display header
        if ($this->submittype=='form'){
            $navlinks[] = array('name' => $streditinga, 'link' => '', 'type' => 'title');
            $navigation = build_navigation($navlinks);
            print_header_simple($streditinga, '', $navigation, $mform->focus(), "", false);            
            
            // display form
            $mform->display();
        }     

    }

    /**
     * check security ok or abort
     * @param stdObject $course
     */
    function security_check($course){
        global $CFG;
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        // non-flash user agents, make sure user logged in and with correct capability
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'flash')===false){
            if ($this->submittype=='form'){
                require_login($course);
                require_capability('moodle/course:manageactivities', $context);
                exit;
            } else {
                // security issue reported by Petr Skoda
                $ajaxsec=new ajax_security();
                $validlogin=$ajaxsec->valid_login($course);
                //echo ($validlogin->output());
                if (!$validlogin->success) {
                    echo ('<submitted>false</submitted>');
                    exit;
                }
                $hascap=$ajaxsec->has_capability('moodle/course:manageactivities', $context);
                //echo ($hascap->output());
                if (!$hascap->success){
                    echo ('<submitted>false</submitted>');
                    exit;
                }
            }
        } else {
            // flash user agents - check valid upload key exists
            // create securitykey for this file (using flashupkey)
            $user=get_record('user', 'id', $this->userid);
            $ip=$_SERVER['REMOTE_ADDR'];

            // clean up file name
            // note: can't use clean_filename function because flash uses
            // separate session and it doesn't seem to pick up $CFG->unicodecleanfilename
            //$cleanname=preg_replace('/[^\.a-zA-Z0-9\d\_-]/','', $this->file['name'] );

            // NOTE - cleanname is now simpler because the above wont cope with japanese chars, etc
            $cleanname=str_replace('/','',$this->file['name']);
            $cleanname=str_replace("\\",'',$cleanname);

            // $securitykey=md5($this->flashupkey.$ip.$user->currentlogin.$cleanname); //  REMOVED - ip address inconsistent between browser and flash if using proxies

            $salt=isset($CFG->passwordsaltmain) ? $CFG->passwordsaltmain : '';

            //$securitykey=md5($this->flashupkey.$salt.$user->currentlogin.$cleanname); // use clean file name for key // REMOVED - current login forces log out login and clean name does not work well with japanese chars

            //$securitykey=md5($this->flashupkey.$salt.$user->firstaccess.urlencode($this->file['name']));
            $securitykey=md5($this->flashupkey.$salt.$user->firstaccess.urlencode($this->file['name']));
            
            if (!enhanced_file_db::get_upload_key_live($this->sesskey, $course->id, $this->userid, $securitykey)){
                echo ('<submitted>false</submitted><error_msg>Security key invalid for this file, please log out and back in.'.$this->flashupkey.$salt.$user->firstaccess.urlencode($this->file['name']).$this->file['name'].'</error_msg></submitted>');
                exit;
            } else {
                // mark upload complete!
                enhanced_file_db::set_upload_key_complete($this->sesskey, $course->id, $this->userid, $securitykey);
            }
        }
    }


    /**
     * return existing resource records
     * @return stdObject
     */
    function existing_resources($file){
        global $CFG, $db;

        // set file ref
        $directory=str_replace('~ROOT~','',$this->directory);
        if ($directory!=''){
            $directory.='/';
        }
        $fileref = str_replace('//','/', $directory.clean_filename($file['name']));

        // get records
        $sql='SELECT res.*, cs.section as sectionnumber FROM '.$CFG->prefix.'resource res LEFT JOIN '.$CFG->prefix.'course_modules cm ON cm.instance=res.id  LEFT JOIN '.$CFG->prefix.'course_sections cs ON cs.id=cm.section  WHERE (res.name=\''.addslashes($file['name']).'\' OR res.name=\''.addslashes(clean_filename($file['name'])).'\') AND res.reference =\''.$fileref.'\' AND cs.section='.intval($this->section).' AND res.course='.$this->course->id;
        $rs=get_records_sql($sql);
        return ($rs);
    }
    
    function process($file, $filefield){

        // create upload directory
        if (! $basedir = make_upload_directory($this->course->id)) {
            // @to do error here - failed to create upload directory
            return (false);
        }
        // upload file        
        $um = new upload_manager($filefield,false,false,$this->course,false,0);
        $directory=str_replace('~ROOT~','',$this->directory);
        if ($directory!=''){
            $updir = $basedir.'/'.$directory;
            $fref=$directory . '/' . clean_filename($file['name']);
        } else {
            $updir = $basedir;
            $fref=clean_filename($file['name']);
        }
        $fileref = str_replace('//','/', $updir.'/'.$file);

        if (!$um->process_file_uploads($updir)) {
            // @ to do error here - failed to upload file
            return (false);
        }

        // if resource type is directory then make sure directory has been added to course section
        if ($this->resourcetype=='directory'){
            $this->add_directory();
        }

        // if resource type is not files then don't bother adding file resources
        if ($this->resourcetype!='files'){
            return (true);
        }

        $rs=$this->existing_resources($file);
        if ($rs){
            // resources already exist with this filename so don't bother adding again
            return (true);
        }


        $customname=trim(urldecode(optional_param('customname', '', PARAM_TEXT)));
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'flash')===false && $this->submittype=='ajax'){
            $customname=stripslashes($customname);
        }
        $resmodname=$customname!='' ? $customname : $file['name'];
        

        $resmod              = new StdClass();
        $resmod->type        = 'file';
        $resmod->name        = addslashes($resmodname);
        $resmod->summary     = '';
        $resmod->reference   = $fref;
        $resmod->windowpopup = 0;
        //$resmod->visible     = 1;
        $resmod->visible     = $this->visibility; // enhancement by Amanda Doughty December 2010
        $resmod->course      = $this->course->id;
        $resmod->coursemodule = '';
        $resmod->section     = $this->cw->section;
        $resmod->module      = $this->module->id;
        $resmod->modulename  = $this->module->name;
        $resmod->instance    = '';
        $resmod->add         = $this->module->name;
        $resmod->update      = 0;
        $resmod->return      = 0;
        $resmod->submitbutton = 'save';

        $resinst = enhancedfile_add_instance($resmod);
        
        if (!$resinst) {
            // @ to do error here - failed to create resource instance
            return (false);
        }
        
        if (!isset($resmod->groupmode)) { // to deal with pre-1.5 modules
            $resmod->groupmode = $this->course->groupmode;  /// Default groupmode the same as course
        }
    
        $resmod->instance = $resinst;

        // add coures module resource instance
        if (! $resmod->coursemodule = add_course_module($resmod) ) {
            // @ to do error here - failed to create course module resource instance
            return (false);
        }
        
        // add module instance to section
        if (! $modsectionid = add_mod_to_section($resmod) ) {
            // @ to do error here - failed to add module instance to section
            return (false);
        }        
        
        if (! set_field("course_modules", "section", $modsectionid, "id", $resmod->coursemodule)) {
            // @ to do error here - failed to update course_modules table with modsectionid
            return (false);
        }

       if (!isset($resmod->visible)) {   // We get the section's visible field status
            $resmod->visible = get_field("course_sections","visible","id",$modsectionid);
        }
        
        // make sure visibility is set correctly (in particular in calendar)
        set_coursemodule_visible($resmod->coursemodule, $resmod->visible);
    
        // add to logs
        add_to_log($this->course->id, "course", "add mod",
                   "../mod/$resmod->modulename/view.php?id=$resmod->coursemodule",
                   "$resmod->modulename $resmod->instance");
        add_to_log($this->course->id, $resmod->modulename, "add",
                   "view.php?id=$resmod->coursemodule",
                   "$resmod->instance", $resmod->coursemodule);
           
        rebuild_course_cache($this->course->id);        

        return ($modsectionid);
    }

    /**
     * add directory as resource (but only if not already added).
     */
    protected function add_directory(){
        $directory=str_replace('~ROOT~','',$this->directory);
        $dirresname=str_replace('~ROOT~',get_string('rootfolder', 'enhancedfile'),$this->directory); // resource name

        if ($this->dir_res_exists($directory)){
            return; // no need to add directory, already exists
        }

        $tmparr=explode('/', $directory);
        $dirname=$tmparr[count($tmparr)-1];

        $resmod=new stdClass();
        $resmod->name        = addslashes($dirresname);
        $resmod->type        = 'directory';
        $resmod->course      = $this->course->id;
        $resmod->coursemodule = '';
        $resmod->section     = $this->cw->section;
        $resmod->module      = $this->module->id;
        $resmod->modulename  = $this->module->name;
        $resmod->summary     = addslashes($directory);
        $resmod->reference   = addslashes($directory);
        $resmod->windowpopup = 0;
        //$resmod->visible     = 1;
        $resmod->visible     = $this->visibility; // enhancement by Amanda Doughty December 2010
        $resmod->instance    = '';
        $resmod->add         = $this->module->name;
        $resmod->update      = 0;
        $resmod->return      = 0;
        $resmod->submitbutton = 'save';

        $resinst = enhancedfile_add_instance($resmod);

        if (!$resinst) {
            // @ to do error here - failed to create resource instance
            return (false);
        }

        if (!isset($resmod->groupmode)) { // to deal with pre-1.5 modules
            $resmod->groupmode = $this->course->groupmode;  /// Default groupmode the same as course
        }

        $resmod->instance = $resinst;

        // add coures module resource instance
        if (! $resmod->coursemodule = add_course_module($resmod) ) {
            // @ to do error here - failed to create course module resource instance
            return (false);
        }

        // add module instance to section
        if (! $modsectionid = add_mod_to_section($resmod) ) {
            // @ to do error here - failed to add module instance to section
            return (false);
        }

        if (! set_field("course_modules", "section", $modsectionid, "id", $resmod->coursemodule)) {
            // @ to do error here - failed to update course_modules table with modsectionid
            return (false);
        }

        if (!isset($resmod->visible)) {   // We get the section's visible field status
            $resmod->visible = get_field("course_sections","visible","id",$modsectionid);
        }

        // make sure visibility is set correctly (in particular in calendar)
        set_coursemodule_visible($resmod->coursemodule, $resmod->visible);

        // add to logs
        add_to_log($this->course->id, "course", "add mod",
                   "../mod/$resmod->modulename/view.php?id=$resmod->coursemodule",
                   "$resmod->modulename $resmod->instance");
        add_to_log($this->course->id, $resmod->modulename, "add",
                   "view.php?id=$resmod->coursemodule",
                   "$resmod->instance", $resmod->coursemodule);

        rebuild_course_cache($this->course->id);

        return ($modsectionid);

    }

    protected function dir_res_exists($directory){
        global $CFG;
        $directory=addslashes($directory);
        $sql='SELECT cm.* FROM '.$CFG->prefix.'course_modules cm LEFT JOIN '.$CFG->prefix.'course_sections cs ON cm.section=cs.id LEFT JOIN '.$CFG->prefix.'resource rm ON cm.instance=rm.id WHERE cm.course='.$this->course->id.' AND cs.section='.$this->section.' AND rm.type=\'directory\' AND rm.reference=\''.$directory.'\'';
        $rs=get_records_sql($sql);
        return ($rs!==false);
    }
}

$res_inst=new resource_file_upload();
?>
