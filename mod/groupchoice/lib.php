<?php // $Id: lib.php,v 1.59.2.27 2009/03/26 21:24:07 danmarsden Exp $

require_once("$CFG->libdir/grouplib.php");

$COLUMN_HEIGHT = 300;

define('GROUPCHOICE_PUBLISH_ANONYMOUS', '0');
define('GROUPCHOICE_PUBLISH_NAMES',     '1');

define('GROUPCHOICE_SHOWRESULTS_NOT',          '0');
define('GROUPCHOICE_SHOWRESULTS_AFTER_ANSWER', '1');
define('GROUPCHOICE_SHOWRESULTS_AFTER_CLOSE',  '2');
define('GROUPCHOICE_SHOWRESULTS_ALWAYS',       '3');

define('GROUPCHOICE_DISPLAY_HORIZONTAL',  '0');
define('GROUPCHOICE_DISPLAY_VERTICAL',    '1');

$GROUPCHOICE_PUBLISH = array (GROUPCHOICE_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'groupchoice'),
                         GROUPCHOICE_PUBLISH_NAMES      => get_string('publishnames','groupchoice'));

$GROUPCHOICE_SHOWRESULTS = array (GROUPCHOICE_SHOWRESULTS_NOT          => get_string('publishnot','groupchoice'),
                         GROUPCHOICE_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer','groupchoice'),
                         GROUPCHOICE_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose','groupchoice'),
                         GROUPCHOICE_SHOWRESULTS_ALWAYS       => get_string('publishalways','groupchoice'));

$GROUPCHOICE_DISPLAY = array (GROUPCHOICE_DISPLAY_HORIZONTAL   => get_string('displayhorizontal','groupchoice'),
                         GROUPCHOICE_DISPLAY_VERTICAL     => get_string('displayvertical','groupchoice'));

/// Standard functions /////////////////////////////////////////////////////////


function groupchoice_add_instance($choice) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will create a new instance and return the id number
// of the new instance.
    $choice->timemodified = time();

    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

    //insert answers
    if ($choice->id = insert_record("groupchoice", $choice)) {
        foreach ($choice->option as $key => $value) {
            $value = trim($value);
            if (isset($value) && ($value != '') && ($value != '0')) {
                $option = new object();
                $option->text = $value;
                $option->choiceid = $choice->id;
                if (isset($choice->limit[$key])) {
                    $option->maxanswers = $choice->limit[$key];
                }
                $option->timemodified = time();
                insert_record("groupchoice_options", $option);
            }
        }
    }
    return $choice->id;
}


function groupchoice_update_instance($choice) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will update an existing instance with new data.

    $choice->id = $choice->instance;
    $choice->timemodified = time();


    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

    //update, delete or insert answers
    foreach ($choice->option as $key => $value) {
        $value = trim($value);
        $option = new object();
        $option->text = $value;
        $option->choiceid = $choice->id;
        if (isset($choice->limit[$key])) {
            $option->maxanswers = $choice->limit[$key];
        }
        $option->timemodified = time();
        if (isset($choice->optionid[$key]) && !empty($choice->optionid[$key])){//existing choice record
            $option->id=$choice->optionid[$key];
            if (isset($value) && $value != '' && $value != '0') {
                update_record("groupchoice_options", $option);
            } else { //empty old option - needs to be deleted.
                delete_records("groupchoice_options", "id", $option->id);
            }
        } else {
            if (isset($value) && $value != '' && $value != '0') {
                insert_record("groupchoice_options", $option);
            }
        }
    }

    return update_record('groupchoice', $choice);

}

function groupchoice_get_membership($choice) {
    //$usergroup = false;
    $usergroups = array();
	foreach($choice->option as $key => $val)
	{
		if(groups_is_member((int)$val))
		{
			$usergroups[] = (int)$val;
			//break;
		}
	}
    return $usergroups;
}




