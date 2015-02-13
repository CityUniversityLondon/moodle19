<?php
/******************************************************************************\
 *
 * Filename:    rsslib.php
 *
 *		This file adds support to rss feeds generation
 *
 *		This function is the main entry point to ipodcast
 *		rss feeds generation. Foreach site ipodcast with rss enabled
 *		build one XML rss structure. *
 *
 * History:     01/03/06 Tom Dolsky     - Tested and added this copyright and info
 * 				01/09/06 Tom Dolsky     - Removed rss file building changed to xml on the fly
 * 				01/20/06 Tom Dolsky     - Made Channel Summary tag plain text
 * 				02/06/06 Tom Dolsky     - Added routines for the itunes subscribe link
 * 				02/07/06 Tom Dolsky     - Ran the feed through feed validator and made necassary changes
 * 				02/19/06 Tom Dolsky     - Moved all needed rsslib functions to this file ipodcast should now work without replacing lib/rsslib.php
 * 				02/28/06 Tom Dolsky     - Added mpa and mpb file types
 *              07/07/06 Tom Dolsky     - Added a key hash encoded with an authorization key
 *				10/17/06 Tom Dolsky     - Added ability to Sort XML enclosures
 *				10/23/06 Tom Dolsky     - Fixed enclosure length error
 *				10/24/06 Tom Dolsky     - Added image size variables to rss header
 *				01/02/07 Tom Dolsky     - Added pcast:// uri for Macintosh only
 *				01/02/07 Tom Dolsky     - Added ability to support pdf files
 *				01/02/07 Tom Dolsky     - Changed stripslashes to stripslashes_safe
 *				01/02/07 Tom Dolsky     - Fixed & character in categories
 *
 * Copyright, Thomas E. Dolsky Cytek Media Systems, Inc.
 * 					tomd@cytekmedia.com
 *
\******************************************************************************/

require_once($CFG->libdir.'/rsslib.php');
require_once($CFG->libdir.'/weblib.php');
require_once($CFG->libdir.'/html2text.php');
require_once($CFG->dirroot.'/mod/ipodcast/mp3lib.php');

//This function will return all itunes tags in the item
function ipodcast_add_itunes_tags($item) {

    global $CFG;
	
    $result = '';
		
    if (!empty($item->itunes)) {
        if (isset($item->itunes->subtitle)) {
            $result .= rss_full_tag('itunes:subtitle',2,false,html_to_text($item->itunes->subtitle));
		}
       if (isset($item->itunes->author)) {
           $result .= rss_full_tag('itunes:author',2,false,$item->itunes->author);
		}
        if (isset($item->itunes->summary)) {
            $result .= rss_full_tag('itunes:summary',2,false,html_to_text($item->itunes->summary));
		}
		if (isset($item->itunes->owner->name) && isset($item->itunes->owner->email)) {
			$result .= rss_start_tag('itunes:owner',2,true);
			$result .= "      <itunes:name>" . htmlspecialchars($item->itunes->owner->name) . "</itunes:name>\n";
			$result .= "      <itunes:email>" . htmlspecialchars($item->itunes->owner->email) . "</itunes:email>\n";
			$result .= rss_end_tag('itunes:owner',2,true);
			}
        if (isset($item->itunes->image)) {
			$result .= "    <itunes:image href=\"";
			$result .= htmlspecialchars($item->itunes->image);
			$result .= "\"/>\n";
		}
        if (isset($item->itunes->block)) {
           	$result .= rss_full_tag('itunes:block',2,false,$item->itunes->block);
			}
        if (isset($item->itunes->topcategory)) {			//must have top category
			if($topcategory = get_record("ipodcast_itunes_categories" , "id" , $item->itunes->topcategory)) {
				if(isset($item->itunes->nestedcategory)) {
					if($nestedcategory = get_record("ipodcast_itunes_nested" , "id" , $item->itunes->nestedcategory)) { // AD
						$result .= "    <itunes:category text=\"" . htmlspecialchars($topcategory->name) . "\">\n";
						$result .= "      <itunes:category text=\"" . htmlspecialchars($nestedcategory->name) . "\"/>\n";
						$result .= rss_end_tag('itunes:category',2,true);
					} else {
						$result = $result ;
					}
				} else {
					$result .= "    <itunes:category text=\"" . htmlspecialchars($topcategory->name) . "\"/>\n";		
				}
			} else {
				$result = $result ;
			}
		}
        if (isset($item->itunes->duration)) {
            $result .= rss_full_tag('itunes:duration',2,false,$item->itunes->duration);
		}
        if (isset($item->itunes->explicit)) {
            $result .= rss_full_tag('itunes:explicit',2,false,$item->itunes->explicit);
		}
        if (isset($item->itunes->keywords)) {
            $result .= rss_full_tag('itunes:keywords',2,false,$item->itunes->keywords);
		}
        if (isset($item->itunes->newfeedurl)) {
            $result .= rss_full_tag('itunes:newfeedurl',2,false,$item->itunes->newfeedurl);
		}
//This tag is in the itunes tag spec but seems to create an error
//        if (isset($item->itunes->pubdate)) {
//            $result .= rss_full_tag('itunes:pubDate',2,false,date('r',$item->itunes->pubdate));
//		}
    } else {
        $result = false;
    }
    return $result;
}

