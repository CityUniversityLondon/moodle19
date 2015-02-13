<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
$settings->add(
  new admin_setting_heading(
    "respondusws_moduledescheader",
	get_string("moduledescheader", "respondusws"),
	get_string("moduledescription", "respondusws")
  )
);
if (!isset($module->version)
  || !isset($module->rws_release)
  || !isset($module->requires)
  || !isset($module->rws_latest)) {
	$version_file = dirname(__FILE__) . "/version.php";
	if (is_readable($version_file)) {
		include($version_file); 
	}
}
if (isset($module->version)) {
	$settings->add(
	  new admin_setting_heading(
		"respondusws_moduleversionheader",
		get_string("moduleversionheader", "respondusws"),
		"$module->version ($module->rws_release)"
	  )
	);
}
if (isset($module->rws_latest) && $module->rws_latest < $CFG->version) {
	$warning = get_string("upgradewarning", "respondusws");
	$warning .= $module->rws_latest;
	$settings->add(
	  new admin_setting_heading(
		"respondusws_upgradewarningheader",
		get_string("upgradewarningheader", "respondusws"),
		$warning
	  )
	);
}
$settings->add(
  new admin_setting_heading(
    "respondusws_adminsettingsheader",
	get_string("adminsettingsheader", "respondusws"),
	get_string("noadminsettings", "respondusws")
  )
);
?>
