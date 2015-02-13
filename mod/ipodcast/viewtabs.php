<?php

    if (empty($ipodcast)) {
        error('You cannot call this script in that way');
    }
    if (!isset($currenttab)) {
        $currenttab = '';
    }
    if (!isset($cm)) {
        $cm = get_coursemodule_from_instance('ipodcast', $ipodcast->id);
    }
    if (!isset($course)) {
        $course = get_record('course', 'id', $ipodcast->course);
    }

    $tabs = array();
    $row  = array();
    $inactive = array();

    $row[] = new tabobject('view', "view.php?id=$cm->id&tab=" . IPODCAST_STANDARD_VIEW, get_string('viewipodcast', 'ipodcast', get_string('modulename', 'ipodcast')));
        $row[] = new tabobject('comment', "view.php?id=$cm->id&tab=" . IPODCAST_COMMENT_VIEW, get_string('comments', 'ipodcast', get_string('modulename', 'ipodcast')));	
    if (isteacheredit($course->id)) {
        $row[] = new tabobject('views', "view.php?id=$cm->id&tab=" . IPODCAST_VIEWS_VIEW, get_string('viewstab', 'ipodcast', get_string('modulename', 'ipodcast')));	
        $row[] = new tabobject('edit', "view.php?id=$cm->id&tab=" . IPODCAST_EDIT_VIEW, get_string('editipodcast', 'ipodcast', get_string('modulename', 'ipodcast')));
		if($ipodcast_course->enablerssitunes) {
    		$row[] = new tabobject('itunes', "view.php?id=$cm->id&tab=" . IPODCAST_ITUNES_VIEW, get_string('itunes', 'ipodcast'));
		}
    	$row[] = new tabobject('attachment', "view.php?id=$cm->id&tab=" . IPODCAST_ATTACHMENT_VIEW, get_string('attachment', 'ipodcast'));
    	$row[] = new tabobject('visibility', "view.php?id=$cm->id&tab=" . IPODCAST_VISIBILITY_VIEW, get_string('visibility', 'ipodcast'));
    	}

    $tabs[] = $row;

    print_tabs($tabs, $currenttab, $inactive);

?>
