<?php

    if (empty($ipodcast_course)) {
        error('You cannot call this script in that way');
    }
    if (!isset($currenttab)) {
        $currenttab = '';
    }

    if (!isset($course)) {
        $course = get_record('course', 'id', $ipodcast_course->course);
    }

    $tabs = array();
    $row  = array();
    $inactive = array();

     if (isteacheredit($course->id)) {
        $row[] = new tabobject('courseedit', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_EDIT_VIEW, get_string('courseedittab', 'ipodcast'));        
		$row[] = new tabobject('coursesetup', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_SETUP_VIEW, get_string('coursesetuptab', 'ipodcast'));
   		$row[] = new tabobject('courseitunes', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_ITUNES_VIEW, get_string('courseitunestab', 'ipodcast'));
   		$row[] = new tabobject('coursedarwin', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_DARWIN_VIEW, get_string('coursedarwintab', 'ipodcast'));
    	$row[] = new tabobject('courseimage', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_IMAGE_VIEW, get_string('courseimagetab', 'ipodcast'));
//    	$row[] = new tabobject('coursevisibility', "setupcourse.php?id=$course->id&tab=" . IPODCASTCOURSE_VISIBILITY_VIEW, get_string('coursevisibility', 'ipodcast'));
    	}

    $tabs[] = $row;

    print_tabs($tabs, $currenttab, $inactive);

?>
