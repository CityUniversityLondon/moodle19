<?php
function getBlog($userId, $selected){

        global $CFG, $USER;
               
        if (empty($CFG->bloglevel)) {
            error('Blogging is disabled!');
        }

        $postid = null;
        $tagid = null;
        $tag = null;
        $start = null;
               

        $filterselect = $USER->id; 
        $filtertype = 'user';
        $bloglimit = optional_param('limit', get_user_preferences('blogpagesize', 30), PARAM_INT);
        if ($USER->id != $userId){
           error('Invalid id found!');
        }
        if (!$user = get_record( 'user', 'id', $filterselect) ) {
                error('Incorrect user id');
        }

        if ($user->deleted) {
           print_header();
           print_heading(get_string('userdeleted'));
           print_footer();
           die;
        }

        $blogEntries = blog_fetch_entries($postid, $bloglimit, $start, $filtertype, $filterselect, $tagid, $tag, $sort='created DESC', true);
        
        if (empty($blogEntries)){
            return 0;
        }

        if (sizeof($selected) > 0){

            $newblog;
            $icount=0;

            foreach ($blogEntries as $blog){
                 foreach ($selected as $id){
                        if ($blog->id == $id ){
                            $newblog[$icount] = $blog;
                            $newblog[$icount]->selected = true;
                            $icount++;
                        }
                 }

            }

            return $newblog;
        }        

        return $blogEntries;
        
}

function getBlogOutput($blogEntries){
        $output = "";
        foreach ($blogEntries as $blog){
                    
                    $output.="<div class='blogpost'>";
                    $output.="<div class='datetime'>created: ".userdate($blog->created)."</div>";

                    if (isset($blog->selected)){
                        if ($blog->selected){
                            $output.="<div class='blogtitle'>".$blog->subject;
                        }else{
                            $output.="<div class='blogtitle'><input type='checkbox' name='asset[]' value=".$blog->id." /> ".$blog->subject;
                        }
                    }else{
                        $output.="<div class='blogtitle'><input type='checkbox' name='asset[]' value=".$blog->id." /> ".$blog->subject;
                    }

                    $output.="</div>";
                    $output.="<div class='blogcontent'>".format_text($blog->summary, $blog->format)."</div>";


                    if ($blog->attachment) {
                        $output.="<div class='attachments'>";
                        $output.= blog_print_attachments($blog);
                        $output.="</div>";
                    }

                    $output.="</div>";

        }

        return $output;

}
?>