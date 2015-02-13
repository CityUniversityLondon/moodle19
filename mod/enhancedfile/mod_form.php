<?php

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->libdir.'/gtlib_yui/config_gtlib.php');
require_once ($CFG->libdir.'/weblib.php');

class mod_enhancedfile_mod_form extends moodleform_mod {
    
    /**
     * submit to upload.php for this module insetad of course/mod_edit.php
     * @param $instance
     * @param $section
     * @param $cm
     * @return unknown_type
     */
    function mod_enhancedfile_mod_form($instance, $section, $cm){
        global $CFG, $USER;
		
        $this->_instance = $instance;
        $this->_section = $section;
        $this->_cm = $cm;        
        parent::moodleform($CFG->wwwroot.'/mod/enhancedfile/upload.php');
    }
    
    /**
     * override parent constructor function 
     */     
    function moodleform_mod($instance, $section, $cm) {}    
    
    function definition() {
        
        global $USER, $CFG, $COURSE;

        $courseid=required_param('course',PARAM_INT);
        $section=required_param('section', PARAM_INT);

        // set max upload file size
        $this->upload_max_filesize = get_max_upload_file_size($CFG->maxbytes, $COURSE->maxbytes);

        $mform  =& $this->_form;
        
        // add hidden fields
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->addElement('hidden', 'userid', $USER->id);
        //$mform->addElement('hidden', 'section', $section); // gets added later on (dont know how!)
        $mform->addElement('hidden', 'add', 'file'); // always add, never update (update handled by standard link to file or website)
        $mform->addElement('hidden', '_submittype', 'form'); // submit type is by form (alternative is ajax)

// ===== FORM FILE ELEMENTS ==== //         

        $mform->addElement('header', 'fileelements', get_string('filedetails', 'enhancedfile'));
        
        // add standard file browser        
        $mform->addElement('file', 'file', '<span id="lableaddfile">'.get_string('addfile','enhancedfile').'</span>');
        $mform->addRule('file', null,'required');

        // add standard file browsers
        $mform->addElement('file', 'file2', get_string('addfile','enhancedfile'));
        $mform->addElement('file', 'file3', get_string('addfile','enhancedfile'));
        $mform->addElement('file', 'file4', get_string('addfile','enhancedfile'));
        $mform->addElement('file', 'file5', get_string('addfile','enhancedfile'));

       
        // add static file queue container field
        // $mform->addElement('static','filequeuecontainer', '');
        // $mform->setDefault('filequeuecontainer','<div id="filequeuecontainer"></div>');

        // add some space
        //$mform->addElement('static','br1', '<br />', '<br />');
        
        // add directory selector
        $rawdirs = get_directory_list($CFG->dataroot.'/'.$courseid, array($CFG->moddata, 'backupdata'), true, true, false);
        $dirs = array();
        $dirs['~ROOT~']='['.get_string('rootfolder', 'enhancedfile').']';
        foreach ($rawdirs as $rawdir) {
            $dirs[$rawdir] = $rawdir;
        }
        $mform->addElement('select', 'directory', get_string('uploadtofolder', 'enhancedfile'), $dirs);
                
        // add directory / file management
        $mform->addElement('static','fileman', get_string('filemanagement', 'enhancedfile'), '<a id="managefiles" class="button" target="_blank" href="'.$CFG->wwwroot.'/files/?id='.$COURSE->id.'">'.get_string('managefiles','enhancedfile').'</a>');

        // add resource type selector
        $radioarray=array();
        $radioarray[] = &MoodleQuickForm::createElement('radio', 'resourcetype', '', get_string('linktofiles', 'enhancedfile'), 'files');
        $radioarray[] = &MoodleQuickForm::createElement('radio', 'resourcetype', '', get_string('directory'), 'directory');
        $mform->setDefault('resourcetype', 'files');
        $mform->addGroup($radioarray, 'radioar', get_string('resourcetype', 'enhancedfile'), array(' '), false);

        // add preload images
        $mform->addElement('static','imgpreload', '', $this->preload_images_html());
        
        
// ===== FOMR DISPLAY SETTINGS ==== //
        /*
        $mform->addElement('header', 'displaysettings', get_string('display', 'resource'));
        $mform->addElement('checkbox', 'forcedownload', get_string('forcedownload', 'resource'));
        $mform->setHelpButton('forcedownload', array('forcedownload', get_string('forcedownload', 'resource'), 'resource'));
        $mform->disabledIf('forcedownload', 'windowpopup', 'eq', 1);

        $woptions = array(0 => get_string('pagewindow', 'resource'), 1 => get_string('newwindow', 'resource'));
        $mform->addElement('select', 'windowpopup', get_string('display', 'resource'), $woptions);
        $mform->disabledIf('windowpopup', 'forcedownload', 'checked');

        $navoptions = array(0 => get_string('keepnavigationvisibleno','resource'), 1 => get_string('keepnavigationvisibleyesframe','resource'), 2 => get_string('keepnavigationvisibleyesobject','resource'));
        $mform->addElement('select', 'framepage', get_string('keepnavigationvisible', 'resource'), $navoptions);
        
        $mform->setHelpButton('framepage', array('frameifpossible', get_string('keepnavigationvisible', 'resource'), 'resource'));
        $mform->setDefault('framepage', 0);
        $mform->disabledIf('framepage', 'windowpopup', 'eq', 1);
        $mform->disabledIf('framepage', 'forcedownload', 'checked');
        $mform->setAdvanced('framepage');

        $mform->addElement('static','shownavigationwarning','','<i>'.get_string('keepnavigationvisiblewarning', 'resource').'</i>');
         */

        
// ===== REQUIRE JAVASCRIPT ==== //             

        // add static script field
        $mform->addElement('static','staticscript', $this->static_script());        
        
        // Use minified scripts for gtlib ?
        $gtminstr=isset($CFG->gtlib->loadjs_source) && $CFG->gtlib->loadjs_source ? '' : '-min';
                
        // Load YUI
        //require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_json', 'yui_dragdrop', 'yui_datasource', 'yui_element', 'yui_datatable', 'yui_connection', 'yui_uploader'));
        require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_json', 'yui_dragdrop', 'yui_datasource', 'yui_element', 'yui_datatable', 'yui_connection', 'yui_animation'));

        // Load Flash detection lib
        require_js($CFG->wwwroot.'/mod/enhancedfile/lib/flashdetect-min.js');

        // Load yui_2.8.0r4 YUI uploader file (one supplied with moodle 1.9.7 does not work with uploader.removeFile function
        // require_js(array('yui_uploader'));
        require_js($CFG->wwwroot.'/mod/enhancedfile/extlib/uploader/uploader-min.js');
        
        // Load GTLib libs
        require_js($CFG->wwwroot.'/lib/gtlib_yui/lib.gt_all.php');
        require_js($CFG->wwwroot.'/lib/gtlib_yui/widgets/dialog/lib.gt.dialog.js');
        // require_js($CFG->wwwroot.'/lib/gtlib_yui/widgets/dialog/lib.gt.yuidialog.js');
        
        // Load multifile uploader js     
        require_js($CFG->wwwroot.'/mod/enhancedfile/lib/multifile.js');
        
        // standard features for resources        
        $features = array('groups'=>false, 'groupings'=>false, 'groupmembersonly'=>true,
                          'outcomes'=>false, 'gradecat'=>false, 'idnumber'=>false);
        $this->standard_coursemodule_elements($features);
         


        // buttons
        $buttonText = get_string('buttontext', 'enhancedfile');
        $this->add_action_buttons(true, $buttonText, null);

    }
    
