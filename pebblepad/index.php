<?php  // $Id: index.php, for moodle block v1.9.5 1/10/2009 09:29:06 Buck,S v1.3

    /*
    *   @pageData = the output for the user selection images and header
    *   @selectData = the current string that will display the dynamically built select boxes
    */

    require_once(dirname(__FILE__) . '/../config.php');
    require_once($CFG->dirroot     . '/course/lib.php');
    require_once($CFG->dirroot     . '/pebblepad/include.php');
    require_once($CFG->dirroot     . '/mod/forum/lib.php');
    require_once($CFG->dirroot     . '/mod/resource/lib.php');
    require_once($CFG->dirroot     . '/mod/quiz/lib.php');
    require_once($CFG->dirroot     . '/mod/scorm/locallib.php');
    require_once($CFG->dirroot     . '/mod/assignment/lib.php');
    
    //for the submission assignments filter
    include_once($CFG->dirroot     . '/mod/assignment/type/online/assignment.class.php');
    include_once($CFG->dirroot     . '/mod/assignment/type/offline/assignment.class.php');

    //custom includes
    require_once($CFG->dirroot     . '/pebblepad/include/scorm.php');
    require_once($CFG->dirroot     . '/pebblepad/include/course.php');
    require_once($CFG->dirroot     . '/pebblepad/include/functions.php');
    require_once($CFG->dirroot     . '/pebblepad/pp_mime_type.php');

    require_login();
    if (empty($SITE)) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }

    setBranding();
    $strmymoodle = "Moodle to ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." Export";

    if (isguest()) {
        print_header($strmymoodle);
        notice_yesno(get_string('noguest', 'pebblepad') . '<br /><br />' .
                get_string('liketologin'), get_login_url(), $CFG->wwwroot);
        print_footer();
        die;
    }

    $navigation = build_navigation($strmymoodle);

    $exportType = optional_param('xtype', '0', PARAM_ALPHA);
    
    $pageHeading = '0';
    switch ($exportType){
        case "forum":
            $pageHeading = "FORUMS";
            break;
        case "resource":
            $pageHeading = "RESOURCES";
            break;
        case "scorm":
            $pageHeading = "SCORM RESULTS";
            break;
        case "assignment":
            $pageHeading = "ASSIGNMENTS";
            break;
        default:
            $pageHeading = '0';
    }

    $blogUrl =      $CFG->wwwroot."/pebblepad/preview_export.php?blogid=".$USER->id;
    $forumUrl=      $CFG->wwwroot."/pebblepad/index.php?xtype=forum";
    $resourcesUrl = $CFG->wwwroot."/pebblepad/index.php?xtype=resource";
    $scormUrl =     $CFG->wwwroot."/pebblepad/index.php?xtype=scorm";
    $assUrl =       $CFG->wwwroot."/pebblepad/index.php?xtype=assignment";

          /*  -------------------------- Hard coded for now -------------------- */

    $pageData = '';

    $pageData.= '<table id="" style="margin:0 auto; padding:7px;" >';
    $pageData.= '<tr >';
    $pageData.= '<td style="text-align:center; padding:10px;">';
        $pageData.="<a href='".$assUrl."'>";
        $pageData.="<img src='images/assignment.png' alt='assignment export' title='assignment export' /></a><br />Assignment";
    $pageData.= '</td>';
    $pageData.= '<td style="text-align:center; padding:8px;">';
        $pageData.="<a target='_blank' href='preview_export.php?blogid=".$USER->id."' onclick=\"return openWindow( '$blogUrl' );\" >";
        $pageData.="<img src='images/blog.png' alt='blog export' title='blog export' /></a><br />Blog";
    $pageData.= '</td>';
    $pageData.= '<td style="text-align:center; padding:8px;">';
        $pageData.="<a href='".$forumUrl."'>";
        $pageData.="<img src='images/forum.png' alt='forum export' title='forum export' /></a><br />Forum";
    $pageData.= '</td>';
    $pageData.= '<td style="text-align:center; padding:8px;">';
        $pageData.="<a href='".$resourcesUrl."'>";
        $pageData.="<img src='images/resource.png' alt='resource export' title='resource export' /></a><br />Resource";
    $pageData.= '</td>';
    $pageData.= '<td style="text-align:center; padding:8px;">';
        $pageData.="<a href='".$scormUrl."'>";
        $pageData.="<img src='images/scorm_quiz.png' alt='scorm export' title='scorm export' /></a><br />SCORM";
    $pageData.= '</td>';
    $pageData.= '</tr>';

    if ( $pageHeading !== '0' ){
        $pageData.= '<tr>';
            $pageData.= '<td colspan="5">&nbsp;</td>';
        $pageData.= '</tr>';
        $pageData.= '<tr>';
            $pageData.= '<td colspan="5">Showing</td>';
        $pageData.= '</tr>';
        $pageData.= '<tr>';
            $pageData.= '<td colspan="5" style="font-size:120%; font-weight:bold;">'.$pageHeading.'</td>';
        $pageData.= '</tr>';
    }

    $pageData.= '</table>';
    
    $selectData = "";
    
    if ($exportType != "0"){

        switch ($exportType) {
        case "assignment":
            $selectData.= getAssignmentExportList();
            break;
        case "forum":
            $selectData.= getForumExportList();
            break;
        case "resource":
            $selectData.= getResourceExportList();
            break;
        case "scorm":
            $selectData.= getScormExportList();
            break;
        }
    }