//This function returns the icon (from theme) with the link to rss/file.php
function ipodcast_rss_get_link($courseid, $userid, $modulename, $id, $tooltiptext='') {

    global $CFG, $USER;

    static $pixpath = '';
    static $rsspath = '';

    //In site course, if no logged (userid), use admin->id. Bug 2048.
    if ($courseid == SITEID and empty($userid)) {
        $admin = get_admin();
        $userid = $admin->id;
    }

    if ($CFG->slasharguments) {
        $rsspath = "$CFG->wwwroot/mod/$modulename/getfeed.php/$courseid/$userid/$modulename/$id/rss.xml";
    } else {
        $rsspath = "$CFG->wwwroot/mod/$modulename/getfeed.php?file=/$courseid/$userid/$modulename/$id/rss.xml";
    }

    $rsspix = "$CFG->wwwroot/mod/$modulename/rssfeed.gif";

    return '<a href="'. $rsspath .'"><img src="'. $rsspix .'" title="'. strip_tags($tooltiptext) .'" alt="" /></a>';

}


//This function returns the icon (from theme) with the link to rss/file.php
function ipodcast_podcast_get_link($courseid, $userid, $modulename, $id, $tooltiptext='') {

    global $CFG, $USER;

    static $pixpath = '';
    static $rsspath = '';

    //In site course, if no logged (userid), use admin->id. Bug 2048.
    if ($courseid == SITEID and empty($userid)) {
        $admin = get_admin();
        $userid = $admin->id;
    }


	$client = $_SERVER['HTTP_USER_AGENT']; 
	
	if(strstr($client,"Windows")) {
		//Windows users are here
		if ($CFG->slasharguments) {
			$rsspath = "$CFG->wwwroot/mod/$modulename/getpodcast.php/$courseid/$userid/$modulename/$id/ipodcast.pcast";
		} else {
			$rsspath = "$CFG->wwwroot/mod/$modulename/getpodcast.php?file=/$courseid/$userid/$modulename/$id/ipodcast.pcast";
		}
	} else { 
		//Everything else ends up here
		$wwwroot = str_replace("http:","pcast:",$CFG->wwwroot);
		if ($CFG->slasharguments) {
			$rsspath = "$wwwroot/mod/$modulename/getfeed.php/$courseid/$userid/$modulename/$id/rss.xml";
		} else {
			$rsspath = "$wwwroot/mod/$modulename/getfeed.php?file=/$courseid/$userid/$modulename/$id/rss.xml";
		}
	}

    $rsspix = "$CFG->wwwroot/mod/$modulename/podcast.gif";

    return '<a href="'. $rsspath .'" title="'. strip_tags($tooltiptext) .'" alt="" />' . $tooltiptext . '</a>';
//    return '<a href="'. $rsspath .'"><img src="'. $rsspix .'" title="'. strip_tags($tooltiptext) .'" alt="" /></a>';

}
//This function prints the icon (from theme) with the link to rss/file.php
function ipodcast_rss_print_link($courseid, $userid, $modulename, $id, $tooltiptext='') {

    print ipodcast_rss_get_link($courseid, $userid, $modulename, $id, $tooltiptext);

}

