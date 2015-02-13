<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
function respondusws_add_instance($instance)
{
	if (count_records("respondusws") > 0)
		return get_string("onlyoneinstance", "respondusws");
	$instance->timecreated = time();
	$instance->timemodified = $instance->timecreated;
	$record_id = insert_record("respondusws", $instance);
	if (!$record_id)
		return false;
    return $record_id;
}
function respondusws_update_instance($instance)
{
	$instance->timemodified = time();
    $instance->id = $instance->instance;
	if (!update_record("respondusws", $instance))
		return false;
    return true;
}
function respondusws_delete_instance($id)
{
	if (count_records("respondusws") == 1)
		return get_string("oneinstancerequired", "respondusws");
    $instance = get_record("respondusws", "id", $id);
	if (!$instance)
        return false;
	if (!delete_records("respondusws", "id", $instance->id))
        return false;
    return true;
}
function respondusws_user_outline($course, $user, $mod, $instance)
{
    $summary = new stdClass;
	$summary->time = time();
	$summary->info = get_string("nouseractivity", "respondusws");
	return $summary;
}
function respondusws_user_complete($course, $user, $mod, $instance)
{
	print_string("nouseractivity", "respondusws");
    return true;
}
function respondusws_print_recent_activity($course, $viewfullnames, $timestart)
{
	print_string("nomoduleactivity", "respondusws");
    return true;
}
function respondusws_cron()
{
	return true;
}
function respondusws_grades($instance_id)
{
	return NULL;
}
function respondusws_update_grades($instance, $user_id=0)
{
}
function respondusws_grade_item_update($instance)
{
}
function respondusws_get_participants($instance_id)
{
	return array();
}
function respondusws_scale_used($instance_id, $scale_id)
{
	return false;
}
function respondusws_scale_used_anywhere($scale_id)
{
	return false;
}
function respondusws_install()
{
	$module = get_record("modules", "name", "respondusws");
	if (!$module)
		return false;
	$instance = new stdClass;
	$instance->course = SITEID;
	$instance->name = get_string("sharedname", "respondusws");
	$instance->intro = get_string("sharedintro", "respondusws");
	$instance->introformat = FORMAT_PLAIN;
	$instance->modulename = $module->name;
	$instance->module = $module->id;
	$instance->section = 0;
	$instance->coursemodule = "";
	$instance->instance = "";
	$instance->cmidnumber = "";
	$instance->groupmode = 0;
	$instance->groupingid = 0;
	$instance->groupmembersonly = 0;
	$instance->visible = false; 
	$instance_id = respondusws_add_instance(addslashes_recursive($instance));
	if (!$instance_id || is_string($instance_id))
		return false;
	$instance->instance = $instance_id;
	$cmid = add_course_module($instance);
	if (!$cmid)
		return false;
	$instance->coursemodule = $cmid;
	$section_id = add_mod_to_section($instance);
	if (!$section_id)
		return false;
	if (!set_field("course_modules", "section", $section_id, "id", $cmid))
		return false;
    set_coursemodule_visible($cmid, $instance->visible);
	set_coursemodule_idnumber($cmid, $instance->cmidnumber);
	rebuild_course_cache(SITEID);
    return true;
}
function respondusws_uninstall()
{
	return true;
}
function respondusws_get_view_actions()
{
    return array(
	  "view",
	  "view all"
	  );
}
function respondusws_get_post_actions()
{
    return array(
	  "publish",
	  "retrieve"
	  );
}
function respondusws_delete_course($course, $showfeedback)
{
}
function respondusws_process_options(&$instance)
{
}
function respondusws_process_email($modargs, $body)
{
}
function respondusws_refresh_events($course_id=0)
{
	return true;
}
function respondusws_print_overview($courses, &$htmlarray)
{
    global $CFG;
	if (empty($courses) || !is_array($courses) || count($courses) == 0)
        return;
    $records = get_all_instances_in_courses("respondusws", $courses);
    if (!$records)
        return;
    foreach ($records as $instance) {
		$summary =
		  '<div class="respondusws overview">' .
          '<div class="name">' . get_string("modulename", "respondusws") .
		  ': <a ' . ($instance->visible ? '' : ' class="dimmed"') .
          ' href="' . $CFG->wwwroot . '/mod/respondusws/view.php?id=' .
		  $instance->coursemodule . '">' . $instance->name .
		  '</a></div></div>';
        if (empty($htmlarray[$instance->course]["respondusws"]))
            $htmlarray[$instance->course]["respondusws"] = $summary;
        else
            $htmlarray[$instance->course]["respondusws"] .= $summary;
	}
}
function respondusws_get_coursemodule_info($course_module)
{
	$instance = get_record("respondusws", "id", $course_module->instance);
	if (!$instance)
        return NULL;
    $info = new stdClass;
    $info->name = urlencode($instance->name);
	$info->extra = urlencode($instance->intro);
	$info->icon = "../mod/respondusws/icon.gif";
    return $info;
}
function respondusws_get_types()
{
	$types = array();
	return $types;
}
function respondusws_get_recent_mod_activity(&$activities, &$index, $timestart,
  $course_id, $cmid, $user_id=0, $group_id=0)
{
}
function respondusws_print_recent_mod_activity($activity, $course_id, $detail,
  $modnames, $viewfullnames)
{
	print_string("nomoduleactivity", "respondusws");
}
function respondusws_reset_course_form_definition(&$mform)
{
}
function respondusws_reset_course_form_defaults($course)
{
	$defaults = array();
	return $defaults;
}
function respondusws_reset_userdata($data)
{
    $component = get_string("modulenameplural", "respondusws");
    $status = array();
    if ($data->timeshift) {
        shift_course_mod_dates("choice", array("timecreated", "timemodified"),
		  $data->timeshift, $data->courseid);
        $status[] = array(
		  "component" => $component,
		  "item" => get_string("datechanged"),
		  "error"=> false
		  );
    }
    return $status;
}
function respondusws_check_file_access($attempt_id, $question_id)
{
	return true;
}
function respondusws_role_assign($user_id, $context, $role_id)
{
}
function respondusws_role_unassign($user_id, $context)
{
}
function respondusws_upgrade_submodules()
{
}
function respondusws_question_list_instances($question_id)
{
	return array();
}
?>