// page structure
    
    $button = update_mypebble_icon($USER->id);

    $header = $SITE->shortname . ': ' . $strmymoodle;
    
    $loggedinas = user_login_string();

    if (empty($CFG->langmenu)) {
        $langmenu = '';
    } else {
        $currlang = current_language();
        $langs = get_list_of_languages();
        $langlabel = get_accesshide(get_string('language'));
        $langmenu = popup_form($CFG->wwwroot . '/pebblepad/index.php?lang=', $langs,
                'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
    }
    
    $headerBrand = "Export assets to ".get_string($CFG->pebbleExportBrand, 'block_pebblepad');
    $pageTitle = get_string($CFG->pebbleExportBrand, 'block_pebblepad')." Export";


    print_header($pageTitle, $headerBrand, $navigation, '', '', false, $button, $loggedinas  . $langmenu);

    print("<SCRIPT type='text/javascript' language='javascript' src='".$CFG->wwwroot."/pebblepad/pebblepad.js'></SCRIPT>");
    print("<script type='text/javascript' lang='javascript' src='".$CFG->wwwroot."/pebblepad/jquery-1.3.2.min.js'></script>");
    
    //start of page render

    //fudged style [to do] replace if time with admin config theme
    echo"<style type='text/css' >
        #courselist ul {
            list-style-type:none;
        }
        .odd{
           background-color:#fff; display:block; padding:2px;  margin-bottom:3px;'
        }
        .even{
            background-color:#FDFFF5; display:block; padding:2px;  margin-bottom:3px;'
        }
    </style>";

    //jQuery behaviour
    echo"<script language='javascript'>
       $(document).ready(function() {
            $('.pimg').click(function(){
                swap_image($(this));
                $(this).next('.item_div').toggle('slow');
            });

           function swap_image(this_item) {
                if ($(this_item).attr('src') == 'images/plus.gif') {
                    $(this_item).attr('src', 'images/minus.gif');
                } else {
                   $(this_item).attr('src', 'images/plus.gif');
                }
            }
        });
    </script>";


    //moodle output
    echo '<table id="layout-table" style="text-align:center;" >';
    echo '<tr valign="top"> ';



    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':
            break;
            case 'middle':

    // start resourse render
    echo '<td valign="top" id="middle-column">';
    print_container_start(TRUE);

    // The main content
    print($pageData);
    print($selectData);
        
    // end main container
    print_container_end();
    echo '</td>';
    
            break;
            case 'right':
            break;
        }
    }

    /// Finish the page
    echo '</tr></table>';

    print_footer();


