<?php

/**
 * default object that belongs to an asset
 */
class entry {
    public $id;
    public $title;
    public $type;
    public $selection_type;
    public $text;
    
    function __construct($object) {
       
        $this->id = $object->id;
        $this->title = $object->title;
        $this->type = $object->type;
        $this->selection_type = $object->selection_type;
        $this->text = $object->text;
        
    }

    function set_id($str){
        $this->id = $str;
    }
    function get_id(){
        return $this->id;
    }

    function set_title($str){
        $this->title = $str;
    }
    function get_title(){
        return $this->title;
    }

    function set_type($str){
        $this->type = $str;
    }
    function get_type(){
        return $this->type;
    }
    
    function set_selection_type($str){
        $this->selection_type = $str;
    }
    function get_selection_type(){
        return $this->selection_type;
    }

    function set_text($str){
        $this->text = $str;
    }
    function get_text(){
        return $this->text;
    }

}
?>
