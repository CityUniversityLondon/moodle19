<?php

/**
 * =============================================================================
 * Change log
 * 2011101800
 * - Various fixes to HTML5 uploading with Firefox 4+ (much better, handles bigger files)
 * - Better safari compatibility
 * 
 * 2011101300
 * - Fixed bug with gtlib and form fields in chrome
 * 
 * 2011051900
 * - Fixed bug with multifile.js encodeURI not coping with ampersands, now use encodeURIComponent
 * 
 * 2011020800
 * - Fixed bug with associated library gtlib - now requires gtlib 2011020800+
 *
 * 2011011200
 * - Ability to turn off HTML5 uploading for Flash
 * - Max execution time of 1 hour for uploading files
 *
 * 2010122100
 * - Enhancement from Amanda Doughty - respect visibility setting
 * 
 * 2010111600
 * - Supports HTML5 file uploading (look ma, no Flash!)
 * - Supports HTML5 thumbnailing of image files (see image BEFORE its uploaded)
 * - Ability to change the name of the file resource before its uploaded
 * - Ability to upload collection of files as a directory resource
 * - Fixed manage file browser issue with background colour
 * - Fixed issue with non latin (e.g. Japanese) characters in file names
 * - Fixed issue with quotes in file names
 * 
 * 2010070800
 * - Fixed bug in checkresource.php looking for courseid param instead of course
 *
 * 2010041600
 * - Added json_encode compatability for php <5.2.0
 * 
 * 2010032300
 * - Removed capability mod/enhancedfile:multiupload - more trouble than it was
 * worth!
 *
 * mod/enhancedfile:multiupload
 * 2010031700
 * - Fixed issue with 'cleaning' file names with non latin characters (reported
 * by Vágvölgyi Csaba)
 * 
 * 2010031200
 * - Extra language files and improvements to language file handling in js
 * - Added image preloader to module form
 * - Fixed manage files root folder issue
 * 
 * 2010030301
 * - Added additional security checks to upload.php to stop Flash user agents
 *   from being able to upload any file without authentication! (reported by
 *   Petr Skoda)
 * - Added ability to bring up file manager directly from resource page and then
 *   refresh directory selector if any new directories have been created.
 *
 * 2010022300
 * - Fixed security issue in upload.php (not checking security when uploaded via
 *   Flash)
 *
 * 2010021600 - INITIAL VERSION
 *
 * =============================================================================
 */

    $module->version  = 2011101800;  // The current module version (Date: YYYYMMDDXX)
    $module->requires = 2007020200;  // Requires this Moodle version ?????
    $module->cron     = 0;           // Period for cron to check this module (secs)

?>
