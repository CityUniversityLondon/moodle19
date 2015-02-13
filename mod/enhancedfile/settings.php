<?php  //$Id: settings.php,v 1.1.2.4 2009/11/21 22:23:03 skodak Exp $

require_once($CFG->dirroot.'/mod/enhancedfile/lib.php');

$settings->add(new admin_setting_heading('enhancedfile_settings', get_string('settings', 'enhancedfile'), ''));

$settings->add(new admin_setting_configcheckbox('enhancedfile_html5uploads', get_string('html5uploads', 'enhancedfile'),
                   get_string('cnhtml5uploads', 'enhancedfile'), 0));
				   ?>