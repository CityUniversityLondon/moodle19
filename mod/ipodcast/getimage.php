<?PHP 
/******************************************************************************\
 *
 * Filename:    getimage.php
 *
 *		This file dynamically builds the rss feed image from a 400x400 png
 *
 *
 * History:     01/09/06 Tom Dolsky     - Created and tested
 *              01/02/07 Tom Dolsky     - Updated to support roles
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/


    $nomoodlecookie = true;     // Because it interferes with caching
 
    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/ipodcast/lib.php');
    require_once($CFG->dirroot.'/mod/ipodcast/rsslib.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/rsslib.php');;
    require_once('filelib.php');
	
    $lifetime = 3600;  // Seconds for files to remain in caches - 1 hour
//	print_header();
	
    $relativepath = get_file_argument('getimage.php');

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
    
	//Get ipodcast_courses to check it's visible
    if (!$ipodcast_course = get_record("ipodcast_courses","course",$courseid,"id",$instance)) {
        not_found();
    }
	    
	$isstudent = ipodcast_is_student($ipodcast_course,$userid);	
	$isteacher = ipodcast_is_teacher($ipodcast_course,$userid);
	
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

    add_to_log($courseid, "ipodcast", "get image", "index.php?id=$courseid", "");

	ipodcast_create_image($course);
	
    function not_found() {
        /// error, send some XML with error message
        global $lifetime, $filename;
        send_file(rss_geterrorxmlfile(), $filename, $lifetime, false, true);
    }
?>