function groupchoice_show_form_old($choice, $user, $cm, $usergroups) {

//$cdisplay is an array of the display info for a choice $cdisplay[$optionid]->text  - text name of option.
//                                                                            ->maxanswers -maxanswers for this option
//                                                                            ->full - whether this option is full or not. 0=not full, 1=full
    $cdisplay = array();

    $aid = 0;
    $choicefull = false;
    $cdisplay = array();

    if ($choice->limitanswers) { //set choicefull to true by default if limitanswers.
        $choicefull = true;
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

//	//$usergroup = false;
//    $usergroups = array();
//	foreach($choice->option as $key => $val)
//	{
//		if(groups_is_member((int)$val))
//		{
//			$usergroups[] = (int)$val;
//			//break;
//		}
//	}

    //$usergroups = groupchoice_get_membership($choice);

	//this means the user is not yet a member of any of these groups
	//if($usergroup===false):
    if(empty($usergroups)):

    foreach ($choice->option as $optionid => $text) {
        if (isset($text)) { //make sure there are no dud entries in the db with blank text values.
            $cdisplay[$aid]->optionid = $optionid;
			//$text is actually the group ID
			$groupid = (int)$text;
			$text = groups_get_group_name($groupid);
            $cdisplay[$aid]->text = $text;
            $cdisplay[$aid]->maxanswers = $choice->maxanswers[$optionid];
            
			/*if (isset($allresponses[$optionid])) {
                $cdisplay[$aid]->countanswers = count($allresponses[$optionid]);
            } else {
                $cdisplay[$aid]->countanswers = 0;
            }*/
			$cdisplay[$aid]->countanswers = count_records("groups_members","groupid",$groupid);
            if ($current = groups_is_member((int)$text)) {
                $cdisplay[$aid]->checked = ' checked="checked" ';
            } else {
                $cdisplay[$aid]->checked = '';
            }
            if ( $choice->limitanswers && 
                ($cdisplay[$aid]->countanswers >= $cdisplay[$aid]->maxanswers) && 
                (empty($cdisplay[$aid]->checked)) ) {
                $cdisplay[$aid]->disabled = ' disabled="disabled" ';
            } else {
                $cdisplay[$aid]->disabled = '';
                if ($choice->limitanswers && ($cdisplay[$aid]->countanswers < $cdisplay[$aid]->maxanswers)) {
                    $choicefull = false; //set $choicefull to false - as the above condition hasn't been set.
                }
            }
            $aid++;
        }
    }

    switch ($choice->display) {
        case GROUPCHOICE_DISPLAY_HORIZONTAL:
            echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";

            foreach ($cdisplay as $cd) {
                echo "<td align=\"center\" valign=\"top\">";
                echo "<input type=\"radio\" name=\"answer\" value=\"".$cd->optionid."\" alt=\"".strip_tags(format_text($cd->text))."\"". $cd->checked.$cd->disabled." />";
                if (!empty($cd->disabled)) {
                    echo format_text($cd->text."<br /><strong>".get_string('full','groupchoice')."</strong>");
                } else {
                    echo format_text($cd->text);
                }
                echo "</td>";
            }
            echo "</tr>";
            echo "</table>";
            break;

        case GROUPCHOICE_DISPLAY_VERTICAL:
            $displayoptions->para = false;
            echo "<table cellpadding=\"10\" cellspacing=\"10\" class=\"boxaligncenter\">";
            foreach ($cdisplay as $cd) {
                echo "<tr><td align=\"left\">";
                echo "<input type=\"radio\" name=\"answer\" value=\"".$cd->optionid."\" alt=\"".strip_tags(format_text($cd->text))."\"". $cd->checked.$cd->disabled." />";

                echo format_text($cd->text. ' ', FORMAT_MOODLE, $displayoptions); //display text for option.

                if ($choice->limitanswers && ($choice->showresults==GROUPCHOICE_SHOWRESULTS_ALWAYS) ){ //if limit is enabled, and show results always has been selected, display info beside each choice.
                    echo "</td><td>";

                    if (!empty($cd->disabled)) {
                        echo get_string('full','groupchoice');
                    } elseif(!empty($cd->checked)) {
                                //currently do nothing - maybe some text could be added here to signfy that the choice has been 'selected'
                    } elseif ($cd->maxanswers-$cd->countanswers==1) {
                        echo ($cd->maxanswers - $cd->countanswers);
                        echo " ".get_string('spaceleft','groupchoice');
                    } else {
                        echo ($cd->maxanswers - $cd->countanswers);
                        echo " ".get_string('spacesleft','groupchoice');
                    }
                    echo "</td>";
                } else if ($choice->limitanswers && ($cd->countanswers >= $cd->maxanswers)) {  //if limitanswers and answers exceeded, display "full" beside the choice.
                    echo " <strong>".get_string('full','groupchoice')."</strong>";
                }
                echo "</td>";
                echo "</tr>";
            }
        echo "</table>";
        break;
    }

    //show save choice button
    echo '<div class="button">';
    echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
    echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
    echo "<input type=\"hidden\" name=\"sesskey\" value=\"".sesskey()."\" />";

    if (has_capability('mod/groupchoice:choose', $context, $user->id, false)) { //don't show save button if the logged in user is the guest user.
        if ($choicefull) {
            print_string('choicefull', 'choice');
            echo "</br>";
        } else {
            echo "<input type=\"submit\" value=\"".get_string("savemychoice","groupchoice")."\" />";
        }

    } else {
        print_string('havetologin', 'choice');
    }
    echo "</div>";

	else: //if($usergroup):
        $groupname = '';
        foreach ($usergroups as $usergroup) {
	$groupname = groups_get_group_name($usergroup);

	echo <<<HTML
<div style="text-align:center;">
	You have elected to join $groupname.

HTML;


    if ($choice->allowupdate && $aaa = get_record('groups_members', 'groupid', $usergroup, 'userid', $user->id)) {
        echo "<a href='view.php?id=".$cm->id."&amp;action=delchoice&groupid=$usergroup'>".get_string("removemychoice","groupchoice")."</a>";
    }
        }
	endif;
    echo '</div>';
}

function groupchoice_user_submit_response($formanswer, $choice, $userid, $courseid, $cm) {

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

	foreach($choice->option as $key => $val)
	{
		if(groups_is_member((int)$val))
		{
			error("You are already a member of one of these groups. If this is a mistake, contact support.");
		}
	}

	$group = groups_get_group((int)$choice->option[$formanswer]);
	$groupmembers = groups_get_members((int)$choice->option[$formanswer]);

    $countanswers=0;
    if($choice->limitanswers) {
        // Find out whether groups are being used and enabled
        if ($groupmembers) {
            foreach ($groupmembers as $a) { //only return enrolled users.
                if (has_capability('mod/groupchoice:choose', $context, $a->userid, false)) {
                    $countanswers++;
                }
            }
        }
        $maxans = $choice->maxanswers[$formanswer];
    }

    if (!($choice->limitanswers && ($countanswers >= $maxans) )) {
            $newanswer = NULL;
            $newanswer->groupid = $group->id;
            $newanswer->userid = $userid;
            $newanswer->timeadded = time();
            if (! insert_record("groups_members", $newanswer)) {
                error("Could not add you to this group.");
            }
            add_to_log($courseid, "groupchoice", "choose", "view.php?id=$cm->id", $choice->id, $cm->id);
    } else {
        if (!($current->optionid==$formanswer)) { //check to see if current choice already selected - if not display error
            error("This group is full!");
        }
    }
}

function groupchoice_show_reportlink($cm) {
    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "groupchoice")."</a>";
    echo '</div>';
}

