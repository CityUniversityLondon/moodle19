<?php
// base includes
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot     . '/course/lib.php');
require_once($CFG->dirroot     . '/mod/forum/lib.php');
require_once($CFG->dirroot     . '/mod/resource/lib.php');
require_once($CFG->dirroot     . '/mod/assignment/lib.php');
require_once($CFG->dirroot     . '/mod/scorm/locallib.php');
require_once($CFG->dirroot     . '/lib/questionlib.php');
require_once($CFG->dirroot     . '/blog/lib.php');
require_once($CFG->dirroot     . '/lib/accesslib.php');

// custom includes
require_once($CFG->dirroot     . '/pebblepad/lib.php');
require_once($CFG->dirroot     . '/pebblepad/include.php');
require_once($CFG->dirroot     . '/pebblepad/include/functions.php');
require_once($CFG->dirroot     . '/pebblepad/include/resource.php');
require_once($CFG->dirroot     . '/pebblepad/include/forum.php');
require_once($CFG->dirroot     . '/pebblepad/include/assignment.php');
require_once($CFG->dirroot     . '/pebblepad/include/blog.php');
require_once($CFG->dirroot     . '/pebblepad/include/scorm.php');
include_once($CFG->dirroot     . '/blocks/pebblepad/portfolio_manager.php');

global $CFG, $USER;

// set Pebbleroot account
$current_institution = get_default_portfolio();
if (!empty($current_institution)){
    $CFG->pebbleroot = $current_institution->url;
    $CFG->pebblerootid = $current_institution->id;
    $CFG->sharedsecret = $current_institution->sharedsecret;
}

$output = "";
$export = false;
$exported = false;
$portfolio = null;

// see if the user has chosen to export the preview
if (isset($_POST['export'])){
	// check to see if the export should go ahead - compare the realuser id with the current user id
	if (isset($USER->realuser)) {
		if ($USER->realuser == $USER->id) {
			$export = true;
		}
	} else {
		$export = true;
	}
}

// try to set portfolio
if (isset($_POST['portfolio'])) {
        $zip = 0;
        $portfolio = get_portfolio($_POST['portfolio']);
        if(!empty($portfolio)){
                $CFG->pebbleroot = $portfolio->url;
                $CFG->pebblerootid = $portfolio->id;
                $CFG->sharedsecret = $portfolio->sharedsecret;
        }elseif ($_POST['portfolio'] == -1){
                    if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
                        include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
                        $acc = get_acc_data($acc);
                        $zip = 1;
                        $CFG->pebblerootid = $acc->id;
                    }
        }elseif(!empty($current_institution)){
                        $CFG->pebbleroot = $current_institution->url;
                        $CFG->pebblerootid = $current_institution->id;
                        $CFG->sharedsecret = $current_institution->sharedsecret;
        }
}

//get string parameters passed
$pebbleTags = $_SERVER['QUERY_STRING'];  // not currently used

