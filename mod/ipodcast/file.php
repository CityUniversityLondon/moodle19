<?PHP
/******************************************************************************\
 *
 * Filename:    file.php
 *
 *				This is used to fool iTunes into downloading a file
 * 				All media files should link to this file
 *              Almost entirely stolen from /rss/files.php
 *				All existing Copyrights for that file are in place *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *				01/19/06 Tom Dolsky     - Added podcast views logging for the instructor
 *				07/07/06 Tom Dolsky     - Made file.php media extension independant
 *				07/07/06 Tom Dolsky     - Moved some functions to filelib.php
 *				07/07/06 Tom Dolsky     - Added more specific error messages
 *				07/07/06 Tom Dolsky     - Added file checking with appropriate error
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

	global $CFG;
    $nomoodlecookie = true;     // Because it interferes with caching
 
    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('filelib.php');

    $fileinfo = array();
	$fileinfo = file_get_params();
    $lifetime = 3600;  // Seconds for files to remain in caches - 1 hour
    
	if(!file_check_permissions($fileinfo)) {
		permission_error();
	}
	
			//Send the file
    if($fileinfo->extension == "jpg" || $fileinfo->extension == "png" ) {
		$imagepath = file_get_image_resized($fileinfo);
		if($imagepath == null) { 
			file_not_found();
		}		
		file_add_to_log($fileinfo);
		$file = split("/",$imagepath);
		
		$count = 0;
		if($file) {
			$count = count($file);
		}
			
		send_file($imagepath, $file[$count - 1] , $lifetime);
		//delete $imagepath here
		
	} else {
		//Get file path and check for valid file
		$pathname = file_get_path($fileinfo);
		if($pathname == null) { 
			file_not_found();
		}
		
		file_add_to_log($fileinfo);
		
		$file = split("/",$pathname);
		
		$count = 0;
		if($file) {
			$count = count($file);
		}	
		send_file($pathname, $file[$count - 1] , $lifetime);	
	}

?>