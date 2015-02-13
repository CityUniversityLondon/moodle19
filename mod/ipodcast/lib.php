<?PHP
/******************************************************************************\
 *
 * Filename:    lib.php
 *
 *		This file contains all library function for iPodcast module
 *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info.
 *				01/19/06 Tom Dolsky     - Add Tab form printing.
 *				02/04/06 Tom Dolsky     - Fixed ipodcast deleting if its an external reference.
 *				02/19/06 Tom Dolsky     - Added embedded media player.
 *				02/28/06 Tom Dolsky     - Added mpa and mpb file formats.
 *				03/24/06 Tom Dolsky     - Podcast delete now deletes matching views and comments.
 *				03/24/06 Tom Dolsky     - Fixed course delete bug.  Now course delete also deletes podcasts.
 *				10/18/06 Tom Dolsky     - Fields now properly escaped for Database unfriendly characters.
 *				10/18/06 Tom Dolsky     - Fixed comments not properly being displayed.
 *              10/20/06 Tom Dolsky     - Added hinting for Darwin files during posting.
 *              10/23/06 Tom Dolsky     - Added path configs for mpeg4ip executables.
 *              10/23/06 Tom Dolsky     - Added file and path checking for mpeg4ip executables.
 *              10/23/06 Tom Dolsky     - Added hinting button to attachment page.
 *              10/23/06 Tom Dolsky     - Fixed isteacher bug in attachment link.
 *              10/24/06 Tom Dolsky     - Added image size settings in image tab.
 *              10/24/06 Tom Dolsky     - Attachment windows create ipodcast directory if one doesn't exist.
 *				10/25/06 Tom Dolsky     - Variable fixes for moodle 1.7
 *				10/25/06 Tom Dolsky     - Added some variables to prepare for student posting
 *				10/25/06 Tom Dolsky     - Remove hinting button if no attachment or an mp3 file
 *				10/25/06 Tom Dolsky     - Remove streaming link if mp3 file attached
 *				11/06/06 Tom Dolsky     - Teacher list for course broke in 1.7 beta added version checking to get by for now
 *				12/22/06 Tom Dolsky     - Added roles based Teacher list for setupcourse
 *				12/23/06 Tom Dolsky     - Fixed several bugs in getting teacher list
 *				12/23/06 Tom Dolsky     - Changed teacher list to be the role of legacy:editingteacher
 *				01/02/07 Tom Dolsky     - Changed stripslashes to stripslashes_safe
 *				01/02/07 Tom Dolsky     - Added pdf support
 *				01/02/07 Tom Dolsky     - Added moodle 1.7 isteacher and isstudent routines
 *				01/02/07 Tom Dolsky     - Fixed some slashed argument links
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
	global $CFG;
    require_once($CFG->libdir.'/filelib.php');
    require_once($CFG->libdir.'/weblib.php');

define("CURRENTLY_RECORDING", 1);
define("RECORDED_NOT_SHRUNK", 2);
define("READY_TO_PUBLISH", 3);
define("RECORDING_POSTED", 4);
define("CURRENTLY_SHRINKING", 5);
define("CURRENTLY_PUBLISHING", 6);
define("RECORDED_READY_TO_POST", 7);
define("MOODLE_PUBLISHED", 8);

define("IPODCAST_NO_VIEW", -1);
define("IPODCAST_STANDARD_VIEW", 0);
define("IPODCAST_EDIT_VIEW", 1);
define("IPODCAST_ITUNES_VIEW", 2);
define("IPODCAST_ATTACHMENT_VIEW", 3);
define("IPODCAST_VISIBILITY_VIEW", 4);
define("IPODCAST_COMMENT_VIEW", 5);
define("IPODCAST_VIEWS_VIEW", 6);

define("IPODCASTCOURSE_NO_VIEW", -1);
define("IPODCASTCOURSE_EDIT_VIEW", 0);
define("IPODCASTCOURSE_SETUP_VIEW", 1);
define("IPODCASTCOURSE_ITUNES_VIEW", 2);
define("IPODCASTCOURSE_DARWIN_VIEW", 3);
define("IPODCASTCOURSE_IMAGE_VIEW", 4);

/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_add_instance($ipodcastentry) {

    global $USER;
	
    if (!empty($ipodcastentry->timevisibility)) {
        $ipodcastentry->timestart  = make_timestamp($ipodcastentry->startyear, $ipodcastentry->startmonth, $ipodcastentry->startday, 
                                                  $ipodcastentry->starthour, $ipodcastentry->startminute, 0);
        $ipodcastentry->timefinish = make_timestamp($ipodcastentry->finishyear, $ipodcastentry->finishmonth, $ipodcastentry->finishday, 
                                                  $ipodcastentry->finishhour, $ipodcastentry->finishminute, 0);
    } else {
        $ipodcastentry->timestart  = 0;
        $ipodcastentry->timefinish = 0;
    }
	
	$ipodcastentry->timecreated = time();
	$ipodcastentry->timemodified = time();
	if(!isset($ipodcastentry->userid)) {
		$ipodcastentry->userid = $USER->id;
	}
	
	$result = insert_record("ipodcast", $ipodcastentry);
	return $result;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_add_course_instance($ipodcastentry) {

    global $USER;

	$ipodcastentry->timecreated = time();
	$ipodcastentry->timemodified = $ipodcastentry->timecreated;
	$ipodcastentry->userid= $USER->id;
	
	return insert_record("ipodcast_courses", $ipodcastentry);

}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_add_comment_instance($comment) {

    global $USER;

	$comment->timemodified = time();
	$comment->userid = $USER->id;
	
	return insert_record("ipodcast_comments", $comment);

}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_add_view_instance($view) {

	$view->timemodified = time();
	
	return insert_record("ipodcast_views", $view);
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_update_instance($ipodcastentry) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.
    if (!empty($ipodcastentry->timevisibility)) {
        $ipodcastentry->timestart  = make_timestamp($ipodcastentry->startyear, $ipodcastentry->startmonth, $ipodcastentry->startday, 
                                                  $ipodcastentry->starthour, $ipodcastentry->startminute, 0);
        $ipodcastentry->timefinish = make_timestamp($ipodcastentry->finishyear, $ipodcastentry->finishmonth, $ipodcastentry->finishday, 
                                                  $ipodcastentry->finishhour, $ipodcastentry->finishminute, 0);

    } else {
        $ipodcastentry->timestart  = 0;
        $ipodcastentry->timefinish = 0;
    }
	
   $ipodcastentry->timemodified = time();
	$ipodcastentry->id = $ipodcastentry->instance;
	
	$result = update_record("ipodcast", $ipodcastentry);
  	return $result;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_update_course_instance($ipodcastentry) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will update an existing instance with new data.

   	$ipodcastentry->timemodified = time();
	$ipodcastentry->id = $ipodcastentry->instance;

   	return update_record("ipodcast_courses", $ipodcastentry);
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

	global $CFG;
	
    if (! $ipodcast = get_record("ipodcast", "id", "$id")) {
        return false;
    }

	//Delete matching views entry here
	if ($views = get_records("ipodcast_views", "entryid", "$id")) {
		delete_records("ipodcast_views", "entryid", "$id");    
		}

	//Delete matching comments entry here
	if ($comments = get_records("ipodcast_comments", "entryid", "$id")) {
		delete_records("ipodcast_comments", "entryid", "$id");    
		}
		
	//Delete matching tsseries entry here
	if ($tsseries = get_records("ipodcast_tsseries", "ipodcastid", "$id")) {
		delete_records("ipodcast_tsseries", "ipodcastid", "$id");    
		}
		
		
	//Delete media file if there is one
	
	if(!empty($ipodcast->attachment)) {
		if (! $ipodcastcourses = get_record("ipodcast_courses", "id", "$ipodcast->ipodcastcourseid")) {
			return false;
		}
		if(!(strstr($ipodcast->attachment,"http://")) && !(strstr($ipodcast->attachment,"https://"))) {
			$fullfile = cleardoubleslashes("$CFG->dataroot/$ipodcastcourses->course/ipodcast/$ipodcast->attachment");	
			//Need to check that file exists here
			
			if (! fulldelete($fullfile)) {
				echo "<br />Error: Could not delete: $fullfile";
			}
		}
	}
	
    $result = true;

    # Delete any dependent records here #

    if (! delete_records("ipodcast", "id", "$ipodcast->id")) {
        $result = false;
    }

    return $result;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_user_outline($course, $user, $mod, $ipodcast) {
/// Return a small object with summary information about what a 
/// user has done with a given particular instance of this module
/// Used for user activity reports.
/// $return->time = the time they did it
/// $return->info = a short text description

    return $return;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_user_complete($course, $user, $mod, $ipodcast) {
/// Print a detailed representation of what a  user has done with 
/// a given particular instance of this module, for user activity reports.

    return true;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_recent_activity($course, $isteacher, $timestart) {
/// Given a course and a time, this module should find recent activity 
/// that has occurred in NEWMODULE activities and print it out. 
/// Return true if there was output, or false is there was none.

    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}
/******************************************************************************\
 *
\******************************************************************************/
function get_tracks ($mediafile) {
    global $CFG;
	$result = array();
	$tracks = array();	
	$eachline = array();	
	$linenum = 0;
	$starttracks = false;
	
	if(empty($CFG->ipodcast_mp4infopath)) {
		echo "            Error mp4creator path not set.</br>\n";										
		return false;										
	} else if(!(file_exists($CFG->ipodcast_mp4infopath)) && !(file_exists($CFG->ipodcast_mp4infopath . ".exe"))) {
		echo "            mp4info not found.</br>\n";	
		return false;										
	} 
	
	exec($CFG->ipodcast_mp4infopath . ' ' . $mediafile,$result,$return);
	
	if($return) {
		echo "            mp4info returned error = " . $return . "</br>\n";
		return false;
	} else {
	
		foreach($result as $line) {
			$eachline[$linenum] = preg_split("/\t/ ",$line);
			if($starttracks == false) {
				if($eachline[$linenum][0] == "Track") {
					$starttracks = true;		
				}
			} else {
				$track = NULL;
				$track->track = $eachline[$linenum][0];
				$track->type = $eachline[$linenum][1];
				$track->info = $eachline[$linenum][2];
				//echo "get_tracks() Track = " . $track->track . " : Type = " . $track->type . " : info = " . $track->info . "\n";
				$tracks[] = $track;
			}
			$linenum++;
		}
		
	}				
	return $tracks;
}
/******************************************************************************\
 *
\******************************************************************************/
function check_for_hint($tracknum, $tracks) {

	foreach($tracks as $track) {
//		echo $track->info . "\n" ;
		$comparestring = "for track " . $tracknum;
		if(stristr($track->info,$comparestring)) {
//			echo "Track hinted\n";	
			return true;						
		}
	}		
return false;
}
/******************************************************************************\
 *
\******************************************************************************/
function hint_tracks($course, $attachment) {
	global $CFG;
	$tracks = array();
	$mediafile = cleardoubleslashes($CFG->dataroot . '\\' . $course . '\ipodcast\\' . $attachment);
	$mediafile = '"' . $mediafile . '"';
	$tracks = get_tracks ($mediafile);
	//echo "            hint_tracks() course = " . $course . "Attachment = " . $attachment . "\n";										
	
	if(empty($CFG->ipodcast_mp4creatorpath)) {
		echo "            Error mp4creator path not set.</br>\n";										
		return false;										
	} else if(!(file_exists($CFG->ipodcast_mp4creatorpath)) && !(file_exists($CFG->ipodcast_mp4creatorpath . ".exe"))) {
		echo "            mp4creator not found</br>\n";							
		return false;										
	}
	
	foreach($tracks as $track) {
			if((strcasecmp($track->type,"video") == 0 )|| (strcasecmp($track->type,"audio") == 0 ) ) {
				if(check_for_hint($track->track,$tracks) == false) {
					echo "            Hinting media track= " . $track->track . " : Type = " . $track->type . " : " . $track->info . "</br>\n";
					exec($CFG->ipodcast_mp4creatorpath .' -hint=' . $track->track . ' ' . $mediafile,$result,$return);
					if($return) {
						echo "            mp4creator returned error = " . $return . "</br>\n";
						return false;
					}					
										
				} else {
					echo "            Track " . $track->track ." already hinted </br>\n";							
				}
			}
	}
	return true;		
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_cron () {
/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such 
/// as sending out mail, toggling flags etc ... 

    global $CFG;
	$cron_time = time();

//Cleanup records
ipodcast_cleanup();

if ($ipodcastcourses = get_records("ipodcast_courses")) {
    	foreach ($ipodcastcourses as $ipodcastcourse) {	
			echo "\n    Checking $ipodcastcourse->name for unpublished podcasts\n";	
			if($posts = get_records("ipodcast_tsseries", "ipodcastcourseid", $ipodcastcourse->id)) {
					foreach ($posts as $post) {
//						echo "\n    Checking $post->name for unpublished\n";	
						if($post->status == RECORDING_POSTED) {
							echo "\n      " . $post->name . " is not published\n";								
							ipodcast_update_course_module($post);
						}
					}
				}
						
//		echo "\n    Checking iPodcasts for $ipodcast->name\n";	
			$items = array();
        	$info = array();
        	if ($recs = get_records_sql ("SELECT e.id AS entryid,
                                             e.name AS entryname, 
                                             e.timestart AS entrytimestart,
                                             e.timefinish AS entrytimefinish,
											 cm.visible AS entryvisible,	
											 cm.id AS entrycoursemodule
                                      FROM {$CFG->prefix}course_modules cm,
										   {$CFG->prefix}modules md,
									  	   {$CFG->prefix}ipodcast e
                                      WHERE cm.course = '$ipodcastcourse->course' AND
                                			cm.instance = e.id AND
                                			md.id = cm.module AND
								 			e.ipodcastcourseid = '$ipodcastcourse->id' AND
											md.name = 'ipodcast'
                                      ORDER BY e.timecreated desc")) {
			
			  	foreach ($recs as $rec) {
					if($rec->entryvisible) {
//						echo "        Found $rec->entryname is visible\n";				
						if($rec->entrytimefinish  !=0) {
							if($cron_time < $rec->entrytimestart || $cron_time > $rec->entrytimefinish) {
//								echo "    Making $rec->entryname not visible\n";
        						set_field("course_modules", "visible", "0", "id", $rec->entrycoursemodule); // Show all related activity modules													
							}
						}						
					} else {
//						echo "        Found $rec->entryname is not visible\n";
						if ($rec->entrytimestart != 0) {
							if($cron_time > $rec->entrytimestart && $cron_time < $rec->entrytimefinish) {
//								echo "    Making $rec->entryname visible\n";														
        						set_field("course_modules", "visible", "1", "id", $rec->entrycoursemodule); // Show all related activity modules													
							}
						}						
					}
				}				
			}
		}
	}
	
    return true;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_cleanup() {
	//Do clean up on ipodcast courses that the course has been deleted
	echo "\n      Checking for stale entries\n";	
	
	if ($ipodcast_courses = get_records("ipodcast_courses")) {
		foreach($ipodcast_courses as $ipodcast_course) {
			if(!$course = get_record("course","id",$ipodcast_course->course)) {
				delete_records("ipodcast_courses", "id", $ipodcast_course->id);
			}    
		}
	}
}
/******************************************************************************\
 *
\******************************************************************************/		
function ipodcast_grades($ipodcastid) {
/// Must return an array of grades for a given instance of this module, 
/// indexed by user.  It also returns a maximum allowed grade.
///
///    $return->grades = array of grades;
///    $return->maxgrade = maximum allowed grade;
///
///    return $return;

   return NULL;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_get_participants($ipodcastid) {
//Must return an array of user records (all data) who are participants
//for a given instance of NEWMODULE. Must include every user involved
//in the instance, independient of his role (student, teacher, admin...)
//See other modules as example.

    return false;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_scale_used ($ipodcastid,$scaleid) {
//This function returns if a scale is being used by one ipodcast
//it it has support for grading and scales. Commented code should be
//modified if necessary. See forum, glossary or journal modules
//as reference.
   
    $return = false;

    //$rec = get_record("ipodcast","id","$ipodcastcourseid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}


/*** Moodle 1.7 compatibility functions *****
 *
 ********************************************/
function ipodcast_context($ipodcast_course) {
    if (is_object($ipodcast_course)) {
        $ipodcast_course = $ipodcast_course->course;
    }
    return get_context_instance(CONTEXT_COURSE, $ipodcast_course);
}

function ipodcast_is_teacher($ipodcast_course, $userid=NULL) {
	global $CFG;
	if (isset($CFG->release) && substr($CFG->release, 0, 3)<=1.6) {		
		return isteacher($ipodcast_course->course,$userid);
	} else {
    	return has_capability('mod/ipodcast:edit', ipodcast_context($ipodcast_course), $userid);
	}
}
 
function ipodcast_is_teacheredit($ipodcast_course, $userid=NULL) {
	global $CFG;
	if (isset($CFG->release) && substr($CFG->release, 0, 3)<=1.6) {		
		return isteacheredit($ipodcast_course->course,$userid);
	} else {
		return has_capability('mod/ipodcast:edit', ipodcast_context($ipodcast_course), $userid);
		  // and has_capability('moodle/site:accessallgroups', ipodcast_context($ipodcast_course), $userid);
	}
}

function ipodcast_is_student($ipodcast_course, $userid=NULL) {	 	
	global $CFG;
	if (isset($CFG->release) && substr($CFG->release, 0, 3)<=1.6) {		 
		return isstudent($ipodcast_course->course,$userid);
	} else {
        return has_capability('mod/ipodcast:participate', ipodcast_context($ipodcast_course), $userid);
	}
}

function ipodcast_get_students($ipodcast_course, $sort='u.lastaccess', $fields='u.*') {
	global $CFG;
	if (isset($CFG->release) && substr($CFG->release, 0, 3)<=1.6) {		
		return $users = get_records("user_students","course",$ipodcast_course->course);
	} else {
    	return $users = get_users_by_capability(ipodcast_context($ipodcast_course), 'mod/ipodcast:participate', $fields, $sort);
	}
}

function ipodcast_get_teachers($ipodcast_course, $sort='u.lastaccess', $fields='u.*') {
	global $CFG;
	if (isset($CFG->release) && substr($CFG->release, 0, 3)<=1.6) {		
		return $users = get_records("user_teachers","course",$ipodcast_course->course);
	} else {
    	return $users = get_users_by_capability(ipodcast_context($ipodcast_course), 'mod/ipodcast:owner', $fields, $sort);
	}
}

/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_update_course_module($post) {
		global $CFG;
		$tempid = $post->id;
		
       include_once("$CFG->dirroot/course/lib.php");
			
        if (! $module = get_record("modules", "name", "ipodcast")) {
            echo "module not found";
			return false;
        }
		
        if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $post->ipodcastcourseid)) {
            echo "ipodcast not found\n";
			return false;
        }
		        
		if($post->status == RECORDING_POSTED) {
			$post->summary = $ipodcastcourse->summary;
			$post->subtitle = $ipodcastcourse->subtitle;
			$post->explicit = $ipodcastcourse->explicit;
			$post->keywords = $ipodcastcourse->keywords;
			$post->topcategory = $ipodcastcourse->topcategory;
			$post->nestedcategory = $ipodcastcourse->nestedcategory;
			//BUG: fix me ----- Check for attachment available here

			$post->attachment = cleardoubleslashes("/" . $post->attachment);

			if($ipodcastcourse->enabledarwin) {
				if(!hint_tracks($ipodcastcourse->course,$post->attachment)) {
					echo "Error hinting file for Darwin streaming server.\n";
				}
			}
					
			//Check return value here
			$newid = ipodcast_add_instance($post);
			
			$mod->course = $ipodcastcourse->course;
			$mod->module = $module->id;
			$mod->instance = $newid;
			$mod->section = $post->section;
			
			if (! $mod->coursemodule = add_course_module($mod) ) {
				notify("Could not add a new course module to the course '$course->fullname'");
				return false;
			}
			
			if (! $sectionid = add_mod_to_section($mod) ) {
				notify("Could not add the new course module to that section");
				return false;
			}
			
			if (! set_field("course_modules", "section", $sectionid, "id", $mod->coursemodule)) {
				notify("Could not update the course module with the correct section");
				return false;
			}
				rebuild_course_cache($ipodcastcourse->course);
	
			execute_sql("  UPDATE `{$CFG->prefix}ipodcast_tsseries` SET `status` = 8 WHERE id = {$tempid} ",false);			
			execute_sql("  UPDATE `{$CFG->prefix}ipodcast_tsseries` SET `ipodcastid` = {$newid} WHERE id = {$tempid} ",false);			
		}
				
	return true;
}		

function ipodcast_make_editing_buttons($mod, $absolute=false, $moveselect=true, $indent=-1, $section=-1) {
    global $CFG, $USER;

    static $str;
    static $sesskey;

    if (!isset($str)) {
        $str->delete    = get_string("delete");
        $str->update    = get_string("update");
        $str->duplicate    = get_string("duplicate");
        $str->hide      = get_string("hide");
        $str->show      = get_string("show");
        $str->clicktochange  = get_string("clicktochange");
        $str->forcedmode     = get_string("forcedmode");
        $sesskey = sesskey();
    }

    if ($section >= 0) {
        $section = '&amp;sr='.$section;   // Section return
    } else {
        $section = '';
    }

    if ($absolute) {
        $path = $CFG->wwwroot.'/course';
    } else {
        $path = '../../';
    }

    if ($mod->visible) {
        $hideshow = '<a title="'.$str->hide.'" href="'.$path.'/mod.php?hide='.$mod->id.
                    '&amp;sesskey='.$sesskey.$section.'"><img'.
                    ' src="'.$CFG->pixpath.'/t/hide.gif" hspace="2" height="11" width="11" '.
                    ' border="0" alt="'.$str->hide.'" /></a> ';
    } else {
        $hideshow = '<a title="'.$str->show.'" href="'.$path.'/mod.php?show='.$mod->id.
                    '&amp;sesskey='.$sesskey.$section.'"><img'.
                    ' src="'.$CFG->pixpath.'/t/show.gif" hspace="2" height="11" width="11" '.
                    ' border="0" alt="'.$str->show.'" /></a> ';
    }

    return '<span class="commands"><a title="'.$str->update.'" href="'.$CFG->wwwroot.'/mod/ipodcast/view.php?id='.$mod->id.
           '&amp;sesskey='.$sesskey.'"><img'.
           ' src="'.$CFG->pixpath.'/t/edit.gif" hspace="2" height="11" width="11" border="0" '.
           ' alt="'.$str->update.'" /></a>'.
           '<a title="'.$str->delete.'" href="'.$path.'/mod.php?delete='.$mod->id.
           '&amp;sesskey='.$sesskey.$section.'"><img'.
           ' src="'.$CFG->pixpath.'/t/delete.gif" hspace="2" height="11" width="11" border="0" '.
           ' alt="'.$str->delete.'" /></a>' . $hideshow . '</span>';
}
/******************************************************************************\
 *
\******************************************************************************/
function print_attachment_link($course, $ipodcast) {
	
	global $CFG;
	global $USER;
	
	echo "<tr valign=\"top\">\n";
	echo "<td width=\"15%\" align=\"right\"><b>Attachment:</b></td>\n";					
	echo "<td width=\"85%\" align=\"left\">\n";	
							
	if(strstr($ipodcast->attachment,"http://") || strstr($ipodcast->attachment,"https://")) {
		echo "<A HREF=\"$ipodcast->attachment\"> " . $ipodcast->name . " Attachment</A>\n";	
//	} else if(strstr($attachment,"file:")) {  //right now its just else to make compatible with our old links
	} else  {
        if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
            error( "ipodcast not found");
        }	
		$path = "$ipodcast->attachment";
	
		if(!empty($ipodcast->attachment)) {
			if(mimeinfo("type",$path) == "audio/mpeg" || mimeinfo("type",$path) == "audio/mp3") {	
				$type = "mp3";
			} else if(mimeinfo("type",$path) == "audio/m4a" || mimeinfo("type",$path) == "audio/x-m4a") {
				$type = "m4a";
			} else if(mimeinfo("type",$path) == "audio/m4b" || mimeinfo("type",$path) == "audio/x-m4b") {
				$type = "m4b";
			} else if(mimeinfo("type",$path) == "video/m4v" || mimeinfo("type",$path) == "video/x-m4v") {
				$type = "m4v";
			} else if(mimeinfo("type",$path) == "video/mp4") {
				$type = "mp4";
			} else if(mimeinfo("type",$path) == "video/quicktime") {
				$type = "mov";
			} else if(mimeinfo("type",$path) == "application/pdf") {
				$type = "pdf";
			} else {
				$templink = "";
			}
			
			$tohash = "$course:$USER->id:$ipodcast->id:$ipodcast->attachment:$ipodcastcourse->authkey";
			$temphash = md5($tohash);
		
			if ($CFG->slasharguments) {
				$tempurl = cleardoubleslashes("/mod/ipodcast/file.$type?file=/$temphash/$course/$USER->id/ipodcast/$ipodcast->id/$ipodcast->attachment");
			} else {
				$tempurl = cleardoubleslashes("/mod/ipodcast/file.$type/$temphash/$course/$USER->id/ipodcast/$ipodcast->id/$ipodcast->attachment");			
			}
			$tempurl = $CFG->wwwroot . 	$tempurl;
			if($CFG->ipodcast_usemediafilter) {
				$templink = "<A HREF=\"$tempurl\">" . stripslashes_safe($ipodcast->name) . " Attachment</A>\n";
				$templink .= "</br>\n";
				$templink .= ipodcast_mediaplugin_filter($course,$tempurl);
			} else {			
				$templink = "<A HREF=\"$tempurl\">" . stripslashes_safe($ipodcast->name) . " Attachment</A>\n";
			}
			echo $templink;
		} else {
			echo get_string('noattachment', 'ipodcast');
		}	
	
	}		

	echo"</td>";
	echo"</tr>";

}
/******************************************************************************\
 *
\******************************************************************************/
function check_attachment_link($ipodcast) {
	
	global $CFG;
	

	if(strstr($ipodcast->attachment,"http://") || strstr($ipodcast->attachment,"https://")) {
		return true;
//	} else if(strstr($attachment,"file:")) {  //right now its just else to make compatible with our old links
	} else  {
        if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
            error( "ipodcast not found");
        }		
		
		$temp_pathname = cleardoubleslashes("$CFG->dataroot/$ipodcastcourse->course/ipodcast/$ipodcast->attachment");
	//Check that file exists
		if (!file_exists($temp_pathname)) {
			return false;
		} else {
			return true;
		}	
	}		
}
/******************************************************************************\
 *
\******************************************************************************/
function check_image_link($ipodcast_course) {
	
	global $CFG;
	
	if(strstr($ipodcast_course->image,"http://") || strstr($ipodcast_course->image,"https://")) {
		return true;
//	} else if(strstr($attachment,"file:")) {  //right now its just else to make compatible with our old links
	} else  {
		$temp_pathname = cleardoubleslashes("$CFG->dataroot/$ipodcast_course->course/ipodcast/$ipodcast_course->image");
	//Check that file exists
		if (!file_exists($temp_pathname)) {
			return false;
		} else {
			return true;
		}	
	}		
}
/******************************************************************************\
 *
\******************************************************************************/
function print_darwin_link($course, $ipodcast) {
	
	global $CFG;
	global $USER;

	if (! $ipodcast_course = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
		echo "ipodcast not found\n";
		return false;
	}	
	echo "<tr valign=\"top\">\n";
	echo "<td width=\"15%\" align=\"right\"><b>Darwin Streaming:</b></td>\n";					
	echo "<td width=\"85%\" align=\"left\">\n";	
							
	if(strstr($ipodcast->attachment,"http://") || strstr($ipodcast->attachment,"https://")) {
		echo "<A HREF=\"$ipodcast->attachment\">" . stripslashes_safe($ipodcast->name) . " Attachment</A>\n";	
	} else  {
		$path = "$ipodcast->attachment";

		if(!empty($ipodcast->attachment)) {
			if(mimeinfo("type",$path) == "audio/mpeg" || mimeinfo("type",$path) == "audio/mp3") {	
				$type = "mp3";
			} else if(mimeinfo("type",$path) == "audio/m4a" || mimeinfo("type",$path) == "audio/x-m4a") {
				$type = "m4a";
			} else if(mimeinfo("type",$path) == "audio/m4b" || mimeinfo("type",$path) == "audio/x-m4b") {
				$type = "m4b";
			} else if(mimeinfo("type",$path) == "video/m4v" || mimeinfo("type",$path) == "video/x-m4v") {
				$type = "m4v";
			} else if(mimeinfo("type",$path) == "video/mp4") {
				$type = "mp4";
			} else if(mimeinfo("type",$path) == "video/quicktime") {
				$type = "mov";
			} else {
				$templink = "";
			}
			
			if($type == "mp3") {
				echo get_string('nomp3streaming', 'ipodcast');
			} else {
				$tohash = "$course:$USER->id:$ipodcast->id:$ipodcast->attachment:$ipodcast_course->authkey";
				$temphash = md5($tohash);
					
				if ($CFG->slasharguments) {
					$tempurl = cleardoubleslashes("/mod/ipodcast/file.$type/$temphash/$course/$USER->id/ipodcast/$ipodcast->id/$ipodcast->attachment");
				} else {
					$tempurl = cleardoubleslashes("/mod/ipodcast/file.$type?file=/$temphash/$course/$USER->id/ipodcast/$ipodcast->id/$ipodcast->attachment");				
				}
				$tempurl = $CFG->wwwroot . 	$tempurl;
		
				$tempdarwinurl = cleardoubleslashes("$course/ipodcast/$ipodcast->attachment");
				$tempdarwinurl = $ipodcast_course->darwinurl . $tempdarwinurl;
				$templink = "<EMBED SRC=\"sample.mov\" width=\"320\" height=\"265\" qtsrc=\"$tempdarwinurl\" qtsrcdontusebrowser>\n";
				echo $templink;				
			}

		} else {
			echo get_string('noattachment', 'ipodcast');
		}		
		
	
	}		

	echo"</td>";
	echo"</tr>";

}

/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_itunes($ipodcast, $form, $post="view.php") {
	global $CFG;
	global $USER;

	if (!isset($form->topcategory)) {
		$form->topcategory = $ipodcast->topcategory;
	}				
	if (!isset($form->nestedcategory)) {
		$form->nestedcategory = $ipodcast->nestedcategory;
	}				
	if (!isset($form->keywords)) {
		$form->keywords = stripslashes_safe($ipodcast->keywords);
	}				
	if (!isset($form->subtitle)) {
		$form->subtitle = stripslashes_safe($ipodcast->subtitle);
	}
	if (!isset($form->explicit)) {
		$form->explicit = $ipodcast->explicit;
	}
		
?>
<form name="form" method="post" action=" <?php echo "$post" ?>">
<center>
<table cellpadding="5">	
	
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("coursesubtitle", get_string("coursesubtitle", "ipodcast"), "ipodcast") ?>
	  	<?php print_string("subtitle","ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	</tr>
	
	<tr>
	  <td width="10%">&nbsp;</td>
	  		<td align="left"><input type="text" name="subtitle" size="100" value="<?php p($form->subtitle) ?>" /></td>
	  <td>&nbsp;</td>
	</tr>	
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("coursekeywords", get_string("coursekeywords", "ipodcast"), "ipodcast") ?>
	  	<?php print_string("keywords", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
		<td  width="10%" align="right">&nbsp;</td>
		<td align="left"><input type="text" name="keywords" size="100" value="<?php p($form->keywords) ?>" /></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("coursecategory", get_string("coursecategory", "ipodcast"), "ipodcast") ?>
	  	<?php print_string("category","ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	</tr>
		<tr>
		  <td  width="10%" align="right">&nbsp;</td>
		  <td  align="left"><select name="topcategory" size="4" onClick="document.form.nestedcategory.value=0;updatednested(this.selectedIndex + 1, document.form.nestedcategory.value)" style="width: 150px">
			<?php
			if($topcategories = get_records("ipodcast_itunes_categories")) {
				foreach ($topcategories as $topcategory) {
					if($topcategory->id == $form->topcategory) {
						echo "<option selected value=\"$topcategory->id\">$topcategory->name</option>\n";
					} else {
						echo "<option value=\"$topcategory->id\">$topcategory->name</option>\n";					
					}
				 }	
				 echo "</select>";	
			}
			?>	
			<select name="nestedcategory" size="4" style="width: 150px">
			</select>			</td>
			<td>&nbsp;</td>
		  </tr>		
		<tr>
			<td colspan = "2" align="left"><b>
			  <?php helpbutton("explicit", get_string("explicit", "ipodcast"), "ipodcast") ?>
			<?php print_string("explicit", "ipodcast") ?>:</b></td>		
				  <td>&nbsp;</td>
		        			        
		</tr>				
		<tr>
		  <td>&nbsp;</td>
		  <td align="left"><select size="1" name="explicit">
			<?php
					$cselected = "";
					$yselected = "";
					$nselected = "";
					if ($ipodcast->explicit == 2) {
						$cselected = " selected=\"selected\" ";
					} else if($ipodcast->explicit == 1) {
						$yselected = " selected=\"selected\" ";
					} else {
						$nselected = " selected=\"selected\" ";
					}
				?>
			<option value="2" <?php p($cselected) ?>><?php print_string("clean", "ipodcast") ?></option>
			<option value="0" <?php p($nselected) ?>><?php print_string("no") ?></option>
			<option value="1" <?php p($yselected) ?>><?php print_string("yes") ?></option>
		  </select></td>
	  <td>&nbsp;</td>
		  
		</tr>
</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCAST_ITUNES_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('saveipodcast', "ipodcast") ?>" />
</center>
</form>	
<script language="javascript" type="text/javascript">
var topcategorylist=document.form.topcategory
var nestedcategorylist=document.form.nestedcategory

var nestedcategoryArray = new Array()
nestedcategoryArray[0] = ""

<?php
if($topcategories = get_records("ipodcast_itunes_categories")) {
	foreach ($topcategories as $topcategory) {
		if($nestedcategories = get_records("ipodcast_itunes_nested", "topcategoryid" , $topcategory->id)) { // AD
			echo "nestedcategoryArray[$topcategory->id]=[\"None|0\" ,";
			$count = 0;
			foreach ($nestedcategories as $nestedcategory) {
				if($count) {
					echo ", \"$nestedcategory->name|$nestedcategory->id\"";
				} else {
					echo "\"$nestedcategory->name|$nestedcategory->id\"";								
				}
				$count++;
			}
			echo "]\n";						 		
		} else {
			echo "nestedcategoryArray[$topcategory->id]=\"\"\n";						
		}
		
	}
}
?>
function updatednested(selectedcategory, selectednesteditem) {
	nestedcategorylist.options.length=0
	if(selectedcategory>0) {
		if(nestedcategoryArray[selectedcategory].length > 0) {
			for (i=0; i<nestedcategoryArray[selectedcategory].length; i++) {
				if(nestedcategoryArray[selectedcategory][i].split("|")[1] == selectednesteditem) {
					nestedcategorylist.options[nestedcategorylist.options.length]=
					new Option(nestedcategoryArray[selectedcategory][i].split("|")[0],nestedcategoryArray[selectedcategory][i].split("|")[1],true,true)
				} else {
				nestedcategorylist.options[nestedcategorylist.options.length]=
					new Option(nestedcategoryArray[selectedcategory][i].split("|")[0],nestedcategoryArray[selectedcategory][i].split("|")[1])
				}
			}
		} else {
			nestedcategorylist.options[0]= new Option("None",0,true,true)
		}
	}
}

//Need delay for some reason I need to investigate this further
document.onLoad = setTimeout('updatednested(<?php p($ipodcast->topcategory) ?>,<?php p($ipodcast->nestedcategory)?>)',5)
</script>

<?php
			
	}
/******************************************************************************\
 *
\******************************************************************************/	
function ipodcast_print_attachment($ipodcast, $form, $post="view.php") {
	global $CFG;
	global $USER;
	require_once('filelib.php');

	
   if (!$cm = get_record("course_modules", "id", $form->id)) {
		error("Course Module ID was incorrect");
	}
	
	if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
		echo "ipodcast not found\n";
		return false;
	}	
		
	if (!isset($form->attachment)) {
		$form->attachment = $ipodcast->attachment;
	}	
	
    if (! $basedir = make_upload_directory("$ipodcastcourse->course")) {
        error("The site administrator needs to fix the file permissions");
    }
	
	if (!file_exists("$ipodcastcourse->course/ipodcast")) {
		if (! make_upload_directory("$ipodcastcourse->course/ipodcast")) {
        	error("The site administrator needs to fix the folder permissions");
		}
	}	
		
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">		
	<tr>
		<td align="right" valign="top"><b><?php print_string("attachment", "ipodcast") ?></b></td>
		<td align="left"><input type="text" name="attachment" size="100" value="<?php p($form->attachment) ?>" /></td>
	</tr>
	<?php
	if(!check_attachment_link($ipodcast)) {
	?>
	<tr>
		<td colspan="2" align="center" valign="top"><b><?php print_string("attacherr", "ipodcast") ?></b></td>
	</tr>
	<?php
	}
	?>
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCAST_ATTACHMENT_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('saveipodcast', "ipodcast") ?>" />

	<?php 
	//button_to_popup_window('/mod/ipodcast/uploadpopup.php?id={$cm->id}')
	$url = 	"/mod/ipodcast/uploadpopup.php?id=" . $cm->id;
	button_to_popup_window($url, 'popup', 'Change',480, 800, 'Popup window', 'none', false, '', '' );
	
	$fileinfo = array();
	$fileinfo = null;
	$fileinfo->filename = $form->attachment;
	$fileinfo->extension = file_get_extension($fileinfo);
	if($ipodcastcourse->enabledarwin && $fileinfo->extension != "mp3" && !empty($form->attachment)) {
		$url = 	"/mod/ipodcast/hintpopup.php?id=" . $cm->id;
		button_to_popup_window($url, 'popup', 'Hint file',480, 800, 'Popup window', 'none', false, '', '' );
	}
	?>	
	</center>
	</form>

<?php

}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_visibility($ipodcast, $form, $post="view.php") {
	global $CFG;
	global $USER;
	
   if (!$cm = get_record("course_modules", "id", $form->id)) {
		error("Course Module ID was incorrect");
	}	
	if (!$course = get_record("course", "id", $cm->course)) {
		error("Course is misconfigured");
	}	
	if(!isset($form->section)) {
		if (!$course_section = get_record("course_sections", "id", $cm->section)) {
			error("Course is misconfigured");
		}	
		$form->section = $course_section->section;
	}
	
	if (!isset($form->timefinish)) {
		if($ipodcast->timefinish) {
			$form->timefinish = $ipodcast->timefinish;
			$form->timevisibility = 1;
		} else {
			$form->timefinish = "";
		}
	}			
	if (!isset($form->timestart)) {
		if($ipodcast->timestart) {
			$form->timestart = $ipodcast->timestart;
			$form->timevisibility = 1;
		} else {
			$form->timestart = "";
		}	
	}									
	if (!isset($form->visible)) {
		$form->visible = $cm->visible;
	}

?>

	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">
	<tr>
	  <td align="right" valign="top">
		<?php helpbutton("currentvisibility", get_string("currentvisibility", "ipodcast"), "ipodcast") ?>
		<b><?php print_string("currentvisibility", "ipodcast") ?>:</b></td>
	  <td align="left">
		<select size="1"  id="menuvisible" name="visible">
		<option value="0" ><?php print_string("no") ?></option>
		<option value="1" ><?php print_string("yes") ?></option>
		</select>
		</td>
	</tr>		  
	<tr>
		<td align="right" valign="top">
		<?php helpbutton("timeview", get_string("timeview", "ipodcast"), "ipodcast") ?>
		<b><?php print_string("allowtimeview", "ipodcast") ?>:</b></td>
		<td align="left">
			<?php 
				echo "<script type=\"text/javascript\">\n";
				echo "  var subitemstime = ['startday','startmonth','startyear','starthour', 'startminute',".
									   "'finishday','finishmonth','finishyear','finishhour','finishminute'];";
				echo "</script>";
	
				echo "<input name=\"timevisibility\" type=\"checkbox\" value=\"1\" ";
				echo " onclick=\"lockoptions('form','timevisibility', subitemstime) ; updatevisibility(getvisiblevalue(document.form.timevisibility.checked))\" ";			
			
				if (isset($form->timevisibility) && $form->timevisibility == 1) {
					echo " checked=\"checked\" ";
				}
				echo " />";
	
				print_string("viewingtimetime", "ipodcast");
	
				echo "<table cellpadding=\"5\" align=\"left\"><tr><td align=\"right\" nowrap=\"nowrap\">";
				echo get_string("from").":";
				echo "</td><td align=\"left\" nowrap=\"nowrap\">";
				if (!$ipodcast->timestart and $course->format == "weeks") {
					$form->timestart  = $course->startdate + (($form->section - 1) * 604800) + 3600;
				}			
				print_date_selector("startday", "startmonth", "startyear", $form->timestart);
				print_time_selector("starthour", "startminute", $form->timestart);
				echo "</td></tr>";
				echo "<tr><td align=\"right\" nowrap=\"nowrap\">";
				echo get_string("to").":";
				echo "</td><td align=\"left\" nowrap=\"nowrap\">";
				if (!$ipodcast->timefinish and $course->format == "weeks") {
					$form->timefinish  = $course->startdate + (($form->section) * 604800) - 3600;
				}			
				print_date_selector("finishday", "finishmonth", "finishyear", $form->timefinish);
				print_time_selector("finishhour", "finishminute", $form->timefinish);
				echo "</td></tr></table>";
				
				echo "<input type=\"hidden\" name=\"hstartday\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hstartmonth\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hstartyear\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hstarthour\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hstartminute\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hfinishday\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hfinishmonth\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hfinishyear\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hfinishhour\" value=\"0\" />";
				echo "<input type=\"hidden\" name=\"hfinishminute\" value=\"0\" />";
	
	
	
				if (empty($form->timevisibility)) {
					echo "<script type=\"text/javascript\">";
					echo "lockoptions('form','timevisibility', subitemstime);";
					echo "</script>";
				}
			?>    </td>
	</tr>				
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCAST_VISIBILITY_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('saveipodcast', "ipodcast") ?>" />
	</center>
	</form>
	<script language="javascript" type="text/javascript">
	var visiblelist=document.form.visible
	
	function updatevisibility(visibility) {
		visiblelist.options.length=0
		if(visibility) {
			visiblelist.options[0]= new Option("<?php print_string('visibletostudents','ipodcast',moodle_strtolower($course->students)) ?>","1",true,true)
			visiblelist.options[1]= new Option("<?php print_string('notvisibletostudents','ipodcast',moodle_strtolower($course->students)) ?>","0")
		} else {
			visiblelist.options[0]= new Option("<?php print_string('visibletostudents','ipodcast',moodle_strtolower($course->students)) ?>","1")
			visiblelist.options[1]= new Option("<?php print_string('notvisibletostudents','ipodcast',moodle_strtolower($course->students)) ?>","0",true,true)
		}
	}
	//Need to move thi to the caller
	function getvisiblevalue(mything) {
		if( mything == 1 ) {
			return false;
		} else {
			return true;
		}
	}
	
	//Need delay for some reason I need to investigate this further
	document.onLoad = setTimeout('updatevisibility(<?php p($form->visible) ?>)',5)
	</script>


<?php

}	
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_edit($ipodcast, $form, $post="view.php") {
	global $CFG;
	global $USER;
	$usehtmleditor = can_use_html_editor();
    $defaultformat = FORMAT_HTML;
	
	if (!isset($form->summary)) {
		$form->summary = stripslashes_safe($ipodcast->summary);
	}
	if (!isset($form->name)) {
		$form->name = stripslashes_safe($ipodcast->name);
	}				
	if (!isset($form->notes)) {
		$form->notes = stripslashes_safe($ipodcast->notes);
	}
	
	if (!$ipodcast_course = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
           error("ipodcast id is incorrect");
    }
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">
	<tr>
	  <td align="right" valign="top"><b><?php print_string("name") ?>:</b></td>
	  <td align="left"><input type="text" name="name" size="100" value="<?php p($form->name) ?>" /></td>
	</tr>
	<tr>
	  <td align="right" valign="top"><b><?php print_string("summary") ?>:</b></td>
	  <td align="left"><?php print_textarea($usehtmleditor, 20, 50, 680, 400, "summary", $form->summary); ?></td>
	</tr>
	<tr>
	  <td align="right" valign="top"><b><?php print_string("notes", "ipodcast") ?>:</b></td>
	  <td align="left"><input type="text" name="notes" size="100" value="<?php p($form->notes) ?>" /></td>
	</tr>
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCAST_EDIT_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('saveipodcast', "ipodcast") ?>" />
	</center>
	</form>
	<?php
	 if ($usehtmleditor) {
		 use_html_editor("summary");
	 }		

}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_comment($ipodcast,$form = "", $post="view.php")
	{
	global $CFG;
	global $USER;
	
	if (!$cm = get_record("course_modules", "id", $form->id)) {
		error("Course Module ID was incorrect");
	}
	if (!$course = get_record("course", "id", $cm->course)) {
		error("Course is misconfigured");
	}			

	//If user is the teacher then display comments
    if (isteacheredit($course->id)) {
		if (!$comments = get_records("ipodcast_comments", "entryid", $ipodcast->id)) {
			echo get_string('nocomments','ipodcast',get_string('modulename','ipodcast'));
		} else {	
			$timenow = time();
			$strcomment  = get_string("comment","ipodcast");
			$strstudent  = $course->student;
			$strdate = get_string("date");
		
			$table->head  = array ($strstudent, $strdate, $strcomment);
			$table->align = array ("CENTER", "LEFT", "CENTER");
	
			$currentsection = "";
		
			foreach ($comments as $comment) {
				$user = get_record("user","id",$comment->userid);
				$linedata = array (fullname($user), userdate($comment->timemodified), stripslashes_safe($comment->comments)); // AD
				$table->data[] = $linedata;
			}
			echo "<br />";
			print_table($table);
		}
	} else {

		if (!$comment = get_record("ipodcast_comments", "entryid", $ipodcast->id , "userid", $USER->id)) {
			if(!isset($comment->comments)) { // AD
				$form->comments = ""; // AD
			}
			$action = "add";
		} else {
			$form->comments = stripslashes_safe($comment->comments); // AD and line 1346
			$form->commentid = $comment->id;		
			$action = "update";
		}
		

		?>
		<form name="form" method="post" action=" <?php echo "$post" ?>">
		<center>
		<table width="680" cellpadding="5">	
		<tr>
		  <td align="right" valign="top"><b><?php print_string("comment", "ipodcast") ?>:</b></td>
		  <td align="left"><input type="text" name="comments" size="100" value="<?php p($form->comments) ?>" /></td>
		</tr>	
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="commentid"          value="<?php p($form->commentid) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCAST_COMMENT_VIEW) ?>" />
	<input type="hidden" name="action"          value="<?php p($action)?>" />
	<input type="submit" value="<?php print_string('saveipodcast', "ipodcast") ?>" />
	</center>
	</form>
	<?php
	}
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_views($ipodcast,$form = "", $post="view.php")
	{
	global $CFG;
	global $USER;
	
	if (!$cm = get_record("course_modules", "id", $form->id)) {
		error("Course Module ID was incorrect");
	}
	if (!$course = get_record("course", "id", $cm->course)) {
		error("Course is misconfigured");
	}			

	//If user is the teacher then display comments
    if (isteacheredit($course->id)) {
		if (!$views = get_records("ipodcast_views", "entryid", $ipodcast->id)) {
			echo get_string('noviews','ipodcast',get_string('modulename','ipodcast'));
		} else {	
			$timenow = time();
			$strviews  = get_string("viewstab","ipodcast");
			$strstudent  = $course->student;
			$strdate = get_string("date");
		
			$table->head  = array ($strstudent, $strdate, $strviews);
			$table->align = array ("CENTER", "LEFT", "CENTER");
	
			$currentsection = "";

			foreach ($views as $view) {
				$user = get_record("user","id",$view->userid);
				$linedata = array (fullname($user), userdate($view->timemodified), $view->views);
				$table->data[] = $linedata;
			}
			echo "<br />";
			print_table($table);
		}
	} 
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_view($ipodcast,$form = "", $post="view.php")
	{
	global $CFG;
	global $USER;
	$recbytsseries = null;

   if (!$cm = get_record("course_modules", "id", $form->id)) {
		error("Course Module ID was incorrect");
	}	
	
	if (!$ipodcast_course = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
        error("ipodcast id is incorrect");
    }
		
	if ($ipodcast_tsseries = get_record("ipodcast_tsseries", "ipodcastid", $ipodcast->id)) {
		$recbytsseries = true;
	} else {
		$recbytsseries = false;
	}

		?>
		<form name="form" method="post" action=" <?php echo "$post" ?>">
		<center>
		<table width="680" cellpadding="5">
			<tr valign="top">
				<td width="15%" align="right"><b>Name:</b></td>
				<td width="85%" align="left"><?php echo stripslashes_safe($ipodcast->name) ?></td>
			</tr>		
			<tr valign="top">
				<td width="15%" align="right"><b>Summary:</b></td>
				<td width="85%" align="left"><?php echo stripslashes_safe($ipodcast->summary) ?></td>
			</tr>
			<?php if($recbytsseries) { ?>			
					<tr valign="top">
						<td width="15%" align="right"><b>Room:</b></td>
						<td width="85%" align="left"><?php echo $ipodcast_tsseries->roomname ?></td>
					</tr>
			<?php } ?>			
			<tr valign="top"> <?php
				echo "<td width=\"15%\" align=\"right\"><b>Notes:</b></td>";
				echo "<td width=\"85%\" align=\"left\">" .  stripslashes_safe($ipodcast->notes) . "</td>";
			echo "</tr>";
			echo "<tr valign=\"top\">";
				echo "<td width=\"15%\" align=\"right\"><b>Viewable:</b></td>";
				echo "<td width=\"85%\" align=\"left\">";
					if( $ipodcast->timestart != 0 ||  $ipodcast->timefinish != 0) {
						echo "<b>From:</b>";
						echo " " . userdate($ipodcast->timestart);
						echo "<br><b>To:</b>";
						echo "   " . userdate($ipodcast->timefinish);
					} else if ($cm->visible == 0){
						echo "Not visible to students";				
					} else {
						echo "Anytime";				
					}
				echo "</td>\n";
			echo "</tr>\n";
			
		$isteacher = isteacher($ipodcast_course->course,$USER->id);
		
		//Check for file ipodcast visibility
		if($cm->visible || $isteacher) {
			print_attachment_link($ipodcast_course->course,$ipodcast);
			ipodcast_print_darwin($ipodcast);
		}			

			
			echo"<tr valign=\"top\">\n";
				echo"<td>&nbsp;</td>\n";
				echo"<td>\n";
				echo"<input type=\"hidden\" name=\"id\"value=\"" . $ipodcast_course->course . "\" />\n";
				echo"</td>\n";				        
			echo"</tr>\n";			        
		echo"</table>\n";
		echo"</center>\n";
		echo"</form>\n";	
	}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_print_darwin($ipodcast)
	{
	global $CFG;
	global $USER;
	require_once('filelib.php');
	
	if (!$ipodcast_course = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
        error("ipodcast id is incorrect");
    }
	
	$fileinfo = array();
	$fileinfo = null;
	$fileinfo->filename = $ipodcast->attachment;
	$fileinfo->extension = file_get_extension($fileinfo);
	if($ipodcast_course->enabledarwin && $fileinfo->extension != "mp3") {
			print_darwin_link($ipodcast_course->course,$ipodcast);

	}
			
	}

/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_mediaplugin_filter($courseid, $url) {
    global $CFG, $THEME;
	
	if(mimeinfo("type",$url) == "audio/mpeg" || mimeinfo("type",$url) == "audio/mp3") {	
		if (empty($CFG->filter_mediaplugin_ignore_mp3)) {
			static $c;
	
			if (empty($c)) {
				if (!empty($THEME->filter_mediaplugin_colors)) {
					$c = $THEME->filter_mediaplugin_colors;   // You can set this up in your theme/xxx/config.php
				} else {
					$c = 'bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&waitForPlay=yes&';
				}
			}
			$c = htmlentities($c);
	
			$html  = '<object class="mediaplugin mp3" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"' . "\n";
			$html .= ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" ' . "\n";
			$html .= ' width="90" height="15" id="mp3player">' . "\n";
			$html .= " <param name=\"movie\" value=\"$CFG->wwwroot/filter/mediaplugin/mp3player.swf?src=$url\" />" . "\n";
			$html .= ' <param name="quality" value="high" />' . "\n";
			$html .= ' <param name="bgcolor" value="#333333" />' . "\n";
			$html .= ' <param name="flashvars" value="'.$c.'" />' . "\n";
			$html .= " <embed src=\"$CFG->wwwroot/filter/mediaplugin/mp3player.swf?src=$url\" " . "\n";
			$html .= "  quality=\"high\" bgcolor=\"#333333\" width=\"90\" height=\"15\" name=\"mp3player\" " . "\n";
			$html .= ' type="application/x-shockwave-flash" ' . "\n";
			$html .= ' flashvars="'.$c.'" ' . "\n";
			$html .= ' pluginspage="http://www.macromedia.com/go/getflashplayer">' . "\n";
			$html .= '</embed>' . "\n";
			$html .= '</object>&nbsp;' . "\n";
		}
	} else if(mimeinfo("type",$url) == "video/quicktime" || mimeinfo("type",$url) == "video/mp4" || mimeinfo("type",$url) == "video/m4v") {
		if (empty($CFG->filter_mediaplugin_ignore_mov)) {
	
			$html  = '<p class="mediaplugin mov"><object classid="CLSID:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"' . "\n";
			$html .= '        codebase="http://www.apple.com/qtactivex/qtplugin.cab" ' . "\n";
			$html .= '        height="300" width="400"' . "\n";
			$html .= '        id="quicktime" type="application/x-oleobject">' . "\n";
			$html .= "<param name=\"src\" value=\"$url\" />" . "\n";
			$html .= '<param name="autoplay" value="false" />' . "\n";
			$html .= '<param name="loop" value="true" />' . "\n";
			$html .= '<param name="controller" value="true" />' . "\n";
			$html .= '<param name="scale" value="aspect" />' . "\n";
			$html .= "\n<embed src=\"$url\" name=\"quicktime\" type=\"" . mimeinfo("type",$url) . "\" " . "\n";		//might need to change this
			$html .= ' height="300" width="400" scale="aspect" ' . "\n";
			$html .= ' autoplay="false" controller="true" loop="true" ' . "\n";
			$html .= ' pluginspage="http://quicktime.apple.com/">' . "\n";
			$html .= '</embed>' . "\n";
			$html .= '</object></p>' . "\n";
		}
	} else if(mimeinfo("type",$url) == "application/pdf") {
		$html = "";
	} else {
		$html = "";	
	}

    return $html;
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcast_popup_window ($url, $name='popup', $linkname='click here',
                               $height=480, $width=640, $title='Popup window', $options='none', $return=false) {

    global $CFG;


    if ($options == 'none') {
        $options = 'menubar=0,location=0,scrollbars,resizable,width='. $width .',height='. $height;
    }

    echo '<a target="'. $name;
    echo '" title="'. $title;
    echo '" href="';
    echo $CFG->wwwroot;
    echo $url .'" ';
    echo "onclick=\"return openpopup('$url', '$name', '$options', $fullscreen);\">$linkname</a>";
 
}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcastcourse_print_edit($ipodcast_course, $form, $post="setupcourse.php") {
	global $CFG;
	global $USER;
	$usehtmleditor = can_use_html_editor();
    $defaultformat = FORMAT_HTML;
	
	if (!isset($form->summary)) {
		$form->summary = stripslashes_safe($ipodcast_course->summary);
	}
	if (!isset($form->name)) {
		$form->name = stripslashes_safe($ipodcast_course->name);
	}				
	if (!isset($form->comments)) { // AD
		$form->comments = stripslashes_safe($ipodcast_course->comments); // AD and line 1629
	}
	if (!isset($form->enablerssfeed)) {
		$form->enablerssfeed = $ipodcast_course->enablerssfeed;
	}
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">
	<tr valign="top">
		<td align="right"><b><?php print_string("name") ?>:</b></td>
		<td align="left">
			<input type="text" name="name" size="100" value="<?php p($form->name) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>
	<tr valign="top">
		<td align="right"><b><?php print_string("summary") ?>:</b></td>
		<td align="left"><?php print_textarea($usehtmleditor, 20, 50, 300, 300, "summary", $form->summary); ?></td>
		<td>&nbsp;</td>			  
	</tr>
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("coursecomment", get_string("coursecomment", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("comment", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
		<td align="left">
			<input type="text" name="comments" size="100" value="<?php p($form->comments) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>				
	</table>
	<input type="hidden" name="enablerssfeed"    value="<?php p($form->enablerssfeed) ?>" />
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCASTCOURSE_EDIT_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('coursesave', "ipodcast") ?>" />
	</center>
	</form>
	<?php
	 if ($usehtmleditor) {
		 use_html_editor("summary");
	 }		

}
/******************************************************************************\
 *
\******************************************************************************/
function ipodcastcourse_print_setup($ipodcast_course, $form, $post="setupcourse.php") {
	global $CFG;
	global $USER;
	
	if (!isset($form->enablerssfeed)) {
		$form->enablerssfeed = $ipodcast_course->enablerssfeed;
	}
	if (!isset($form->authkey)) {
		$form->authkey = $ipodcast_course->authkey;
	}
	if (!isset($form->rssarticles)) {
		$form->rssarticles = $ipodcast_course->rssarticles;
	}
	if (!isset($form->rsssorting)) {
		$form->rsssorting = $ipodcast_course->rsssorting;
	}	
	if (!isset($form->studentcanpost)) {
		$form->studentcanpost = $ipodcast_course->studentcanpost;
	}	
	if (!isset($form->defaultapproval)) {
		$form->defaultapproval = $ipodcast_course->defaultapproval;
	}
	if (!isset($form->attachwithcomment)) {
		$form->attachwithcomment = $ipodcast_course->attachwithcomment;
	}			
			
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("courseauthkey", get_string("courseauthkey", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("authkey", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
		<td align="left">
			<input type="text" name="authkey" size="100" value="<?php p($form->authkey) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>
	
	<tr>
	<td colspan="2" align="left"><b>
	  <?php helpbutton("enablerssfeed", get_string("enablerssfeed", "ipodcast"), "ipodcast") ?>
	<?php print_string("enablerssfeed", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="enablerssfeed">
		<option value="1" 
			  <?php
			   if ( $form->enablerssfeed ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->enablerssfeed ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>		
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("rsssorting", get_string("rsssorting", "ipodcast"), "ipodcast") ?>
	  <?php print_string("rsssorting", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>			  
<tr>
	<td align = "right"></td>				        
	<td align = "left"><select size="1" name="rsssorting">
<?php
						$selected0 = "";
						$selected1 = "";
						$selected2 = "";
						$selected3 = "";
						if ($form->rsssorting == 3) {
							$selected3 = " selected=\"selected\" ";
						} else if($form->rsssorting == 2) {
							$selected2 = " selected=\"selected\" ";
						} else if($form->rsssorting == 1) {
							$selected1 = " selected=\"selected\" ";
						} else {
							$selected0 = " selected=\"selected\" ";
						}
?>
	  <option value="0" <?php p($selected0) ?>><?php print_string("createasc", "ipodcast") ?></option>
	  <option value="1" <?php p($selected1) ?>><?php print_string("createdesc", "ipodcast") ?></option>
	  <option value="2" <?php p($selected2) ?>><?php print_string("timeasc", "ipodcast") ?></option>
	  <option value="3" <?php p($selected3) ?>><?php print_string("timedesc", "ipodcast") ?></option>
	</select></td>
	<td>&nbsp;</td>
		  </tr>		
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("rssarticles", get_string("rssarticles"), "ipodcast") ?>
	  <?php print_string("rssarticles") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>			  
	<tr>
		<td align="right">&nbsp;</td>				        
		  <td align="left">
<?php
unset($choices);
$choices[0] = "0";
$choices[1] = "1";
$choices[2] = "2";
$choices[3] = "3";
$choices[4] = "4";
$choices[5] = "5";
$choices[10] = "10";
$choices[15] = "15";
$choices[20] = "20";
$choices[25] = "25";
$choices[30] = "30";
$choices[40] = "40";
$choices[50] = "50";
choose_from_menu ($choices, "rssarticles", $form->rssarticles, "");
?>			</td>
			  <td>&nbsp;</td>
		  </tr>
	<tr>
	<td colspan="2" align="left"><b>
	  <?php helpbutton("studentcanpost", get_string("studentcanpost", "ipodcast"), "ipodcast") ?>
	<?php print_string("studentcanpost", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="studentcanpost">
		<option value="1" 
			  <?php
			   if ( $form->studentcanpost ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->studentcanpost ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>	
	  
	<tr>
	<td colspan="2" align="left"><b>
	  <?php helpbutton("defaultapproval", get_string("defaultapproval", "ipodcast"), "ipodcast") ?>
	<?php print_string("defaultapproval", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="defaultapproval">
		<option value="1" 
			  <?php
			   if ( $form->defaultapproval ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->defaultapproval ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>	
	  	  	
	<tr>
	<td colspan="2" align="left"><b>
	  <?php helpbutton("attachwithcomment", get_string("attachwithcomment", "ipodcast"), "ipodcast") ?>
	<?php print_string("attachwithcomment", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="attachwithcomment">
		<option value="1" 
			  <?php
			   if ( $form->attachwithcomment ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->attachwithcomment ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>		
		  
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCASTCOURSE_SETUP_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('coursesave', "ipodcast") ?>" />
	</center>
	</form>
	<?php
}

/******************************************************************************\
 *
\******************************************************************************/
function ipodcastcourse_print_itunes($ipodcast_course, $form, $post="setupcourse.php") {
	global $CFG;
	global $USER;
	
	if (!isset($form->subtitle)) {
		$form->subtitle = stripslashes_safe($ipodcast_course->subtitle);
	}
	if (!isset($form->keywords)) {
		$form->keywords = stripslashes_safe($ipodcast_course->keywords);
	}				
	if (!isset($form->explicit)) {
		$form->explicit = $ipodcast_course->explicit;
	}	
	if (!isset($form->userid)) {
		$form->userid = $ipodcast_course->userid;
	}	
	if (!isset($form->topcategory)) {
		$form->topcategory = $ipodcast_course->topcategory;
	}
	if (!isset($form->nestedcategory)) {
		$form->nestedcategory = $ipodcast_course->nestedcategory;
	}
	if (!isset($form->enablerssitunes)) {
		$form->enablerssitunes = $ipodcast_course->enablerssitunes;
	}			
	if (!isset($form->test)) {
		$form->test = "1";
	}			
	

?>		
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">	 
	<tr>
	<td colspan="2" align="left"><b>
	  <?php helpbutton("enablerssitunes", get_string("enablerssitunes", "ipodcast"), "ipodcast") ?>
	<?php print_string("enablerssitunes", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="enablerssitunes">
		<option value="1" 
			  <?php
			   if ( $form->enablerssitunes ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->enablerssitunes ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>		
	 		  
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("coursesubtitle", get_string("coursesubtitle", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("coursesubtitle", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
		<td align="left">
			<input type="text" name="subtitle" size="100" value="<?php p($form->subtitle) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("courseowner", get_string("courseowner", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("courseowner", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
	  <td align="left">
		<select size="1" name="userid">
		</select>
		</td>
		<td>&nbsp;</td>					
	</tr>						
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("coursekeywords", get_string("coursekeywords", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("keywords", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
		<td align="left">
			<input type="text" name="keywords" size="100" value="<?php p($form->keywords) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>						
	<tr>
	  <td colspan="2" align="left"><b>
		<?php helpbutton("coursecategory", get_string("coursecategory", "ipodcast"), "ipodcast") ?>
	  <?php print_string("category","ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	<td align="right">&nbsp;</td>
	<td  align="left">
	
	<select name="topcategory" size="4" onClick="document.form.nestedcategory.value=0 ; updatednested(this.selectedIndex + 1, document.form.nestedcategory.value)" style="width: 150px">
	<?php
	if($topcategories = get_records("ipodcast_itunes_categories")) {
		foreach ($topcategories as $topcategory) {
			if($topcategory->id == $form->topcategory) {
				echo "<option selected value=\"$topcategory->id\">$topcategory->name</option>\n";
			} else {
				echo "<option value=\"$topcategory->id\">$topcategory->name</option>\n";					
			}
		}	
		echo "</select>";	
	}
	?>				
	<select name="nestedcategory" size="4" style="width: 150px">
	</select>					
	</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	<tr>				        
	<td colspan="2" align="left"><b>
	<?php helpbutton("courseexplicit", get_string("courseexplicit", "ipodcast"), "ipodcast") ?>
	<?php print_string("explicit", "ipodcast") ?>:</b></td>				        
	<td>&nbsp;</td>					
	</tr>
	<tr>
	<td align = "right"></td>				        
	<td align = "left"><select size="1" name="explicit">
	<?php
				$cselected = "";
				$yselected = "";
				$nselected = "";
				if ($form->explicit == 2) {
					$cselected = " selected=\"selected\" ";
				} else if($form->explicit == 1) {
					$yselected = " selected=\"selected\" ";
				} else {
					$nselected = " selected=\"selected\" ";
				}
			?>
	<option value="2" <?php p($cselected) ?>><?php print_string("clean", "ipodcast") ?></option>
	<option value="0" <?php p($nselected) ?>><?php print_string("no") ?></option>
	<option value="1" <?php p($yselected) ?>><?php print_string("yes") ?></option>
	</select></td>
	<td>&nbsp;</td>
	</tr>	
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCASTCOURSE_ITUNES_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('coursesave', "ipodcast") ?>" />
	</center>
	</form>	
	
	

<script language="javascript" type="text/javascript">
var topcategorylist=document.form.topcategory
var nestedcategorylist=document.form.nestedcategory

var nestedcategoryArray = new Array()
nestedcategoryArray[0] = ""
	
var ownerlist=document.form.userid
var ownerArray = new Array()
<?php

if($owners = ipodcast_get_teachers($ipodcast_course)) {
	$count = 0;
	foreach ($owners as $owner) {
			echo "ownerArray[$count]=\"" . fullname($owner) . "|$owner->id\"\n";						 		
		$count++;
	}
}

if($topcategories = get_records("ipodcast_itunes_categories")) {
	foreach ($topcategories as $topcategory) {
		if($nestedcategories = get_records("ipodcast_itunes_nested", "topcategoryid" , $topcategory->id)) { // AD
			echo "nestedcategoryArray[$topcategory->id]=[\"None|0\" ,";
			$count = 0;
			foreach ($nestedcategories as $nestedcategory) {
				if($count) {
					echo ", \"$nestedcategory->name|$nestedcategory->id\"";
				} else {
					echo "\"$nestedcategory->name|$nestedcategory->id\"";								
				}
				$count++;
			}
			echo "]\n";						 		
		} else {
			echo "nestedcategoryArray[$topcategory->id]=\"\"\n";						
		}
		
	}
}	
?>

function updatednested(selectedcategory, selectednesteditem) {
	nestedcategorylist.options.length=0
	if(selectedcategory>0) {
		if(nestedcategoryArray[selectedcategory].length > 0) {
			for (i=0; i<nestedcategoryArray[selectedcategory].length; i++) {
				if(nestedcategoryArray[selectedcategory][i].split("|")[1] == selectednesteditem) {
					nestedcategorylist.options[nestedcategorylist.options.length]=
					new Option(nestedcategoryArray[selectedcategory][i].split("|")[0],nestedcategoryArray[selectedcategory][i].split("|")[1],true,true)
				} else {
				nestedcategorylist.options[nestedcategorylist.options.length]=
					new Option(nestedcategoryArray[selectedcategory][i].split("|")[0],nestedcategoryArray[selectedcategory][i].split("|")[1])
				}
			}
		} else {
			nestedcategorylist.options[0]= new Option("None",0,true,true)
		}
	}
}

function updateowner(owner) {
	ownerlist.options.length=0
		for (i=0; i < ownerArray.length; i++) {
			if(ownerArray[i].split("|")[1] == owner) {
				ownerlist.options[ownerlist.options.length]= new Option(ownerArray[i].split("|")[0] ,ownerArray[i].split("|")[1],true,true)		
			} else {
				ownerlist.options[ownerlist.options.length]= new Option(ownerArray[i].split("|")[0] ,ownerArray[i].split("|")[1])					
			}
		}
}

//Need delay for some reason I need to investigate this further
document.onLoad = setTimeout('updatednested(<?php p($form->topcategory) ?>,<?php p($form->nestedcategory)?>); updateowner(<?php p($ipodcast_course->userid) ?>)',5)
</script>

<?php
}

/******************************************************************************\
 *
\******************************************************************************/
function ipodcastcourse_print_darwin($ipodcast_course, $form, $post="setupcourse.php") {
	global $CFG;
	global $USER;
	if (!isset($form->enabledarwin)) {
		$form->enabledarwin = $ipodcast_course->enabledarwin;
	}
	if (!isset($form->darwinurl)) {
		$form->darwinurl = $ipodcast_course->darwinurl;
	}
	
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">	 	
	<tr>
	<td colspan="2" align="left"><b>
	<?php helpbutton("enabledarwin", get_string("enabledarwin", "ipodcast"), "ipodcast") ?>
	<?php print_string("enabledarwin", "ipodcast") ?>:
	  
	</b></td>				        
		  <td>&nbsp;</td>
		  </tr>				  
	<tr>
	  <td align="right">&nbsp;</td>
	  <td align="left"><select size="1" name="enabledarwin">
		<option value="1" 
			  <?php
			   if ( $form->enabledarwin ) {
				  echo "selected=\"selected\"";
			   }?> 
			   ><?php echo get_string("yes") ?></option>
		<option value="0" 
			  <?php
			   if ( !$form->enabledarwin ) {
				  echo "selected=\"selected\"";
			   }?>><?php echo get_string("no") ?> </option>
	  </select></td>
	  <td>&nbsp;</td>
	  </tr>		
	<tr valign="top">
	  <td colspan="2" align="left"><?php helpbutton("coursedarwinurl", get_string("coursedarwinurl", "ipodcast"), "ipodcast") ?>
	  <b><?php print_string("coursedarwinurl", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	  </tr>
	<tr valign="top">
		<td align="right">&nbsp;</td>
		<td align="left">
			<input type="text" name="darwinurl" size="100" value="<?php p($form->darwinurl) ?>" />    </td>
		<td>&nbsp;</td>					
	</tr>
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCASTCOURSE_DARWIN_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('coursesave', "ipodcast") ?>" />
	</center>
	</form>		
<?php
			
}

/******************************************************************************\
 *
\******************************************************************************/	
function ipodcastcourse_print_image($ipodcast_course, $form, $post="setupcourse.php") {
	global $CFG;
	global $USER;
	
		
	if (!isset($form->image)) {
		$form->image = $ipodcast_course->image;
	}	
	if (!isset($form->imageheight)) {
		$form->imageheight = $ipodcast_course->imageheight;
	}	
	if (!isset($form->imagewidth)) {
		$form->imagewidth = $ipodcast_course->imagewidth;
	}
		
    if (! $basedir = make_upload_directory("$ipodcast_course->course")) {
        error("The site administrator needs to fix the file permissions");
    }
	
	if (!file_exists("$ipodcast_course->course/ipodcast")) {
		if (! make_upload_directory("$ipodcast_course->course/ipodcast")) {
        	error("The site administrator needs to fix the folder permissions");
		}
	}	
		
?>
	<form name="form" method="post" action=" <?php echo "$post" ?>">
	<center>
	<table cellpadding="5">		
	<tr>
		<td>&nbsp;</td>	
		<td align="center" valign="top"><b><?php print_string("courseimagedesc", "ipodcast") ?></b></td>
	</tr>
	<tr>
		<td align="right" valign="top"><b><?php print_string("courseimagefile", "ipodcast") ?></b></td>
		<td align="left"><input type="text" name="image" size="100" value="<?php p($form->image) ?>" /></td>
	</tr>
	<?php
	if(!check_image_link($ipodcast_course)) {
	?>
	<tr>
		<td colspan="2" align="center" valign="top"><b><?php print_string("imageerr", "ipodcast") ?></b></td>
	</tr>
	<?php
	}
	?>
	<tr>
	  <td colspan="2" align="left"><b>
	  <?php helpbutton("imageheight", get_string("imageheight", "ipodcast"), "ipodcast") ?>
	  <?php print_string("imageheight", "ipodcast") ?>:</b></td>
	  <td>&nbsp;</td>
	</tr>			  
	<tr>
		<td align="right">&nbsp;</td>				        
		<td align="left">
		<?php
		unset($choices);
		$height[0] = "Dont Resize";
		$height[16] = "16";
		$height[32] = "32";
		$height[48] = "48";
		$height[64] = "64";
		$height[128] = "128";
		$height[144] = "144";
		$height[200] = "200";
		$height[400] = "400";
		choose_from_menu ($height, "imageheight", $form->imageheight, "");
	?>			
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="left"><b>
		<?php helpbutton("imagewidth", get_string("imagewidth", "ipodcast"), "ipodcast") ?>
		<?php print_string("imagewidth", "ipodcast") ?>:</b></td>
		<td>&nbsp;</td>
	</tr>			  
	<tr>
		<td align="right">&nbsp;</td>				        
		<td align="left">
			<?php
			unset($choices);
			$width[0] = "Dont Resize";
			$width[16] = "16";
			$width[32] = "32";
			$width[48] = "48";
			$width[64] = "64";
			$width[128] = "128";
			$width[144] = "144";
			$width[200] = "200";
			$width[400] = "400";
			choose_from_menu ($width, "imagewidth", $form->imagewidth, "");
			?>			
		</td>
		<td>&nbsp;</td>
	</tr>	
	</table>
	<input type="hidden" name="id"          value="<?php p($form->id) ?>" />
	<input type="hidden" name="tab"          value="<?php p(IPODCASTCOURSE_IMAGE_VIEW) ?>" />
	<input type="hidden" name="action"          value="update" />
	<input type="submit" value="<?php print_string('coursesave', "ipodcast") ?>" />

	<?php 
	//button_to_popup_window('/mod/ipodcast/uploadpopup.php?id={$cm->id}')
	$url = 	"/mod/ipodcast/imagepopup.php?id=" . $ipodcast_course->course;
	button_to_popup_window($url, 'popup', 'Change',480, 800, 'Popup window', 'none', false, '', '' );
	?>	
	</center>
	</form>

<?php

}
?>
