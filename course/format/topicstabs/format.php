<?php // $Id: format.php,v 1.83.2.3 2008/12/10 06:05:27 dongsheng Exp $
      // Display the whole course as "topics" made of of modules
      // In fact, this is very similar to the "weeks" format, in that
      // each "topic" is actually a week.  The main difference is that
      // the dates aren't printed - it's just an aesthetic thing for
      // courses that aren't so rigidly defined by time.
      // Included from "view.php"
      

    require_once($CFG->libdir.'/ajax/ajaxlib.php');
  
    $topic = optional_param('topic', -1, PARAM_INT);

    // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);

    $preferred_width_left  = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]),  
                                            BLOCK_L_MAX_WIDTH);
    $preferred_width_right = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), 
                                            BLOCK_R_MAX_WIDTH);

    if ($topic != -1) {
        $displaysection = course_set_display($course->id, $topic);
    } else {
        if (isset($USER->display[$course->id])) {       // for admins, mostly
            $displaysection = $USER->display[$course->id];
        } else {
            $displaysection = course_set_display($course->id, 0);
        }
    }

    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
        $course->marker = $marker;
        if (! set_field("course", "marker", $marker, "id", $course->id)) {
            error("Could not mark that topic for this course");
        }
    }

    $streditsummary   = get_string('editsummary');
    $stradd           = get_string('add');
    $stractivities    = get_string('activities');
    $strshowalltopics = get_string('showalltopics');
    $strtopic         = get_string('topic');
    $strgroups        = get_string('groups');
    $strgroupmy       = get_string('groupmy');
    $editing          = $PAGE->user_is_editing();

    if ($editing) {
        $strstudents = moodle_strtolower($course->students);
        $strtopichide = get_string('topichide', '', $strstudents);
        $strtopicshow = get_string('topicshow', '', $strstudents);
        $strmarkthistopic = get_string('markthistopic');
        $strmarkedthistopic = get_string('markedthistopic');
        $strmoveup = get_string('moveup');
        $strmovedown = get_string('movedown');
    }


/// Layout the whole page as three big columns.
    echo '<table id="layout-table" cellspacing="0" summary="'.get_string('layouttable').'"><tr>';

/// The left column ...
    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':

    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
        echo '<td style="width:'.$preferred_width_left.'px" id="left-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        print_container_end();
        echo '</td>';
    }

            break;
            case 'middle':
/// Start main column
    echo '<td id="middle-column">';
    print_container_start();
    echo skip_main_destination();

    print_heading_block(get_string('topicoutline'), 'outline');

    echo '<table class="topics" width="100%" summary="'.get_string('layouttable').'">';

/// If currently moving a file then show the current clipboard
    if (ismoving($course->id)) {
        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', addslashes($USER->activitycopyname)));
        $strcancel= get_string('cancel');
        echo '<tr class="clipboard">';
        echo '<td colspan="3">';
        echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.$USER->sesskey.'">'.$strcancel.'</a>)';
        echo '</td>';
        echo '</tr>';
    }

/// Print Section 0

    $section = 0;
    $thissection = $sections[$section];

    if ($thissection->summary or $thissection->sequence or isediting($course->id)) {
        echo '<tr id="section-0" class="section main">';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        
        echo '<div class="summary">';
        $summaryformatoptions->noclean = true;
        echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

        if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            echo '<a title="'.$streditsummary.'" '.
                 ' href="editsection.php?id='.$thissection->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" '.
                 ' alt="'.$streditsummary.'" /></a><br /><br />';
        }
        echo '</div>';

        print_section($course, $thissection, $mods, $modnamesused);

        if (isediting($course->id)) {
            print_section_add_menus($course, $section, $modnames);
        }

        echo '</td>';
        echo '<td class="right side">&nbsp;</td>';
        echo '</tr>';
        echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
    }


