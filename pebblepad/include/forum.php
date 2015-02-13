<?php
function getForumPosts($forumId, $selected){
        global $CFG, $USER;
        if (!$discussion = get_record('forum_discussions', 'id', $forumId)) {
            error("Discussion ID was incorrect or no longer exists");
        }

        if (!$course = get_record('course', 'id', $discussion->course)) {
            error("Course ID is incorrect - discussion is faulty");
        }

        if (!$forum = get_record('forum', 'id', $discussion->forum)) {
            notify("Bad forum ID stored in this discussion");
        }
        $cm = null;
        $sort = null;

        require_course_login($course, true, $cm);

        $posts = forum_get_discussion_posts($discussion->id, $sort, $forumId);

        //push
        if (!empty($posts)){
            foreach($posts as $p){
                $p->course = $course->id;
                $p->forum = $forum->id;
            }
        }

        if (sizeof($selected) > 0){

           $newPosts = array();
           $icount = 0;

           foreach($posts as $p){
               foreach ($selected as $id){
                        if ($p->id == $id ){
                                $newPosts[$icount] = $p;
                                $newPosts[$icount]->selected = true;
                                $icount++;
                        }
               }
           }
           return $newPosts;
        }
        return $posts;
}

function getForumPost($forumId){

        if (!$discussion = get_record('forum_discussions', 'id', $forumId)) {
            error("Discussion ID was incorrect or no longer exists");
        }

        if (!$course = get_record('course', 'id', $discussion->course)) {
            error("Course ID is incorrect - discussion is faulty");
        }

        if (!$forum = get_record('forum', 'id', $discussion->forum)) {
            notify("Bad forum ID stored in this discussion");
        }

        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {
            error('Course Module ID was incorrect');
        }

        $parent = $discussion->firstpost;

        if (! $post = forum_get_post_full($parent)) {
            error("Discussion no longer exists", "$CFG->wwwroot/pebblepad/index.php?cid=$courseId");
        }

        if (!forum_user_can_view_post($post, $course, $cm, $forum, $discussion)) {
            error('You do not have permissions to view this post', "$CFG->wwwroot/pebblepad/index.php?cid=$courseId");
        }

        //push course id into the post
        $post->course = $course->id;
        $post->forum = $forum->id;
        
        return $post;

}

function getForumOutput($post, $posts){

        global $USER;
        $output = "";
        
        $output.="<div class='forumtopic' >";
        if (!empty($post)){
            $output.="<div class='datetime'>created: ".userdate($post->created)."</div>";

            $output.="<div class='forumtopictitle'>TITLE: ".$post->subject;

            //who posted the topic
            if ($USER->id == $post->userid){
                $output.="<div class='forumtopicowner'>BY: ".$post->firstname." ".$post->lastname."</div></div>";
            }else{
                $output.="<div class='forumtopicowner'>BY: ";
                $output.="A.N. Other";
                $output.="</div></div>";
            }
            $output.="<div class='forumtopicbody'>".$post->message."</div>";

            if ($post->attachment) {
                $output.="<div class='attachments'>";
                $output.= forum_print_attachments($post);
                $output.="</div>";
            }

            $output.="</div>";
            
        }else{
            $output.= "No forum discussion found</div>";
        }
        
        if (!empty($posts)){

            foreach($posts as $reply){
                $output.="<div class='forumreply'>";
                $output.="<div class='datetime'>created: ".userdate($reply->created)."</div>";

                if (isset($reply->selected)){
                    if ($reply->selected){
                        $output.="<div class='forumtopictitle'>".$reply->subject;
                    }else{
                        $output.="<div class='forumtopictitle'><input type='checkbox' name='asset[]' value=".$reply->id." />".$reply->subject;
                    }
                }else{
                    $output.="<div class='forumtopictitle'><input type='checkbox' name='asset[]' value=".$reply->id." />".$reply->subject;
                }

                // find out who reply poster is
                if ($reply->userid == $USER->id){
                    $output.="<div class='forumtopicowner'>BY: ".$reply->firstname." ".$reply->lastname."</div></div>";
                }else{
                    $output.="<div class='forumtopicowner'>BY: A.N. Other</div></div>";
                }
                $output.="<div class='forumtopicbody'>".$reply->message."</div>";

                if ($reply->attachment) {
                    $output.="<div class='attachments'>";
                    $output.= forum_print_attachments($reply);
                    $output.="</div>";
                }

                $output.= "</div>";
            }
        }else{
            $output.= "No replies found</div>";
        }

        return $output;
}
?>