function groupchoice_show_results_old($choice, $course, $cm, $allresponses, $forcepublish='') {
    global $CFG, $COLUMN_HEIGHT;
    
    print_heading(get_string("responses","groupchoice"));
    if (empty($forcepublish)) { //alow the publish setting to be overridden
        $forcepublish = $choice->publish;
    }

    if (empty($allresponses)) {
        print_heading(get_string("nousersyet"));
        return false;
    }

    $totalresponsecount = 0;
    foreach ($allresponses as $optionid => $userlist) {
        if ($choice->showunanswered || $optionid) {
            $totalresponsecount += count($userlist);
        }
    }
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $hascapfullnames = has_capability('moodle/site:viewfullnames', $context);
    
    $viewresponses = has_capability('mod/groupchoice:readresponses', $context);

    switch ($forcepublish) {
        case GROUPCHOICE_PUBLISH_NAMES:
            echo '<div id="tablecontainer">';
            if ($viewresponses) {
                echo '<form id="attemptsform" method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="return confirm(\''.addslashes(get_string('deleteattemptcheck','groupchoice')).'\');">';
                echo '<div>';
                echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                echo '<input type="hidden" name="mode" value="overview" />';
            }

            echo "<table cellpadding=\"5\" cellspacing=\"10\" class=\"results names\">";
            echo "<tr>";
  
            $columncount = array(); // number of votes in each column
            if ($choice->showunanswered) {
                $columncount[0] = 0;
                echo "<th class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'choice');
                echo "</th>";
            }
            $count = 1;


            foreach ($choice->option as $optionid => $optiontext) {
                $columncount[$optionid] = 0; // init counters
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string(groups_get_group_name($optiontext));
                echo "</th>";
                $count++;
            }
            echo "</tr><tr>";

            if ($choice->showunanswered) {
                echo "<td class=\"col$count data\" >";
                // added empty row so that when the next iteration is empty,
                // we do not get <table></table> erro from w3c validator
                // MDL-7861


                echo "<table class=\"choiceresponse\"><tr><td></td></tr>";
                if (!empty($allresponses[0])) {
                    foreach ($allresponses[0] as $user) {
                        echo "<tr>";
                        echo "<td class=\"picture\">";
                        print_user_picture($user->id, $course->id, $user->picture);
                        echo "</td><td class=\"fullname\">";
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                        echo fullname($user, $hascapfullnames);
                        echo "</a>";
                        echo "</td></tr>";
                    }
                }
                echo "</table></td>";



            }
            $count = 0;
            foreach ($choice->option as $optionid => $optiontext) {
                    echo '<td class="col'.$count.' data" >';

                    // added empty row so that when the next iteration is empty,
                    // we do not get <table></table> erro from w3c validator
                    // MDL-7861
                    echo '<table class="choiceresponse"><tr><td></td></tr>';
                    if (isset($allresponses[$optionid])) {
                        foreach ($allresponses[$optionid] as $user) {
                            $columncount[$optionid] += 1;
                            echo '<tr><td class="attemptcell">';
                            if ($viewresponses and has_capability('mod/choice:deleteresponses',$context)) {
                                echo '<input type="checkbox" name="attemptid[' . $optiontext . '][]" value="'. $user->id. '" />';
                            }
                            echo '</td><td class="picture">';
                            print_user_picture($user->id, $course->id, $user->picture);
                            echo '</td><td class="fullname">';
                            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                            echo fullname($user, $hascapfullnames);
                            echo '</a>';
                            echo '</td></tr>';
                       }
                    }
                    $count++;
                    echo '</table></td>';
            }
            echo "</tr><tr>";
            $count = 0;
            
            if ($choice->showunanswered) {
                echo "<td></td>";
            }
            
            foreach ($choice->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"count\">";
                if ($choice->limitanswers) {
                    echo get_string("taken","groupchoice").":";
                    echo $columncount[$optionid];
                    echo "<br/>";
                    echo get_string("limit","groupchoice").":";
                    $groupchoice_option = get_record("groupchoice_options", "id", $optionid);
                    echo $groupchoice_option->maxanswers;
                } else {
                    if (isset($columncount[$optionid])) {
                        echo $columncount[$optionid];
                    }
                }
                echo "</td>";
                $count++;
            }
            echo "</tr>";

            /// Print "Select all" etc.
            if ($viewresponses and has_capability('mod/choice:deleteresponses',$context)) {
                echo '<tr><td></td><td>';
                echo '<a href="javascript:select_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectall', 'quiz').'</a> / ';
                echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectnone', 'quiz').'</a> ';
                echo '&nbsp;&nbsp;';
                $options = array('delete' => get_string('delete'));
                echo choose_from_menu($options, 'action', '', get_string('withselected', 'quiz'), 'if(this.selectedIndex > 0) submitFormById(\'attemptsform\');', '', true);
                echo '<noscript id="noscriptmenuaction" style="display: inline;">';
                echo '<div>';
                echo '<input type="submit" value="'.get_string('go').'" /></div></noscript>';
                echo '<script type="text/javascript">'."\n<!--\n".'document.getElementById("noscriptmenuaction").style.display = "none";'."\n-->\n".'</script>';
                echo '</td><td></td></tr>';
            }
            
            echo "</table></div>";
            if ($viewresponses) {
                echo "</form></div>";
            }
            break;
        
        
        case GROUPCHOICE_PUBLISH_ANONYMOUS:

            echo "<table cellpadding=\"5\" cellspacing=\"0\" class=\"results anonymous\">";
            echo "<tr>";
            $maxcolumn = 0;
            if ($choice->showunanswered) {
                echo "<th  class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'choice');
                echo "</th>";
                $column[0] = 0;
                foreach ($allresponses[0] as $user) {
                    $column[0]++;
                }
                $maxcolumn = $column[0];
            }
            $count = 1;

            foreach ($choice->option as $optionid => $optiontext) {
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string(groups_get_group_name($optiontext));
                echo "</th>";
                
                $column[$optionid] = 0;
                if (isset($allresponses[$optionid])) {
                    $column[$optionid] = count($allresponses[$optionid]);
                    if ($column[$optionid] > $maxcolumn) {
                        $maxcolumn = $column[$optionid];
                    }
                } else {
                    $column[$optionid] = 0;
                }
            }
            echo "</tr><tr>";

            $height = 0;

            if ($choice->showunanswered) {
                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[0] / (float)$maxcolumn);
                }
                echo "<td style=\"vertical-align:bottom\" align=\"center\" class=\"col0 data\">";
                echo "<img src=\"column.png\" height=\"$height\" width=\"49\" alt=\"\" />";
                echo "</td>";
            }
            $count = 1;
            foreach ($choice->option as $optionid => $optiontext) {
                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[$optionid] / (float)$maxcolumn);
                }
                echo "<td style=\"vertical-align:bottom\" align=\"center\" class=\"col$count data\">";
                echo "<img src=\"column.png\" height=\"$height\" width=\"49\" alt=\"\" />";
                echo "</td>";
                $count++;
            }
            echo "</tr><tr>";


            if ($choice->showunanswered) {
                echo '<td align="center" class="col0 count">';
                if (!$choice->limitanswers) {
                    echo $column[0];
                    echo '<br />('.format_float(((float)$column[0]/(float)$totalresponsecount)*100.0,1).'%)';
                }
                echo '</td>';
            }
            $count = 1;
            foreach ($choice->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"col$count count\">";
                if ($choice->limitanswers) {
                    echo get_string("taken","groupchoice").":";
                    echo $column[$optionid].'<br />';
                    echo get_string("limit","groupchoice").":";
                    $groupchoice_option = get_record("groupchoice_options", "id", $optionid);
                    echo $groupchoice_option->maxanswers;
                } else {  
                    echo $column[$optionid];
                    echo '<br />('.format_float(((float)$column[$optionid]/(float)$totalresponsecount)*100.0,1).'%)';
                }
                echo "</td>";
                $count++;
            }
            echo "</tr></table>";
            
            break;
    }
}