/// Now all the normal modules by topic
/// Everything below uses "section" terminology - each "section" is a topic.

    $timenow = time();
    $section = 1;
    $sectionmenu = array();

    while ($section <= $course->numsections) {

        if (!empty($sections[$section])) {
            $thissection = $sections[$section];

        } else {
            unset($thissection);
            $thissection->course = $course->id;   // Create a new section structure
            $thissection->section = $section;
            $thissection->summary = '';
            $thissection->visible = 1;
            if (!$thissection->id = insert_record('course_sections', $thissection)) {
                notify('Error inserting new topic!');
            }
        }

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

        if (!empty($displaysection) and $displaysection != $section) {
            if ($showsection) {
                $strsummary = strip_tags(format_string($thissection->summary,true));
                if (strlen($strsummary) < 57) {
                    $strsummary = ' - '.$strsummary;
                } else {
                    $strsummary = ' - '.substr($strsummary, 0, 60).'...';
                }
                $sectionmenu['topic='.$section] = s($section.$strsummary);
            }
            $section++;
            continue;
        }

        if ($showsection) {

            $currenttopic = ($course->marker == $section);

            $currenttext = '';
            if (!$thissection->visible) {
                $sectionstyle = ' hidden';
            } else if ($currenttopic) {
                $sectionstyle = ' current';
                $currenttext = get_accesshide(get_string('currenttopic','access'));
            } else {
                $sectionstyle = '';
            }

            echo '<tr id="section-'.$section.'" class="section main'.$sectionstyle.'">';
            echo '<td class="left side">'.$currenttext.$section.'</td>';

            echo '<td class="content">';
            if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                echo get_string('notavailable');
            } else {
                echo '<div class="summary">';
                $summaryformatoptions->noclean = true;
                echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                         '<img src="'.$CFG->pixpath.'/t/edit.gif" alt="'.$streditsummary.'" /></a><br /><br />';
                }
                echo '</div>';

                print_section_mh($course, $thissection, $mods, $modnamesused);            
                
                if (isediting($course->id)) {
                    print_section_add_menus($course, $section, $modnames);
                }
            }
            echo '</td>';

            echo '<td class="right side">';
            if ($displaysection == $section) {      // Show the zoom boxes
                echo '<a href="view.php?id='.$course->id.'&amp;topic=0#section-'.$section.'" title="'.$strshowalltopics.'">'.
                     '<img src="'.$CFG->pixpath.'/i/all.gif" alt="'.$strshowalltopics.'" /></a><br />';
            } else {
                $strshowonlytopic = get_string('showonlytopic', '', $section);
                echo '<a href="view.php?id='.$course->id.'&amp;topic='.$section.'" title="'.$strshowonlytopic.'">'.
                     '<img src="'.$CFG->pixpath.'/i/one.gif" alt="'.$strshowonlytopic.'" /></a><br />';
            }

            if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                if ($course->marker == $section) {  // Show the "light globe" on/off
                    echo '<a href="view.php?id='.$course->id.'&amp;marker=0&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strmarkedthistopic.'">'.
                         '<img src="'.$CFG->pixpath.'/i/marked.gif" alt="'.$strmarkedthistopic.'" /></a><br />';
                } else {
                    echo '<a href="view.php?id='.$course->id.'&amp;marker='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strmarkthistopic.'">'.
                         '<img src="'.$CFG->pixpath.'/i/marker.gif" alt="'.$strmarkthistopic.'" /></a><br />';
                }

                if ($thissection->visible) {        // Show the hide/show eye
                    echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strtopichide.'">'.
                         '<img src="'.$CFG->pixpath.'/i/hide.gif" alt="'.$strtopichide.'" /></a><br />';
                } else {
                    echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strtopicshow.'">'.
                         '<img src="'.$CFG->pixpath.'/i/show.gif" alt="'.$strtopicshow.'" /></a><br />';
                }

                if ($section > 1) {                       // Add a arrow to move section up
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.$USER->sesskey.'#section-'.($section-1).'" title="'.$strmoveup.'">'.
                         '<img src="'.$CFG->pixpath.'/t/up.gif" alt="'.$strmoveup.'" /></a><br />';
                }

                if ($section < $course->numsections) {    // Add a arrow to move section down
                    echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.$USER->sesskey.'#section-'.($section+1).'" title="'.$strmovedown.'">'.
                         '<img src="'.$CFG->pixpath.'/t/down.gif" alt="'.$strmovedown.'" /></a><br />';
                }

            }

            echo '</td></tr>';
            echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
        }

        $section++;
    }
    echo '</table>';

    if (!empty($sectionmenu)) {
        echo '<div class="jumpmenu">';
        echo popup_form($CFG->wwwroot.'/course/view.php?id='.$course->id.'&amp;', $sectionmenu,
                   'sectionmenu', '', get_string('jumpto'), '', '', true);
        echo '</div>';
    }

    print_container_end();
    echo '</td>';

            break;
            case 'right':
    // The right column
    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
        echo '<td style="width:'.$preferred_width_right.'px" id="right-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
        print_container_end();
        echo '</td>';
    }

            break;
        }
    }
    echo '</tr></table>';
    
    
    
    
    
