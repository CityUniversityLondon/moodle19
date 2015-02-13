<?php
/**
 *  assetInput from user form
 */
class assetInput{

    //leap2a default required
    public $id;
    public $course;
    public $name;
    public $timemodified;
    public $reference;
    public $type;
    public $title;
    public $description;
    public $startDate;
    public $endDate;
    public $reason;
    public $knowgledge;
    public $impact;
    public $evidence;
    public $reflection;
    public $hrs;
    public $mins;
    public $points;
    public $reminder;
    public $file;

    function __construct() {
        $timestamp = time();
        $this->id = 0;
        $this->course = 0;
        $this->name = "";
        $this->timemodified = time();//date('Y-m-d\TH:i:s', $timestamp);
        $this->reference = null;
        $this->type = "";
        $this->title = "";
        $this->description = "";
        $this->startDate = "";
        $this->endDate = "";
        $this->reason = "";
        $this->knowgledge = "";
        $this->impact = "";
        $this->evidence = "";
        $this->reflection = "";
        $this->hrs = 0;
        $this->mins = 0;
        $this->points = 0;
        $this->reminder = "";
        $this->file = null;
    }

    function set_title($str){
        $this->title = $str;
    }
    function get_title(){
        return $this->title;
    }

    function set_description($str){
        $this->description = $str;
    }
    function get_description(){
        return $this->description;
    }

    function set_startDate($str){
        $this->startDate = $str;
    }
    function get_startDate(){
        return $this->startDate;
    }

    function set_endDate($str){
        $this->endDate = $str;
    }
    function get_endDate(){
        return $this->endDate;
    }

    function set_reason($str){
        $this->reason = $str;
    }
    function get_reason(){
        return $this->reason;
    }

    function set_knowgledge($str){
        $this->knowgledge = $str;
    }
    function get_knowgledge(){
        return $this->knowgledge;
    }

    function set_impact($str){
        $this->impact = $str;
    }
    function get_impact(){
        return $this->impact;
    }

    function set_evidence($str){
        $this->evidence = $str;
    }
    function get_evidence(){
        return $this->evidence;
    }

    function set_reflection($str){
        $this->reflection = $str;
    }
    function get_reflection(){
        return $this->reflection;
    }

    function set_type($str){
        $this->type = $str;
    }
    function get_type(){
        return $this->type;
    }

    function set_file($file){
        $this->file = $file;
    }
    function get_file(){
        return $this->file;
    }
}

?>