if (isset($_GET['courseid'])){
   $courseId = optional_param('courseid', 0, PARAM_INT);  // clean it
   
   if (! ($course = get_record('course', 'id', $courseId)) ) {  //we must have a course id that the student is enrolled on
      error('Invalid course id');
   }
   require_login($course);


   // *********************************         RESOURCE        ************************************** //

   if (isset($_GET['resourceid'])){

       $resourceId = optional_param('resourceid', 0, PARAM_INT);

       $output = "";
       
       $resource = getResource($course, $resourceId);
       
       if ($export){
           
           
           $leap = new leap2a();
           $exported = TRUE;
           
           $leap->set_objects($resource, 'file');
           $output.= doExport($leap, $zip);
           $output.= getResourceOutput($resource);
       }else{
           $output.= getResourceOutput($resource);
       }
       

   // *********************************         FORUM        ************************************** //

   }elseif(isset($_GET['forumid'])){
       $forumId    = optional_param('forumid', 0, PARAM_INT);
       $selected = array();
       $output = "";

       if ($export){
           if (!$discussion = get_record('forum_discussions', 'id', $forumId)) {
                   error("Discussion ID was incorrect or no longer exists");
           }

           $exported = TRUE;

           if (!empty($_POST['asset'])){
               $selected = $_POST['asset'];
               $posts = getForumPosts($forumId, $selected);
           }else{
               $posts = array();
           }

           $post = getForumPost($forumId); //first post
              
           //build export
           $exported = TRUE;
           $leap = new leap2a();
           $leap->set_objects($discussion, 'discussion', 3);
           $leap->set_objects($post, 'post', 3);
           if (!empty ($posts)){
                $leap->set_objects($posts, 'post', 3);
           }

            $output.= doExport($leap, $zip);
            if (!empty($_POST['asset'])){
                $output.= getForumOutput($post, $posts);
            }else{
                $output.= getForumOutput($post, array());
            }
            
       }else{
           $post = getForumPost($forumId);
           $posts = getForumPosts($forumId, array());
           $output.= getForumOutput($post, $posts);
       }
       
   // *********************************         ASSIGNMENT        ************************************** //

   }elseif(isset($_GET['assignmentid'])){
       $courseId = optional_param('courseid', 0, PARAM_INT);
       $assId = optional_param('assignmentid', 0, PARAM_INT);
       $selected = array(); // values in array 0 = assignment, 1 = submission, 2 = feedback
       $output = "";

       if ($export){
           if (!empty($_POST['asset'])){
                $selected = $_POST['asset'];
           }
           $assignment = getAssignment($courseId, $assId);
           
           if (empty($selected)){
               $output.='<div id="fail" ">No elements selected. Please select the check boxes for the elements required to export.</div>';
               $output.= getAssignmentPreview($assignment);
           }else{

               $exported = TRUE;
               $output.= getAssignmentOutput( $assignment, $selected );
               
               //do the export
               $arry = array('id'=>"assignment/".$assignment->id, 'course'=>$assignment->course, 'name'=>$assignment->name, 'timemodified'=>$assignment->timemodified, 'reference'=>'', 'alltext'=>$output, 'type'=>'html');
               
               $leapass = new leap2a_moodle_object($arry);
               
               $leap = new leap2a();
               $leap->set_objects($leapass, 'file');

               $output = "";
               $output.= doExport($leap, $zip);
               $output.= getAssignmentOutput( $assignment, $selected ); 
           }           

       }else{
           $assignment = getAssignment($courseId, $assId);

           $output.= getAssignmentPreview($assignment);          
       }
       
  
    // *********************************         SCORM        ************************************** //

   }elseif(isset($_GET['scormid'])){
        $output = "";
        $courseId = optional_param('courseid', 0, PARAM_INT);
        $scormId = optional_param('scormid', 0, PARAM_INT);
        
        if ($export){

           $resource = getScormObject($courseId, $scormId);
           
           $resource->alltext = getScormOutput($courseId, $scormId);
           
           //build export
           $exported = TRUE;
           $arry = array('id'=>"scorm/".$resource->id, 'course'=>$resource->course, 'name'=>$resource->name, 'reference'=>'', 'alltext'=>$resource->alltext, 'timemodified'=>$resource->timemodified, 'type'=>'html');
           $ScormRes = new leap2a_moodle_object($arry);

           $leap = new leap2a(); 
           $leap->set_objects($ScormRes, 'file');

           $output = "";
           $output.= doExport($leap, $zip);
           $output.= getScormOutput($courseId, $scormId);

        }else{
            $output.= getScormOutput($courseId, $scormId);
        }
      
   }else{
        error('Insufficient data to work with or a data field was corrupt');
   }

   //we have output so print it
   if ($output != ""){

      print_pebble_header($pebbleTags);
        
      print_element_start();
      echo $output;
      print_element_end();
      // show the export button
      print_export($exported, $pebbleTags, '', null);
      // show legal.
      print_pebble_footer();

   }else{
       error('Output error!');
   }



// *********************************         BLOG        ************************************** //

}elseif(isset($_GET['blogid'])){
    $userId = optional_param('blogid', 0, PARAM_INT);

    $assetType = 'blog';
    $output = "";
    
    if ($export){

           $assetType = "";

           if (!empty($_POST['asset'])){
                $selected = $_POST['asset'];
                //$zip = $_POST['xType'];
           
               if (isset($_POST['assetType'])){
                    $assetType= $_POST['assetType'];
               }

               $blog = getBlog($userId, $selected);

               $exported = TRUE;
               $leap = new leap2a();

               if ($assetType == 'blog'){
                   $params = array(
                        'id'            => 0,
                        'course'        => 0
                    );
                    $blogSelect = new leap2a_moodle_object($params);
                    $leap->set_objects($blogSelect, 'blog');
               }

               $leap->set_objects($blog, 'blog_post');
               
           }else{
               $assetType= $_POST['assetType'];
           }


           if (empty($selected)){
               $output = "<div id='fail'>No Items Selected to Export</div>";
               $blog = getBlog($userId, array());
               $output.= getBlogOutput($blog);
           }else{
               $output.= doExport($leap, $zip);
               $output.= getBlogOutput($blog);
           }
    }else{        
        if ($blog = getBlog($userId, array())){
            $output .= getBlogOutput($blog);
        }else{
            $output .= '<p id="fail">You have not made any posts to your blog.</p>';
            $exported = TRUE;
        }
    }
   
    print_pebble_header($pebbleTags);
    print_element_start();
    echo $output;
    print_element_end();
    print_export($exported, $pebbleTags, $assetType, null);
    // show legal.
    print_pebble_footer();
    
}else{
    error('Invalid id');  
}

?>
