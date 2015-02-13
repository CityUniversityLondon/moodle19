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
 * get directory structure for current course
 */
class ajaj_directorystructure extends ajaj_page{


    public static function factory(){
        return (new ajaj_directorystructure());
    }

    protected function set_required_params(){
        $this->required_params=array(
            'course'=>PARAM_INT
        );
    }

    protected function set_caps(){
        $this->caps=array(
            'moodle/course:manageactivities'
        );
    }

    protected function main(){
        global $CFG, $COURSE;
        $courseid=$COURSE->id;
        $rawdirs = get_directory_list($CFG->dataroot.'/'.$courseid, array($CFG->moddata, 'backupdata'), true, true, false);
        $dirs = array();
        $dirs['~ROOT~']='['.get_string('rootfolder', 'enhancedfile').']';
        foreach ($rawdirs as $rawdir) {
            $dirs[$rawdir] = $rawdir;
        }
        $this->jsonobj->data=(object) array('directories'=>$dirs, 'courseid'=>$courseid);
    }

}

ajaj_directorystructure::factory();

?>