function print_section_mh($course, $section, $mods, $modnamesused, $absolute=false, $width="100%") {
/// Prints a section full of activity modules
    global $CFG, $USER;

    static $initialised;

    static $groupbuttons;
    static $groupbuttonslink;
    static $isediting;
    static $ismoving;
    static $strmovehere;
    static $strmovefull;
    static $strunreadpostsone;
    static $usetracking;
    static $groupings;


    if (!isset($initialised)) {
        $groupbuttons     = ($course->groupmode or (!$course->groupmodeforce));
        $groupbuttonslink = (!$course->groupmodeforce);
        $isediting        = isediting($course->id);
        $ismoving         = $isediting && ismoving($course->id);
        if ($ismoving) {
            $strmovehere  = get_string("movehere");
            $strmovefull  = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }
        include_once($CFG->dirroot.'/mod/forum/lib.php');
        if ($usetracking = forum_tp_can_track_forums()) {
            $strunreadpostsone = get_string('unreadpostsone', 'forum');
        }
        $initialised = true;
    }

    $labelformatoptions = new object();
    $labelformatoptions->noclean = true;

/// Casting $course->modinfo to string prevents one notice when the field is null
    $modinfo = get_fast_modinfo($course);


    //Acccessibility: replace table with list <ul>, but don't output empty list.
    if (!empty($section->sequence)) {

        // Fix bug #5027, don't want style=\"width:$width\".
        //echo "<ul class=\"section img-text\">\n";

        $sectionmods = explode(",", $section->sequence);
        echo "<table style=\"border:2px solid #ccc; width:100%;\">\n";
        echo "<tr style=\"border-bottom:1px solid #ccc\"><th style=\"width:50%; text-align:center; border-right:1px solid #ccc;\">RESOURCES</th><th style=\"width:50%; text-align:center\">ACTIVITIES</th></tr><tr>";
        
        echo "<td style=\"border-right:1px solid #ccc;\">\n";
                                                                                                   
        // must get moving mod type before we do anything
        $modMoveType = '';
        foreach ($sectionmods as $modnumber) {
            $mod = $mods[$modnumber];
            if ($ismoving and ($mod->id == $USER->activitycopy)) {
                // find out what kind of thing is being moved!
                $modMoveType = $mod->modname;
            }
        }
        
        foreach ($sectionmods as $modnumber) {

            if (empty($mods[$modnumber])) {
                continue;
            }

            $mod = $mods[$modnumber];

            if ($ismoving and $mod->id == $USER->activitycopy) {
                // do not display moving mod
                continue;
            }

            if (isset($modinfo->cms[$modnumber])) {
                if (!$modinfo->cms[$modnumber]->uservisible) {
                    // visibility shortcut
                    continue;
                }
            } else {
                if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php")) {
                    // module not installed
                    continue;
                }
                if (!coursemodule_visible_for_user($mod)) {
                    // full visibility check
                    continue;
                }
            }

            // OK, here's the magic bit, first time around only get items with modname==resource
            if (($mod->modname == 'resource') || ($mod->modname == 'label')) {
              echo '<span class="activity '.$mod->modname.'" id="module-'.$modnumber.'">';  // Unique ID
              if (($ismoving) && (($modMoveType == 'resource') || ($modMoveType == 'label'))) {
                  echo '<a title="'.$strmovefull.'"'.
                       ' href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.$USER->sesskey.'">'.
                       '<img class="movetarget" src="'.$CFG->pixpath.'/movehere.gif" '.
                       ' alt="'.$strmovehere.'" /></a><br />';
              }
  
              if ($mod->indent) {
                  print_spacer(12, 20 * $mod->indent, false);
              }
  
              $extra = '';
              if (!empty($modinfo->cms[$modnumber]->extra)) {
                  $extra = $modinfo->cms[$modnumber]->extra;
              }
  
              if ($mod->modname == "label") {
                  echo "<span class=\"";
                  if (!$mod->visible) {
                      echo 'dimmed_text';
                  } else {
                      echo 'label';
                  }
                  echo '">';
                  echo format_text($extra, FORMAT_HTML, $labelformatoptions);
                  echo "</span>";
                  if (!empty($CFG->enablegroupings) && !empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                      if (!isset($groupings)) {
                          $groupings = groups_get_all_groupings($course->id);
                      }
                      echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                  }
                  echo "<br />\n";
  
              } else { // Normal activity
                  $instancename = format_string($modinfo->cms[$modnumber]->name, true,  $course->id);
  
                  if (!empty($modinfo->cms[$modnumber]->icon)) {
                      $icon = "$CFG->pixpath/".$modinfo->cms[$modnumber]->icon;
                  } else {
                      $icon = "$CFG->modpixpath/$mod->modname/icon.gif";
                  }
  
                  //Accessibility: for files get description via icon.
                  $altname = '';
                  if ('resource'==$mod->modname) {
                      if (!empty($modinfo->cms[$modnumber]->icon)) {
                          $possaltname = $modinfo->cms[$modnumber]->icon;
  
                          $mimetype = mimeinfo_from_icon('type', $possaltname);
                          $altname = get_mimetype_description($mimetype);
                      } else {
                          $altname = $mod->modfullname;
                      }
                  } else {
                      $altname = $mod->modfullname;
                  }
                  // Avoid unnecessary duplication.
                  if (false!==stripos($instancename, $altname)) {
                      $altname = '';
                  }
                  // File type after name, for alphabetic lists (screen reader).
                  if ($altname) {
                      $altname = get_accesshide(' '.$altname);
                  }
  
                  $linkcss = $mod->visible ? "" : " class=\"dimmed\" ";
                  echo '<a '.$linkcss.' '.$extra.        // Title unnecessary!
                       ' href="'.$CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id.'">'.
                       '<img src="'.$icon.'" class="activityicon" alt="" /> <span>'.
                       $instancename.$altname.'</span></a><br />';
  
                  if (!empty($CFG->enablegroupings) && !empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                      if (!isset($groupings)) {
                          $groupings = groups_get_all_groupings($course->id);
                      }
                      echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                  }
              }
              if ($usetracking && $mod->modname == 'forum') {
                  if ($unread = forum_tp_count_forum_unread_posts($mod, $course)) {
                      echo '<span class="unread"> <a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$mod->id.'">';
                      if ($unread == 1) {
                          echo $strunreadpostsone;
                      } else {
                          print_string('unreadpostsnumber', 'forum', $unread);
                      }
                      echo '</a></span>';
                  }
              }
  
              if ($isediting) {
                  // TODO: we must define this as mod property!
                  if ($groupbuttons and $mod->modname != 'label' and $mod->modname != 'resource' and $mod->modname != 'glossary') {
                      if (! $mod->groupmodelink = $groupbuttonslink) {
                          $mod->groupmode = $course->groupmode;
                      }
  
                  } else {
                      $mod->groupmode = false;
                  }
                  echo '&nbsp;&nbsp;';
                  echo make_editing_buttons($mod, $absolute, true, $mod->indent, $section->section);
                  echo "<br />\n";
              }
            }      // end if block
            echo "</span>\n";
        }       // end foreach loop
        echo "</td>\n";
        
        // here we go again!!!
        echo "<td>\n";
        foreach ($sectionmods as $modnumber) {

            if (empty($mods[$modnumber])) {
                continue;
            }

            $mod = $mods[$modnumber];

            if ($ismoving and $mod->id == $USER->activitycopy) {
                // do not display moving mod
                continue;
            }

            if (isset($modinfo->cms[$modnumber])) {
                if (!$modinfo->cms[$modnumber]->uservisible) {
                    // visibility shortcut
                    continue;
                }
            } else {
                if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php")) {
                    // module not installed
                    continue;
                }
                if (!coursemodule_visible_for_user($mod)) {
                    // full visibility check
                    continue;
                }
            }

            // OK, here's the magic bit, second time around only get items with modname!=resource
            if (($mod->modname != 'resource') && ($mod->modname != 'label')) {
              
              echo '<span class="activity '.$mod->modname.'" id="module-'.$modnumber.'">';  // Unique ID
              if (($ismoving) && (($modMoveType != 'resource') && ($modMoveType != 'label'))) {
                  echo '<a title="'.$strmovefull.'"'.
                       ' href="'.$CFG->wwwroot.'/course/mod.php?moveto='.$mod->id.'&amp;sesskey='.$USER->sesskey.'">'.
                       '<img class="movetarget" src="'.$CFG->pixpath.'/movehere.gif" '.
                       ' alt="'.$strmovehere.'" /></a><br />';
              }
  
              if ($mod->indent) {
                  print_spacer(12, 20 * $mod->indent, false);
              }
  
              $extra = '';
              if (!empty($modinfo->cms[$modnumber]->extra)) {
                  $extra = $modinfo->cms[$modnumber]->extra;
              }
  

                $instancename = format_string($modinfo->cms[$modnumber]->name, true,  $course->id);

                if (!empty($modinfo->cms[$modnumber]->icon)) {
                    $icon = "$CFG->pixpath/".$modinfo->cms[$modnumber]->icon;
                } else {
                    $icon = "$CFG->modpixpath/$mod->modname/icon.gif";
                }

                //Accessibility: for files get description via icon.
                $altname = '';
                if ('resource'==$mod->modname) {
                    if (!empty($modinfo->cms[$modnumber]->icon)) {
                        $possaltname = $modinfo->cms[$modnumber]->icon;

                        $mimetype = mimeinfo_from_icon('type', $possaltname);
                        $altname = get_mimetype_description($mimetype);
                    } else {
                        $altname = $mod->modfullname;
                    }
                } else {
                    $altname = $mod->modfullname;
                }
                // Avoid unnecessary duplication.
                if (false!==stripos($instancename, $altname)) {
                    $altname = '';
                }
                // File type after name, for alphabetic lists (screen reader).
                if ($altname) {
                    $altname = get_accesshide(' '.$altname);
                }

                $linkcss = $mod->visible ? "" : " class=\"dimmed\" ";
                echo '<a '.$linkcss.' '.$extra.        // Title unnecessary!
                     ' href="'.$CFG->wwwroot.'/mod/'.$mod->modname.'/view.php?id='.$mod->id.'">'.
                     '<img src="'.$icon.'" class="activityicon" alt="" /> <span>'.
                     $instancename.$altname.'</span></a><br />';

                if (!empty($CFG->enablegroupings) && !empty($mod->groupingid) && has_capability('moodle/course:managegroups', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    if (!isset($groupings)) {
                        $groupings = groups_get_all_groupings($course->id);
                    }
                    echo " <span class=\"groupinglabel\">(".format_string($groupings[$mod->groupingid]->name).')</span>';
                }
              
              if ($usetracking && $mod->modname == 'forum') {
                  if ($unread = forum_tp_count_forum_unread_posts($mod, $course)) {
                      echo '<span class="unread"> <a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$mod->id.'">';
                      if ($unread == 1) {
                          echo $strunreadpostsone;
                      } else {
                          print_string('unreadpostsnumber', 'forum', $unread);
                      }
                      echo '</a></span>';
                  }
              }
  
              if ($isediting) {
                  // TODO: we must define this as mod property!
                  if ($groupbuttons and $mod->modname != 'label' and $mod->modname != 'resource' and $mod->modname != 'glossary') {
                      if (! $mod->groupmodelink = $groupbuttonslink) {
                          $mod->groupmode = $course->groupmode;
                      }
  
                  } else {
                      $mod->groupmode = false;
                  }
                  echo '&nbsp;&nbsp;';
                  echo make_editing_buttons($mod, $absolute, true, $mod->indent, $section->section);
                  echo "<br />\n";
              }
            }      // end if block
            echo "</span>\n";
        }       // end foreach loop        
        echo "</td>\n";
        
        echo "</tr></table>\n";
        
    }

}


?>
