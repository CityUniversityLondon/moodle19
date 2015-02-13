<?php 
/******************************************************************************\
 *
 * Filename:    index.php
 *
 *		This file supplies a list of each individual iPodcast
 *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *				01/19/06 Tom Dolsky     - Removed ipodcast_course visibility
 * 				02/06/06 Tom Dolsky     - Added routines for the itunes subscribe link
 * 				10/20/06 Tom Dolsky     - Fixed apostrophie display in name and notes
 * 				10/24/06 Tom Dolsky     - Fixed empty podcast continue button link
 * 				12/23/06 Tom Dolsky     - Extended the rsslink flags to include global per module and per course
 *				01/02/07 Tom Dolsky     - Changed stripslashes to stripslashes_safe
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

    require_once("../../config.php");
    require_once("lib.php");
    require_once("rsslib.php");
    require_once("$CFG->libdir/rsslib.php");
    require_once("$CFG->libdir/weblib.php");
	global $USER;
	
   $id = required_param('id', PARAM_INT);   // course
   $mode = optional_param('mode', PARAM_INT);   // edit ipodcast or ipodcast course


	if (!empty($edit) && $PAGE->user_allowed_editing()) {
    	if ($edit == 'on') {
        	$USER->editing = true;
        } else if ($edit == 'off') {
            $USER->editing = false;
        }
    }
	
    if (! $ipodcast_course = get_record("ipodcast_courses", "course", $id)) {
        error("iPodcast ID is incorrect");
    }

	if(!$course = get_record("course","id", $ipodcast_course->course)) {
        error("Could not find ipodcast course ID:$ipodcast_course->course");
		die;
		}	
		
    require_course_login($course);		

    add_to_log($course->id, "ipodcast", "view all", "index.php?id=$ipodcast_course->course", $ipodcast_course->name);
	
	$isteacheredit = ipodcast_is_teacheredit($ipodcast_course, $USER->id);

/// Get all required strings

    $stripodcasts = get_string("modulenameplural", "ipodcast");
    $stripodcast  = get_string("modulename", "ipodcast");
    $strrss = get_string("rss");

/// Print the header

    $navlinks = array();
    $navlinks[] = array('name' => $stripodcasts, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple("$stripodcasts", "", $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data
			
	if(!$ipodcasts = get_records("ipodcast","ipodcastcourseid", $ipodcast_course->id)) {
        notice("There are no ipodcasts", "../../course/view.php?id=$ipodcast_course->course");
		die;
		}


    if($isteacheredit) {
		echo '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr valign="top"><td align="right">';
		$options["id"] = "$course->id";
    	print_single_button("setupcourse.php", $options, get_string("coursesetup","ipodcast"));
		echo '</td></tr></table>';	
	}
								
	display_rss_link($ipodcast_course->id);

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
    $strentries  = get_string("notes", "ipodcast");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strentries);
        $table->align = array ("CENTER", "LEFT", "CENTER");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strentries);
        $table->align = array ("CENTER", "LEFT", "CENTER");
    } else {
        $table->head  = array ($strname, $strentries);
        $table->align = array ("LEFT", "CENTER");
    }

    $currentsection = "";
	
    foreach ($ipodcasts as $ipodcast) {
							 
	$cm = get_record_sql("SELECT cm.*, ie.name,cs.section
                           FROM {$CFG->prefix}course_modules cm,
                                {$CFG->prefix}course_sections cs,
                                {$CFG->prefix}modules md,
                                {$CFG->prefix}ipodcast_courses i,
                                {$CFG->prefix}ipodcast ie
                           WHERE cm.course = '$course->id' AND
                                 cm.instance = ie.id AND
								 cm.section = cs.id AND
								 ie.ipodcastcourseid = i.id AND
								 md.name = 'ipodcast' AND
                                 md.id = cm.module AND
                                 ie.id = '$ipodcast->id'"); 
		if(empty($cm->id)){ 						 
            error("Course Module ID was incorrect");
            error('boo');
            error(print_r($cm));
			die;
        }	

        if (!$cm->visible) {
		    //Show dimmed if the mod is hidden
			$link = "<a class=\"dimmed\" href=\"view.php?id=$cm->id\">".format_string(stripslashes_safe($ipodcast->name),true)."</a>";
        } else {
            $link = "<a href=\"view.php?id=$cm->id\">".format_string(stripslashes_safe($ipodcast->name),true)."</a>";
        }
		
		//Add edit buttons if in edit mode
		if(isset($USER->editing) && $USER->editing == true) {
			$link .= "&nbsp;&nbsp;" . ipodcast_make_editing_buttons($cm, true, true, $cm->indent, $cm->section);
		}
		$notes = substr ($ipodcast->notes,0,40);
        $printsection = "";
		
        if ($cm->section !== $currentsection) {
            if ($cm->section) {
                $printsection = $cm->section;
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->section;
        }
		
        if ($course->format == "weeks" or $course->format == "topics") {
            $linedata = array ($printsection, $link, stripslashes_safe($notes));
        } else {
            $linedata = array ($link, stripslashes_safe($notes));
        }
		
        $table->data[] = $linedata;
    }

    echo "<br />";

    print_table($table);

/// Finish the page
    print_footer($course);


function display_rss_link ($ipodcastcourseid) {

    global $USER;
    global $CFG;
	
	if (empty($USER->id)) {
		$userid = NULL;
	} else {
		$userid = $USER->id;
	}	
	
    if (! $ipodcast  = get_record("ipodcast_courses", "id", $ipodcastcourseid)) {
         notify("Error finding ipodcast with $ipodcastcourseid");
    } else {

		$tooltiptext = $ipodcast->name . " RSS feed";	
		
		if (! $ipodcast_course = get_record("ipodcast_courses", "id", $ipodcastcourseid)) {
			error("Course ID was incorrect");
		}			
		$isstudent = ipodcast_is_student($ipodcast_course,$userid);	
		$isteacher = ipodcast_is_teacher($ipodcast_course,$userid);
		
		if ($isstudent || $isteacher || isadmin()) {
			$rssenable = $ipodcast->enablerssfeed && $CFG->ipodcast_enablerssfeeds && $CFG->enablerssfeeds;
		} else	{
			$rssenable = false;
		}

					
		if($rssenable){
            echo '<table width="100%" border="0" cellpadding="3" cellspacing="0">';
			if($ipodcast->enablerssitunes) {
			echo '<tr valign="top">';
			echo '<td align="right">';
	            ipodcast_podcast_print_link($ipodcast->course, $userid, "ipodcast", $ipodcast->id, "Add to iTunes Podcasts");
				echo "</td></tr>";
			}
 //           echo 'rss feeds';			
			echo '<tr valign="top">';
			echo '<td align="right">';
            ipodcast_rss_print_link($ipodcast->course, $userid, "ipodcast", $ipodcast->id, $tooltiptext);
            echo '</td></tr></table>';			
		}
		else {
			echo "&nbsp;";
		}
	}
}
?>