<?php  //$Id: settings.php,v 1.1.2.3 2008/01/24 20:29:36 skodak Exp $

require_once($CFG->dirroot.'/mod/assignment/lib.php');

$settings->add(new admin_setting_configselect('assignment_maxbytes', get_string('maximumsize', 'assignment'),
                   get_string('configmaxbytes', 'assignment'), 1048576, get_max_upload_sizes($CFG->maxbytes)));

$options = array(ASSIGNMENT_COUNT_WORDS   => trim(get_string('numwords', '')),
                 ASSIGNMENT_COUNT_LETTERS => trim(get_string('numletters', '')));
$settings->add(new admin_setting_configselect('assignment_itemstocount', get_string('itemstocount', 'assignment'),
                   get_string('configitemstocount', 'assignment'), ASSIGNMENT_COUNT_WORDS, $options));

$settings->add(new admin_setting_configcheckbox('assignment_showrecentsubmissions', get_string('showrecentsubmissions', 'assignment'),
                   get_string('configshowrecentsubmissions', 'assignment'), 1));

// CMDL-1592 Enable send for marking default as no (REQ0026604)
$settings->add(new admin_setting_configcheckbox('assignment_trackdrafts', get_string('trackdrafts', 'assignment'),
                   get_string('configtrackdrafts', 'assignment'), 1));
// end CMDL-1592

// CMDL-1620 Change default setting in Moodle Assignments (REQ0031559)
$settings->add(new admin_setting_configtext('assignment_allowmaxfiles', get_string('allowmaxfiles', 'assignment'),
                   get_string('configallowmaxfiles', 'assignment'), 1, PARAM_INT));
// end CMDL-1620
?>
