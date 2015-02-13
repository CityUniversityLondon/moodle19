<?php 
/******************************************************************************\
 *
 * Filename:    restorelib.php
 *
 *		This file contains all library function for iPodcast module
 *
 *
 * History:     03/24/06 Tom Dolsky     - First implementation and testing
 *              10/19/06 Tom Dolsky     - Added Darwin fields
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

    //This is the "graphical" structure of the ipodcast mod:
    //
    //                       ipodcast
    //                    (CL,pk->id)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function ipodcast_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            // if necessary, write to restorelog and adjust date/time fields
            if ($restore->course_startdateoffset) {
                restore_log_date_changes('ipodcast', $restore, $info['MOD']['#'], array('ASSESSTIMESTART', 'ASSESSTIMEFINISH'));
            }
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug
            //Now, build the ipodcast_courses record structure
            $ipodcast->course = $restore->course_id;
            $ipodcast->userid = backup_todb($info['MOD']['#']['USERID']['0']['#']);
            $ipodcast->studentcanpost = backup_todb($info['MOD']['#']['STUDENTCANPOST']['0']['#']);
            $ipodcast->defaultapproval = backup_todb($info['MOD']['#']['DEFAULTAPPROVAL']['0']['#']);
            $ipodcast->attachwithcomment= backup_todb($info['MOD']['#']['ATTACHWITHCOMMENT']['0']['#']);
            $ipodcast->enabletsseries = backup_todb($info['MOD']['#']['ENABLETSSERIES']['0']['#']);
            $ipodcast->enabledarwin = backup_todb($info['MOD']['#']['ENABLEDARWIN']['0']['#']);
            $ipodcast->authkey = backup_todb($info['MOD']['#']['AUTHKEY']['0']['#']);
            $ipodcast->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $ipodcast->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
            $ipodcast->comments = backup_todb($info['MOD']['#']['COMMENTS']['0']['#']); // AD
            $ipodcast->image = backup_todb($info['MOD']['#']['IMAGE']['0']['#']);
            $ipodcast->imageheight= backup_todb($info['MOD']['#']['IMAGEHEIGHT']['0']['#']);
            $ipodcast->imagewidth= backup_todb($info['MOD']['#']['IMAGEWIDTH']['0']['#']);
            $ipodcast->darwinurl = backup_todb($info['MOD']['#']['DARWINURL']['0']['#']);
            $ipodcast->rssarticles= backup_todb($info['MOD']['#']['RSSARTICLES']['0']['#']);
            $ipodcast->rsssorting= backup_todb($info['MOD']['#']['RSSSORTING']['0']['#']);
            $ipodcast->enablerssfeed= backup_todb($info['MOD']['#']['ENABLERSSFEED']['0']['#']);
            $ipodcast->enablerssitunes= backup_todb($info['MOD']['#']['ENABLERSSITUNES']['0']['#']);
            $ipodcast->visible = backup_todb($info['MOD']['#']['VISIBLE']['0']['#']);
            $ipodcast->explicit= backup_todb($info['MOD']['#']['EXPLICIT']['0']['#']);
            $ipodcast->subtitle= backup_todb($info['MOD']['#']['SUBTITLE']['0']['#']);
            $ipodcast->keywords= backup_todb($info['MOD']['#']['KEYWORDS']['0']['#']);
            $ipodcast->topcategory = backup_todb($info['MOD']['#']['TOPCATEGORY']['0']['#']);
            $ipodcast->nestedcategory= backup_todb($info['MOD']['#']['NESTEDCATEGORY']['0']['#']);
            $ipodcast->timecreated = backup_todb($info['MOD']['#']['TIMECREATED']['0']['#']);
            $ipodcast->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $ipodcast->sortorder= backup_todb($info['MOD']['#']['SORTORDER']['0']['#']);
 
            //The structure is equal to the db, so insert the ipodcast_courses
            $newid = insert_record ("ipodcast_courses",$ipodcast);

            //Do some output
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","ipodcast")." \"".format_string(stripslashes($ipodcast->name),true)."\"</li>";
            }     
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids

                backup_putid($restore->backup_unique_code,"ipodcast_courses",
                             $mod->id, $newid);
                //Restore ipodcast_entries
                $status = ipodcast_entries_restore_mods($mod->id,$newid,$info,$restore); 
   
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        return $status;
    }

    //This function restores the ipodcasts
    function ipodcast_entries_restore_mods($old_ipodcast_id,$new_ipodcast_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the entries array
        $entries = $info['MOD']['#']['IPODCASTS']['0']['#']['IPODCAST'];

        //Iterate over entries
        for($i = 0; $i < sizeof($entries); $i++) {
            $ent_info = $entries[$i];
            //traverse_xmlize($ent_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($ent_info['#']['ID']['0']['#']);
            $olduserid = backup_todb($ent_info['#']['USERID']['0']['#']);

            //Now, build the IPODCASTS record structure
            $entry->ipodcastcourseid = $new_ipodcast_id;
            $entry->course = $restore->course_id;
            $entry->userid = backup_todb($ent_info['#']['USERID']['0']['#']);
            $entry->name = backup_todb($ent_info['#']['NAME']['0']['#']);
            $entry->summary = backup_todb($ent_info['#']['SUMMARY']['0']['#']);
            $entry->notes = backup_todb($ent_info['#']['NOTES']['0']['#']);
            $entry->attachment = backup_todb($ent_info['#']['ATTACHEDFILE']['0']['#']);
//            $entry->attachmenttype = backup_todb($ent_info['#']['ATTACHMENTTYPE']['0']['#']);
            $entry->duration = backup_todb($ent_info['#']['DURATION']['0']['#']);
            $entry->explicit = backup_todb($ent_info['#']['EXPLICIT']['0']['#']);
            $entry->subtitle = backup_todb($ent_info['#']['SUBTITLE']['0']['#']);
            $entry->keywords = backup_todb($ent_info['#']['KEYWORDS']['0']['#']);
            $entry->topcategory = backup_todb($ent_info['#']['TOPCATEGORY']['0']['#']);
            $entry->nestedcategory = backup_todb($ent_info['#']['NESTEDCATEGORY']['0']['#']);
            $entry->timecreated = backup_todb($ent_info['#']['TIMECREATED']['0']['#']);
            $entry->timemodified = backup_todb($ent_info['#']['TIMEMODIFIED']['0']['#']);
            $entry->teacherentry = backup_todb($ent_info['#']['TEACHERENTRY']['0']['#']);
            $entry->timestart= backup_todb($ent_info['#']['TIMESTART']['0']['#']);
            $entry->timefinish = backup_todb($ent_info['#']['TIMEFINISH']['0']['#']);
            $entry->approved = backup_todb($ent_info['#']['APPROVED']['0']['#']);


            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$entry->userid);
            if ($user) {
                $entry->userid = $user->new_id;
            }

$newid = insert_record ("ipodcast",$entry);

//Do some output
if (($i+1) % 50 == 0) {
echo ".";
if (($i+1) % 1000 == 0) {
echo "<br />";
}
backup_flush(300);
}

if ($newid) {
//We have the newid, update backup_ids
backup_putid($restore->backup_unique_code,"ipodcast",$oldid,$newid);
if($restore->mods["ipodcast"]->userinfo) {
//Restore ipodcast_comments
$status = ipodcast_comments_restore_mods($oldid,$newid,$ent_info,$restore);
//Now restore ipodcast_views
$status = ipodcast_views_restore_mods($oldid,$newid,$ent_info,$restore);
}
//Now restore glossary_ratings
//                    $status = ipodcast_tsseries_restore_mods($oldid,$newid,$ent_info,$restore);
//Now copy moddata associated files if needed
//if ($entry->attachment) {
//$status = ipodcast_restore_files ($old_ipodcast_id, $new_ipodcast_id,
//  $oldid, $newid, $restore);
//}
//                } else {
//                    $status = false;
//                }
            }
        }

        return $status;
    }

    //This function restores the ipodcast_comments
    function ipodcast_comments_restore_mods($old_entry_id,$new_entry_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the comments array
        $comments = $info['#']['COMMENTS']['0']['#']['COMMENT'];

        //Iterate over comments
        for($i = 0; $i < sizeof($comments); $i++) {
            $com_info = $comments[$i];
            //traverse_xmlize($com_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($com_info['#']['ID']['0']['#']);

            //Now, build the IPODCAST_COMMENTS record structure
            $comment->entryid = $new_entry_id;
            $comment->userid = backup_todb($com_info['#']['USERID']['0']['#']);
            $comment->comments = backup_todb($com_info['#']['COMMENTS']['0']['#']); // AD
            $comment->attachment = backup_todb($com_info['#']['ATTACHMENT']['0']['#']);
            $comment->visibility = backup_todb($com_info['#']['VISIBILITY']['0']['#']);
            $comment->timemodified = backup_todb($com_info['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$comment->userid);
            if ($user) {
                $comment->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the glossary_comments
            $newid = insert_record ("ipodcast_comments",$comment);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }
            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,"ipodcast_comments",$oldid,$newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    //This function restores the ipodcast_views
    function ipodcast_views_restore_mods($old_entry_id,$new_entry_id,$info,$restore) {

        global $CFG;

        $status = true;

        //Get the ratings array
        $views = $info['#']['VIEWS']['0']['#']['VIEW'];

        //Iterate over ratings
        for($i = 0; $i < sizeof($views); $i++) {
            $view_info = $views[$i];
            //traverse_xmlize($rat_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the IPODCAST_VIEWS record structure
            $view->entryid = $new_entry_id;
            $view->userid = backup_todb($view_info['#']['USERID']['0']['#']);
            $view->views = backup_todb($view_info['#']['VIEWS']['0']['#']);
            $view->timemodified = backup_todb($view_info['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code,"user",$view->userid);
            if ($user) {
                $view->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the glossary_ratings
            $newid = insert_record ("ipodcast_views",$view);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if (!$newid) {
                $status = false;
            }
        }
     return $status;
  }
        
    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function ipodcast_restore_logs($restore,$log) {
                    
        $status = false;
                   
        //Depending of the action, we recode different things
        switch ($log->action) {
default:
echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";                 //Debug
break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    }
?>