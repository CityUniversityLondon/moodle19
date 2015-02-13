<?php 
/******************************************************************************\
 *
 * Filename:    uploadpopup.php
 *
 *		This file pops up a file window to select attachment
 *
 *
 * History:     02/04/06 Tom Dolsky     - Fixed a bug that caused the window to be blank
 *              02/19/06 Tom Dolsky     - Repaired make directory function
 *              07/07/06 Tom Dolsky     - Directories now function properly
 *              07/13/06 Tom Dolsky     - Repaired delete confirmation
 *              10/17/06 Tom Dolsky     - Repaired incorrect URL in upload popup for playing files
 *              10/17/06 Tom Dolsky     - Can now select file with spaces
 *              10/24/06 Tom Dolsky     - Removed some old debugging echo output.
 *              01/02/07 Tom Dolsky     - Added PDF support.
 *              01/02/07 Tom Dolsky     - Updated support for roles
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

    require_once("../../config.php");
    require_once("lib.php");
    require_once($CFG->libdir.'/filelib.php');
			
			

    $id      = required_param('id', PARAM_INT);
    $file    = optional_param('file', '', PARAM_PATH);
    $wdir    = optional_param('wdir', '', PARAM_PATH);
    $action  = optional_param('action', '', PARAM_ACTION);
    $name    = optional_param('name', '', PARAM_FILE);
    $oldname = optional_param('oldname', '', PARAM_FILE);
    $confirm  = optional_param('confirm', '', PARAM_CLEAN);
    $choose  = optional_param('choose', '', PARAM_CLEAN);
    $userfile= optional_param('userfile','',PARAM_FILE);
    $save = optional_param( 'save','' );
			

    if ($choose) {
        if (count(explode('.', $choose)) != 2) {
            error('Incorrect format for choose parameter');
        }
    }

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $ipodcast = get_record("ipodcast", "id", $cm->instance)) {
            error("Course module is incorrect");
        }
        if (! $ipodcast_course = get_record("ipodcast_courses", "course", $cm->course)) {
            error("Course ID was incorrect");
        }		
		
    } else {
        if (! $ipodcast = get_record("ipodcast", "id", $id)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $ipodcast->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("ipodcast", $ipodcast->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
        if (! $ipodcast_course = get_record("ipodcast_courses", "course", $ipodcast->course)) {
            error("Course ID was incorrect");
        }	
    }

    //require_login($course->id);


	$isteacheredit = ipodcast_is_teacheredit($ipodcast_course, $USER->id);
	
    if (!$isteacheredit ) {
        error("You need to be a teacher with editing privileges");
		die;
    }

    function html_footer() {

        echo "</td></tr></table></body></html>";
//		print_footer('none');
    }
    
    function html_header($course){
            print_header();
						

        echo "<table border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
        echo "<tr>";
        echo "<td colspan=\"2\">";

    }


    if (! $basedir = make_upload_directory("$course->id")) {
        error("The site administrator needs to fix the file permissions");
    }
	if(isset($basedir)) {
		$basedir .= "/ipodcast";
	}
    $baseweb = $CFG->wwwroot;

//  End of configuration and access control


    if (!$wdir) {
        $wdir="/";
    }

    if (($wdir != '/' and detect_munged_arguments($wdir, 0))
      or ($file != '' and detect_munged_arguments($file, 0))) {
        $message = "Error: Directories can not contain \"..\"";
        $wdir = "/";
        $action = "";
    }
	
    switch ($action) {

        case "upload":
            html_header($course);
            require_once($CFG->dirroot.'/lib/uploadlib.php');
            if (!empty($save) and confirm_sesskey()) {
                $course->maxbytes = 0;  // We are ignoring course limits
                $um = new upload_manager('userfile',false,false,$course,false,0);
                $dir = "$basedir$wdir";
                if ($um->process_file_uploads($dir)) {
                    notify(get_string('uploadedfile'));
                }
                // um will take care of error reporting.
                displaydir($cm, $wdir);
            } else {
                $upload_max_filesize = get_max_upload_file_size($CFG->maxbytes);
                $filesize = display_size($upload_max_filesize);

                $struploadafile = get_string("uploadafile");
                $struploadthisfile = get_string("uploadthisfile");
                $strmaxsize = get_string("maxsize", "", $filesize);
                $strcancel = get_string("cancel");

				echo "<p>$struploadafile ($strmaxsize) --> <b>$wdir</b>\n";
                echo "<table><tr><td colspan=\"2\">\n";
                echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"uploadpopup.php\">\n";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n";
                echo " <input type=\"hidden\" name=\"action\" value=\"upload\" />\n";
                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />\n";
                upload_print_form_fragment(1,array('userfile'),null,false,null,$upload_max_filesize,0,false);
                echo " </td><tr><td width=\"10\">";
                echo " <input type=\"submit\" name=\"save\" value=\"$struploadthisfile\" />\n";
                echo "</form>";
                echo "</td><td width=\"100%\">";
                echo "<form action=\"uploadpopup.php\" method=\"get\">\n";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n";
                echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />\n";
                echo " <input type=\"submit\" value=\"$strcancel\" />\n";
                echo "</form>";
                echo "</td></tr></table>";
	          }
            html_footer();
            break;

        case "delete":
            if (!empty($confirm) and confirm_sesskey()) {
                html_header($course);
                foreach ($USER->filelist as $file) {
					$file = cleardoubleslashes($file);
					$fullfile = $basedir . "/" . $file;
                    if (! fulldelete($fullfile)) {
                        echo "<br />Error: Could not delete: $fullfile";
                    }
                }
                clearfilelist();
                displaydir($cm, $wdir);
                html_footer();

            } else {
                html_header($course);
                if (setfilelist($_POST)) {
                    echo "<p align=\"center\">".get_string("deletecheckwarning").":</p>";
                    print_simple_box_start("center");
                    printfilelist($USER->filelist);
                    print_simple_box_end();
                    echo "<br />";
                    notice_yesno (get_string("deletecheckfiles"), 
                                "uploadpopup.php?id=$id&amp;wdir=$wdir&amp;action=delete&amp;confirm=1&amp;sesskey=$USER->sesskey&amp;choose=$choose",
                                "uploadpopup.php?id=$id&amp;wdir=$wdir&amp;action=cancel&amp;choose=$choose");
                } else {
                    displaydir($cm, $wdir);
                }
                html_footer();
            }
            break;

        case "move":
            html_header($course);
            if (($count = setfilelist($_POST)) and confirm_sesskey()) {
                $USER->fileop     = $action;
                $USER->filesource = $wdir;
                echo "<p align=\"center\">";
                print_string("selectednowmove", "moodle", $count);
                echo "</p>";
            }
            displaydir($cm, $wdir);
            html_footer();
            break;

        case "paste":
            html_header($course);
            if (isset($USER->fileop) and ($USER->fileop == "move") and confirm_sesskey()) {
                foreach ($USER->filelist as $file) {
                    $shortfile = basename($file);
                    $oldfile = $basedir.$file;
                    $newfile = $basedir.$wdir."/".$shortfile;
                    if (!rename($oldfile, $newfile)) {
                        echo "<p>Error: $shortfile not moved";
                    }
                }
            }
            clearfilelist();
            displaydir($cm, $wdir);
            html_footer();
            break;

        case "rename":
            if (!empty($name) and confirm_sesskey()) {
                html_header($course);
                //$name = clean_filename($name);
                if (file_exists($basedir.$wdir."/".$name)) {
                    echo "Error: $name already exists!";
                } else if (!rename($basedir.$wdir."/".$oldname, $basedir.$wdir."/".$name)) {
                    echo "Error: could not rename $oldname to $name";
                }
                displaydir($cm, $wdir);
                    
            } else {
                $strrename = get_string("rename");
                $strcancel = get_string("cancel");
                $strrenamefileto = get_string("renamefileto", "moodle", $file);
                html_header($course);
                echo "<p>$strrenamefileto:";
                echo "<table><tr><td>";
                echo "<form action=\"uploadpopup.php\" method=\"post\" name=\"form\">";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
                echo " <input type=\"hidden\" name=\"action\" value=\"rename\" />";
                echo " <input type=\"hidden\" name=\"oldname\" value=\"$file\" />";
                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
                echo " <input type=\"text\" name=\"name\" size=\"35\" value=\"$file\" />";
                echo " <input type=\"submit\" value=\"$strrename\" />";
                echo "</form>";
                echo "</td><td>";
                echo "<form action=\"uploadpopup.php\" method=\"get\">";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
                echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />";
                echo " <input type=\"submit\" value=\"$strcancel\" />";
                echo "</form>";
                echo "</td></tr></table>";
            }
            html_footer();
            break;
			
        case "select":
			html_header($course);
			
			//$name = clean_filename($name);
			if (!(file_exists($basedir.$wdir."/".$name))) {			
				echo "Error: $name doesnt exist!";			
			} else {				
				if($wdir == "/") {
					$ipodcast->attachment = "/" .$name;
				} else {
					$ipodcast->attachment = $wdir."/".$name;			
				}
				
				$ipodcast->instance = $cm->instance;
				if(ipodcast_update_instance(addslashes_object($ipodcast))) {
					echo "Selected file $name";
				} else {
					echo "Error inserting file $name";
				}

			}
			displaydir($cm, $wdir);
            html_footer();
            break;

        case "makedir":
            if (!empty($name) and confirm_sesskey()) {
                html_header($course);
                //$name = clean_filename($name);

                if (file_exists("$basedir/ipodcast/$wdir/$name")) {
                    echo "Error: $name already exists!";
                } else if (! make_upload_directory("$course->id/ipodcast/$wdir/$name")) {
                    echo "Error: could not create $name";
                }
                displaydir($cm, $wdir);
                    
            } else {
                $strcreate = get_string("create");
                $strcancel = get_string("cancel");
                $strcreatefolder = get_string("createfolder", "moodle", $wdir);
                html_header($course);
                echo "<p>$strcreatefolder:";
                echo "<table>\n<tr>\n<td>";
                echo "<form action=\"uploadpopup.php\" method=\"post\" name=\"form\">\n";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n";
                echo " <input type=\"hidden\" name=\"action\" value=\"makedir\" />\n";
                echo " <input type=\"text\" name=\"name\" size=\"35\" />";
                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />\n";
                echo " <input type=\"submit\" value=\"$strcreate\" />\n";
                echo "</form>\n";
                echo "</td>\n<td>\n";
                echo "<form action=\"uploadpopup.php\" method=\"get\">\n";
                echo ' <input type="hidden" name="choose" value="'.$choose.'">';
                echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />\n";
                echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />\n";
                echo " <input type=\"submit\" value=\"$strcancel\" />\n";
                echo "</form>\n";
                echo "</td>\n</tr>\n</table>\n";
            }
            html_footer();
            break;

        case "cancel":
            clearfilelist();

        default:
            html_header($course);
            displaydir($cm, $wdir);
            html_footer();
            break;
}


/// FILE FUNCTIONS ///////////////////////////////////////////////////////////


function setfilelist($VARS) {
    global $USER;

    $USER->filelist = array ();
    $USER->fileop = "";

    $count = 0;
    foreach ($VARS as $key => $val) {
        if (substr($key,0,4) == "file") {
            $count++;
            $val = rawurldecode($val);
            if (!detect_munged_arguments($val, 0)) {
                $USER->filelist[] = $val;
            }
        }
    }
    return $count;
}

function clearfilelist() {
    global $USER;

    $USER->filelist = array ();
    $USER->fileop = "";
}


function printfilelist($filelist) {
    global $CFG, $basedir;

    foreach ($filelist as $file) {
		$file = cleardoubleslashes($file);
        if (is_dir($basedir.$file)) {
            echo "<img src=\"$CFG->pixpath/f/folder.gif\" height=\"16\" width=\"16\" alt=\"\" /> $file<br />";
            $subfilelist = array();
            $currdir = opendir($basedir.$file);
            while (false !== ($subfile = readdir($currdir))) {
                if ($subfile <> ".." && $subfile <> ".") {
                    $subfilelist[] = $file."/".$subfile;
                }
            }
            printfilelist($subfilelist);

        } else { 
            $icon = mimeinfo("icon", $file);
            if($icon == "audio.gif" || $icon == "video.gif" ) {
				echo "<img src=\"$CFG->pixpath/f/$icon\"  height=\"16\" width=\"16\" alt=\"\" /> $file<br />";
			}
        }
    }
}


function print_cell($alignment='center', $text='&nbsp;', $class='') {
    if ($class) {
        $class = ' class="'.$class.'"';
    }
    echo '<td align="'.$alignment.'" nowrap="nowrap"'.$class.'>'.$text.'</td>';
}

function displaydir ($cm, $wdir) {
//  $wdir == / or /a or /a/b/c/d  etc

    global $basedir;
    global $id;
    global $USER, $CFG;
    global $choose;

    if (!$ipodcastentry = get_record("ipodcast", "id", $cm->instance)) {
         error("Course module instance is incorrect");
     }    
    if (!$ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcastentry->ipodcastcourseid)) {
         error("Course module instance is incorrect");
     }
	
	$selectedfilename = $ipodcastentry->attachment;

    $fullpath = $basedir.$wdir;

    $directory = opendir($fullpath);             // Find all files
    while (false !== ($file = readdir($directory))) {
        if ($file == "." || $file == "..") {
            continue;
        }
        
        if (is_dir($fullpath."/".$file)) {
            $dirlist[] = $file;
        } else {
            $filelist[] = $file;
        }
    }
    closedir($directory);

    $strname = get_string("name");
    $strsize = get_string("size");
    $strmodified = get_string("modified");
    $straction = get_string("action");
    $strmakeafolder = get_string("makeafolder");
    $struploadafile = get_string("uploadafile");
    $strwithchosenfiles = get_string("withchosenfiles");
    $strmovetoanotherfolder = get_string("movetoanotherfolder");
    $strmovefilestohere = get_string("movefilestohere");
    $strdeletecompletely = get_string("deletecompletely");
    $strselect = get_string("select");
    $strselected = get_string("selected","ipodcast");
    $strrename = get_string("rename");
    $strdone   = get_string("done","ipodcast");
    $stredit   = get_string("edit");
    $strlist   = get_string("list");
    $strrestore= get_string("restore");
    $strchoose   = get_string("choose");


    echo "<form action=\"uploadpopup.php\" method=\"post\" name=\"dirform\">\n";
    echo '<input type="hidden" name="choose" value="'.$choose.'">';
//    echo "<hr width=\"640\" align=\"center\" noshade=\"noshade\" size=\"1\" />";
    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"640\" class=\"files\">\n";    
    echo "<tr>\n";
    echo "<th width=\"5\"></th>\n";
    echo "<th align=\"left\" class=\"header name\">$strname</th>\n";
    echo "<th align=\"right\" class=\"header size\">$strsize</th>\n";
    echo "<th align=\"right\" class=\"header date\">$strmodified</th>\n";
    echo "<th align=\"right\" class=\"header commands\">$straction</th>\n";
    echo "</tr>\n";

//    if ($wdir == "/") {
//        $wdir = "";
//   }
    if (!empty($wdir) && $wdir != "/ipodcast") {
        $dirlist[] = '..';
    }

    $count = 0;

    if (!empty($dirlist)) {
        asort($dirlist);
        foreach ($dirlist as $dir) {
            echo "<tr class=\"folder\">";

            if ($dir == '..') {
                $fileurl = rawurlencode(dirname($wdir));
                print_cell();
                print_cell('left', '<a href="uploadpopup.php?id='.$id.'&amp;wdir='.$fileurl.'&amp;choose='.$choose.'"><img src="'.$CFG->pixpath.'/f/parent.gif" height="16" width="16" alt="'.get_string('parentfolder').'" /></a> <a href="uploadpopup.php?id='.$id.'&amp;wdir='.$fileurl.'&amp;choose='.$choose.'">'.get_string('parentfolder').'</a>', 'name');
                print_cell();
                print_cell();
                print_cell();

            } else {
                $count++;
                $filename = $fullpath."/".$dir;
                $fileurl  = rawurlencode($wdir."/".$dir);
                $filesafe = rawurlencode($dir);
                $filesize = display_size(get_directory_size("$fullpath/$dir"));
                $filedate = userdate(filemtime($filename), "%d %b %Y, %I:%M %p");
                print_cell("center", "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\" />", 'checkbox');
                print_cell("left", "<a href=\"uploadpopup.php?id=$id&amp;wdir=$fileurl&amp;choose=$choose\"><img src=\"$CFG->pixpath/f/folder.gif\" height=\"16\" width=\"16\" border=\"0\" alt=\"Folder\" /></a> <a href=\"uploadpopup.php?id=$id&amp;wdir=$fileurl&amp;choose=$choose\">".htmlspecialchars($dir)."</a>", 'name');
                print_cell("right", $filesize, 'size');
                print_cell("right", $filedate, 'date');
				
            }
    
            echo "</tr>";
        }
    }


    if (!empty($filelist)) {
        asort($filelist);
        foreach ($filelist as $file) {

            $icon = mimeinfo("icon", $file);
			if(!($icon == "video.gif" || $icon == "audio.gif" || $icon == "pdf.gif" )) {
				continue;
				}
				
            $count++;
            $filename    = $fullpath."/".$file;
            $fileurl     = "$wdir/$file";
            $filesafe    = rawurlencode($file);
            $fileurlsafe = rawurlencode($fileurl);
            $filedate    = userdate(filemtime($filename), "%d %b %Y, %I:%M %p");

            if (substr($fileurl,0,1) == '/') {
                $selectfile = substr($fileurl,1);
            } else {
                $selectfile = $fileurl;
            }
		//echo "Selectedfilename = " . $selectedfilename . "<br>\n" ;
		//echo "wdir = " . $wdir . "<br>\n" ;
		//echo "file = " . $file . "<br>\n" ;
		if($wdir == "/") {
			$filetocompare = "/" .$file;
		} else {
			$filetocompare = $wdir . "/" . $file;
		}
		//echo "filetocompare = " . $filetocompare . "<br>\n" ;

 		if(strcmp($selectedfilename,$filetocompare) == 0) {
           		echo "<tr bgcolor=\"#CCCCCC\" class=\"file\">";
		} else {
           		echo "<tr class=\"file\">";			
		}
            print_cell("center", "<input type=\"checkbox\" name=\"file$count\" value=\"$fileurl\" />", 'checkbox');
            echo "<td align=\"left\" nowrap=\"nowrap\" class=\"name\">";

		$tohash = "$cm->course:$USER->id:$ipodcastentry->id:$filetocompare:$ipodcastcourse->authkey";
		$hash = md5($tohash);

      	if ($CFG->slasharguments) {
			$ffurl = "/mod/ipodcast/file.php/$hash/$cm->course/$USER->id/ipodcast/$ipodcastentry->id";
      	} else {
			$ffurl = "/mod/ipodcast/file.php?file=/$hash/$cm->course/$USER->id/ipodcast/$ipodcastentry->id";
      	}
	
		$newffurl = cleardoubleslashes($ffurl . $filetocompare);


            link_to_popup_window ($newffurl , "display", 
                                  "<img src=\"$CFG->pixpath/f/$icon\" height=\"16\" width=\"16\" border=\"0\" alt=\"File\" />");
            echo '&nbsp;';
            link_to_popup_window ($newffurl, "display", 
                                  htmlspecialchars($file),
                                  480, 640);
            echo "</td>";

            $file_size = filesize($filename);
            print_cell("right", display_size($file_size), 'size');
            print_cell("right", $filedate, 'date');

            if ($choose) {
                $edittext = "<strong><a onclick=\"return set_value('$selectfile')\" href=\"#\">$strchoose</a></strong>&nbsp;";
            } else {
                $edittext = '';
            }


            if ($icon == "text.gif" || $icon == "html.gif") {
                $edittext .= "<a href=\"uploadpopup.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=edit&amp;choose=$choose\">$stredit</a>";
            } else if ($icon == "zip.gif") {
                $edittext .= "<a href=\"uploadpopup.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=unzip&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strunzip</a>&nbsp;";
                $edittext .= "<a href=\"uploadpopup.php?id=$id&amp;wdir=$wdir&amp;file=$fileurl&amp;action=listzip&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strlist</a> ";
                if (!empty($CFG->backup_version) and isteacheredit($id)) {
                    $edittext .= "<a href=\"uploadpopup.php?id=$id&amp;wdir=$wdir&amp;file=$filesafe&amp;action=restore&amp;sesskey=$USER->sesskey&amp;choose=$choose\">$strrestore</a> ";
                }
            }

		//echo "Selectedfilename = " . $selectedfilename . "<br>\n" ;
		//echo "wdir = " . $wdir . "<br>\n" ;
		//echo "file = " . $file . "<br>\n" ;
		if($wdir == "/") {
			$filetocompare = "/" .$file;
		} else {
			$filetocompare = $wdir . "/" . $file;
		}

		//echo "filetocompare = " . $filetocompare . "<br>\n" ;
			
            if ($icon == "audio.gif" || $icon == "video.gif"  || $icon == "pdf.gif" ) {
			if(strcmp($selectedfilename,$filetocompare) == 0) {
				print_cell("right", "$edittext $strselected", 'commands');
			} else {
				print_cell("right", "$edittext <a href=\"uploadpopup.php?id=$id&amp;wdir=$wdir&amp;name=$filesafe&amp;action=select&amp;choose=$choose\">$strselect</a>", 'commands');
			}
    		}
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "<hr width=\"640\" align=\"center\" noshade=\"noshade\" size=\"1\" />";

    if (empty($wdir)) {
        $wdir = "/";
    }

    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"640\">";    
    echo "<tr><td>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo '<input type="hidden" name="choose" value="'.$choose.'">';
    echo "<input type=\"hidden\" name=\"wdir\" value=\"$wdir\" /> ";
    echo "<input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
    $options = array (
                   "move" => "$strmovetoanotherfolder",
                   "delete" => "$strdeletecompletely",
               );
    if (!empty($count)) {
        choose_from_menu ($options, "action", "", "$strwithchosenfiles...", "javascript:document.dirform.submit()");
    }

    echo "</form>";
    echo "<td align=\"center\">";
    if (!empty($USER->fileop) and ($USER->fileop == "move") and ($USER->filesource <> $wdir)) {
        echo "<form action=\"uploadpopup.php\" method=\"get\">";
        echo ' <input type="hidden" name="choose" value="'.$choose.'">';
        echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />";
        echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
        echo " <input type=\"hidden\" name=\"action\" value=\"paste\" />";
        echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
        echo " <input type=\"submit\" value=\"$strmovefilestohere\" />";
        echo "</form>";
    }
    echo "<td align=\"right\">";
        echo "<form action=\"uploadpopup.php\" method=\"get\">";
        echo ' <input type="hidden" name="choose" value="'.$choose.'">';
        echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />";
        echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
        echo " <input type=\"hidden\" name=\"action\" value=\"makedir\" />";
        echo " <input type=\"submit\" value=\"$strmakeafolder\" />";
        echo "</form>";
    echo "</td>";
    echo "<td align=\"right\">";
        echo "<form action=\"uploadpopup.php\" method=\"get\">";
        echo ' <input type="hidden" name="choose" value="'.$choose.'">';
        echo " <input type=\"hidden\" name=\"id\" value=\"$id\" />";
        echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
        echo " <input type=\"hidden\" name=\"action\" value=\"upload\" />";
        echo " <input type=\"submit\" value=\"$struploadafile\" />";
        echo "</form>";
		echo "</td>";
    echo "<td align=\"right\">";
		ipodcast_close_window("closewindow","updateparent()");
		echo "</td></tr>";
		echo "</table>";
?>
	<script language="javascript" type="text/javascript">
	function updateparent() {
	window.opener.location.reload();
	}		
	</script>
<?php
//		echo "<hr width=\"640\" align=\"center\" noshade=\"noshade\" size=\"1\" />";
		//Back button goes here

}

function ipodcast_close_window($name='closewindow', $options='') {
	if(!empty($options)) {
		$script = $options . ';' ;
	} else {
		$script = '' ;
	}	
    echo '<center>' . "\n";
    echo '<script type="text/javascript">' . "\n";
    echo '<!--' . "\n";
    echo "document.write('<form>');\n";
    echo "document.write('<input type=\"button\" onclick=\"" . $script . "self.close();\" value=\"".get_string("closewindow")."\" />');\n";
    echo "document.write('</form>');\n";
    echo '-->' . "\n";
    echo '</script>' . "\n";
    echo '<noscript>' . "\n";
    print_string($name);
    echo '</noscript>' . "\n";
    echo '</center>' . "\n";
}


?>
