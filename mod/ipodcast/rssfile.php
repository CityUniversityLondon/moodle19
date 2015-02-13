<?PHP 
/******************************************************************************\
 *
 * Filename:    rssfile.php
 *
 *		This file adds
 *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/


    $nomoodlecookie = true;     // Because it interferes with caching
 
    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/rsslib.php');

    $lifetime = 3600;  // Seconds for files to remain in caches - 1 hour

    $relativepath = get_file_argument('rssfile.php');

    if (!$relativepath) {
        not_found();
    }

    // extract relative path components
    $args = explode('/', trim($relativepath, '/'));
    
    if (count($args) < 5) {
        not_found();
    }

    $courseid   = (int)$args[0];
    $userid     = (int)$args[1];
    $modulename = clean_param($args[2], PARAM_FILE);
    $instance   = (int)$args[3];
    $filename   = 'rss.xml';
    
    if (!$course = get_record("course", "id", $courseid)) {
        not_found();
    }
	
    //Check name of module
    $mods = get_list_of_plugins("mod");
    if (!in_array(strtolower($modulename), $mods)) {
        not_found();
    }
    
	$isstudent = isstudent($courseid,$userid);
    $isteacher = isteacher($courseid,$userid);

    //Check for "security" if !course->guest or course->password
    if ($course->id != SITEID) {
        if ((!$course->guest || $course->password) && (!($isstudent || $isteacher))) {
            not_found();
        }
    }

    //Check for "security" if the course is hidden or the activity is hidden 
	//The per course module visiblity is set in the xml file
    if ((!$course->visible) && (!$isteacher)) {
        not_found();
    }

    $pathname = $CFG->dataroot.'/rss/'.$modulename.'/'.$instance.'.xml';

    //Check that file exists
    if (!file_exists($pathname)) {
        not_found();
    }

    add_to_log($courseid, "rss", "get feed", "index.php?id=$courseid", "");

    //Send it to user!
    send_file($pathname, $filename, $lifetime);

    function not_found() {
        /// error, send some XML with error message
        global $lifetime, $filename;
        send_file(rss_geterrorxmlfile(), $filename, $lifetime, false, true);
    }
?>
