<?php 
/******************************************************************************\
 *
 * Filename:    backuplib.php
 *
 *        This file contains all library function for iPodcast module
 *
 *
 * History:     03/24/06 Tom Dolsky     - First implementation and testing
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 *                     tomtek@cytekmedia.com
 *
\******************************************************************************/

    //This is the "graphical" structure of the ipodcast mod:
    //
    //                       ipodcast
    //                     (CL,pk->id)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
    function ipodcast_backup_mods($bf,$preferences) {
            
        global $CFG;
        $status = true;
        $pass = 0;

        //Iterate over podcast table
        $ipodcasts = get_records ("ipodcast","course", $preferences->backup_course, 'course');
        if ($ipodcasts) {
            foreach ($ipodcasts as $ipodcast) {
                if (backup_mod_selected($preferences,'ipodcast',$ipodcast->id)) {
                    $status = ipodcast_backup_one_mod($bf,$preferences,$ipodcast);
                }
            }
        }
        return $status;
    }
    
    //this function backs up a specific instance of the podcast module
    function ipodcast_backup_one_mod($bf,$preferences,$ipodcast) {
        
        global $CFG;
        global $global_ipodcast_runonce;
        global $global_ipodcast_count;
        global $global_ipodcast_saved;
          
        if (is_numeric($ipodcast)) {
            $ipodcast = get_record('ipodcast','id',$ipodcast);
        }        
        
        $status = true;
        // Initialize globals if first pass
        if(!is_numeric($global_ipodcast_runonce)) {
            $global_ipodcast_runonce = 0;
        }
        if(!is_numeric($global_ipodcast_saved)) {
            $global_ipodcast_saved = 0;
        } 
        if($global_ipodcast_runonce == 0)
        {
            // Get Data from ipodcast_courses
            $ipodcastcourses = get_records("ipodcast_courses","course", $preferences->backup_course, 'course');
            if ($ipodcastcourses) {
                fwrite ($bf,start_tag("MOD",3,true));
                foreach  ($ipodcastcourses as $ipodcastcourse) {
                                              
                    //Print ipodcast course data
                    // fwrite ($bf,full_tag("ID",4,false,$ipodcastcourse->id));
                    fwrite ($bf,full_tag("ID",4,false,$ipodcast->id));  //Needs to be $ipodcast->id to allow selective backup/restore
                    fwrite ($bf,full_tag("MODTYPE",4,false,"ipodcast"));
                    fwrite ($bf,full_tag("USERID",4,false,$ipodcastcourse->userid));
                    fwrite ($bf,full_tag("STUDENTCANPOST",4,false,$ipodcastcourse->studentcanpost));
                    fwrite ($bf,full_tag("DEFAULTAPPROVAL",4,false,$ipodcastcourse->defaultapproval));
                    fwrite ($bf,full_tag("ATTACHWITHCOMMENT",4,false,$ipodcastcourse->attachwithcomment));
                    fwrite ($bf,full_tag("ENABLETSSERIES",4,false,$ipodcastcourse->enabletsseries));
                    fwrite ($bf,full_tag("ENABLEDARWIN",4,false,$ipodcastcourse->enabledarwin));
                    fwrite ($bf,full_tag("DARWINURL",4,false,$ipodcastcourse->darwinurl));
                    fwrite ($bf,full_tag("AUTHKEY",4,false,$ipodcastcourse->authkey));
                    fwrite ($bf,full_tag("NAME",4,false,$ipodcastcourse->name));
                    fwrite ($bf,full_tag("SUMMARY",4,false,$ipodcastcourse->summary));
                    fwrite ($bf,full_tag("COMMENT",4,false,$ipodcastcourse->comments)); // AD
                    fwrite ($bf,full_tag("IMAGE",4,false,$ipodcastcourse->image));
                    fwrite ($bf,full_tag("IMAGEHEIGHT",4,false,$ipodcastcourse->imageheight));
                    fwrite ($bf,full_tag("IMAGEWIDTH",4,false,$ipodcastcourse->imagewidth));
                    fwrite ($bf,full_tag("RSSARTICLES",4,false,$ipodcastcourse->rssarticles));
                    fwrite ($bf,full_tag("RSSSORTING",4,false,$ipodcastcourse->rsssorting));
                    fwrite ($bf,full_tag("ENABLERSSFEED",4,false,$ipodcastcourse->enablerssfeed));
                    fwrite ($bf,full_tag("ENABLERSSITUNES",4,false,$ipodcastcourse->enablerssitunes));
                    fwrite ($bf,full_tag("VISIBLE",4,false,$ipodcastcourse->visible));
                    fwrite ($bf,full_tag("EXPLICIT",4,false,$ipodcastcourse->explicit));
                    fwrite ($bf,full_tag("SUBTITLE",4,false,$ipodcastcourse->subtitle));
                    fwrite ($bf,full_tag("KEYWORDS",4,false,$ipodcastcourse->keywords));
                    fwrite ($bf,full_tag("TOPCATEGORY",4,false,$ipodcastcourse->topcategory));
                    fwrite ($bf,full_tag("NESTEDCATEGORY",4,false,$ipodcastcourse->nestedcategory));
                    fwrite ($bf,full_tag("TIMECREATED",4,false,$ipodcastcourse->timecreated));
                    fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$ipodcastcourse->timemodified));
                    fwrite ($bf,full_tag("SORTORDER",4,false,$ipodcastcourse->sortorder));
                }
            }
        $global_ipodcast_runonce++;
        $status = fwrite ($bf,start_tag("IPODCASTS",5,true));
        // Count the number of podcast episodes to back up
        foreach($preferences->mods['ipodcast']->instances as $a){
            if($a->backup == 1) {
                $global_ipodcast_count++;
            }
        }
        
        }
        // Back up the requested instance
        if( $ipodcast->course == $preferences->backup_course) {
        backup_ipodcast_entries($bf,$preferences,$ipodcast->id);
        backup_ipodcast_tsseries($bf,$preferences,$ipodcast->id);
        $global_ipodcast_saved++;
        }
        // echo $global_ipodcast_saved;   //Debug Line
        //Close XML Structure if Required
        if($global_ipodcast_saved == $global_ipodcast_count){
            fwrite($bf,end_tag("IPODCASTS",5,true));
            fwrite($bf,end_tag("MOD",3,true));
        } 
        
        return $status;
        }
        

    //Backup backup_ipodcast_entries (executed from ipodcast_backup_mods)
    function backup_ipodcast_entries ($bf,$preferences,$entryid) {

        global $CFG;
        $status = true;

        $ipodcasts = get_records("ipodcast","id",$entryid,"id");
        if ($ipodcasts) {
            foreach ($ipodcasts as $ipodcast) {
                    //echo 'Episode ' . $ipodcast->name . '<br />';  //DEBUG LINE
                    $status =fwrite ($bf,start_tag("IPODCAST",6,true));

                    fwrite ($bf,full_tag("ID",7,false,$ipodcast->id));
                    // fwrite ($bf,full_tag("IPODCASTCOURSEID",7,false,$ipodcast->ipodcastcourseid)); 
                    fwrite ($bf,full_tag("USERID",7,false,$ipodcast->userid));
                    fwrite ($bf,full_tag("NAME",7,false,$ipodcast->name));
                    fwrite ($bf,full_tag("SUMMARY",7,false,$ipodcast->summary));
                    fwrite ($bf,full_tag("NOTES",7,false,$ipodcast->notes));
                    fwrite ($bf,full_tag("ATTACHEDFILE",7,false,$ipodcast->attachment));
                    fwrite ($bf,full_tag("DURATION",7,false,$ipodcast->duration));
                    fwrite ($bf,full_tag("EXPLICIT",7,false,$ipodcast->explicit));
                    fwrite ($bf,full_tag("SUBTITLE",7,false,$ipodcast->subtitle));
                    fwrite ($bf,full_tag("KEYWORDS",7,false,$ipodcast->keywords));
                    fwrite ($bf,full_tag("TOPCATEGORY",7,false,$ipodcast->topcategory));
                    fwrite ($bf,full_tag("NESTEDCATEGORY",7,false,$ipodcast->nestedcategory));
                    fwrite ($bf,full_tag("TIMECREATED",7,false,$ipodcast->timecreated));
                    fwrite ($bf,full_tag("TIMEMODIFIED",7,false,$ipodcast->timemodified));
                    fwrite ($bf,full_tag("TEACHERENTRY",7,false,$ipodcast->teacherentry));
                    fwrite ($bf,full_tag("TIMESTART",7,false,$ipodcast->timestart));
                    fwrite ($bf,full_tag("TIMEFINISH",7,false,$ipodcast->timefinish));
                    fwrite ($bf,full_tag("APPROVED",7,false,$ipodcast->approved));

                    //Backup student data
                    if ($preferences->mods["ipodcast"]->userinfo) {
                        backup_ipodcast_comments($bf,$preferences,$ipodcast->id);
                        backup_ipodcast_views($bf,$preferences,$ipodcast->id);
                    }        

                    $status =fwrite ($bf,end_tag("IPODCAST",6,true));        
            }
//            $status =fwrite ($bf,end_tag("IPODCASTS",6,true));
        }
        return $status;
    }
    
    //Backup ipodcast_comments contents (executed from backup_ipodcast_entries)
    function backup_ipodcast_comments ($bf,$preferences,$entryid) {

        global $CFG;

        $status = true;

        $comments = get_records("ipodcast_comments","entryid",$entryid);
        if ($comments) {
            $status =fwrite ($bf,start_tag("COMMENTS",10,true));
            foreach ($comments as $comment) {
                $status =fwrite ($bf,start_tag("COMMENT",11,true));

                fwrite ($bf,full_tag("ID",12,false,$comment->id));
                fwrite ($bf,full_tag("USERID",12,false,$comment->userid));
                fwrite ($bf,full_tag("COMMENT",12,false,$comment->comments)); // AD
                fwrite ($bf,full_tag("ATTACHMENT",12,false,$comment->attachment));
                fwrite ($bf,full_tag("VISIBILITY",12,false,$comment->visibility));
                fwrite ($bf,full_tag("TIMEMODIFIED",12,false,$comment->timemodified));

                $status =fwrite ($bf,end_tag("COMMENT",11,true));        
            }
            $status =fwrite ($bf,end_tag("COMMENTS",10,true));
        }
        return $status;
    }

    //Backup ipodcast_views contents (executed from backup_ipodcast_entries)
    function backup_ipodcast_views ($bf,$preferences,$entryid) {

        global $CFG;

        $status = true;

        $views = get_records("ipodcast_views","entryid",$entryid);
        if ($views) {
            $status =fwrite ($bf,start_tag("VIEWS",10,true));
            foreach ($views as $view) {
                $status =fwrite ($bf,start_tag("VIEW",11,true));

                fwrite ($bf,full_tag("ID",12,false,$view->id));
                fwrite ($bf,full_tag("USERID",12,false,$view->userid));
                fwrite ($bf,full_tag("VIEWS",12,false,$view->views));
                fwrite ($bf,full_tag("TIMEMODIFIED",12,false,$view->timemodified));

                $status =fwrite ($bf,end_tag("VIEW",11,true));        
            }
            $status =fwrite ($bf,end_tag("VIEWS",10,true));
        }
        return $status;
    }
    
    
    //Backup ipodcast_tsseries contents (executed from ipodcast_backup_mods)
    function backup_ipodcast_tsseries ($bf,$preferences,$entryid) {

        global $CFG;

        $status = true;

        $tsseries = get_records("ipodcast_tsseries","ipodcastcourseid",$entryid);
        if ($tsseries) {
            $status =fwrite ($bf,start_tag("TSSERIES",6,true));
            foreach ($tsseries as $ts) {
                $status =fwrite ($bf,start_tag("TS",7,true));

                fwrite ($bf,full_tag("ID",8,false,$ts->id));
                fwrite ($bf,full_tag("IPODCASTCOURSEID",8,false,$ts->ipodcastcourseid));
                fwrite ($bf,full_tag("IPODCASTID",8,false,$ts->ipodcastid));
                fwrite ($bf,full_tag("USERID",8,false,$ts->userid));
                fwrite ($bf,full_tag("NAME",8,false,$ts->name));
                fwrite ($bf,full_tag("SUMMARY",8,false,$ts->summary));
                fwrite ($bf,full_tag("NOTES",8,false,$ts->notes));
                fwrite ($bf,full_tag("DURATION",8,false,$ts->duration));
                fwrite ($bf,full_tag("TIMECREATED",8,false,$ts->timecreated));
                fwrite ($bf,full_tag("TIMEMODIFIED",8,false,$ts->timemodified));
                fwrite ($bf,full_tag("STATUS",8,false,$ts->status));
                fwrite ($bf,full_tag("ATTACHMENT",8,false,$ts->attachment));
                fwrite ($bf,full_tag("ROOMNAME",8,false,$ts->roomname));
                fwrite ($bf,full_tag("SECTION",8,false,$ts->section));

                $status =fwrite ($bf,end_tag("TS",7,true));        
            }
            $status =fwrite ($bf,end_tag("TSSERIES",6,true));
        }
        return $status;
    }
        
    ////Return an array of info (name,value)
    function ipodcast_check_backup_mods($course,$user_data=false,$backup_unique_code) {
         //First the course data
         $info[0][0] = get_string("modulenameplural","ipodcast");
         $info[0][1] = count_records("ipodcast_courses", "course", "$course");
         return $info;
    } 

?>