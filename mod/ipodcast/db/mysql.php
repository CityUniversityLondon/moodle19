<?PHP
/******************************************************************************\
 *
 * Filename:    mysql.php
 *
 *				This function does anything necessary to upgrade 
 *				older versions to match current functionality  *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 *              02/01/06 Tom Dolsky     - Removed two fields for version v.20
 *              03/24/06 Tom Dolsky     - Added course field to ipodcast table v.27
 *              03/24/06 Tom Dolsky     - Do cleanup on state records that would't get deleted in previos versions v.27
 *              05/15/06 Tom Dolsky     - Added streaming flag
 *              10/20/06 Tom Dolsky     - Added darwin enable flag to ipodcast course settings
 *              10/20/06 Tom Dolsky     - Adds a root slash if one doesnt exist on attachment
 *              10/23/06 Tom Dolsky     - Changed config for darwin base url from streambaseURL to ipodcast_darwinurl
 *              10/24/06 Tom Dolsky     - Added image heigth and width db fields
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/
function ipodcast_upgrade($oldversion) {

    global $CFG;
	
/******************************************************************************/
    if ($oldversion < 2006020301) {

        execute_sql('ALTER TABLE `'.$CFG->prefix.'ipodcast`  DROP `section`');					//Added V.20
        execute_sql('ALTER TABLE `'.$CFG->prefix.'ipodcast_courses` DROP `StreamURL`');			//Added V.20

    }
	
/******************************************************************************/
	
     if ($oldversion < 2006020705) {
       if ($ipodcasts = get_records('ipodcast')) {
           foreach($ipodcasts as $ipodcast) {
               if(!empty($ipodcast->keywords)) {
		 	  	$newkeywords = str_replace(' ',',',$ipodcast->keywords);
		 	  	set_field('ipodcast','keywords',$newkeywords,'id',$ipodcast->id);
		 	  }
           }
       }
	  
       if ($ipodcast_courses = get_records('ipodcast_courses')) {
           foreach($ipodcast_courses as $ipodcast_course) {
               if(!empty($ipodcast_course->keywords)) {
		 	  	$newkeywords = str_replace(' ',',',$ipodcast_course->keywords);
		 	  	set_field('ipodcast_courses','keywords',$newkeywords,'id',$ipodcast_course->id);
		 	  }
           }
       }	  
    }
	
/******************************************************************************/
		
     if ($oldversion < 2006032404) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast ADD `course` INT(10) UNSIGNED DEFAULT '0' NOT NULL AFTER `ipodcastcourseid` ");
	 
       if ($ipodcasts = get_records('ipodcast')) {
           foreach($ipodcasts as $ipodcast) {
		   		$ipodcast_course = get_record('ipodcast_courses','id',$ipodcast->ipodcastcourseid);
		 	  	set_field('ipodcast','course',$ipodcast_course->course,'id',$ipodcast->id);
           }
       }
	} 
	  
/******************************************************************************/

    if ($oldversion < 2006032405) {
		//course delete previously didn't delete ipodcast_course info
		if ($ipodcast_courses = get_records("ipodcast_courses")) {
			foreach($ipodcast_courses as $ipodcast_course) {
				if(!$course = get_record("course","id",$ipodcast_course->course)) {
					delete_records("ipodcast_courses", "id", $ipodcast_course->id);
				}    
			}
		}
		//podcast delete previously didn't delete ipodcast_comment info
		if ($ipodcast_comments = get_records("ipodcast_comments")) {
			foreach($ipodcast_comments as $ipodcast_comment) {
				if(!$ipodcast = get_record("ipodcast","id",$ipodcast_comment->entryid)) {
					delete_records("ipodcast_comments", "id", $ipodcast_comment->id);
				}    
			}
		}
		
		//podcast delete previously didn't delete ipodcast_view info
		if ($ipodcast_views = get_records("ipodcast_views")) {
			foreach($ipodcast_views as $ipodcast_view) {
				if(!$ipodcast = get_record("ipodcast","id",$ipodcast_view->entryid)) {
					delete_records("ipodcast_views", "id", $ipodcast_view->id);
				}    
			}
		}			
	}
	
/******************************************************************************/
	
     if ($oldversion < 2006051502) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `enabledarwin` TINYINT(2) UNSIGNED DEFAULT '0' NOT NULL AFTER `enabletsseries` ");
	 	} 
		
/******************************************************************************/
		
     if ($oldversion < 2006070701) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `authkey` VARCHAR(255) DEFAULT '12345678' NOT NULL AFTER `enabledarwin` ");
	 	}
		
/******************************************************************************/
		
     if ($oldversion < 2006101701) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `rsssorting` TINYINT(2) DEFAULT '1' NOT NULL AFTER `rssarticles` ");
	 	}	
		