//This function prints the icon (from theme) with the link to rss/file.php
function ipodcast_podcast_print_link($courseid, $userid, $modulename, $id, $tooltiptext='') {

    print ipodcast_podcast_get_link($courseid, $userid, $modulename, $id, $tooltiptext);

}

function ipodcast_build_podcast_link($item) {
    global $CFG;
	
    $result = '';

        //xml headers
        $result .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$result .= "<!DOCTYPE pcast PUBLIC \"-//Apple Computer//DTD PCAST 1.0//EN\" \"http://www.itunes.com/DTDs/pcast-1.0.dtd\">\n"; 

        //open the channel
        $result .= rss_start_tag('pcast version="1.0"', 1, true);
		$result .= "\n";
        $result .= rss_start_tag('channel', 3, true);
		$result .= "      <link rel=\"feed\" type=\"application/rss+xml\" href=\"$item->link\" />\n";
        $result .= rss_full_tag('title', 5, false, $item->title);
        $result .= rss_full_tag('subtitle', 5, false, $item->subtitle);		
		//Need category here I think?
		$result .= rss_end_tag('channel', 3, true);	
		$result .= rss_end_tag('pcast', 1, true);	

	return $result;
}

function ipodcast_build_feeds($courseid,$userid) {

        global $CFG;	
		$result = false;
       
	   //Check CFG->enablerssfeeds
        if (!empty($CFG->enablerssfeeds) && !empty($CFG->ipodcast_enablerssfeeds)) {
            //Iterate over all glossaries
            if ($ipodcastcourses = get_records("ipodcast_courses","course",$courseid)) {
                foreach ($ipodcastcourses as $ipodcastcourse) {
                    if ($ipodcastcourse->enablerssfeed !=0 && $ipodcastcourse->rssarticles !=0) {
                        //Ignore hidden forums
//                        if (!instance_is_visible('ipodcast',$ipodcast)) {
//                            continue;
//                        }
                        //Get the XML contents		     
						$items = getfeed_for_course($ipodcastcourse, $userid);			     
		
						//First all rss feeds common headers
						$header = ipodcast_standard_header($ipodcastcourse, $userid);														  

						//Now all the rss items
						if (!empty($header)) {
							$articles = getfeed_add_items($ipodcastcourse->course, $items);
						}
						// Do feed even if there are no items
						 if (!empty($header)) {
							 $footer = rss_standard_footer();
						 }
						//Now, if everything is ok, concatenate it
						if (!empty($header) && !empty($footer)) {
							$result = $header.$articles.$footer;
//									ipodcast_create_image("ipodcast",$ipodcast);
						} else {
							$result = false;
						}
                    }
                }
            }
        }
        return $result;
    }	
	