include 'alternative_display.php';

function groupchoice_delete_responses($attemptids, $choice) {

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }

    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }


    foreach($attemptids as $groupid => $attemptid) {
        foreach($attemptid as $userid) {
            //die('groupid: ' . $groupid . ' userid: ' . $userid);
            groups_remove_member($groupid, $userid);
        }
    }
    return true;
}


function groupchoice_delete_instance($id) {
// Given an ID of an instance of this module,
// this function will permanently delete the instance
// and any data that depends on it.

    if (! $choice = get_record("groupchoice", "id", "$id")) {
        return false;
    }

    $result = true;

    if (! delete_records("groupchoice_options", "choiceid", "$choice->id")) {
        $result = false;
    }

    if (! delete_records("groupchoice", "id", "$choice->id")) {
        $result = false;
    }

    return $result;
}

function groupchoice_get_participants($choiceid) {
//Returns the users with data in one choice

    global $CFG;

    //Get students
    $students = get_records_sql("SELECT DISTINCT u.id, u.id
                                 FROM {$CFG->prefix}user u,
                                      {$CFG->prefix}groups_members a
                                 WHERE a.groupid = '$choiceid' and
                                       u.id = a.userid");

    //Return students array (it contains an array of unique users)
    return ($students);
}


function groupchoice_get_option_text($choice, $id) {
// Returns text string which is the answer that matches the id
    if ($result = get_record("groupchoice_options", "id", $id)) {
        return $result->text;
    } else {
        return get_string("notanswered","groupchoice");
    }
}

function groupchoice_get_choice($choiceid) {
// Gets a full choice record

    if ($choice = get_record("groupchoice", "id", $choiceid)) {
        if ($options = get_records("groupchoice_options", "choiceid", $choiceid, "id")) {
            foreach ($options as $option) {
                $choice->option[$option->id] = $option->text;
                $choice->maxanswers[$option->id] = $option->maxanswers;
            }
            return $choice;
        }
    }
    return false;
}

function groupchoice_get_view_actions() {
    return array('view','view all','report');
}

function groupchoice_get_post_actions() {
    return array('choose','choose again');
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the choice.
 * @param $mform form passed by reference
 */
function groupchoice_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'choiceheader', get_string('modulenameplural','groupchoice'));
    $mform->addElement('advcheckbox', 'reset_choice', get_string('removeresponses','groupchoice'));
}