/******************************************************************************/
			 
     if ($oldversion < 2006101912) { 
	 	$defaultdarwin;
		if ($configs = get_records("config")) {
				foreach($configs as $config) {
					if($config->name == "streambaseURL") {
						set_field('config','name','ipodcast_darwinurl','id',$config->id);
						$defaultdarwin = $config->value;
					}
				}
        	}
			
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `darwinurl` VARCHAR(255) AFTER `enabledarwin` ");
		
			if ($ipodcast_courses = get_records("ipodcast_courses")) {
				foreach($ipodcast_courses as $ipodcast_course) {
					set_field('ipodcast_courses','darwinurl',$defaultdarwin,'id',$ipodcast_course->id);
				}
        	}
					
	 	}
		
/******************************************************************************/
	
     if ($oldversion < 2006102302) {
       if ($ipodcasts = get_records('ipodcast')) {
           foreach($ipodcasts as $ipodcast) {
		 	  	if($ipodcast->attachment[0] != '/') {
					set_field('ipodcast','attachment', '/' . $ipodcast->attachment,'id',$ipodcast->id);
				}
           }
       }
	}  
/******************************************************************************/
		
     if ($oldversion < 2006102304) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `image` VARCHAR(255) NOT NULL AFTER `comment` ");
	 	}
			
/******************************************************************************/
		
     if ($oldversion < 2006102402) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `imageheight` INT(10) DEFAULT '144' NOT NULL AFTER `image` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `imagewidth` INT(10) DEFAULT '144' NOT NULL AFTER `imageheight` ");
	 	}	

/******************************************************************************/

     if ($oldversion < 2006102506) {
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_comments ADD `attachment` VARCHAR(255) DEFAULT '' NOT NULL AFTER `comment` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_comments ADD `visibility` TINYINT(2) DEFAULT '0' NOT NULL AFTER `attachment` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `attachwithcomment`  TINYINT(2) DEFAULT '0' NOT NULL AFTER `studentcanpost` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_courses ADD `defaultapproval`  TINYINT(2) DEFAULT '0' NOT NULL AFTER `studentcanpost` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast ADD `approved`  TINYINT(2) DEFAULT '0' NOT NULL AFTER `timefinish` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast ADD `teacherentry`  TINYINT(2) DEFAULT '0' NOT NULL AFTER `timemodified` ");
		//All entries before this are teacher entries
       if ($ipodcasts = get_records('ipodcast')) {
           foreach($ipodcasts as $ipodcast) {
				set_field('ipodcast','teacherentry', '1','id',$ipodcast->id);
           }
       }
	 }	
