<?php

include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
include_once($CFG->libdir.'/weblib.php');
include_once($CFG->dirroot.'/mod/enhancedfile/lib/json.php');

/**
 * @todo - base filelistcheck on ajaj_page
 */

class filelistcheck{

    var $uploaddir;
    var $courseid;
    var $section;
    var $fileliststr;
    var $filelist;
    var $filelistbyname;
    var $warnoverwrites;
    var $method; // ajax or flash

    /**
     * constructor
     * @return void
     */
    function __construct(){
        $this->setvars();
        $this->setwarnoverwrites();
        $jsonobj=(object) array('responseok'=>true);
        if (!empty($this->warnoverwrites)){
            $jsonobj->warnoverwrites=$this->warnoverwrites;
        }
        echo (json_encode($jsonobj));
    }

    /**
     * factory function
     * @return stdObject
     */
    public static function factory(){
        return new filelistcheck();
    }

    /**
     * set basic class vars from params
     * @return void
     */
    protected function setvars(){
        global $CFG;
        $this->courseid=optional_param('course', 1, PARAM_INT);
        $this->section=optional_param('section', 1, PARAM_INT);
        $this->method=optional_param('method','flash',PARAM_ALPHA);
        $updir=optional_param('updir', '', PARAM_TEXT);
        $updir=str_ireplace('~ROOT~', '', $updir);
        $updir=urldecode($updir);
        $this->uploaddir=$CFG->dataroot.'/'.$this->courseid;
        $this->uploaddir.=$updir!='' ? '/'.$updir : '';
        $this->uploaddir.='/';
        $this->fileliststr=optional_param('filelist', '', PARAM_TEXT);
        $this->filelist=explode('~FDEL~', $this->fileliststr);
        $this->filelistbyname=array();
        foreach ($this->filelist as $key=>$file){
            $filearr=explode('~FPDEL~', $file);
            $filename=urldecode($filearr[0]);
            $fileid=$filearr[1];
            if ($this->method!='flash'){
                $fileid=stripslashes($fileid);
            }
            $fileobj=(object) array ('name'=>$filename, 'cleanname'=>$this->clean_filename($filename), 'id'=>$fileid);
            $this->filelist[$key]=$fileobj;
            $this->filelistbyname[$this->clean_filename($filename)]=$fileobj;
        }
    }

    protected function clean_filename($filename){
        return (clean_filename($filename));
        /*
        $cleanname=str_replace('/','',$filename);
        $cleanname=str_replace("\\",'',$cleanname);
        return ($cleanname);
        */         
    }

    /**
     * set warn overwrites array - array of files to be overwritten
     * @return void
     */
    protected function setwarnoverwrites(){
        $this->warnoverwrites=array();
        if (is_dir($this->uploaddir)) {
            $dh = opendir($this->uploaddir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    if ($file!='.' && $file!='..'){
                        $cleanfilename=$this->clean_filename($file);
                        if (isset($this->filelistbyname[$file]) || isset($this->filelistbyname[$cleanfilename])){
                            $fileobj=$this->filelistbyname[$file];
                            $this->warnoverwrites[$fileobj->id]=$file;
                        }
                    }
                }
                closedir($dh);
            }
        }
        asort($this->warnoverwrites);
    }
}

filelistcheck::factory();
?>