// show resourses
function getResourceExportList(){
        global $USER, $CFG;

        $courses  = get_my_courses($USER->id, 'visible DESC,sortorder ASC', array('summary'));

        $data = "";

        if (!empty($courses)){
             $data.="<div id='courselist' style='width:99%; text-align:left; padding:10px;' >";
             
             foreach ($courses as $course){
             require_login($course);
             $data.= "<ul style='display:block;'>";
                    $data.= "<li style='background-color:#eFeFeF; margin-bottom:3px;' ><img class='pimg' style='cursor:pointer;' id='".$course->id."' title='expand' alt='+' src='images/plus.gif' > ".$course->fullname;
                            $resources = get_all_instances_in_course("resource", $course);
                            if (!empty($resources)){
                                $data.= "<ul id='".$course->id."' class='item_div' style='display: none;' >"; //show hide div

                                    $cnt = 0;
                                    foreach($resources as $resource){
										$continue = true;
                                        if (!empty($resource->alltext) || !empty($resource->reference)) {
                                            if (!empty($resource->reference)) {
                                                if (!pebble_mime_content_type($resource->reference)) {
                                                        $continue = false;
                                                }
                                            }
                                            if ($continue == true) {
                                                $previewUrl = $CFG->wwwroot."/pebblepad/preview_export.php?resourceid=".$resource->id."&courseid=".$course->id;

                                                if ( ($cnt % 2) == 0 ){
                                                        $data.= "<li class='even'>";
                                                }else{
                                                        $data.= "<li class='odd'>";
                                                }

                                                $data.= $resource->name;
                                                $data.= "<a href='".$previewUrl."' onclick=\"return openWindow( '$previewUrl' );\"'>";
                                                $data.= "<img class='aimg' src='images/preview.gif'  target='_blank' title='preview to export' alt='view' >";
                                                $data.= "</a>";
                                                $data.= "</li>";
                                                $cnt += 1;
                                            }
                                        }
                                    }
                                    
                                $data.= "</ul>";
                            }else{
                                $data.= "<ul class='item_div' style='display: none;' ><li class='odd'>No Resources Found</li></ul>";
                            }
                    $data.= "</li>";
             $data.= "</ul>";
             }
            $data.="</div>";
        }else{
           $data.= "No Courses Found";
        }
        return $data;
}


// show scorm's
function getScormExportList(){
        global $USER, $CFG;
        $courses  = get_my_courses($USER->id, 'visible DESC,sortorder ASC', array('summary'));
        $data = "";

        if (!empty($courses)){
           // pre check for an attempt
           $newCourses = array();
           $recFnd = false;
           foreach($courses as $course){
                $scorms = get_all_instances_in_course("scorm", $course);
                foreach($scorms as $scorm){
                    if(!$scoes = get_records_select('scorm_scoes',"scorm='$scorm->id' ORDER BY id")){
                        error('Missing script parameter');
                    }
                    foreach($scoes as $sco){
                        if ($sco->launch!='') {
                            $scoId = $sco->id;
                        }
                    }
                    $trackData = getTrackData($scoId, 1); //todo check for attempts as current demos have only 1 and overwrite on update
                    $scorm->status = getScormStatus($trackData);
                    if ($scorm->status != "notattempted"){
                       if (!$recFnd){
                          $newCourses[] = $course;
                          $recFnd = true;
                       }
                    }
                }
                $courses = $newCourses; //the new courses with submissions
            }
            if (empty($courses)){
               $data.= "No Attempts Found";
            }


           $data.="<div id='courselist' style='width:99%; text-align:left; padding:10px;' >";

                foreach ($courses as $course){
                    require_login($course);
                    $data.= "<ul style='display:block;'>";
                        $data.= "<li style='background-color:#eFeFeF; margin-bottom:3px;' ><img class='pimg' style='cursor:pointer;' id='im".$course->id."' title='expand' alt='+' src='images/plus.gif' > ".$course->fullname;
                           
                            $scorms = get_all_instances_in_course("scorm", $course);
                            if (!empty($scorms)){
                                $data.= "<ul id='".$course->id."' class='item_div' style='display: none;' >";
                                
                                $cnt = 0;
                                foreach($scorms as $scorm){

                                    if(!$scoes = get_records_select('scorm_scoes',"scorm='$scorm->id' ORDER BY id")){
                                        error('Missing script parameter');
                                    }
                                    foreach($scoes as $sco){
                                        if ($sco->launch!='') {
                                            $scoId = $sco->id;
                                        }
                                    }
                                    $trackData = getTrackData($scoId, 1); //todo check for attempts as current demos have only 1 and overwrite on update
                                    $scorm->status = getScormStatus($trackData);
                                    if ($scorm->status != "notattempted"){
                                        $previewUrl = $CFG->wwwroot."/pebblepad/preview_export.php?courseid=".$course->id."&scormid=".$scorm->id;

                                        if ( ($cnt % 2) == 0 ){
                                            $data.= "<li class='even'>";
                                        }else{
                                            $data.= "<li class='odd'>";
                                        }

                                        $data.= $scorm->name;
                                        $data.= "<a href='".$previewUrl."' onclick=\"return openWindow( '$previewUrl' );\"'>";
                                        $data.= "<img class='aimg' src='images/preview.gif' target='_blank' title='preview to export' alt='view' />";
                                        $data.= "</a>";
                                        $data.= "</li>";
                                        $cnt += 1;
                                    }
                                }
                                
                                $data.= "</ul>";
                            }else{
                                $data.= "<ul class='item_div' style='display: none;' ><li class='odd'>No Quiz Records</li></ul>";
                            }
                        $data.= "</li>";
                    $data.= "</ul>";
                }
           $data.="</div>";
        }else{
            $data.= "No Courses Found";
        }
        return $data;
}


