<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
function respondusws_backup_mods($bf, $preferences)
{
    $ok = true; 
	if ($ok) {
		$instances = get_records("respondusws", "course",
		  $preferences->backup_course, "id");
	}
    if ($ok && $instances !== false) {
        foreach ($instances as $instance) {
			$selected = backup_mod_selected($preferences, "respondusws",
			  $instance->id);
            if ($selected)
                $ok = respondusws_backup_one_mod($bf, $preferences, $instance);
            if (!$ok)
				break;
        }
    }
    return $ok;
}
function respondusws_backup_one_mod($bf, $preferences, $instance)
{
	$ok = true;
    if ($ok && is_numeric($instance)) {
        $instance = get_record("respondusws", "id", $instance);
		$ok = ($instance !== false);
	}
	if ($ok) {
		fwrite($bf, start_tag("MOD", 3, true));
		fwrite($bf, full_tag("ID", 4, false, $instance->id));
		fwrite($bf, full_tag("MODTYPE", 4, false, "respondusws"));
		fwrite($bf, full_tag("NAME", 4, false, $instance->name));
		fwrite($bf, full_tag("INTRO", 4, false, $instance->intro));
		fwrite($bf, full_tag("INTROFORMAT", 4, false, $instance->intro));
		fwrite($bf, full_tag("TIMECREATED", 4, false, $instance->timecreated));
		fwrite($bf, full_tag("TIMEMODIFIED", 4, false,
		  $instance->timemodified));
		$selected = backup_userdata_selected($preferences, "respondusws",
		  $instance->id);
        if ($selected) {
		}
		$bytes = fwrite($bf, end_tag("MOD", 3, true));
		$ok = ($bytes !== false);
	}
	return $ok;
}
function respondusws_encode_content_links($content, $preferences)
{
	$result = $content;
    global $CFG;
    $base = preg_quote($CFG->wwwroot, "/");
    $buscar = "/(" . $base . "\/mod\/respondusws\/index.php\?id\=)([0-9]+)/";
    $result= preg_replace($buscar, '$@RESPONDUSWSINDEX*$2@$', $result);
    $buscar= "/(" . $base . "\/mod\/respondusws\/view.php\?id\=)([0-9]+)/";
    $result= preg_replace($buscar, '$@RESPONDUSWSVIEWBYID*$2@$', $result);
	return $result;
}
function respondusws_check_backup_mods($course, $user_data=false,
  $backup_unique_code, $instances=null)
{
	$info = array();
    if (!empty($instances) && is_array($instances) && count($instances)) {
        foreach ($instances as $id => $instance) {
            $inst_info = respondusws_check_backup_mods_instances($instance,
			  $backup_unique_code);
            if (!empty($inst_info))
			  $info += $inst_info;
        }
        return $info;
    }
    $info[0][0] = get_string("modulenameplural", "respondusws");
    $info[0][1] = count_records("respondusws", "course", $course);
    if ($user_data) {
    }
    return $info;
}
function respondusws_check_backup_mods_instances($instance,
  $backup_unique_code)
{
	$info = array();
    $info[$instance->id."0"][0] = "<b>" . $instance->name . "</b>";
    $info[$instance->id."0"][1] = ""; 
    if (!empty($instance->userdata)) {
    }
    return $info;
}
?>
