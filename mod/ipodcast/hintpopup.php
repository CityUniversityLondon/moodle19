<?php 
/******************************************************************************\
 *
 * Filename:    hintpopup.php
 *
 *		This file pops up a file window to select attachment
 *
 *
 * History:     10/23/06 Tom Dolsky     - Created hintopup file
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
		if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
			echo "ipodcast not found\n";
			return false;
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
		if (! $ipodcastcourse = get_record("ipodcast_courses", "id", $ipodcast->ipodcastcourseid)) {
			echo "ipodcast not found\n";
			return false;
		}			
    }

	$isteacheredit = ipodcast_is_teacheredit($ipodcastcourse, $USER->id);
	
    if (! $isteacheredit ) {
        error("You need to be a teacher with editing privileges");
		die;
    }
	
/******************************************************************************\
 *
\******************************************************************************/  

	html_header($course);
	echo "Hinting file " . $ipodcast->attachment . "</br>\n";
	
	if($ipodcastcourse->enabledarwin) {
		if(!hint_tracks($course->id,$ipodcast->attachment)) {
			echo "Error hinting file for Darwin streaming server.\n";
		}
	} else {
		echo "Darwin not enabled for this course.\n";	
	}
	
	
    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" width=\"640\">";    
    echo "<tr><td align=\"right\">";
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
	html_footer();
	
/******************************************************************************\
 *
\******************************************************************************/
function html_footer() {
	echo "</td></tr></table></body></html>";
}
	
/******************************************************************************\
 *
\******************************************************************************/  
function html_header($course){
		print_header();
					

	echo "<table border=\"0\" align=\"center\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
	echo "<tr>";
	echo "<td colspan=\"2\">";

}

/******************************************************************************\
 *
\******************************************************************************/  
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