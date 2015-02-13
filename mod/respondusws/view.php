<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
require_once(dirname(dirname(dirname(__FILE__))) . "/config.php");
require_once(dirname(__FILE__) . "/lib.php");
$id = optional_param("id", 0, PARAM_INT);
$a = optional_param("a", 0, PARAM_INT);
if ($id) {
    $cm = get_coursemodule_from_id("respondusws", $id);
	if (!$cm)
        error("Course Module ID was incorrect");
    $course = get_record("course", "id", $cm->course);
	if (!$course)
        error("Course is misconfigured");
    $module = get_record("respondusws", "id", $cm->instance);
	if (!$module)
        error("Course module is incorrect");
} else if ($a) {
    $module = get_record("respondusws", "id", $a);
	if (!$module)
        error("Course module is incorrect");
	$course = get_record("course", "id", $module->course);
	if (!$course)
        error("Course is misconfigured");
    $cm = get_coursemodule_from_instance("respondusws", $module->id,
	  $course->id);
	if (!$cm)
        error("Course Module ID was incorrect");
} else {
    error("You must specify a course module ID or an instance ID");
}
require_login($course, true, $cm);
add_to_log($course->id, "respondusws", "view", "view.php?id=$cm->id",
  "$module->id");
$strmodules = get_string("modulenameplural", "respondusws");
$strmodule  = get_string("modulename", "respondusws");
$navlinks = array();
$navlinks[] = array("name" => $strmodules,
  "link" => "index.php?id=$course->id", "type" => "activity");
$navlinks[] = array("name" => format_string($module->name), "link" => "",
  "type" => "activityinstance");
$navigation = build_navigation($navlinks);
print_header_simple(format_string($module->name), "", $navigation, "", "",
  true, update_module_button($cm->id, $course->id, $strmodule),
  navmenu($course, $cm));
$module->intro = trim($module->intro);
if (!empty($module->intro)) {
    print_box(format_text($module->intro, $module->introformat), "generalbox",
	  "intro");
}
else
	print_box("No module instance data currently available");
print_footer($course);
?>
