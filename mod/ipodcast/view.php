<?PHP
/******************************************************************************\
 *
 * Filename:    view.php
 *
 *		This file supplies the views for the entire course iPodcast
 *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *				10/18/06 Tom Dolsky     - Fields now properly escaped for Database unfriendly characters
 *				10/23/06 Tom Dolsky     - Fixed tab text headings
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

    global $USER;
	global $CFG;

    require_once("../../config.php");
    require_once("lib.php");
    require_once("$CFG->libdir/rsslib.php");
    require_once("$CFG->libdir/filelib.php");
    require_once($CFG->libdir.'/blocklib.php');
	
    $id = optional_param('id', PARAM_INT);           // Course Module ID
    $tab  = optional_param('tab', IPODCAST_STANDARD_VIEW);    // which view by tab
    $action  = optional_param('action', '');
	//The id should be the course module id
	if (isset($SESSION->modform)) {   // Variables are stored in the session
        $form = $SESSION->modform;
        unset($SESSION->modform);
    } else {
        $form = (object)$_POST;
    }	
	
    if (!empty($id)) {
        if (!$cm = get_coursemodule_from_id('ipodcast',$id)) {
            error("Course Module ID was incorrect");
        }
        if (!$course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
        if (!$ipodcast = get_record("ipodcast", "id", $cm->instance)) {
            error("Course module instance is incorrect");
        }
		if (!$ipodcast_course = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
            error("ipodcast id is incorrect");
        }

    	$form->id = $id;
	} else {
        error("Must specify iPodcast ID or course module ID");
    }

    require_course_login($course);	
    $isteacher = isteacher($course->id);
	
	if (!empty($edit) && $PAGE->user_allowed_editing()) {
        if ($edit == 'on') {
            $USER->editing = true;
        } else if ($edit == 'off') {
            $USER->editing = false;
        }
	}

    add_to_log($course->id, "ipodcast", "view", "view.php?id=$cm->id", stripslashes_safe($ipodcast->name));

/// Print the page header

    $stripodcasts = get_string("modulenameplural", "ipodcasts");
    $stripodcast  = get_string("modulename", "ipodcast");

    $navigation = build_navigation('', $cm);
    print_header_simple(format_string($ipodcast->name), "", $navigation, "", "", true,
                  "", navmenu($course, $cm));                  
                  
	//If in editing mode go straight to edit tab	
	if(isset($USER->editing) && $USER->editing == true && !isset($tab)) {
		$tab = IPODCAST_EDIT_VIEW;
	}

	$icon = "<img align=\"middle\" height=\"16\" width=\"16\" src=\"$CFG->modpixpath/ipodcast/icon.gif\" alt=\"\" />&nbsp;";
	
	switch($tab) {
		case IPODCAST_STANDARD_VIEW:
			print_heading_with_help(get_string("view","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'view';
			break;
		case IPODCAST_EDIT_VIEW:
			print_heading_with_help(get_string("edit","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'edit';
			break;
		case IPODCAST_ITUNES_VIEW:
			print_heading_with_help(get_string("itunes","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'itunes';
			break;
		case IPODCAST_ATTACHMENT_VIEW:
			print_heading_with_help(get_string("attachment","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'attachment';
			break;
		case IPODCAST_VISIBILITY_VIEW:
			print_heading_with_help(get_string("edit","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'visibility';
			break;
		case IPODCAST_COMMENT_VIEW:
			print_heading_with_help(get_string("commentson","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'comment';
			break;
		case IPODCAST_VIEWS_VIEW:
			print_heading_with_help(get_string("views","ipodcast") . " " . stripslashes_safe($ipodcast->name), 'ipodcast' , 'ipodcast', $icon);		
			$currenttab = 'views';
			break;
		default:
			$currenttab = 'view';
			break;
	}
	
    // Print heading and tabs for teacher
     include('viewtabs.php');
/// Print the main part of the page
	
?>		
	<table width = "800" align="center"  id="view" class="generalbox" border="0" cellpadding="5" cellspacing="0">
	<tr>
		<td  class="generalboxcontent">            
		</p>
		<?php 
		switch($currenttab) {
			case 'view';
				ipodcast_print_view($ipodcast,$form); 			
				break;
			case 'edit';
    			switch ($action) {			
					case "update":
						$ipodcast->timemodified = time();
						$ipodcast->name =  $form->name;
						$ipodcast->notes = $form->notes;		
						$ipodcast->summary = $form->summary;

						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_EDIT_VIEW;
						if(!update_record("ipodcast", addslashes_object($ipodcast))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}		
						redirect($next_url);
						break;
					default:
						ipodcast_print_edit($ipodcast, $form); 			
						break;
				}
				break;
			case 'itunes';
    			switch ($action) {			
					case "update":
						$ipodcast->timemodified = time();
						$ipodcast->subtitle = $form->subtitle;
						$ipodcast->keywords = $form->keywords;
						$ipodcast->topcategory = $form->topcategory;		
						$ipodcast->nestedcategory = $form->nestedcategory;		
						$ipodcast->explicit = $form->explicit;		
						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_ITUNES_VIEW;
					
						if(!update_record("ipodcast", addslashes_object($ipodcast))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}		
						redirect($next_url);
						break;
					default:
						ipodcast_print_itunes($ipodcast, $form); 			
						break;
				}
				break;
			case 'attachment';
    			switch ($action) {			
					case "update":
						$ipodcast->timemodified = time();
						$ipodcast->attachment = $form->attachment;
						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_ATTACHMENT_VIEW;
					
						if(!update_record("ipodcast", addslashes_object($ipodcast))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}		
						redirect($next_url);
						break;
					default:
						ipodcast_print_attachment($ipodcast, $form); 			
						break;
				}
				break;
			case 'visibility';
    			switch ($action) {			
					case "update":
						$ipodcast->timemodified = time();	
			
						if (isset($form->startyear)) {
							$ipodcast->timestart  = make_timestamp($form->startyear, $form->startmonth, $form->startday, 
																	  $form->starthour, $form->startminute, 0);
							$ipodcast->timefinish = make_timestamp($form->finishyear, $form->finishmonth, $form->finishday, 
																	  $form->finishhour, $form->finishminute, 0);					
						} else {
							$ipodcast->timestart  = 0;
							$ipodcast->timefinish = 0;
						}
			
						if(isset($form->visible)) {
							set_field("course_modules", "visible", $form->visible, "id",  $cm->id); // Show all related activity modules												
						}
						
						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_VISIBILITY_VIEW;
						if(!update_record("ipodcast", addslashes_object($ipodcast))) {
							error("Could not update ipodcast record: ".$db->ErrorMsg(), $next_url);
						}		
						redirect($next_url);
						break;
					default:
						ipodcast_print_visibility($ipodcast, $form); 			
						break;
				}
				break;
			case 'comment';
    			switch ($action) {			
					case "add":
        				$comment = NULL;
		                unset($comment);	
						$comment->entryid = $ipodcast->id;
						$comment->comments = addslashes($form->comments); // AD
						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_COMMENT_VIEW;
						if(!ipodcast_add_comment_instance($comment)) {
							error("Could not add comment record: ".$db->ErrorMsg(), $next_url);						
						}
						redirect($next_url);
						break;
					case "update":
						$next_url = "$CFG->wwwroot/mod/ipodcast/view.php?id=$id&tab=" . IPODCAST_COMMENT_VIEW;
						if($comment = get_record("ipodcast_comments","id",$form->commentid)) {
							$comment->timemodified = time();
							$comment->comments = addslashes($form->comments);	// AD
							if(!update_record("ipodcast_comments", $comment)) {
								error("Could not update ipodcast comment record: ".$db->ErrorMsg(), $next_url);
							}								
						}
						redirect($next_url);
						break;
					default:
						ipodcast_print_comment($ipodcast, $form); 			
						break;
				}
				break;				
			case 'views';
				ipodcast_print_views($ipodcast, $form); 			
				break;
			default:
				ipodcast_print_view($ipodcast); 			
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