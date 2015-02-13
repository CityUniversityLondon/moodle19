<?PHP
/******************************************************************************\
 *
 * Filename:    setupcourse.php
 *
 *		This file is a setup screen for the Course settings iPodcast
 *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *              05/15/06 Tom Dolsky     - Moved rss articles outside of enablerssitunes
 *              07/07/06 Tom Dolsky     - Added initial Darwin Support
 *				10/17/06 Tom Dolsky     - Added ability to Sort XML enclosures
 *				10/20/06 Tom Dolsky     - Added iPodcast specific darwinurl
 *				10/23/06 Tom Dolsky     - Converted to tabbed format
 *				10/23/06 Tom Dolsky     - Added image selection tab
 *				10/24/06 Tom Dolsky     - Fixed new course setup with new tab view
 *				10/25/06 Tom Dolsky     - Variable fixes for moodle 1.7
 *				12/23/06 Tom Dolsky     - Removed fields causing notices
 *				12/23/06 Tom Dolsky     - added the enablerssfeed to form
 *				01/02/07 Tom Dolsky     - Fixed some addslashes bugs
 *				01/04/07 Tom Dolsky     - Fixed some stripslashes bugs
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

    global $USER;
	global $CFG;

    require_once("../../config.php");
    require_once("lib.php");
    require_once($CFG->libdir .'/rsslib.php');
    require_once($CFG->libdir .'/filelib.php');
    require_once($CFG->libdir .'/blocklib.php');
    require_once($CFG->libdir .'/weblib.php');

	
    $id = optional_param('id', PARAM_INT);           // Course Module ID
    $tab  = optional_param('tab', IPODCASTCOURSE_EDIT_VIEW);    // which view by tab
    $action  = optional_param('action', '');
		
       
	if (isset($SESSION->modform)) {   // Variables are stored in the session
        $form = $SESSION->modform;
        unset($SESSION->modform);
    } else {
        $form = (object)$_POST;
    }	

    if ($feedback = data_submitted()) {      // No incoming data?
		if (!empty($feedback->cancel)) {          // User hit cancel button
			  redirect("$CFG->wwwroot/course/setupcourse.php?id=$id");
//			  error( " User hit cancel ");
		}
    }
	
	$usehtmleditor = can_use_html_editor();
    $defaultformat = FORMAT_HTML;

    if (!empty($id)) {
		if (!$ipodcast_course = get_record("ipodcast_courses", "course", $id)) {
			$action = "new";
//	        $icon = "<img align=\"middle\" height=\"16\" width=\"16\" src=\"$CFG->modpixpath/ipodcast/icon.gif\" alt=\"\" />&nbsp;";					
//			print_heading_with_help("iPodcast settings have not been set for this Course. Setting defaults.", 'ipodcast', 'ipodcast', $icon);
			if (!$course = get_record("course", "id", $id)) {
				error("Course is misconfigured");
			}	
			unset($ipodcast_course);
			$ipodcast_course->name = addslashes($course->fullname . " Podcast");
			$ipodcast_course->summary = addslashes($course->summary);
			$ipodcast_course->subtitle = html_to_text(addslashes($course->summary));
			$ipodcast_course->comments = ""; // AD
			$ipodcast_course->keywords = "";
//			$ipodcast_course->id = $id;
			$ipodcast_course->userid = $USER->id;
			$ipodcast_course->course = $course->id;
			$ipodcast_course->rssarticles = 5;
			$ipodcast_course->topcategory = 4;
			$ipodcast_course->nestedcategory = 14;
			$ipodcast_course->enablerssfeed = 1;
			$ipodcast_course->enablerssitunes = 1;
			$ipodcast_course->enabletsseries = 0;
			$ipodcast_course->enabledarwin = 0;
			if(isset($CFG->ipodcast_darwinurl)) {
				$ipodcast_course->darwinurl = $CFG->ipodcast_darwinurl;
			} else {
				$ipodcast_course->darwinurl = "";			
			}
			$ipodcast_course->image = "";
			$ipodcast_course->imagewidth = 144;
			$ipodcast_course->imageheight = 144;
			$ipodcast_course->explicit = 0;
			$ipodcast_course->rsssorting = 0;
			$ipodcast_course->authkey = "12345678";
			
			$return = ipodcast_add_course_instance($ipodcast_course);
			
			if (!$return) {
				error("Could not add a new instance", "../../course/view.php?id=$course->id");
			} else if (is_string($return)) {
				error($return, "../../course/view.php?id=$course->id");
			}
			
			$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$course->id&action = new&tab=" . IPODCASTCOURSE_EDIT_VIEW;
			redirect($next_url);	
								
	    } else {
			if (!$course = get_record("course", "id", $ipodcast_course->course)) {
				error("Course is misconfigured");
			}			
		}
	
    	$form->id = $id;
	} else {
        error("Must specify course ID");
    }

	require_course_login($course);	
    $isteacher = isteacher($course->id);		
		
/// Print the page header
    if ($course->category) {
        $navigation = "<A HREF=\"../../course/view.php?id=$course->id\">$course->shortname</A> ->";
    }

    $stripodcasts = get_string("modulenameplural", "ipodcast");
    $stripodcast  = get_string("modulename", "ipodcast");

    $navlinks = array();
    $navlinks[] = array('name' => $stripodcasts, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple("$stripodcasts", "", $navigation, "", "", true, "", navmenu($course));
    
	if($isteacher == true && !isset($tab)) {
		$tab = IPODCASTCOURSE_EDIT_VIEW;
	}

	$icon = "<img align=\"middle\" height=\"16\" width=\"16\" src=\"$CFG->modpixpath/ipodcast/icon.gif\" alt=\"\" />&nbsp;";
	
	switch($tab) {
		case IPODCASTCOURSE_EDIT_VIEW:
			print_heading_with_help(get_string("courseedit","ipodcast") . " " . stripslashes_safe($ipodcast_course->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'courseedit';
			break;
		case IPODCASTCOURSE_SETUP_VIEW:
			print_heading_with_help(get_string("coursesetup","ipodcast") . " " . stripslashes_safe($ipodcast_course->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'coursesetup';
			break;
		case IPODCASTCOURSE_ITUNES_VIEW:
			print_heading_with_help(get_string("courseitunes","ipodcast") . " " . stripslashes_safe($ipodcast_course->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'courseitunes';
			break;
		case IPODCASTCOURSE_IMAGE_VIEW:
			print_heading_with_help(get_string("courseimage","ipodcast") . " " . stripslashes_safe($ipodcast_course->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'courseimage';
			break;
//		case IPODCASTCOURSE_VISIBILITY_VIEW:
//			print_heading_with_help(get_string("coursevisibility","ipodcast") . " " . $ipodcast->name, 'ipodcast' , 'ipodcast', $icon);		
//			$currenttab = 'coursevisibility';
//			break;
		case IPODCASTCOURSE_DARWIN_VIEW:
			print_heading_with_help(get_string("coursedarwin","ipodcast") . " " . stripslashes_safe($ipodcast_course->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'coursedarwin';
			break;
		default:
			$currenttab = 'courseedit';
			break;
	}
	
    // Print heading and tabs for teacher
     include('setupcoursetabs.php');
/// Print the main part of the page
	
?>		
	<table width = "800" align="center"  id="view" class="generalbox" border="0" cellpadding="5" cellspacing="0">
	<tr>
		<td  class="generalboxcontent">            
		</p>
		<?php 
		switch($currenttab) {
			case 'courseedit';
    			switch ($action) {			
//					case "new":
//						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_EDIT_VIEW;
//						redirect($next_url);
//						break;
					case "update":
						$ipodcast_course->timemodified = time();
						$ipodcast_course->summary = $form->summary;
						$ipodcast_course->name = $form->name;
						$ipodcast_course->comments = $form->comments; // AD
						$ipodcast_course->enablerssfeed = $form->enablerssfeed;
													
						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_EDIT_VIEW;
						if(!update_record("ipodcast_courses", addslashes_object($ipodcast_course))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}
						redirect($next_url);
						break;
					default:
						ipodcastcourse_print_edit($ipodcast_course, $form); 			
						break;
				}
				break;
			case 'coursesetup';
    			switch ($action) {			
					case "update":
						$ipodcast_course->timemodified = time();
						$ipodcast_course->enablerssfeed = $form->enablerssfeed;
						$ipodcast_course->authkey = $form->authkey;
						$ipodcast_course->rssarticles = $form->rssarticles;
						$ipodcast_course->rsssorting = $form->rsssorting;
						$ipodcast_course->studentcanpost = $form->studentcanpost;
						$ipodcast_course->defaultapproval = $form->defaultapproval;
						$ipodcast_course->attachwithcomment = $form->attachwithcomment;
													
						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_SETUP_VIEW;
						if(!update_record("ipodcast_courses", addslashes_object($ipodcast_course))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}
						redirect($next_url);
						break;
					default:
						ipodcastcourse_print_setup($ipodcast_course, $form); 			
						break;
				}
				break;
			case 'courseitunes';
    			switch ($action) {			
					case "update":
						$ipodcast_course->timemodified = time();
						$ipodcast_course->subtitle = $form->subtitle;
						$ipodcast_course->keywords = $form->keywords;
						$ipodcast_course->explicit = $form->explicit;
						$ipodcast_course->userid = $form->userid;
						$ipodcast_course->topcategory = $form->topcategory;
						$ipodcast_course->nestedcategory = $form->nestedcategory;
						$ipodcast_course->enablerssitunes = $form->enablerssitunes;
						
						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_ITUNES_VIEW;
						if(!update_record("ipodcast_courses", addslashes_object($ipodcast_course))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}
						redirect($next_url);
						break;
					default:
						ipodcastcourse_print_itunes($ipodcast_course, $form); 			
						break;
				}
				break;
			case 'courseimage';
    			switch ($action) {			
					case "update":
						$ipodcast_course->timemodified = time();
						$ipodcast_course->image = $form->image;
						$ipodcast_course->imageheight = $form->imageheight;
						$ipodcast_course->imagewidth = $form->imagewidth;
						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_IMAGE_VIEW;
						if(!update_record("ipodcast_courses", addslashes_object($ipodcast_course))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}
						redirect($next_url);
						break;
					default:
						ipodcastcourse_print_image($ipodcast_course, $form); 			
						break;
				}
				break;
//			case 'coursevisibility';
//    			switch ($action) {			
//					case "update":
//						$ipodcast_course->timemodified = time();	
//			
//						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_VISIBILITY_VIEW;
//						redirect($next_url);
//						break;
//					default:
//						ipodcastcourse_print_visibility($ipodcast_course, $form); 			
//						break;
//				}
//				break;		
			case 'coursedarwin';
	   			switch ($action) {	
					case "update":
						$ipodcast_course->timemodified = time();
						$ipodcast_course->enabledarwin = $form->enabledarwin;
						$ipodcast_course->darwinurl = $form->darwinurl;
						$next_url = "$CFG->wwwroot/mod/ipodcast/setupcourse.php?id=$id&tab=" . IPODCASTCOURSE_DARWIN_VIEW;
						if(!update_record("ipodcast_courses", addslashes_object($ipodcast_course))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}
						redirect($next_url);
						break;
					default:
						ipodcastcourse_print_darwin($ipodcast_course, $form);
					} 			
				break;
			default:
				ipodcastcourse_print_edit($ipodcast_course, $form); 			
				break;
		}
		?>
		</td>
	</tr>
	</table>		
<?php	
/// Finish the page
    print_footer($course);
?>
		
