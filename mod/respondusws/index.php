<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
require_once(dirname(dirname(dirname(__FILE__))) . "/config.php");
require_once(dirname(__FILE__) . "/lib.php");
$id = required_param("id", PARAM_INT);
$course = get_record("course", "id", $id);
if (!$course)
    error("Course ID is incorrect");
require_course_login($course);
$instances = get_records("respondusws", "course", $id, "id");
if (!$instances) {
	notice("respondusws module not installed");
	die;
}
add_to_log($course->id, "respondusws", "view all", "index.php?id=$course->id");
$strmodules = get_string("modulenameplural", "respondusws");
$strmodule = get_string("modulename", "respondusws");
$navlinks = array();
$navlinks[] = array("name" => $strmodules, "link" => "", "type" => "activity");
$navigation = build_navigation($navlinks);
print_header_simple($strmodules, "", $navigation, "", "", true, "",
  navmenu($course));
$modules = get_all_instances_in_course("respondusws", $course);
if (!$modules) {
    notice(get_string("noinstances", "respondusws"),
	  "../../course/view.php?id=$course->id");
    die;
}
$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');
if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}
foreach ($modules as $module) {
    if (!$module->visible) 
        $link = "<a class=\"dimmed\" href=\"view.php?id=$module->coursemodule\">$module->name</a>";
    else 
        $link = "<a href=\"view.php?id=$module->coursemodule\">$module->name</a>";
    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($module->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}
print_heading($strmodules);
print_table($table);
print_footer($course);
?>
