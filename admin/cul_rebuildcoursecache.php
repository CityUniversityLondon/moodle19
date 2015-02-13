<?php

/**
 * Quick admin tool to rebuild course cache.
 * @copyright 2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * //TODO: Lang strings!
 */

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

// workaround for problems with compression
if (ini_get('zlib.output_compression')) {
    @ini_set('zlib.output_compression', 'Off');
}

admin_externalpage_setup('cul_rebuildcoursecache');

$sure       = optional_param('sure', 0, PARAM_BOOL);
$specifyids = optional_param('specifyids', '', PARAM_NOTAGS);
$specifyids = trim($specifyids);

admin_externalpage_print_header();

print_heading('Rebuild course cache');

if (!data_submitted() or !confirm_sesskey() or !$sure) {   /// Print a form
    print_simple_box_start('center');
    echo '<div class="mdl-align">';
    echo '<form action="cul_rebuildcoursecache.php" method="post">';
    echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
    echo 'Course ids (comma-separated) - leave empty for \'all courses\':-<br />';
    echo '<textarea name="specifyids" rows="10" cols="65"></textarea><br /><br />';
    echo '<label for="sure">Are you sure?</label><input type="checkbox" id="sure" name="sure" value="1" /></div>';
    echo '<input type="submit" value="Yes, do it now" /><br />';
    echo '</form>';
    echo '</div>';
    print_simple_box_end();
    admin_externalpage_print_footer();
    exit;
}

print_simple_box_start('center');

if (empty($specifyids)) {
    notify('Rebuilding course cache for all courses...');
    rebuild_course_cache();
    notify('...finished.', 'notifysuccess');
} else {
    $courseids = preg_split('/[\s,]+/', $specifyids);
    foreach ($courseids as $courseid) {

        $courseid = trim($courseid);
        if (empty($courseid)) {
            continue;
        } else if (!preg_match('/\A\d+\z/', $courseid)) {
            notify("INVALID FORMAT - '{$courseid}' is not a course id! <br />", 'notifyproblem');
            continue;
        }

        if (record_exists('course', 'id', $courseid)) {
            rebuild_course_cache($courseid);
            notify("SUCCESS - Course ID: {$courseid} <br />", 'notifysuccess');
        } else {
            notify("FAIL - Could not find Course ID: {$courseid} <br />", 'notifyproblem');
        }
    }
}
echo '<br /><br />';
print_single_button("index.php", null, get_string('continue'));
print_simple_box_end();
admin_externalpage_print_footer();

