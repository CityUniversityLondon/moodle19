<?php

$delim = strstr(PHP_OS, "WIN") ? "\\" : "/";

function zip_directory ($parentdir, $dirname, $destfile) {
    // Recursively zip a directory and its contents to a destination zip file
    global $CFG;

    if (empty($CFG->zip)) {    // Use built-in php-based zip function
        include_once("$CFG->libdir/pclzip/pclzip.lib.php");
        // Maybe TODO: rewrite parentdir if PCLZIP_OPT_REMOVE_PATH does not work under win32
        // Create the archive
        $archive = new PclZip(cleardoubleslashes($parentdir.$destfile));
        if ($archive->create($parentdir.$dirname, PCLZIP_OPT_REMOVE_PATH, $parentdir) == 0) {
            notice($archive->errorInfo(true));
            return false;
        }

    } else { // Use external zip program
        //Construct the command
        $separator = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? ' &' : ' ;';
        $command = 'cd '.escapeshellarg($parentdir).$separator.' '.
                    escapeshellarg($CFG->zip).' -r '.$destfile.' '.
                    escapeshellarg(cleardoubleslashes("$dirname"));
        //All converted to backslashes in WIN
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = str_replace('/','\\',$command);
        }
        //die($parentdir);
        Exec($command);
    }
}


function unzip_responses ($zipfile, $checkmd5, $moddir, $destination = '', $showstatus = true, $uploadresponses = false) {
//Unzip one zip file to a destination dir
//Both parameters must be FULL paths
//If destination isn't specified, it will be the
//SAME directory where the zip file resides.

    global $CFG, $delim;

    //Extract everything from zipfile
    $path_parts = pathinfo(cleardoubleslashes($zipfile));
    $zippath = $path_parts["dirname"];       //The path of the zip file
    $zipfilename = $path_parts["basename"];  //The name of the zip file
    $extension = $path_parts["extension"];    //The extension of the file

    //If no file, error
    if (empty($zipfilename)) {
        return false;
    }

    //If no extension, error
    if (empty($extension)) {
        return false;
    }

    //Clear $zipfile
    $zipfile = cleardoubleslashes($zipfile);

    //Check zipfile exists
    if (!file_exists($zipfile)) {
        return false;
    }

    //If no destination, passed let's go with the same directory
    if (empty($destination)) {
        $destination = $zippath;
    }

    //Clear $destination
    $destpath = rtrim(cleardoubleslashes($destination), "/");

    //Check destination path exists
    if (!is_dir($destpath)) {
        return false;
    }

    //Check destination path is writable. TODO!!

    //Everything is ready:
    //    -$zippath is the path where the zip file resides (dir)
    //    -$zipfilename is the name of the zip file (without path)
    //    -$destpath is the destination path where the zip file will uncompressed (dir)

    $list = array();

    require_once($CFG->libdir . $delim . 'filelib.php');

    // Create a new directory name - $temppath = $CFG->dataroot/temp/unzip/<random_string>
    do {
        $temppath = $CFG->dataroot . $delim . 'temp' . $delim . 'unzip' . $delim . random_string(10); 
    } while (file_exists($temppath));

    // Create the $temppath directory
    if (!check_dir_exists($temppath, true, true)) {
        return false;
    }

    // Extract the zip file into $temppath
    if (empty($CFG->unzip)) {    // Use built-in php-based unzip function ///////////////////

        include_once($CFG->libdir . $delim . 'pclzip' . $delim . 'pclzip.lib.php');
        $archive = new PclZip(cleardoubleslashes($zippath . $delim . $zipfilename));
        if (!$list = $archive->extract(PCLZIP_OPT_PATH, $temppath,
                                       PCLZIP_CB_PRE_EXTRACT, 'unzip_cleanfilename',
                                       PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, $temppath
                                       )) {
            if (!empty($showstatus)) {
                notice($archive->errorInfo(true));
            }
            return false;
        }

    } else {                     // Use external unzip program

       $separator = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? ' &' : ' ;';
       $redirection = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '' : ' 2>&1';

       $command = 'cd '.escapeshellarg($zippath).$separator.
                    escapeshellarg($CFG->unzip).' -o '.
                    escapeshellarg(cleardoubleslashes($zippath . $delim . $zipfilename)).' -d '.
                    escapeshellarg($temppath).$redirection;
        //All converted to backslashes in WIN
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = str_replace('/','\\',$command);
        }

        Exec($command,$list);
    }

//    // test the check file
    $checkfilepath = checkfile($temppath);

    if (!$checkfilepath) {
        error(get_string('responsecheckfilemissing', 'assignment'));
        die;
    }

    //$currdir = opendir($temppath);
    $checkfilename = $checkfilepath . $delim . 'do_not_edit.txt';
    $checkfile = fopen($checkfilename, 'r') or error('Cannot unzip this file');
    $checkfilemd5 = fread($checkfile, 32);

    if (!($checkfilemd5 == $checkmd5)) {
        error(get_string('responsecheckfilemismatch', 'assignment'));
        fclose($checkfile);
        die;
    }

    fclose($checkfile);
    unlink($checkfilename);    

    // It looks like the matching download, but has the check file been moved?
    // We need to find the assignment directory
    if (!$pos = strrpos($checkfilepath, $moddir)) {
        error(get_string('responsedirerror', 'assignment'));
    }

    // CMDL-1290 fix for bulk download with groups
    //$temppath = substr($checkfilepath, 0, $pos) . $moddir; // this was causing
    // problems where an OS had renamed a duplicate directory
    $temppath = $checkfilepath;
    // end CMDL-1290

    // Now $temppath = $CFG->dataroot/temp/unzip/<random_string>/path_to_do_not_edit.txt
    // $destpath = $CFG->dataroot/<course->id>/moddata/assignment/<cm->instance>
    unzip_process_temp_responses_dir($temppath, $destpath, $uploadresponses);
    fulldelete($temppath);

    //Display some info about the unzip execution
    if ($showstatus) {
        unzip_show_status($list, $checkfilepath, $destpath); 
    }

    return true;
}

