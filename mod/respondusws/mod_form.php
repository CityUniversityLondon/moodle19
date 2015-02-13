<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
require_once($CFG->dirroot.'/course/moodleform_mod.php');
class mod_respondusws_mod_form extends moodleform_mod
{
	function definition()
	{
        global $COURSE;
        $mform =& $this->_form;
        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name",
		  get_string("responduswsname", "respondusws"), array("size"=>"64"));
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $mform->addRule("name", get_string("maximumchars", "", 255),
		  "maxlength", 255, "client");
        $mform->addElement("htmleditor", "intro",
		  get_string("responduswsintro", "respondusws"));
        $mform->setType("intro", PARAM_RAW);
        $mform->addRule("intro", get_string("required"), "required", null,
		  "client");
        $mform->setHelpButton("intro", array("writing", "richtext"), false,
		  "editorhelpbutton");
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
	}
	function data_preprocessing(&$default_values)
	{
		parent::data_preprocessing($default_values);
	}
	function definition_after_data()
	{
		parent::definition_after_data();
	}
	function validation($data, $files)
	{
		$errors = parent::validation($data, $files);
        if (count($errors) == 0)
            return true;
        else
            return $errors;
	}
}
?>