function getForumExportList(){
        global $USER, $CFG;
        $courses  = get_my_courses($USER->id, 'visible DESC,sortorder ASC', array('summary'));
        $data = "";
        if (!empty($courses)){
             $data.="<div id='courselist' style='width:99%; text-align:left; padding:10px;' >";
                foreach ($courses as $course){
                    require_login($course);
                    $data.= "<ul style='display:block;' />";
                        $data.= "<li style='background-color:#eFeFeF; margin-bottom:3px;' ><img class='pimg' style='cursor:pointer;' id='course".$course->id."' title='expand' alt='+' src='images/plus.gif' > ".$course->fullname;
                        $forums = get_records('forum', 'course', $course->id);
                        if (!empty($forums)){
                            $data.= "<ul id='".$course->id."' class='item_div' style='display:none;' >";
                            foreach($forums as $forum){
                                if ($course->id == $forum->course){
                                        $data.= "<li style='background-color:#eFeFeF; margin-bottom:3px; margin-left:15px;' ><img class='pimg' style='cursor:pointer;' id='forum".$forum->id."' title='expand' alt='+' src='images/plus.gif' > ".$forum->name;
                                        $discussions = get_records("forum_discussions", "forum", $forum->id, "timemodified ASC");
                                        if (!empty($discussions)){
                                            $data.= "<ul id='f".$forum->id."' class='item_div' style='display:none;' >";
                                            $cnt = 0;
                                            foreach($discussions as $discussion){
                                                $previewUrl = $CFG->wwwroot."/pebblepad/preview_export.php?courseid=".$course->id."&forumid=".$discussion->id;

                                                if ( ($cnt % 2) == 0 ){
                                                    $data.= "<li class='even'>";
                                                }else{
                                                    $data.= "<li class='odd'>";
                                                }

                                                $data.= $discussion->name;
                                                $data.= " <a href='".$previewUrl."' onclick=\"return openWindow( '$previewUrl' );\"'>";
                                                $data.= "  <img src='images/preview.gif'  target='_blank' title='preview to export' alt='view' />";
                                                $data.= " </a>";
                                                $data.= "</li>";
                                                $cnt += 1;

                                            }
                                            $data.= "</ul>";
                                        }else{
                                            $data.= "<ul class='item_div' style='display: none;' ><li class='odd'>No Discussions</li></ul>";
                                        }
                                        $data.= "</li>";
                                }
                            }
                            $data.= "</ul>";
                        }else{
                            $data.= "<ul class='item_div' style='display: none;' ><li class='odd'>No Records</li></ul>";
                        }
                        $data.= "</li>";
                    $data.= "</ul>";
                }
            $data.="</div>";
        }else{
            $data.= "No Courses Found";
        }
        return $data;
}