    /**
     * generate static script for this form
     * @return string
     */
    function static_script(){
        global $USER, $CFG, $COURSE;
		if (!isset($CFG->enhancedfile_html5uploads)){
			$CFG->enhancedfile_html5uploads=0;
		}
        $courseid=optional_param('course', $COURSE->id, PARAM_INT);
        $section=optional_param('section', 0, PARAM_INT);        
        $script='<script type="text/javascript">/*<![CDATA[*/            
            if (typeof(moodle_lang)=="undefined"){
                var moodle_lang=new Array();
            }
            moodle_lang["enhancedfile"]='.$this->langtojson().';
			var file_html5uploads='.($CFG->enhancedfile_html5uploads==1 ? 'true' : 'false').';
            var file_wwwroot="'.$CFG->wwwroot.'";
            var file_courseid='.intval($courseid).';
            var file_section='.intval($section).';  
            var file_maxsize='.intval($this->upload_max_filesize).'; // size in bytes
            var file_maxsizetxt="'.display_size($this->upload_max_filesize).'";        
            var file_sesskey="'.$USER->sesskey.'";    
            function ImportStyleSheet(shtLoc){
                // add style sheet via javascript
                var link = document.createElement( "link" );
                link.setAttribute( "href", shtLoc );
                link.setAttribute( "type", "text/css" );
                link.setAttribute( "rel", "stylesheet" );
                var head = document.getElementsByTagName("head").item(0);
                head.appendChild(link);
            }

            ImportStyleSheet("'.$CFG->wwwroot.'/mod/enhancedfile/style.css");
            
            ImportStyleSheet("'.$CFG->wwwroot.'/mod/enhancedfile/mime.css");
            
            ImportStyleSheet("'.$CFG->wwwroot.'/lib/gtlib_yui/widgets/dialog/themes/standard/dialog.css");
            
            if (navigator.appVersion.indexOf("MSIE 6")>-1){
                ImportStyleSheet("'.$CFG->wwwroot.'/lib/gtlib_yui/widgets/dialog/themes/standard/dialog_ie6.css");
            }

            ImportStyleSheet("'.$CFG->wwwroot.'/lib/yui/assets/skins/sam/datatable.css");
            ImportStyleSheet("'.$CFG->wwwroot.'/lib/yui/container/assets/skins/sam/container.css");
            ImportStyleSheet("'.$CFG->wwwroot.'/lib/yui/container/assets/skins/sam/container-skin.css");
            
            
        /*]]>*/    
        </script>
        ';
        return ($script);
    }

