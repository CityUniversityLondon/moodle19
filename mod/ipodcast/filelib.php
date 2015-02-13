<?PHP
/******************************************************************************\
 *
 * Filename:    filelib.php
 *
 *				media file download library
 *
 * History:     07/07/06 Tom Dolsky     - Tested and added this copyright and info
 *              07/07/06 Tom Dolsky     - Added a key hash encoded with an authorization key
 *              07/25/06 Tom Dolsky     - Fixed filetype error.
 *              10/18/06 Tom Dolsky     - Teacher now has ability to view file when podcast is invisible.
 *              10/23/06 Tom Dolsky     - Added jpg file type.
 *              10/24/06 Tom Dolsky     - Rebuilt extension parsing.
 *              10/25/06 Tom Dolsky     - made sure file_get_extension only returns lower case.
 *              10/25/06 Tom Dolsky     - fixed get attachent log to support hash.
 *              01/02/07 Tom Dolsky     - Updated to support roles
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
global $CFG;

require_once($CFG->dirroot.'/mod/ipodcast/lib.php');

	function file_check_permissions($fileinfo) {
		
		if (!$course = get_record("course", "id", $fileinfo->courseid)) {
			file_database_error();
		}
		
		if($fileinfo->instance > 0) {
			if(!$ipodcast = get_record("ipodcast","id",$fileinfo->instance)) {
				file_database_error();	
			}
			
			if(!$ipodcastcourse = get_record("ipodcast_courses","id",$ipodcast->ipodcastcourseid)) {
				file_database_error();	
			}	
			//Check name of module    	
			if(!$module = get_record("modules","name",$fileinfo->modulename)) {
				file_database_error();
			}
			if (!$cm = get_record("course_modules","module",$module->id,"instance",$fileinfo->instance)) {
				file_database_error();
			}
		} else {
			if(!$ipodcastcourse = get_record("ipodcast_courses","course",$fileinfo->courseid)) {
				file_database_error();	
			}	
		
			$cm->visible = true;
		}
		
		
		$isstudent = ipodcast_is_student($ipodcastcourse,$fileinfo->userid);	
		$isteacher = ipodcast_is_teacher($ipodcastcourse,$fileinfo->userid);
				
		//Check for file ipodcast visibility
		//BUG fixme This should be changed to editing teacher
		if((!$cm->visible) && (!$isteacher)) {
        	file_visibility_error();
		}
	
		if ($course->id != SITEID) {
			if ((!$course->guest || $course->password) && (!($isstudent || $isteacher))) {
				file_security_error();
			}
		}
	
		//Check for "security" if the course is hidden or the activity is hidden 
		//The per course module visiblity is set in the xml file
		if ((!$course->visible) && (!$isteacher)) {
				file_cvisibility_error();
		}
		
		
		return TRUE;
	
	}
/******************************************************************************\
 *
 * Function     file_get_params(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *              10/23/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
   function file_get_params() {
	    $fileinfo = array();
	    $fileinfo = null;
		//convert this to parse extension
		 $relativepath = get_file_argument('file.php');
				
		if (!$relativepath) {
			url_parse_error();
		}
						
		$args = explode('/', trim($relativepath, '/'),6);		
		
		if (count($args) < 5) {
			url_parse_error();
		}

		$fileinfo->hash       = $args[0];
		$fileinfo->courseid   = (int)$args[1];
		$fileinfo->userid     = (int)$args[2];
		$fileinfo->modulename = clean_param($args[3], PARAM_FILE);
		$fileinfo->instance   = (int)$args[4];
		//filename needs to be the remaining after $args[4]
		$fileinfo->filename = "/" . $args[5];
		$fileinfo->extension = file_get_extension($fileinfo);
		
		if (! $ipodcastcourse = get_record("ipodcast_courses", "course", $fileinfo->courseid)) {
			url_parse_error();
		}
		
		$tohash = "$fileinfo->courseid:$fileinfo->userid:$fileinfo->instance:$fileinfo->filename:$ipodcastcourse->authkey";
		$temphash = md5($tohash);

		//echo "authkey :" . $ipodcastcourse->authkey . ": Supplied hash :" . $fileinfo->hash . ": Computed hash :" . $temphash . "<br>\n";
		if ($temphash != $fileinfo->hash) {
			url_parse_error();
		}
					
		return $fileinfo;
	}
/******************************************************************************\
 *
 * Function     file_get_image_resized($fileinfo)
 *
 *
 * History:     10/24/06 Tom Dolsky created
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_get_image_resized($fileinfo) {
		global $CFG;	
		
        if (! $ipodcast_course = get_record("ipodcast_courses", "course", $fileinfo->courseid)) {
            //echo "ipodcast not found\n";
			return false;
        }		
		
		$temp_pathname = cleardoubleslashes("$CFG->dataroot/$fileinfo->courseid/$fileinfo->modulename/$fileinfo->filename");
		//Check that file exists
		if (!file_exists($temp_pathname)) {
			return null;
		}
		
		if($fileinfo->filename[0] = "/") {
			$file = explode("/", $fileinfo->filename);
			$filename = "resize-" . $file [1];
		}
		
		$dest_pathname = cleardoubleslashes("$CFG->dataroot/$fileinfo->courseid/$fileinfo->modulename/$filename");
		
		//If resizing is off then bypass all routines
		if($ipodcast_course->imagewidth == 0 && $ipodcast_course->imageheight == 0) {
			$dest_pathname = $temp_pathname;
			return $dest_pathname;			
		}
		if($fileinfo->extension == "jpg") {
			$src_image = imagecreatefromjpeg($temp_pathname);
			$dest_image = file_resize_image($src_image,$ipodcast_course->imagewidth,$ipodcast_course->imageheight);			
			imagejpeg($dest_image, $dest_pathname);	
		} else {
			$src_image = imagecreatefrompng($temp_pathname);
			$dest_image = file_resize_image($src_image,$ipodcast_course->imagewidth,$ipodcast_course->imageheight);			
			imagepng($dest_image, $dest_pathname);	
		}
		
		return $dest_pathname;
	}
/******************************************************************************\
 *
 * Function     file_get_image_size($fileinfo)
 *
 *
 * History:     10/24/06 Tom Dolsky created
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/	
function file_get_image_size($fileinfo)
{
	global $CFG;	
		
	$temp_pathname = cleardoubleslashes("$CFG->dataroot/$fileinfo->courseid/$fileinfo->modulename/$fileinfo->filename");

	//Check that file exists
	if (!file_exists($temp_pathname)) {
		return null;
	}

	if($fileinfo->extension == "jpg") {
		$src_image = imagecreatefromjpeg($temp_pathname);
	} else {
		$src_image = imagecreatefrompng($temp_pathname);
	}


	$fileinfo->imagewidth = imagesx($src_image);
	$fileinfo->imageheight = imagesy($src_image);
	
	return $fileinfo;
	
}			
/******************************************************************************\
 *
 * Function     file_get_image_resized($fileinfo)
 *
 *
 * History:     10/24/06 Tom Dolsky created
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/	
function file_resize_image($src_image,$newWidth,$newHeight)
{
	$old_x = imagesx($src_image);
	$old_y = imagesy($src_image);
	
	if($newWidth == 0) {
		$newWidth = $old_x;	
	}
	
	if($newHeight == 0) {
		$newHeight = $old_y;	
	}	
	
	if($old_x > $old_y)
	{
		$thumb_w = $newWidth;
		$thumb_h = $old_y*($newHeight/$old_x);
	}
	
	if($old_x < $old_y)
	{
		$thumb_w = $old_x*($newWidth/$old_y);
		$thumb_h = $newHeight;
	}
	
	if($old_x == $old_y)
	{
		$thumb_w = $newWidth;
		$thumb_h = $newHeight;
	}
	
	$image = imagecreatetruecolor($thumb_w, $thumb_h);
	imagecopyresized($image, $src_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
	
	return $image;
	
}	
/******************************************************************************\
 *
 * Function     file_get_extension($fileinfo)
 *
 *
 * History:     10/24/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
function file_get_extension($fileinfo) {
	if (eregi('\.([a-z0-9]+)$', $fileinfo->filename, $match)) {
		if (isset($match[1])) {
			return strtolower ($match[1]);
		} else {
			return 'xxx';   // By default
		}
	} else {
		return 'xxx';   // By default
	}
}

/******************************************************************************\
 *
 * Function     file_get_path($fileinfo)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_get_path($fileinfo) {
		global $CFG;	
		
		$temp_pathname = cleardoubleslashes("$CFG->dataroot/$fileinfo->courseid/$fileinfo->modulename/$fileinfo->filename");
	//Check that file exists
		if (!file_exists($temp_pathname)) {
			return null;
		}
		return $temp_pathname;
	}
/******************************************************************************\
 *
 * Function     file_add_to_log($fileinfo)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
	function file_add_to_log($fileinfo) {
    	require_once("lib.php");
		
		if (! $ipodcastcourse = get_record("ipodcast_courses", "course", $fileinfo->courseid)) {
			url_parse_error();
		}
		
		if(!empty($fileinfo->filename)) {
			$tohash = "$fileinfo->courseid:$fileinfo->userid:$fileinfo->instance:$fileinfo->filename:$ipodcastcourse->authkey";
			$temphash = md5($tohash);
			$tempurl = cleardoubleslashes("file." . $fileinfo->extension ."?file=/$temphash/$fileinfo->courseid/$fileinfo->userid/ipodcast/$fileinfo->instance/$fileinfo->filename");
		
		}	
		//If it did find it then add an entry
		if(!$view = get_record("ipodcast_views", "entryid", $fileinfo->instance, "userid", $fileinfo->userid)) {
			$view=NULL;
			unset($view);
			$view->userid=$fileinfo->userid;
			$view->views=1;
			$view->entryid=$fileinfo->instance;
			
			if(!ipodcast_add_view_instance($view)) {
				file_database_error();
			}
		} else {
			$temp_view = $view->views + 1;
			$view->views = $temp_view;
			$view->timemodified=time();
			if(!update_record("ipodcast_views", $view)) {
				file_database_error();
			}		
		}
	
		add_to_log($fileinfo->courseid, "ipodcast", "get attachment", $tempurl, $fileinfo->filename,0 ,$fileinfo->userid);		
	}
/******************************************************************************\
 *
 * Function     file_not_found(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_not_found() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("Error Retreiving File: File Not found");
		print_footer();
		die;
    }
/******************************************************************************\
 *
 * Function     url_parse_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function url_parse_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("Malformed URL");
		print_footer();
		die;
    }
/******************************************************************************\
 *
 * Function     permission_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
   function permission_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("You don't have permission to access this recource.");
		print_footer();
		die;
    }
/******************************************************************************\
 *
 * Function     file_database_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_database_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("Database error.");
		print_footer();
		die;
    }

/******************************************************************************\
 *
 * Function     file_database_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_visibility_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("iPodcast is not visible to you.");
		print_footer();
		die;
    }

/******************************************************************************\
 *
 * Function     file_cvisibility_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_cvisibility_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("Course is no longer visible.");
		print_footer();
		die;
    }
	
/******************************************************************************\
 *
 * Function     file_database_error(void)
 *
 *
 * History:     07/07/06 Tom Dolsky
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
    function file_security_error() {
        /// error, send some XML with error message
        global $lifetime, $filename;
		print_header();
		//Need a link back to page here
		error("You are not a student or teacher of this course!");
		print_footer();
		die;
    }	
?>
