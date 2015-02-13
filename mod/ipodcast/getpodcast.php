<?PHP 
/******************************************************************************\
 *
 * Filename:    getpodcast.php
 *
 *		This file adds
 *
 *
 * History:     02/06/06 Tom Dolsky     - Created Tested and added this copyright and info
 * History:     01/02/07 Tom Dolsky     - Updated to support roles
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/


    $nomoodlecookie = true;     // Because it interferes with caching
    // Need to figure out how to make sure there are no html printable characters in the following file includes
    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/ipodcast/rsslib.php');
    require_once($CFG->dirroot.'/mod/ipodcast/lib.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/rsslib.php');

    $lifetime = 3600;  // Seconds for files to remain in caches - 1 hour
	
    $relativepath = get_file_argument('getpodcast.php');

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
    $filename   = "$modulename-$courseid-$userid-$instance.pcast";
    
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
		
	$isstudent = ipodcast_is_student($ipodcast_course->id,$userid);	
	$isteacher = ipodcast_is_teacher($ipodcast_course->id,$userid);

    //Check for "security" if !course->guest or course->password
    if ($course->id != SITEID) {
        if ((!$course->guest || $course->password) && (!($isstudent || $isteacher))) {
            not_found();
        }
    }

    //Check for "security" if the course is hidden or the activity is hidden 
    if ((!$course->visible) && (!$isteacher)) {
        not_found();
    }

    add_to_log($courseid, "ipodcast", "get podcast link", "index.php?id=$courseid", $filename, 0,$userid);

	$item->title = "$course->fullname";
	$item->subtitle = html2text($course->summary);
	
	if ($CFG->slasharguments) {
		$item->link = "$CFG->wwwroot/mod/ipodcast/getfeed.php/$courseid/$userid/ipodcast/$instance/rss.xml";
	} else {
		$item->link = "$CFG->wwwroot/mod/ipodcast/getfeed.php?file=/$courseid/$userid/ipodcast/$instance/rss.xml";
	}

    if(!$result = ipodcast_build_podcast_link($item)) {
        not_found();
	}	

	send_file($result, $filename, $lifetime, false, true);

    function not_found() {
        /// error, send some XML with error message
        global $lifetime, $filename;
        send_file(rss_geterrorxmlfile(), $filename, $lifetime, false, true);
    }
	
?>
