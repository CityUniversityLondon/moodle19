<?php
/**
 * default reflection object that belongs to an asset
 */
class reflectiondata {
    public $id;
    public $title;
    public $type;
    public $selection_type;
    public $text;
    protected $reminder;

    function __construct() {
        $this->id = 0;
        $this->title = "Reflection on Learning";
        $this->type = 'reflection';
        $this->text =  "";
        $this->reminder = get_rfc3339_datetime();
    }

    function set_text($str){
        $this->text = $str;
    }
    function get_text(){
        return $this->text;
    }

    function set_hrs($str){
        $this->hrs = $str;
    }
    function get_hrs(){
        return $this->hrs;
    }

    function set_mins($str){
        $this->mins = $str;
    }
    function get_mins(){
        return $this->mins;
    }

    function set_points($str){
        $this->points = $str;
    }
    function get_points(){
        return $this->points;
    }

    function set_reminder($str){
        $this->reminder = $str;
    }
    function get_reminder(){
        return $this->reminder;
    }
    
    function set_type($str){
        $this->selection_type = $str;
    }
    function get_type(){
        return $this->selection_type;
    }

    function get_xml(){
        $str = '';
        $str.= '<id>portfolio:'.$this->get_type().'/'.$this->id.'</id>';
        $str.= '<updated>'.get_rfc3339_datetime().'</updated>';
        return $str;
    }

}
?>
