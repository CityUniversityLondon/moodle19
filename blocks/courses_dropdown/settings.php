<?php  //$Id: settings.php,v 1.1.2.2 2007/12/19 17:38:47 skodak Exp $


$options = array('all'=>get_string('allcourses', 'block_courses_dropdown'), 'own'=>get_string('owncourses', 'block_courses_dropdown'));

$settings->add(new admin_setting_configselect('block_courses_dropdown_adminview', get_string('adminview', 'block_courses_dropdown'),
                   get_string('configadminview', 'block_courses_dropdown'), 'all', $options));

$settings->add(new admin_setting_configcheckbox('block_courses_dropdown_hideallcourseslink', get_string('hideallcourseslink', 'block_courses_dropdown'),
                   get_string('confighideallcourseslink', 'block_courses_dropdown'), 0));


?>