/**
 * Course reset form defaults.
 */
function groupchoice_reset_course_form_defaults($course) {
    return array('reset_choice'=>1);
}

/**
 * Actual implementation of the rest coures functionality, delete all the
 * choice responses for course $data->courseid.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function groupchoice_reset_userdata($data) {
    global $CFG;

    $componentstr = get_string('modulenameplural', 'groupchoice');
    $status = array();

    if (!empty($data->reset_choice)) {
        $choicessql = "SELECT ch.id
                         FROM {$CFG->prefix}groupchoice ch
                        WHERE ch.course={$data->courseid}";

        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses','groupchoice'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('choice', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}






function groupchoice_get_response_data($choice, $cm, $groupmode) {
    global $CFG, $USER;

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Get the current group
    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

/// Initialise the returned array, which is a matrix:  $allresponses[responseid][userid] = responseobject
    $allresponses = array();
    $allgrouparr = array();

/// First get all the users who have access here
/// To start with we assume they are all "unanswered" then move them later
    $allresponses[0] = get_users_by_capability($context, 'mod/groupchoice:choose', 'u.id, u.picture, u.firstname, u.lastname, u.idnumber', 'u.lastname ASC,u.firstname ASC', '', '', $currentgroup, '', false, true);

/// Get all the recorded responses for this choice
    /*$rawresponses = get_records('groups_members', 'choiceid', $choice->id);

/// Use the responses to move users into the correct column

    if ($rawresponses) {
        foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group
                $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                unset($allresponses[0][$response->userid]);   // Remove from unanswered column
            }
        }
    }
    if (empty($allresponses[0])) {
        unset($allresponses[0]);
    }*/
	
	foreach($choice->option as $opt => $gid)
	{
		$groupmem = groups_get_members((int)$gid,'u.id, u.picture, u.firstname, u.lastname, u.idnumber', 'u.lastname ASC,u.firstname ASC');
		$grouparr = array();
		//tried array_intersect, doesn't work with stdClass objects - have to do it manually (sigh)

        if ($groupmem) {
            foreach($groupmem as $i)
            {
                //STUPID but can't think of a better way
                foreach($allresponses[0] as $j)
                {
                    if($i->id == $j->id)
                    {
                        $grouparr[] = $i;
                        $allgrouparr[] = $i;
                        //unset($allresponses[0][$j->id]);   // AD: Remove from unanswered column - another mistake by original coder
                        break;
                    }
                }
            }
        }

		if(count($grouparr)>0)
		{
			$allresponses[$opt] = $grouparr;
		}
	}

    foreach($allgrouparr as $j)
    {
        unset($allresponses[0][$j->id]);   // AD: Remove from unanswered column - another mistake by original coder
    }

    return $allresponses;
}

/**
 * Returns all other caps used in module
 */
function groupchoice_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

?>