//This function return all the common headers for every rss feed in the site
function ipodcast_standard_header($ipodcastcourse, $userid) {
	require_once('filelib.php');
    global $CFG, $USER;
    $item = array();
    static $pixpath = '';

    $status = true;
    $result = "";

    if (!$site = get_site()) {
        $status = false;
    }

    if ($status) {
		if(!$course = get_record("course" , "id" , $ipodcastcourse->course)) {
			return false;
		}
		
	if(!$user = get_record("user" , "id" , $ipodcastcourse->userid)) {
		return false;
	}
        
		//Calculate title, link and summary
        if (empty($course->fullname)) {
            $title = $site->fullname;
        } else {
			$title = $course->fullname;
		}        
		//Need to fix this
		if (empty($course->fullname)) {
            $link = $CFG->wwwroot;
        } else {
			$link = $CFG->wwwroot;
		}
		
        if (empty($course->summary)) {
            $description = stripslashes_safe($site->summary);
        } else {
			$description = stripslashes_safe($course->summary);
		}
		
        //xml headers
        $result .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		
        if($ipodcastcourse->enablerssitunes) {
			$result .= "<rss xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\" version=\"2.0\">\n"; 
		} else {
			$result .= "<rss version=\"2.0\">\n";
		}

        //open the channel
        $result .= rss_start_tag('channel', 1, true);

        //write channel info
        $result .= rss_full_tag('ttl', 2, false, "60");
        $result .= rss_full_tag('title', 2, false, $title);
//		$result .= rss_full_tag('author', 2, false, fullname($user));
        $result .= rss_full_tag('link', 2, false, $link);
        $result .= rss_full_tag('description', 2, false, $description);
        $result .= rss_full_tag('generator', 2, false, 'Moodle http://www.moodle.org/');
        if (!empty($USER->lang)) {
            $result .= rss_full_tag('language', 2, false, substr($USER->lang,0,2));
        } else {
        	$result .= rss_full_tag('language', 2, false, substr($CFG->lang,0,2));		//broke needs actual data
		}
        $today = getdate();		//Incorrect  this should be date of recording
        $result .= rss_full_tag('copyright', 2, false, '&#xA9;'. $today['year'] .' '. $site->fullname);
        if (!empty($USER->email)) {
            $result .= rss_full_tag('managingEditor', 2, false, $USER->email);
            $result .= rss_full_tag('webMaster', 2, false, $USER->email);
        }
		
		if ($CFG->slasharguments) {
			if(!empty($ipodcastcourse->image)) {		
				$templink = "$CFG->wwwroot/mod/ipodcast/file.jpg";
				$tohash = "$ipodcastcourse->course:$userid:0:$ipodcastcourse->image:$ipodcastcourse->authkey";
				$temphash = md5($tohash);
				$filelink = cleardoubleslashes("/$temphash/$ipodcastcourse->course/$userid/ipodcast/0/$ipodcastcourse->image");
				$imageurl = $templink . $filelink;	
			} else {
				$imageurl = "$CFG->wwwroot/mod/ipodcast/getimage.php/$ipodcastcourse->course/$userid/ipodcast/$ipodcastcourse->id/image.jpg";
			}
		} else {
			if(!empty($ipodcastcourse->image)) {		
				$templink = "$CFG->wwwroot/mod/ipodcast/file.jpg";
				$tohash = "$ipodcastcourse->course:$userid:0:$ipodcastcourse->image:$ipodcastcourse->authkey";
				$temphash = md5($tohash);
				$filelink = cleardoubleslashes("?file=/$temphash/$ipodcastcourse->course/$userid/ipodcast/0/$ipodcastcourse->image");		
				$imageurl = $templink . $filelink;	
			} else {
				$imageurl = "$CFG->wwwroot/mod/ipodcast/getimage.php?file=/$ipodcastcourse->course/$userid/ipodcast/$ipodcastcourse->id/image.jpg";
			}
		}
	
		//write the info 
		$result .= rss_start_tag('image', 2, true);
		$result .= rss_full_tag('url', 3, false, $imageurl);
		$result .= rss_full_tag('title', 3, false, 'moodle');
		$result .= rss_full_tag('link', 3, false, $CFG->wwwroot);
		
		//After moving resize to the setup page and removing dynamic remove this if and use the resized filename
		if(($ipodcastcourse->imagewidth > 0) || ($ipodcastcourse->imageheight > 0)) {
			$result .= rss_full_tag('width', 3, false, $ipodcastcourse->imagewidth);
			$result .= rss_full_tag('height', 3, false, $ipodcastcourse->imageheight);
		} else {
			$fileinfo = array();
			$fileinfo = null;
			$fileinfo->courseid 		= $ipodcastcourse->course;
			$fileinfo->filename 	= $ipodcastcourse->image;
			$fileinfo->modulename 	= "ipodcast";			//bug fix me - get module name from database
			$fileinfo->userid     	= $userid;
			$fileinfo->extension = file_get_extension($fileinfo);
			$fileinfo = file_get_image_size($fileinfo);
			
			if($fileinfo == null) {
				$result .= rss_full_tag('width', 3, false, '144');
				$result .= rss_full_tag('height', 3, false, '144');			
			} else {
				$result .= rss_full_tag('width', 3, false, $fileinfo->imagewidth);
				$result .= rss_full_tag('height', 3, false, $fileinfo->imageheight);
			}
		}
		$result .= rss_end_tag('image', 2, true);	
				
        if($ipodcastcourse->enablerssitunes) {
			//Finish image url here
			$item = NULL;
            $item->itunes = array();                
            $item->itunes = NULL;
            $item->itunes->owner = array();                
            $item->itunes->owner = NULL;
			$item->itunes->author = fullname($user);
			$item->itunes->owner->name = fullname($user);
			$item->itunes->owner->email = $user->email;
			if($ipodcastcourse->explicit == 2) {
				$item->itunes->explicit = "clean";
			} else if ($ipodcastcourse->explicit== 1) {
				$item->itunes->explicit = "yes";
			} else {
				$item->itunes->explicit = "no";
			}			                
			$item->itunes->topcategory = $ipodcastcourse->topcategory;		//Need this on new ipodcast page
			$item->itunes->nestedcategory = $ipodcastcourse->nestedcategory;		//Need this on new ipodcast page
			$item->itunes->keywords = stripslashes_safe($ipodcastcourse->keywords);
			$item->itunes->summary = $description;
			$item->itunes->subtitle = stripslashes_safe($ipodcastcourse->subtitle);
			$item->itunes->image = $imageurl;
			$result .= ipodcast_add_itunes_tags($item);	
		}
    }

    if (!$status) {
        return false;
    } else {
        return $result;
    }
}


    //This function returns "items" record array to be used to build the rss feed
    //for a Type=without author ipodcast
    function getfeed_for_course($ipodcastcourse, $userid, $newsince=0) {

        global $CFG;

        $items = array();
        $info = array();

        if ($newsince) {
            $newsince = " AND e.timemodified > '$newsince'";
        } else {
            $newsince = "";
        }

        if ($ipodcastcourse->rsssorting == 0) {
            $sorting = "ORDER BY e.timecreated asc";
        } else if ($ipodcastcourse->rsssorting == 1) {
            $sorting = "ORDER BY e.timecreated desc";
        } else if ($ipodcastcourse->rsssorting == 2) {
            $sorting = "ORDER BY cm.section asc";
        } else if ($ipodcastcourse->rsssorting == 3) {
            $sorting = "ORDER BY cm.section desc";
		}

		
        if ($recs = get_records_sql ("SELECT e.id AS entryid,
                                             e.name AS entryname,
                                             e.summary AS entrysummary,
                                             e.attachment AS entryattachment,
											 e.explicit AS entryexplicit,
											 e.keywords AS entrykeywords,
											 e.topcategory AS entrytopcategory,
											 e.nestedcategory AS entrynestedcategory,
											 e.subtitle AS entrysubtitle,
                                             e.timecreated AS entrytimecreated,
                                             e.timemodified AS entrytimemodified,
                                             e.duration AS entryduration,
                                             c.enablerssitunes AS entryenablerssitunes,
                                             c.authkey AS authkey,
                                             u.id AS userid,
                                             u.firstname AS userfirstname,
                                             u.lastname AS userlastname,
                                             u.email AS useremail,
											 cm.id AS entrycoursemodule,
											 cm.section AS entrycoursesection,
											 cm.visible AS entrycmvisibility,
											 cs.visible AS entrycsvisibility,
											 cs.id AS courseid
                                      FROM {$CFG->prefix}course cs,
										   {$CFG->prefix}course_modules cm,
										   {$CFG->prefix}modules md,
									  	   {$CFG->prefix}ipodcast e,
                                           {$CFG->prefix}ipodcast_courses c,
                                           {$CFG->prefix}user u
                                      WHERE e.ipodcastcourseid = '$ipodcastcourse->id' AND
                                            c.id = e.ipodcastcourseid AND
											md.name = 'ipodcast' AND
                                            cm.instance = e.id AND
								 			cm.course = c.course AND
								 			cs.id = c.course AND
                                			u.id = e.userid
											$newsince
                                            $sorting ")) {
							  
            //Are we just looking for new ones?  If so, then return now.
           if ($newsince) {
                return true;
            }
            //Iterate over each entry to get ipodcast->rssarticles records
            $articlesleft = $ipodcastcourse->rssarticles;
            $item = NULL;
			$item->itunes = NULL;                
            $user = NULL;
            foreach ($recs as $rec) {
				if($rec->entrycsvisibility == 0 || $rec->entrycmvisibility == 0) {
					continue;
				}
                unset($item);
                unset($user);
                $item->title = stripslashes_safe($rec->entryname);
                $user->firstname = $rec->userfirstname;
                $user->lastname = $rec->userlastname;
                $item->author = fullname($user);
                $item->email = $rec->useremail;
                $item->pubdate = $rec->entrytimecreated;
                $item->link = $CFG->wwwroot."/mod/ipodcast/view.php?id=".$rec->entrycoursemodule;				
	
				if(!empty($rec->entryattachment)) {
					
					$templink = rss_build_link($item,$rec);
					
					if(isset($templink)) {
						//echo $templink . "\n";
						$item->enclosure = $templink;
						$item->guid = $templink . "-" . $rec->entrytimemodified;
		
						$templink = "<a href = \"$templink\">" . stripslashes_safe($rec->entryname) . "</a>";
						$summary = 	format_text(stripslashes_safe($rec->entrysummary),FORMAT_HTML,NULL,$ipodcastcourse->course);
						$summary = $templink . "<br />" . $summary;
					} else {
						$summary = 	format_text(stripslashes_safe($rec->entrysummary),FORMAT_HTML,NULL,$ipodcastcourse->course);					
					}
					
				}						
					
				//Need to check summary layout to make sure its what I want
				$item->description = $summary;
				
				//iTunes section				             
				if($rec->entryenablerssitunes)  {
					$item->itunes->author = fullname($user);
//					$item->itunes->topcategory = $rec->entrytopcategory;		//These are not in the itunes xml spec but they do work but fail validators
//					$item->itunes->nestedcategory = $rec->entrynestedcategory;		//These are not in the itunes xml spec but they do work but fail validators
					if($rec->entryexplicit == 2) {
						$item->itunes->explicit = "clean";
					} else if ($rec->entryexplicit == 1) {
						$item->itunes->explicit = "yes";
					} else {
						$item->itunes->explicit = "no";
					}
					$item->itunes->keywords = stripslashes_safe($rec->entrykeywords);
					$item->itunes->subtitle = stripslashes_safe($rec->entrysubtitle);
//					$item->itunes->pubdate = $rec->entrytimecreated;
					$item->itunes->summary = stripslashes_safe($summary);
				}					
					
				$items[] = $item;
				$articlesleft--;
				if ($articlesleft < 1) {
					break;
				}										
            }
        }
        return $items;
    }
	

//This function returns the rss XML code for every item passed in the array
//item->title: The title of the item
//item->author: The author of the item. Optional !!
//item->pubdate: The pubdate of the item
//item->link: The link url of the item
//item->description: The content of the item
function getfeed_add_items($courseid, $items) {

    global $CFG;
        
    $result = '';
	if(!$course = get_record("ipodcast_courses", "course", $courseid)) {
		echo "Invalid Course ID:$courseid";
		return false;
	}
	
    if (!empty($items)) {
        foreach ($items as $item) {
            $result .= rss_start_tag('item',2,true);
            //Include the category if exists (some rss readers will use it to group items)
            if (isset($item->category)) {
                $result .= rss_full_tag('category',3,false,$item->category);
            }
            $result .= rss_full_tag('title',3,false,$item->title);
            $result .= rss_full_tag('link',3,false,$item->link);
            $result .= rss_full_tag('pubDate',3,false,date('r',$item->pubdate));
            //Include the author if exists 
         	if(!($course->enablerssitunes)) {
				if (isset($item->author)) {
					//$result .= rss_full_tag('author',3,false,$item->author);
					//We put it in the description instead because it's more important 
					//for moodle than most other feeds, and most rss software seems to ignore
					//the author field ...
					$item->description = get_string('byname','',$item->author).'. &nbsp;<p />'.$item->description.'</p>';
				}
			} else {
				//Don't add the author to the description in iTunes it seems to handle it
				if(isset($item->email)) {
					$result .= rss_full_tag('author',3,false,$item->email);			//Put back in for iTunes converted to email for feed validator	
				}
			}
			
			$result .= rss_full_tag('description',3,false,$item->description);
           	$result .= getfeed_add_enclosures($item);			
			if(isset($item->guid)) {
				$result .= rss_full_tag('guid',2,false,$item->guid);
			} else {
            	$result .= getfeed_add_guid($item);
			}
			
         	if($course->enablerssitunes) {			
				$result .= ipodcast_add_itunes_tags($item);
			}
			
            $result .= rss_end_tag('item',2,true);

        }
    } else {
        $result = false;
    }
    return $result;
}

function imagettftextalign($image, $size, $angle, $x, $y, $color, $font, $text, $alignment='C') {
   
   //check width of the text
   $bbox = imagettfbbox ($size, $angle, $font, $text);
   $textWidth = $bbox[2] - $bbox[0];
   switch ($alignment) {
       case "R":
           $x -= $textWidth; 
           break;
       case "C":
           $x -= $textWidth / 2;
           break;
   }
       
   //write text
   imagettftext ($image, $size, $angle, $x, $y, $color, $font, $text);

} 

function ipodcast_create_image($mod, $width=144, $height=144) {
	global $CFG;
	
  	$currlang = current_language();
  	if (file_exists("$CFG->dirroot/lang/$currlang/fonts/default.ttf")) {
    	$font = "$CFG->dirroot/lang/$currlang/fonts/default.ttf";
  	} else if (file_exists("$CFG->libdir/default.ttf")) {
    	$font = "$CFG->libdir/default.ttf";
  	} else {
  		// Not sure what else to do so I did this
    	$font = "C:\windows\fonts\ARIALBD.TTF";
  	}
	
	$copyfrom =  cleardoubleslashes("$CFG->dirroot/mod/ipodcast/image.png");
	
	$image = imagecreatefrompng($copyfrom);
	$imageout = imagecreatetruecolor($width,$height);
	$font_size = 18;
	$color = imagecolorallocate($image, 0,0,0);
	$overlay_text =  $mod->fullname;
	
	imagettftextalign ($image, $font_size, 0, imagesx($image) / 2, 65, $color, $font ,$overlay_text, "C");
	imagecopyresampled ($imageout, $image ,0 ,0 ,0 ,0 , $width, $height, imagesx($image),imagesy($image));
	
	header("Content-type: image/jpg");
	
	imagepng($imageout);	//output the updated png file to browser
	imagedestroy($imageout);		//Free memory
	
}

function getfeed_add_enclosures($item){

    $returnstring = '';
	
	// list of media file extensions and their respective mime types
	// could/should we put this to some more central place?
	//Should be the mime types
	$mediafiletypes = array(
		'mp3'  => 'audio/mpeg',
		'm3u'  => 'audio/x-mpegurl',
		'pls'  => 'audio/x-mpegurl',
		'ogg'  => 'application/ogg',
		'm4a'  => 'audio/x-m4a',
		'm4b'  => 'audio/x-m4b',
		'mpeg' => 'video/mpg',
		'mpg'  => 'video/mpg',
		'mp4'  => 'video/mp4',
		'm4v'  => 'video/x-m4v',
		'mov'  => 'video/quicktime',
		'avi'  => 'video/x-msvideo',
		'wmv'  => 'video/x-msvideo',
		'pdf'  => 'application/pdf'
	);
		    	
	if(!(isset($item->enclosure))) {
    	$rss_text = $item->description;
		//If the enclosure variable is not set then pull the enclosure out of the description
		// take into account attachments (e.g. from forum)
		if (isset($item->attachments) && is_array($item->attachments)) {
			foreach ($item->attachments as $attachment){
				$rss_text .= " <a href='$attachment'/>"; //just to make sure the regexp groks it
			}
		}
		
		// regular expression (hopefully) matching all links to media files
		$medialinkpattern = '@href\s*=\s*(\'|")(\S+(' . implode('|', array_keys($mediafiletypes)) . '))\1@Usie';
	
		if (!preg_match_all($medialinkpattern, $rss_text, $matches)){
			return $returnstring;
		}
	
		// loop over matches of regular expression 
		for ($i = 0; $i < count($matches[2]); $i++){
			$url = htmlspecialchars($matches[2][$i]);
			$type = $mediafiletypes[strtolower($matches[3][$i])];               
			$length = $item->length;
			// the rss_*_tag functions can't deal with methods, unfortunately
			$returnstring .= "    <enclosure url='$url' length='$length' type='$type' />\n";
		}
	} else {
        $type = mimeinfo("type", $item->enclosure) ;
		$url = $item->enclosure;           
		if(isset($item->length)){ 
			$length = $item->length;
		} else {
			$length = 0;
		}
		$returnstring .= "      <enclosure url='$url' length='$length' type='$type' />\n";
	}
	
	return $returnstring;	
}

function getfeed_add_guid($item){

    $returnstring = '';
    $rss_text = $item->description;
    
    // take into account attachments (e.g. from forum)
    if (isset($item->attachments) && is_array($item->attachments)) {
        foreach ($item->attachments as $attachment){
            $rss_text .= " <a href='$attachment'/>"; //just to make sure the regexp groks it
        }
    }
    
    // list of media file extensions and their respective mime types
    // could/should we put this to some more central place?
    $mediafiletypes = array(
        'mp3'  => 'audio/mpeg',
        'm3u'  => 'audio/x-mpegurl',
        'pls'  => 'audio/x-mpegurl',
        'ogg'  => 'application/ogg',
		'm4a'  => 'video/x-m4a',
        'm4b'  => 'audio/x-m4b',
		'mp4'  => 'video/mp4',
		'm4v'  => 'video/x-m4v',
        'mpeg' => 'video/mpg',
        'mpg'  => 'video/mpg',
        'mov'  => 'video/quicktime',
        'avi'  => 'video/x-msvideo',
        'wmv'  => 'video/x-msvideo',
		'pdf'  => 'application/pdf'
    );

    // regular expression (hopefully) matching all links to media files
    $medialinkpattern = '@href\s*=\s*(\'|")(\S+(' . implode('|', array_keys($mediafiletypes)) . '))\1@Usie';

    if (!preg_match_all($medialinkpattern, $rss_text, $matches)){
        return $returnstring;
    }

    // loop over matches of regular expression 
    for ($i = 0; $i < count($matches[2]); $i++){
        $url = htmlspecialchars($matches[2][$i]);
        // the rss_*_tag functions can't deal with methods, unfortunately
        $returnstring .= "        <guid>$url</guid>\n";
    }
    
    return $returnstring;
}

function rss_build_link($item,$rec) {
	global $CFG;
	
	$item->length = 0;
	if(strstr($rec->entryattachment,"http://") || strstr($rec->entryattachment,"https://")) {
		$templink = $rec->entryattachment;
	 } else {
	 
		$path = cleardoubleslashes("$CFG->dataroot/$rec->courseid/ipodcast/$rec->entryattachment");
		if(file_exists($path)) {
				$filesize     = filesize($path);
				$item->length = $filesize;
			} else {
				$item->length = 0;							
			}
			
		$templink = "$CFG->wwwroot/mod/ipodcast/file";
		$tohash = "$rec->courseid:$rec->userid:$rec->entryid:$rec->entryattachment:$rec->authkey";
		$temphash = md5($tohash);
		$filelink = "?file=/$temphash/$rec->courseid/$rec->userid/ipodcast/$rec->entryid/$rec->entryattachment";
		
		//get mp3 info i am sure there is a better way but for now this will do
		if(mimeinfo("type",$path) == "audio/mpeg" || mimeinfo("type",$path) == "audio/mp3") {
			$info = NULL;
			$info = get_mp3_info($path);
			if($rec->entryenablerssitunes == true) {
				$item->itunes->duration = $info->length;		
			}
			$templink .= cleardoubleslashes(".mp3" . $filelink);	
		} else if(mimeinfo("type",$path) == "audio/m4a" || mimeinfo("type",$path) == "audio/x-m4a") {
			$templink .= cleardoubleslashes(".m4a" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else if(mimeinfo("type",$path) == "audio/m4b" || mimeinfo("type",$path) == "audio/x-m4b") {
			$templink .= cleardoubleslashes(".m4b" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else if(mimeinfo("type",$path) == "video/m4v" || mimeinfo("type",$path) == "video/x-m4v") {
			$templink .= cleardoubleslashes(".m4v" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else if(mimeinfo("type",$path) == "video/mp4") {
			$templink .= cleardoubleslashes(".mp4" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else if(mimeinfo("type",$path) == "video/quicktime") {
			$templink .= cleardoubleslashes(".mov" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else if(mimeinfo("type",$path) == "application/pdf") {
			$templink .= cleardoubleslashes(".pdf" . $filelink);	
			if($rec->entryduration && ($rec->entryenablerssitunes == true)) {
				$item->itunes->duration = $rec->entryduration;									
			}
		} else {
			$templink = "";
		}
	}
	
return $templink;

}			
?>