/******************************************************************************/
     if ($oldversion < 2006110608) {
	 
        execute_sql('TRUNCATE TABLE `'.$CFG->prefix.'ipodcast_itunes_categories`');
        execute_sql('TRUNCATE TABLE `'.$CFG->prefix.'ipodcast_itunes_nested_categories`');
		
		//Since Apple seems to change categories often we need a means to convert old to new
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_itunes_categories ADD `previousid` INT(10) DEFAULT '0' NOT NULL AFTER `name` ");
        execute_sql(" ALTER TABLE {$CFG->prefix}ipodcast_itunes_nested_categories ADD `previousid` INT(10) DEFAULT '0' NOT NULL AFTER `topcategoryid` ");

		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (1, 'Arts', 1)");
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (2, 'Business', 3)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (3, 'Comedy', 4)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (4, 'Education', 5)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (5, 'Games & Hobbies', 1)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (6, 'Government & Organizations', 0)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (7, 'Health', 8)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (8, 'Kids & Family', 6)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (9, 'Music', 11)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (10, 'News & Politics', 12)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (11, 'Religion & Spirituality', 15)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (12, 'Science & Medicine', 16)");
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (13, 'Society & Culture', 9)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (14, 'Sports & Recreation', 0)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (15, 'Technology', 18)"); 
		execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_categories (id,name,previousid) VALUES  (16, 'TV & Film', 1)");
			
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (1, 'Design', 1, 3)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (2, 'Fashion & Beautiy', 1, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (3, 'Food', 1, 17)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (4, 'Literature', 1, 2)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (5, 'Performing Arts', 1, 6)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (6, 'Visual Arts', 1, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (7, 'Business News', 2, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (8, 'Careers', 2, 10)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (9, 'Investing', 2, 11)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (10, 'Management & Marketing', 2, 13)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (11, 'Shopping', 2, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (12, 'Education Technology', 4, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (13, 'Higher Education', 4, 15)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (14, 'K-12', 4, 16)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (15, 'Language Courses', 4, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (16, 'Training', 4, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (17, 'Automotive', 5, 54)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (18, 'Aviation', 5, 55)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (19, 'Hobbies', 5, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (20, 'Other Games', 5, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (21, 'Video Games', 5, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (22, 'Local', 6, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (23, 'National', 6, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (24, 'Non-Profit', 6, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (25, 'Regional', 6, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (26, 'Alternative Health', 7, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (27, 'Fitness & Nutrition', 7, 17)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (28, 'Self-Help', 7, 20)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (29, 'Sexuality', 7, 21)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (30, 'Buddhism', 11, 38)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (31, 'Christianity', 11, 39)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (32, 'Hinduism', 11, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (33, 'Islam', 11, 40)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (34, 'Judaism', 11, 41)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (35, 'Other', 11, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (36, 'Medicine', 12, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (37, 'Natural Sciences', 12, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (38, 'Social Sciences', 12, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (39, 'History', 13, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (40, 'Personal Journals', 13, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (41, 'Philosophy', 13, 43)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (42, 'Places & Travel', 13, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (43, 'Amateur', 14, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (44, 'College & High School', 14, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (45, 'Outdoor', 14, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (46, 'Professional', 14, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (47, 'Gadgets', 15, 0)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (48, 'Tech News', 15, 0)"); 
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (49, 'Podcasting', 15, 51)");
        execute_sql(" INSERT INTO {$CFG->prefix}ipodcast_itunes_nested_categories (id,name,topcategoryid,previousid) VALUES  (50, 'Software How-To', 15, 0)"); 		

		//Make sure we don't have any null category fields
		if ($ipodcast_courses = get_records("ipodcast_courses")) {
			foreach($ipodcast_courses as $ipodcast_course) {
			////Make sure that none of the category fields are null
				if($ipodcast_course->topcategory == NULL) {
					set_field('ipodcast_courses','topcategory','0','id',$ipodcast_course->id);
				}
				if($ipodcast_course->nestedcategory == NULL) {
					set_field('ipodcast_courses','nestedcategory','0','id',$ipodcast_course->id);
				}
			////Make sure that none of the category fields are null
				//Now set the fields to the new values the best we can
				if($ipodcast_course->nestedcategory == '0') {
					if ($topcategories = get_records("ipodcast_itunes_categories")) {
						foreach($topcategories as $topcategory) {
							if($topcategory->previousid == $ipodcast_course->topcategory) {
								set_field('ipodcast_courses','topcategory',$topcategory->id,'id',$ipodcast_course->id);	
							}
						}
					}
				} else {
					if ($nestedcategories = get_records("ipodcast_itunes_nested_categories")) {
						foreach($nestedcategories as $nestedcategory) {
							if($nestedcategory->previousid == $ipodcast_course->nestedcategory) {
								set_field('ipodcast_courses','nestedcategory',$nestedcategory->id,'id',$ipodcast_course->id);	
								set_field('ipodcast_courses','topcategory',$nestedcategory->topcategoryid,'id',$ipodcast_course->id);	
							}
						}
					}				
				}												
			}
		}	
		
		
		//Make sure we don't have any null category fields
		if ($ipodcasts = get_records("ipodcast")) {
			foreach($ipodcasts as $ipodcast) {
			////Make sure that none of the category fields are null
				if($ipodcast->topcategory == NULL) {
					set_field('ipodcast','topcategory','0','id',$ipodcast->id);
				}
				if($ipodcast->nestedcategory == NULL) {
					set_field('ipodcast','nestedcategory','0','id',$ipodcast->id);
				}
			////Make sure that none of the category fields are null
				//Now set the fields to the new values the best we can
				if($ipodcast->nestedcategory == '0') {
					if ($topcategories = get_records("ipodcast_itunes_categories")) {
						foreach($topcategories as $topcategory) {
							if($topcategory->previousid == $ipodcast->topcategory) {
								set_field('ipodcast','topcategory',$topcategory->id,'id',$ipodcast->id);	
							}
						}
					}
				} else {
					if ($nestedcategories = get_records("ipodcast_itunes_nested_categories")) {
						foreach($nestedcategories as $nestedcategory) {
							if($nestedcategory->previousid == $ipodcast_course->nestedcategory) {
								set_field('ipodcast','nestedcategory',$nestedcategory->id,'id',$ipodcast->id);	
								set_field('ipodcast','topcategory',$nestedcategory->topcategoryid,'id',$ipodcast->id);	
							}
						}
					}				
				}												
			}
		}				
	}

	if ($oldversion < 2007010401) {
		$sourcefile = $CFG->dirroot . "/mod/ipodcast/lang/" . $CFG->lang . "/ipodcast.php";
		$destfile =	$CFG->dirroot . "/lang/" . $CFG->lang . "/ipodcast.php";
		if (!file_exists($sourcefile)) {
			echo "Error: Source language file $sourcefile missing!";
		} else if (file_exists($destfile)) {
			if(!rename($destfile, $destfile . ".bak")) {
				echo "Error: could not rename $destfile to $destfile.bak!";
			}
			if(!copy($sourcefile, $destfile)) {
				echo "Error: could not copy $sourcefile to $destfile!";
			}			
		} else if(!copy($sourcefile, $destfile)) {
			echo "Error: could not copy $sourcefile to $destfile!";
		}	
	}	 
	
								
    return true;
}

?>