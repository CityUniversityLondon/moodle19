<?php

function groupchoice_show_results($choice, $course, $cm, $allresponses, $forcepublish='') {
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
    $width = '950px';

    switch ($forcepublish) {
        case GROUPCHOICE_PUBLISH_NAMES:
            echo '<div id="tablecontainer">';

            if ($viewresponses) {
                echo '<form id="attemptsform" method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="return confirm(\''.addslashes(get_string('deleteattemptcheck','groupchoice')).'\');">';
                echo '<div id="viewresponses" class="clearfix">';
                echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                echo '<input type="hidden" name="mode" value="overview" />';
            }

            $columncount = array(); // number of votes in each column
            if ($choice->showunanswered) {
                $columncount[0] = 0;
                $width = '760px';
                echo "<div id=\"not_answered\">";
                echo "<dl class=\"groupchoice results names\">";
                echo "<dt class=\"col0 header\" >";
                print_string('notanswered', 'choice');
                echo "</dt>";

                if (!empty($allresponses[0])) {

                    foreach ($allresponses[0] as $user) {
                        echo "<dd class=\"fullname\">";
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                        echo fullname($user, $hascapfullnames);
                        echo "</a>";
                        echo "</dd>";
                    }

                }

                echo "</dl>";
                echo "</div><!--not_answered-->"; // div id not_answered
            }

            $count = 1;
            echo "<div id=\"answered\" style=\"width:$width\">";

            foreach ($choice->option as $optionid => $optiontext) {
                $columncount[$optionid] = 0; // init counters
                echo "<dl class=\"groupchoice results names\">";
                echo "<dt class=\"col$count header\" >";
                echo format_string(groups_get_group_name($optiontext));

                if (isset($allresponses[$optionid])) {

                    foreach ($allresponses[$optionid] as $user) {
                        $columncount[$optionid] += 1;
                        echo '<dd class="col'.$count.' data" >';
                        if ($viewresponses and has_capability('mod/choice:deleteresponses',$context)) {
                            echo '<input type="checkbox" name="attemptid[' . $optiontext . '][]" value="'. $user->id. '" />';
                        }
                        //echo '</dd><dd class="fullname">';
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                        echo fullname($user, $hascapfullnames);
                        echo '</a>';
                        echo '</dd>';
                   }
                }

                echo "<dd align=\"center\" class=\"count\">";

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

                echo '</dd>';
                echo '</dl>';
                $count++;
            }
            echo "</div><!--answered-->"; // div id answered
            $count = 0;



            //echo "</div>";
            if ($viewresponses) {
                echo "</div><!--viewresponses-->";

                /// Print "Select all" etc.
                if (has_capability('mod/choice:deleteresponses',$context)) {
                    echo "<div class=\"clearfix\">";
                    echo '<a href="javascript:select_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectall', 'quiz').'</a> / ';
                    echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectnone', 'quiz').'</a> ';
                    echo '&nbsp;&nbsp;';
                    $options = array('delete' => get_string('delete'));
                    echo choose_from_menu($options, 'action', '', get_string('withselected', 'quiz'), 'if(this.selectedIndex > 0) submitFormById(\'attemptsform\');', '', true);
                    echo '<noscript id="noscriptmenuaction" style="display: inline;">';
                    echo '<div>';
                    echo '<input type="submit" value="'.get_string('go').'" /></div><!--noscript--></noscript>';
                    echo '<script type="text/javascript">'."\n<!--\n".'document.getElementById("noscriptmenuaction").style.display = "none";'."\n-->\n".'</script>';
                    echo "</div><!--buttons-->";
                }
                
            echo "</form>";
            }
            echo "</div><!--tablecontainer-->";

            break;


        case GROUPCHOICE_PUBLISH_ANONYMOUS:

            $maxcolumn = 0;

            if ($choice->showunanswered) {
                $width = '760px';
                $height = 0;
                echo "<div id=\"not_answered\">";
                echo "<dl class=\"groupchoice anon\">";
                echo "<dt  class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'choice');
                echo "</dt>";
                $column[0] = 0;

                foreach ($allresponses[0] as $user) {
                    $column[0]++;
                }

                $maxcolumn = $column[0];

                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[0] / (float)$maxcolumn);
                }

                echo "<dd style=\"vertical-align:bottom; height:{$COLUMN_HEIGHT}px\" align=\"center\" class=\"col0 data\">";
                echo "<img src=\"column.png\" heighclearfixt=\"$height\" width=\"49\" alt=\"\" />";
                echo "</dd>";
                echo "<dd>";

                if (!$choice->limitanswers) {
                    echo $column[0];
                    echo '<br />('.format_float(((float)$column[0]/(float)$totalresponsecount)*100.0,1).'%)';
                }

                echo '</dd>';
                echo "</dl>";
                echo "</div><!--not_answered-->"; // div id not_answered
            }

            $count = 1;

            echo "<div id=\"answered\" style=\"width:$width\">";

            foreach ($choice->option as $optionid => $optiontext) {
                echo "<dl class=\"groupchoice anon\">";
                echo "<dt class=\"col$count header\" scope=\"col\">";
                echo format_string(groups_get_group_name($optiontext));
                echo "</dt>";
                $column[$optionid] = 0;

                if (isset($allresponses[$optionid])) {
                    $column[$optionid] = count($allresponses[$optionid]);

                    if ($column[$optionid] > $maxcolumn) {
                        $maxcolumn = $column[$optionid];
                    }

                } else {
                    $column[$optionid] = 0;
                }

                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[$optionid] / (float)$maxcolumn);
                }

                echo "<dd style=\"vertical-align:bottom; height:{$COLUMN_HEIGHT}px\"  align=\"center\" class=\"col$count data\" >";
                echo "<img src=\"column.png\" height=\"$height\" width=\"49\" alt=\"\" />";
                echo "</dd>";
                echo "<dd align=\"center\" class=\"col$count count\">";

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

                echo "</dd>";
                echo "</dl>";
                $count++;
            }

            echo "</div><!--not_answered-->"; // div id answered
           // echo "</div>";

            break;
    }
}



