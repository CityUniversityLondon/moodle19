<?php

include_once('json.php');
include_once('ajax_security.php');

/**
 * class ajax page
 */
abstract class ajax_page {
    /**
     * ajax security
     * @var stdObject $asec
     */
    protected $asec;

    /**
     * required capabilities
     * @var array $caps
     */
    protected $caps;

    /**
     * capability check type - all or any
     * @var boolean
     */
    protected $capany=true;

    /**
     * required params
     * array hashed by param name with value as param type
     * @var array $required_params
     */
    protected $required_params;

    /**
     * optional params
     * array hashed by param name with value as param type
     * @var array $optional_params
     */
    protected $optional_params;

    /**
     * requires log in
     * @var boolean
     */
    protected $requireslogin=true;

    /**
     * array of security check objects
     * @var array
     */
    protected $secchecks;

    /**
     * output security notices as they happen
     * @var boolean
     */
    protected $secalertlive=false;

    /**
     * ajax security type
     * @var integer
     */
    protected $ajaxsectype=AJAX_SECURITY;

    /**
     * array of critical error messages
     * @var array
     */
    protected $criticalerrors;

    /**
     * object / array of param vals recovered from get or post - hashed by param key
     * @var array|stdObject
     */
    protected $paramvals;

    /**
     * constructor
     * @return void
     */
    function __construct(){
        $this->asec=new ajax_security($this->ajaxsectype);
        $this->secchecks=array();
        $this->critical_errors=array();
        $this->set_caps();
        $this->set_required_params();
        $this->set_optional_params();
        $this->set_params();
        $this->page_start();
        if ($this->security_check()){
            if (!empty($this->criticalerrors)){
                $this->dieoncriticalerrors();
            }
            $this->main();
        } else {
            $this->security_alert();
        }
    }

    /**
     * set capabilities to check for
     * @return boolean
     */
    protected function set_caps(){
        $this->caps=array();
    }

    /**
     * this is to be overridden by sub class
     * set required params to check for
     * (array hashed by param name with value as param type)
     * @return boolean
     */
    protected function set_required_params(){
        $this->required_params=array();
    }

    /**
     * this is to be overridden by sub class
     * set optional params to check for
     * (array hashed by param name with value as param type)
     * @return boolean
     */
    protected function set_optional_params(){
        $this->optional_params=array();
    }

    /**
     * get params and set their values
     */
    protected function set_params(){
        if (!empty($this->required_params)){
            foreach ($this->required_params as $param=>$type){
                $param=strval($param);
                $pval=optional_param($param, false, $type);
                if ($pval===false){
                    $this->criticalerrors[]=get_string('requiredparammissing','enhancedfile',$param);
                } else {
                    $this->paramvals[$param]=$pval;
                }
            }
        }
        if (!empty($this->optional_params)){
            foreach ($this->optional_params as $param=>$type){
                $param=strval($param);
                $pval=optional_param($param, false, $type);
                $this->paramvals[$param]=$pval;
            }
        }
        // recast param vals to stdObject
        $this->paramvals=(object) $this->paramvals;
    }

    /**
     * output warning and die on critical errors
     * @todo - escape xml
     */
    protected function dieoncriticalerrors(){
        echo ('<criticalerrors>');
        foreach ($this->criticalerrors as $criterr){
            echo ('<criticalerror>'.$criterr.'</criticalerror>');
        }
        echo ('</criticalerrors>');
        die;
    }

    /**
     * security check
     * @global stdObject $COURSE
     * @return boolean
     */
    protected function security_check(){
        global $COURSE;
        $course=optional_param('course',0,PARAM_INT);
        $validlogin=$this->asec->valid_login($course);
        $this->secchecks[]=$validlogin;
        if ($this->secalertlive){
            echo ($validlogin->output());
        }
        if ($validlogin->success==false){
            return (false);
        }
        if (!empty($this->caps)){
            $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
            foreach ($this->caps as $cap){
                $hascap=$this->asec->has_capability($cap, $context);
                $this->secchecks[]=$hascap;
                if ($this->secalertlive){
                    echo ($hascap->output());
                }
                if ($hascap->success==false){
                    return (false);
                } else {
                    if ($this->capany){
                        return (true);
                    }
                }
            }
        }
        return (true);
    }
    
    /**
     * start page
     */
    protected function page_start(){
        // prevent caching
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // important - this is so AJAX can parse response as xml
        header('Content-type: text/xml; Charset=utf-8');
        echo ("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
    }

    /**
     * security check failed
     * show security alerts
     * @return null
     */
    protected function security_alert(){
        if ($this->secalertlive){
            return;
        }
        echo ("\n").'<security_checks>';
        foreach ($this->secchecks as $check){
            $check->output();
        }
        echo ("\n").'</security_checks>';
    }

    /**
     * main function - output xml here
     */
    abstract protected function main();
}

/**
 * class ajaj page (JSON output)
 */
abstract class ajaj_page extends ajax_page{

    /**
     * output security notices as they happen
     * @var boolean
     */
    protected $secalertlive=false;

    /**
     * json object to be encoded
     * @var stdObject
     */
    protected $jsonobj;

    /**
     * ajax security type
     * @var integer
     */
    protected $ajaxsectype=AJAJ_SECURITY;

    function __construct(){
        $this->jsonobj=(object) array ('security_pass'=>false, 'security_checks'=>null, 'data'=>null);
        parent::__construct();
        echo (json_encode($this->jsonobj));
    }

    /**
     * security check for json
     * @return boolean
     */
    protected function security_check(){        
        $this->jsonobj->security_pass=parent::security_check();
        $this->jsonobj->security_checks=$this->secchecks;
        return ($this->jsonobj->security_pass);
    }

    /**
     * start page
     */
    protected function page_start(){
        // prevent caching
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }

    /**
     * security alerts - no need to output as they get included in jsonobj
     * @return null
     */
    protected function security_alert(){
        
    }

    /**
     * output warning and die on critical errors
     * @todo - escape xml
     */
    protected function dieoncriticalerrors(){
        $this->jsonobj->criticalerrors=$this->criticalerrors;
        echo (json_encode($this->jsonobj));
        die;
    }


}

?>
