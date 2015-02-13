<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ('../../../config.php');
include_once ($CFG->libdir.'/dmllib.php');
include_once ($CFG->dirroot.'/mod/enhancedfile/lib/ajax_security.php');
include_once ($CFG->dirroot.'/mod/enhancedfile/lib/ajax.php');
include_once ($CFG->dirroot.'/mod/enhancedfile/lib/db.php');


/**
 * create upload keys so that flash user agent can be authorised to upload files
 * this file will be accessed by the browser user agent, not flash
 */
class ajaj_makeuploadkeys extends ajaj_page{

    protected $filelist;
    protected $filelistbyname;

    public static function factory(){
        return (new ajaj_makeuploadkeys());
    }
    protected function set_caps(){
        $this->caps=array(
            // 'mod/enhancedfile:multiupload', // removed in version 2010032300
            'moodle/course:manageactivities'
        );
    }
    protected function set_required_params(){
        $this->required_params=array(
            'sesskey'=>PARAM_TEXT,
            'filelist'=>PARAM_TEXT,
            'course'=>PARAM_INT,
            'section'=>PARAM_INT
        );
    }
    protected function set_optional_params(){
        $this->optional_params=array(
            'userid'=>PARAM_INT
        );
    }
    protected function main(){
        global $USER;
        if (!$this->paramvals->userid){
            $this->paramvals->userid=$USER->id;
        }
        $this->setmasterkey();
        $this->setfilenames();
        $this->createfilekeys();
    }

    /**
     * set file names in filelist
     */
    protected function setfilenames(){
        $fileliststr=$this->paramvals->filelist;
        $this->filelist=explode('~FDEL~', $fileliststr);
        $this->filelistbyname=array();
        foreach ($this->filelist as $key=>$file){
            $filearr=explode('~FPDEL~', $file);
            $filename=urldecode($filearr[0]);

            // clean up file name
            // note: can't use clean_filename function because flash uses
            // separate session and it doesn't seem to pick up $CFG->unicodecleanfilename
            // $filenameclean=preg_replace('/[^\.a-zA-Z0-9\d\_-]/','', $filename );

            // clean name is now simpler because clean_filename does not work with Japanese chars, etc.
            $filenameclean=str_replace('/','',$filename);
            $filenameclean=str_replace("\\",'',$filenameclean);

            $fileid=$filearr[1];
            $fileobj=(object) array ('rawname'=>$filearr[0],'name'=>$filename, 'safename'=>urlencode(stripslashes($filearr[0])), 'cleanname'=>$filenameclean, 'id'=>$fileid);
            $this->filelist[$key]=$fileobj;
            $this->filelistbyname[$filenameclean]=$fileobj;
        }
    }
    /**
     * set master key
     * to consider - could make this more secure by generating 1 key for
     * each file - problem is upload queue sends 1 block of post data so we
     * would need to write a new upload queue that allows seperate post data
     * for each item in queue    
     * @return string
     */
    protected function setmasterkey(){
        $key=md5(uniqid('', rand(1,4)));
        $this->jsonobj->data->key=$key;
    }

    /**
     * create file keys for this session
     */
    protected function createfilekeys(){
        global $CFG, $USER;
        $hashkey=$this->jsonobj->data->key;
        // first remove old file keys
        enhanced_file_db::remove_old_file_keys();
        // create file upload keys
        $this->jsonobj->data->fkeyscreated=array();
        foreach ($this->filelistbyname as $fname=>$obj){
            // create individual security key
            $ip=$_SERVER['REMOTE_ADDR'];
            //$securitykey=md5($hashkey.$ip.$USER->currentlogin.$fname); // REMOVED ip address as inconsistent between browser and flash with proxy servers
            $salt=isset($CFG->passwordsaltmain) ? $CFG->passwordsaltmain : '';
            //$securitykey=md5($hashkey.$salt.$USER->currentlogin.$obj->cleanname); // use clean file name for key
            $securitykey=md5($hashkey.$salt.$USER->firstaccess.$obj->safename); // works with japanese chars, etc..
            $kok=enhanced_file_db::create_upload_key($obj->safename, $this->paramvals->sesskey, $this->paramvals->course, $this->paramvals->userid, $securitykey);
            $this->jsonobj->data->fkeyscreated[$obj->safename]=array('success'=>$kok, 'cleanname'=>$obj->cleanname, 'htmlname'=>htmlentities($obj->name), 'toberemoved'=>$hashkey.$salt.$USER->firstaccess.urlencode($obj->name));
        }
    }
}

ajaj_makeuploadkeys::factory();

?>