function groupchoice_show_form($choice, $user, $cm, $usergroups) {
    $cdisplay = array();
    $aid = 0;
    $choicefull = false;
    $cdisplay = array();

    if ($choice->limitanswers) { //set choicefull to true by default if limitanswers.
        $choicefull = true;
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if(empty($usergroups)):

    foreach ($choice->option as $optionid => $text) {
        if (isset($text)) { //make sure there are no dud entries in the db with blank text values.
            $cdisplay[$aid]->optionid = $optionid;
            //$text is actually the group ID
            $groupid = (int)$text;
            $text = groups_get_group_name($groupid);
            $cdisplay[$aid]->text = $text;
            $cdisplay[$aid]->maxanswers = $choice->maxanswers[$optionid];
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
            echo "<div id=\"groupselect\" class=\"boxaligncenter\">";

            foreach ($cdisplay as $cd) {
                echo "<dl class=\"groupchoice\">";
                echo "<dd align=\"center\" valign=\"top\" class=\"select\">";
                echo "<input type=\"radio\" name=\"answer\" value=\"".$cd->optionid."\" alt=\"".strip_tags(format_text($cd->text))."\"". $cd->checked.$cd->disabled." />";
                if (!empty($cd->disabled)) {
                    echo format_text($cd->text."<br /><strong>".get_string('full','groupchoice')."</strong>");
                } else {
                    echo format_text($cd->text);
                }
                echo "</dd>";
                echo "</dl>";
            }

            echo "</div><!--groupselect-->";
            break;

        case GROUPCHOICE_DISPLAY_VERTICAL:
            $displayoptions->para = false;
            echo "<table id=\"vertical\" cellpadding=\"10\" cellspacing=\"10\" class=\"boxaligncenter\">";
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
        echo "</table><!--vertical-->";
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
    echo "</div><!--save choice button-->";

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
        echo '</div><!--joined group-->';
    }
    
    endif;
    //echo '</div>';
}