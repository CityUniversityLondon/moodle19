<?php

function getAssignment($courseId, $assId){
               
       if (!$assignment = get_record("assignment", "id", $assId)) {
            error("Course module is incorrect");
       }

       if ($assignment->course != $courseId){
          error("Course id or assignment id is incorrect");
       }

       return $assignment;
}



// display the selected export items review, this is what gets exported
function getAssignmentOutput($assignment, $selected){
       global $CFG, $USER;

       if (! $course = get_record("course", "id", $assignment->course)) {
           error("Course is misconfigured");
       }

       if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
           error("Course Module ID was incorrect");
       }

       require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");

       $assignmentclass = "assignment_$assignment->assignmenttype";
       $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

       if (! $submission = $assignmentinstance->get_submission($USER->id)){
             error("You have not submitted work to this assignment");
       }

       require_once($CFG->libdir.'/gradelib.php');

       $courseId = $course->id;
       //get feedback if any
       $grading_info = grade_get_grades($courseId, 'mod', 'assignment', $assignmentinstance->cm->instance, $USER->id);

       //dodgy hack to get grade data
       $tmp = $grading_info->items[0]->grades[3];
       // check we have a grade
       if ($tmp->str_long_grade != '-'){
          $grade = $tmp->str_long_grade;
       }
       // do we have feedback
       if (!empty($tmp->str_feedback)){
          $feedback = $tmp->str_feedback;
       }

       $output = "";
       $output = "<div id='wrapper'>";
          
           foreach ($selected as $chosen){
                if ($chosen == 0){
                   $output.= "<div style='background-color: #EDEDED; color:#333333; font-weight: bold; padding: 5px; text-align:center;'>".$assignmentinstance->pagetitle."</div>";
                   $output.= "<div id='assignment'>".format_text($assignmentinstance->assignment->description)."</div>";

                   $output.= "<div style='background-color: #EDEDED; color:#333333; font-weight: bold; padding: 5px'>";
                   $output.= get_string('duedate','assignment').": ";
                   $output.= userdate($assignmentinstance->assignment->timedue)."<br />";
          
                   // has user edited work since the grade and mark was given
                   if ( ($submission->timemodified < $submission->timemarked) || (empty($submission->timemarked)) ){
                       $output.= "Last edited: ".userdate($submission->timemodified)."<br />";
                   }else{
                       $output.= "Last edited: <span style='background-color:#F66;padding:2px 4px 2px 4px;'>".userdate($submission->timemodified)."</span><br />";
                   }
                   //has teacher marked work
                   if (empty ($submission->timemarked)){
                      $output.= "Not marked";
                   }else{
                       $output.= "Marked: ".userdate($submission->timemarked);
                   }
                   $output.="</div><br />";
               }
           }
           foreach ($selected as $chosen){
                if ($chosen == 1){
                   $output.= "<div style='background-color: #EDEDED; color:#333333; font-weight: bold; padding: 5px; text-align:center;'>Evidence/Work submitted:</div>";
                   $output.= format_text($submission->data1);
                }
           }

           foreach ($selected as $chosen){
                if ($chosen == 2){
                   if (!empty($grade) || !empty ($feedback)){
                      $output.= "<div style='background-color: #EDEDED; color:#333333; font-weight: bold; padding: 5px; text-align:center;'>feedback/grade</div>";
                      $output.= "<div id='grade'>Grade: ".$grade."</div>";
                      $output.= "<div id='feedback'>".$feedback."</div>";
                   }
                }
           }

       $output.= "</div>";

       return $output;
}



// just display the whole preview
function getAssignmentPreview($assignment){
       global $CFG, $USER;

       if (! $course = get_record("course", "id", $assignment->course)) {
           error("Course is misconfigured");
       }

       if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
           error("Course Module ID was incorrect");
       }

       require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");
       
       $assignmentclass = "assignment_$assignment->assignmenttype";
       $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

       if (! $submission = $assignmentinstance->get_submission($USER->id)){
             error("You have not submitted work to this assignment");
       }

       require_once($CFG->libdir.'/gradelib.php');

       $courseId = $course->id;
       //get feedback if any
       $grading_info = grade_get_grades($courseId, 'mod', 'assignment', $assignmentinstance->cm->instance, $USER->id);

       //dodgy hack to get grade data
       $tmp = $grading_info->items[0]->grades[3];
       // check we have a grade
       if ($tmp->str_long_grade != '-'){
          $grade = $tmp->str_long_grade;
       }
       // do we have feedback
       if (!empty($tmp->str_feedback)){
          $feedback = $tmp->str_feedback;
       }

       $output = "";
       $output = "<div id='wrapper'>";

                   $output.= "<div class='title'><input type='checkbox' value=0 name='asset[]' />".$assignmentinstance->pagetitle."</div>";
                   $output.= "<div id='assignment'>".format_text($assignmentinstance->assignment->description)."</div>";

                   $output.= "<div id='dates'>";
                   $output.= get_string('duedate','assignment').": ";
                   $output.= userdate($assignmentinstance->assignment->timedue)."<br />";

                        // has user edited work since the grade and mark was given
                        if ( ($submission->timemodified < $submission->timemarked) || (empty($submission->timemarked)) ){
                            $output.= "Last edited: ".userdate($submission->timemodified)."<br />";
                        }else{
                            $output.= "Last edited: <span style='background-color:#F66;padding:2px 4px 2px 4px;'>".userdate($submission->timemodified)."</span><br />";
                        }
                        //has teacher marked work
                        if (empty ($submission->timemarked)){
                           $output.= "Not marked";
                        }else{
                            $output.= "Marked: ".userdate($submission->timemarked);
                        }
                        
                   $output.="</div><br />";

                   $output.= "<div class='title'><input type='checkbox' value=1 name='asset[]'/>Evidence/Work submitted:</div>";
                   $output.= format_text($submission->data1);

                   if (!empty($grade) || !empty ($feedback)){
                      $output.= "<div class='title'><input type='checkbox' value=2 name='asset[]'/>feedback/grade</div>";
                      $output.= "<div id='grade'>Grade: ".$grade."</div>";
                      $output.= "<div id='feedback'>".$feedback."</div>";
                   }

       $output.= "</div>";

       return $output;
}
?>