// ASSIGNMENT listing
function getAssignmentExportList(){
        global $USER, $CFG;

        $courses  = get_my_courses($USER->id, 'visible DESC,sortorder ASC', array('summary'));
        $data = "";

        if (!empty($courses)){
            //has each course got any assignment submissions else we don't want to show them?
            $newCourses = array();
            $recFnd = false;
            foreach($courses as $course){
                require_login($course);
                $cms = get_coursemodules_in_course('assignment', $course->id, 'm.assignmenttype, m.timedue');
                $modinfo = get_fast_modinfo($course);
                if (isset($modinfo->instances['assignment'])){
                    foreach ($modinfo->instances['assignment'] as $cm) {

                            if (!$cm->uservisible) {
                                continue;
                            }
                            $cm->assignmenttype = $cms[$cm->id]->assignmenttype;
                            require_once ($CFG->dirroot.'/mod/assignment/type/'.$cm->assignmenttype.'/assignment.class.php');
                            $assignmentclass = 'assignment_'.$cm->assignmenttype;
                            $assignmentinstance = new $assignmentclass($cm->id, NULL, $cm, $course);

                            $submitted = $assignmentinstance->submittedlink(true);
                            if (!$recFnd){
                                if($submitted){
                                   $recFnd = true;
                                   $newCourses[] = $course;
                                }
                            }
                    }
                }
                $courses = $newCourses; //the new courses with submissions
            }
            if (empty($courses)){
               $data.= "No Assignment Submissions Found";
            }

            $data.="<div id='courselist' style='width:99%; text-align:left; padding:10px;' >";

                foreach($courses as $course){
                $data.= "<ul id='listitem' style='display:block;' >";

                        $data.= "<li style='background-color:#eFeFeF; margin-bottom:3px;' ><img class='pimg' style='cursor:pointer;' id='course".$course->id."' title='expand' alt='+' src='images/plus.gif' > ".$course->fullname;

                        $cms = get_coursemodules_in_course('assignment', $course->id, 'm.assignmenttype, m.timedue');
                        
                        $modinfo = get_fast_modinfo($course);

                        $data.= "<ul id='".$course->id."' class='item_div' style='display:none;' >"; //show hide div
                        $cnt = 0;
                        foreach ($modinfo->instances['assignment'] as $cm) {
                            if (!$cm->uservisible) {
                                continue;
                            }
                            $cm->timedue        = $cms[$cm->id]->timedue;
                            $cm->assignmenttype = $cms[$cm->id]->assignmenttype;

                            if (!file_exists($CFG->dirroot.'/mod/assignment/type/'.$cm->assignmenttype.'/assignment.class.php')) {
                            continue;
                            }

                            require_once ($CFG->dirroot.'/mod/assignment/type/'.$cm->assignmenttype.'/assignment.class.php');
                            $assignmentclass = 'assignment_'.$cm->assignmenttype;
                            $assignmentinstance = new $assignmentclass($cm->id, NULL, $cm, $course);

                            $submitted = $assignmentinstance->submittedlink(true);

                            if($submitted){
                               $previewUrl = $CFG->wwwroot."/pebblepad/preview_export.php?courseid=".$course->id."&assignmentid=".$cm->instance;

                                if ( ($cnt % 2) == 0 ){
                                    $data.= "<li class='even'>";
                                }else{
                                    $data.= "<li class='odd'>";
                                }

                                $data.= format_string($cm->name);
                                $data.= "<a href='".$previewUrl."' onclick=\"return openWindow( '$previewUrl' );\"'>";
                                $data.= "<img src='images/preview.gif'  target='_blank' title='preview to export' alt='view' />";
                                $data.= "</a>";
                                $data.= "</li>";
                                $cnt += 1;
                            }
                        }
                        $data.= "</ul>";
                        $data.= "</li>";
                $data.= "</ul>";
                }
            $data.= "</div>";
        }else{
            $data.= "No Courses Found";
        }
        return $data;
}
?>
