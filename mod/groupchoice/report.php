<?php  // $Id: report.php,v 1.60.2.6 2009/02/09 09:26:47 danmarsden Exp $

    require_once("../../config.php");
    require_once("lib.php");

    $id         = required_param('id', PARAM_INT);   //moduleid
    $format     = optional_param('format', GROUPCHOICE_PUBLISH_NAMES, PARAM_INT);
    $download   = optional_param('download', '', PARAM_ALPHA);
    $action     = optional_param('action', '', PARAM_ALPHA);
    $attemptids = optional_param('attemptid', array(), PARAM_INT); //get array of responses to delete.

    if (! $cm = get_coursemodule_from_id('groupchoice', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course module is misconfigured");
    }

    require_login($course->id, false, $cm);
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    
    require_capability('mod/groupchoice:readresponses', $context);
    
    if (!$choice = groupchoice_get_choice($cm->instance)) {
        error("Course module is incorrect");
    }

    $strchoice = get_string("modulename", "groupchoice");
    $strchoices = get_string("modulenameplural", "groupchoice");
    $strresponses = get_string("responses","groupchoice");

    add_to_log($course->id, "groupchoice", "report", "{$CFG->wwwroot}/mod/groupchoice/report.php?id={$cm->id}", "$choice->id",$cm->id);

    if (data_submitted() && $action == 'delete' && has_capability('mod/groupchoice:deleteresponses',$context) && confirm_sesskey()) {
        groupchoice_delete_responses($attemptids, $choice); //delete responses.
        redirect("{$CFG->wwwroot}/mod/groupchoice/report.php?id={$cm->id}");
    }
        
    if (!$download) {

        $navigation = build_navigation($strresponses, $cm);
        print_header_simple(format_string($choice->name).": $strresponses", "", $navigation, "", '', true,
        update_module_button($cm->id, $course->id, $strchoice), navmenu($course, $cm));
        /// Check to see if groups are being used in this choice
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/groupchoice/report.php?id='.$id);
        }
    } else {
        $groupmode = groups_get_activity_groupmode($cm);
    }
    $users = groupchoice_get_response_data($choice, $cm, $groupmode);

    if ($download == "ods" && has_capability('mod/groupchoice:downloadresponses', $context)) {
        require_once("$CFG->libdir/odslib.class.php");
  
    /// Calculate file name 
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.ods';
    /// Creating a workbook
        $workbook = new MoodleODSWorkbook("-");
    /// Send HTTP headers
        $workbook->send($filename);
    /// Creating the first worksheet
        $myxls =& $workbook->add_worksheet($strresponses);

    /// Print names of all the fields
        $myxls->write_string(0,0,get_string("lastname"));
        $myxls->write_string(0,1,get_string("firstname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,4,get_string("choice","groupchoice"));

    /// generate the data for the body of the spreadsheet
        $i=0;  
        $row=1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = groupchoice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $studentid=(!empty($user->idnumber) ? $user->idnumber : " ");
                    $myxls->write_string($row,2,$studentid);

                    if (isset($option_text)) {
						if(is_numeric($option_text))
							$myxls->write_string($row,3,groups_get_group_name((int)$option_text));
						else
	                        $myxls->write_string($row,3,format_string($option_text,true));
                    }
                    $row++;
                    $pos=4;
                }
            }
        }
        /// Close the workbook
        $workbook->close();

        exit;
    }

    //print spreadsheet if one is asked for:
    if ($download == "xls" && has_capability('mod/groupchoice:downloadresponses', $context)) {
        require_once("$CFG->libdir/excellib.class.php");
  
    /// Calculate file name 
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.xls';
    /// Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
    /// Send HTTP headers
        $workbook->send($filename);
    /// Creating the first worksheet
        $myxls =& $workbook->add_worksheet($strresponses);

    /// Print names of all the fields
        $myxls->write_string(0,0,get_string("lastname"));
        $myxls->write_string(0,1,get_string("firstname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("choice","groupchoice"));
        
              
    /// generate the data for the body of the spreadsheet
        $i=0;  
        $row=1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = groupchoice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $studentid=(!empty($user->idnumber) ? $user->idnumber : " ");
                    $myxls->write_string($row,2,$studentid);
                    if (isset($option_text)) {
						if(is_numeric($option_text))
							$myxls->write_string($row,3,groups_get_group_name((int)$option_text));
						else
	                        $myxls->write_string($row,3,format_string($option_text,true));
                    }
                    $row++;
                }
            }
            $pos=4;
        }
        /// Close the workbook
        $workbook->close();
        exit;
    }

    // print text file  
    if ($download == "txt" && has_capability('mod/groupchoice:downloadresponses', $context)) {
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.txt';

        header("Content-Type: text/plain\n");
        header("Content-Disposition: attachment; filename=$filename");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

        /// Print names of all the fields

        echo get_string("firstname")."\t".get_string("lastname") . "\t". get_string("idnumber") . "\t";
        echo get_string("choice","groupchoice"). "\n";        

        /// generate the data for the body of the spreadsheet
        $i=0;  
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = groupchoice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    echo $user->lastname;
                    echo "\t".$user->firstname;
                    $studentid = " ";
                    if (!empty($user->idnumber)) {
                        $studentid = $user->idnumber;
                    }
                    echo "\t". $studentid."\t";
                    if (isset($option_text)) {
                        if(is_numeric($option_text))
							echo groups_get_group_name((int)$option_text);
						else
							echo format_string($option_text,true);
                    }
                    echo "\n";
                }
            }
        }
        exit;
    }
    groupchoice_show_results($choice, $course, $cm, $users, $format); //show table with students responses.

   //now give links for downloading spreadsheets. 
    if (!empty($users) && has_capability('mod/groupchoice:downloadresponses',$context)) {
        echo "<br />\n";
        echo "<table class=\"downloadreport\"><tr>\n";
        echo "<td>";
        $options = array();
        $options["id"] = "$cm->id";   
        $options["download"] = "ods";
        print_single_button("report.php", $options, get_string("downloadods"));
        echo "</td><td>";
        $options["download"] = "xls";
        print_single_button("report.php", $options, get_string("downloadexcel"));
        echo "</td><td>";
        $options["download"] = "txt";    
        print_single_button("report.php", $options, get_string("downloadtext"));

        echo "</td></tr></table>";
    }
    print_footer($course);

?>