    protected function langtojson(){
        global $USER, $CFG;
        //$langloc=$CFG->dirroot.'/mod/enhancedfile/lang/'.$USER->lang.'/enhancedfile.php';
        // always use english language version to get language keys
        // we are using get_string to convert the lang string values to the
        // users native language later
        $langloc=$CFG->dirroot.'/mod/enhancedfile/lang/en_utf8/enhancedfile.php';
        if (file_exists ($langloc)){
            $handle = fopen($langloc, "r");
            $contents = fread($handle, filesize($langloc));
            fclose($handle);
            $contents=str_ireplace('<?php', '', $contents);
            $contents=str_ireplace('<?', '', $contents);
            $contents=str_ireplace('?>', '', $contents);
            eval ($contents);
            $jsstr='';
            foreach ($string as $key=>$val){
                $jsstr.=$jsstr=='' ? '' : ',';
                $val=get_string($key,'enhancedfile'); // use actual moodle get_string function so that sub languages are respected
                $val=str_replace('\'', "\'", $val); // same as addslashes but only escapes single quotes
                $jsstr.='\''.$key.'\''.':\''.$val.'\'';
            }
            $jsstr='{'.$jsstr.'}';
        } else {
            $jsstr='null';
        }
        return ($jsstr);
    }

    protected function preload_images_html(){
        global $CFG;
        $pixdir=$CFG->dirroot.'/mod/enhancedfile/pix';        
        $imgexts=array(
          'gif',
          'png',
          'jpg'
        );
        // note - don't use display:none - we want these images to load!
        $output='<div style="visibility:hidden; position:absolute; left:-100px; top:-100px; height:1px; width:1px; line-height:1px; font-size:1px">';
        if (is_dir($pixdir)) {
            $dh = opendir($pixdir);
            if ($dh) {
                while (($file = readdir($dh)) !== false)  {
                    
                    if ($file!='.' && $file!='..'){
                        $ext=pathinfo($pixdir.$file, PATHINFO_EXTENSION);
                        if (in_array($ext, $imgexts)){
                            $output.='<img src="'.$CFG->wwwroot.'/mod/enhancedfile/pix/'.$file.'" alt="preloadignore" />';
                        }
                    }
                }
                closedir($dh);
            }            
        }
        $output.='</div>';
        return ($output);
    }

}
?>