/**
 * Sanitize temporary unzipped files and move to target dir.
 * @param string $temppath = $CFG->dataroot/temp/unzip/<random_string>/path_to_do_not_edit.txt
 * @param string $destpath = $CFG->dataroot/<course->id>/moddata/assignment/<cm->instance>
 * @return void
 */
function unzip_process_temp_responses_dir($temppath, $destpath, $uploadresponses=false) {
    global $CFG, $delim;
    add_to_log(SITEID, 'assignment', 'unzip_process_temp_responses_dir', '', 'temppath: '.$temppath.', descpath: '.$destpath);

    $filepermissions = ($CFG->directorypermissions & 0666); // strip execute flags

    if (check_dir_exists($destpath, true, true)) { 

        if (!$currdir = @opendir($temppath)) {
            error(get_string('responsedirerror', 'assignment'));
        }

        while (false !== ($file = readdir($currdir))) {
            if ($file <> ".." && $file <> ".") {
                // full path of file/dir inside $temppath
                $fullfile = $temppath . $delim . $file;
              
                if (is_link($fullfile)) {
                    //somebody tries to sneak in symbolik link - no way!
                    continue;
                }
                $cleanfile = clean_param($file, PARAM_FILE); // no dangerous chars
                if ($cleanfile === '') {
                    // invalid file name
                    continue;
                }
                if ($cleanfile !== $file and file_exists($temppath . $delim . $cleanfile)) {
                    // eh, weird chars collision detected
                    continue;
                }
                
                // strip the extra descriptive directory naming to get just the userid bit
                if (!$uploadresponses && !is_file($fullfile)) {
                    $path_parts = explode('_', $cleanfile);
                    $cleanfile = $path_parts[count($path_parts) - 1];
                }

                // 1st iteration:
                // $descfile = $CFG->dataroot/<course->id>/moddata/assignment/<cm->instance>/<userid>
                // 2nd iteration:
                // $descfile = $CFG->dataroot/<course->id>/moddata/assignment/<cm->instance>/<userid>/<filename>
                // etc
                $descfile = $destpath . $delim . $cleanfile;

                // TODO
                // Create user directories if they don't already exist 
                if (is_dir($fullfile)) {
                    if (check_dir_exists($descfile, true, true)) {
                        add_to_log(SITEID, 'assignment', 'check_dir_exists', '', 'fullfile: '.$fullfile.', descfile: '.$descfile);
                        // recurse into subdirs
                        unzip_process_temp_responses_dir($fullfile, $descfile, true);
                    } else {
                        // Try the make_upload_directory function
                        $dir = str_replace(cleardoubleslashes($CFG->dataroot . '/'), '', cleardoubleslashes($descfile));
                        if (make_upload_directory($dir)) {
                            add_to_log(SITEID, 'assignment', 'make_upload_directory', '', 'fullfile: '.$fullfile.', descfile: '.$descfile);
                            unzip_process_temp_responses_dir($fullfile, $descfile, true);
                        }
                    }
                }

                // On the first iteration through this function we should be at
                // assignment directory level and so we do not want to copy any
                // stray files
                if ($uploadresponses && is_file($fullfile)) {
                    // rename and move the file
                    if (file_exists($descfile)) {
                        //override existing files    
                        unlink($descfile);
                    }

                    $path_parts = pathinfo($descfile);
                    $descpath = $path_parts['dirname'] . "/responses/";

                    if (!file_exists($descpath)) { //create responses dir if it doesn't already exist.
                        mkdir($descpath);
                    }

                    $descfile = $descpath . "{$path_parts['filename']}" . ".{$path_parts['extension']}";
                    rename($fullfile, $descfile);
                    chmod($descfile, $filepermissions);
                }
            }
        }
        closedir($currdir);
    }
}



// $temppath = $CFG->dataroot/temp/unzip/<random_string>
function checkfile ($temppath) {

    global $delim;
    $foundpath = false;

    if ($currdir = @opendir($temppath)) {
        
        while (!$foundpath && false !== ($file = readdir($currdir))) {

            //if (is_dir($temppath. $delim . $file) && $file != "." && $file != "..") {
            if ($file != "." && $file != "..") {

                $checkfilename = $temppath . $delim . 'do_not_edit.txt';

                // If this is a direstory containing our check file then save the
                // directory name and exit the loop
                if (file_exists($checkfilename)) {
                    $foundpath = $temppath;
                    break;
                } else if (is_dir($temppath. $delim . $file)) {
                    $foundpath = checkfile ($temppath. $delim . $file);               
                }

            }
        }
        
    }
    closedir($currdir);
    return (isset($foundpath) ? $foundpath : false);
}

