<?php
/// cron_stats_and_rollover.php
/// sbbf636, 11/10/2011
/// This script runs only the statistics and rollover jobs of Moodle.
/// This script exists because we didn't want to do stats or rollover
/// as part of the normal Moodle cronjob.
/// This script is basically the stats bits of <moodle>/admin/cron.php
/// and the rollover script previously in <moodle>/local/cron.php
///
/// This file is run by the script /usr/local/sbin/run-moodle_stats_and_rollover.

    set_time_limit(0);
    $starttime = microtime();

/// The following is a hack necessary to allow this script to work well
/// from the command line.

    define('FULLME', 'cron');


/// Do not set moodle cookie because we do not need it here, it is better to emulate session
    $nomoodlecookie = true;

/// The current directory in PHP version 4.3.0 and above isn't necessarily the
/// directory of the script when run from the command line. The require_once()
/// would fail, so we'll have to chdir()

    if (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['argv'][0])) {
        chdir(dirname($_SERVER['argv'][0]));
    }

    require_once(dirname(__FILE__) . '/../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/gradelib.php');

/// Extra debugging (set in config.php)
    if (!empty($CFG->showcronsql)) {
        $db->debug = true;
    }
    if (!empty($CFG->showcrondebugging)) {
        $CFG->debug = DEBUG_DEVELOPER;
        $CFG->debugdisplay = true;
    }

/// extra safety
    @session_write_close();

/// check if execution allowed
    if (isset($_SERVER['REMOTE_ADDR'])) { // if the script is accessed via the web.
        if (!empty($CFG->cronclionly)) {
            // This script can only be run via the cli.
            print_error('cronerrorclionly', 'admin');
            exit;
        }
        // This script is being called via the web, so check the password if there is one.
        if (!empty($CFG->cronremotepassword)) {
            $pass = optional_param('password', '', PARAM_RAW);
            if($pass != $CFG->cronremotepassword) {
                // wrong password.
                print_error('cronerrorpassword', 'admin');
                exit;
            }
        }
    }


/// emulate normal session
    $SESSION = new object();
    $USER = get_admin();      /// Temporarily, to provide environment for this script

/// ignore admins timezone, language and locale - use site deafult instead!
    $USER->timezone = $CFG->timezone;
    $USER->lang = '';
    $USER->theme = '';
    course_setup(SITEID);

/// send mime type and encoding
    if (check_browser_version('MSIE')) {
        //ugly IE hack to work around downloading instead of viewing
        @header('Content-Type: text/html; charset=utf-8');
        echo "<xmp>"; //<pre> is not good enough for us here
    } else {
        //send proper plaintext header
        @header('Content-Type: text/plain; charset=utf-8');
    }

/// no more headers and buffers
    while(@ob_end_flush());

/// increase memory limit (PHP 5.2 does different calculation, we need more memory now)
    @raise_memory_limit('128M');

/// Start output log

    $timenow  = time();

    mtrace("Server Time: ".date('r',$timenow)."\n\n");


  // Module Rollover Script

  // data in m_course_rollover
  //   COURSE_FROM      id of donor course, must exist
  //   COURSE_TO        id of destination course, must exist
  //   ROLLOVER_DATE    timestamp, date rollover is due
  //   ROLLOVER_STATUS  0 = done, 1 = to do
  //   EMPTY_FIRST      0 = no, 1 = yes
  //   COPY_ENROLMENTS  0 = no, 1 = yes
  //   COPY_CSS         0 = no, 1 = yes
  //   NEW_FULLNAME     new course fullname (optional)
  // check rollover table exists first!

  if (get_records_select("course_rollover","id > 0")) {

    // temp delete records
    /*
    if (delete_records_select("course_rollover","ID > 0")) {
      mtrace("Deleting old records in course_rollover");
    } else {
      mtrace("Can't delete old records in course_rollover");
    }
    */

    // query db to get modules for rollover
    if ($rollOver = get_records_select("course_rollover","( rollover_status=1 AND rollover_date < " . time() . " )","ID ASC","*")) {
      mtrace( "Looking for courses that need rollover ... ");
      include_once($CFG->dirroot . "/backup/lib.php");
      include_once($CFG->dirroot . "/backup/restorelib.php");
      include_once($CFG->dirroot . "/backup/backuplib.php");
      foreach ($rollOver AS $course) {
        $startTime = time();
        $rolloverSuccess = FALSE;
        $roError = '';
        $roMessage = '';
        $ro_to = '';
        $backupFile = '';
        $autoBackupDate = 0;
        $manualBackupDate = 0;


        // check donor course exists
        if  (!get_record("course","id",$course->course_from)) {
          // clear db messages
          set_field("course_rollover", "backup_source", '' , "id", $course->id);
          set_field("course_rollover", "error_msg", '' , "id", $course->id);


          if ($course->course_from == $course->course_to) {
            mtrace("   Attempting rollover ... (" . $course->course_from . ") -> (" . $course->course_to . ")");
            mtrace("      donor and recipient are the same AND they don't even exist!");
            set_field("course_rollover", "error_msg", 'donor and recipient are the same AND they don\'t even exist!' , "id", $course->id);
          } elseif (!get_record("course","id",$course->course_to)) {
            mtrace("   Attempting rollover ... (" . $course->course_from . ") -> (" . $course->course_to . ")");
            mtrace("      donor and recipient courses don't exist!");
            set_field("course_rollover", "error_msg", 'donor and recipient courses don\'t exist!' , "id", $course->id);
          } else {
            $ct = get_record("course","id",$course->course_to);
            mtrace("   Attempting rollover ... (" . $course->course_from . ") -> {$ct->shortname} (" . $ct->id . ")");
            mtrace("      donor course doesn't exist!");
            set_field("course_rollover", "error_msg", 'donor course doesn\'t exist!' , "id", $course->id);
          }
          $rolloverSuccess = FALSE;

        // check not trying to rollover into same course!
        } elseif ($course->course_from == $course->course_to) {
          $cf = get_record("course","id",$course->course_from);
          mtrace("   Attempting rollover ... " . $cf->shortname . " (" . $cf->id . ") -> " . $cf->shortname . " (" . $cf->id . ")");
          mtrace("      donor and recipient courses are the same!");
          set_field("course_rollover", "error_msg", 'donor and recipient courses are the same!' , "id", $course->id);
          $rolloverSuccess = FALSE;
mtrace( "Looking for courses that need rollover ... ");
        // check that ro_to course exists!
        } elseif (!get_record("course","id",$course->course_to)) {
          $cf = get_record("course","id",$course->course_from);
          mtrace("   Attempting rollover ... {$cf->shortname} (" . $cf->id . ") -> (" . $course->course_to . ")");
          mtrace("      recipient course doesn't exist!");
          set_field("course_rollover", "error_msg", 'recipient course doesn\'t exist!' , "id", $course->id);
          $rolloverSuccess = FALSE;

        // OK, it's all OK, lets go ahead
        } else {/// Unset session variables and destroy it
	@session_unset();
	@session_destroy();

	mtrace("Automated course backups cron script completed correctly");

	$difftime = microtime_diff($starttime, microtime());
	mtrace("Execution took ".$difftime." seconds");

/// finish the IE hack
	if (check_browser_version('MSIE')) {
		echo "</xmp>";
	}
          // get course records for donor and recipient courses
          $cf = get_record("course","id",$course->course_from);
          $ct = get_record("course","id",$course->course_to);
          mtrace("   Attempting rollover ... " . $cf->shortname . " (" . $cf->id . ") -> " . $ct->shortname . " (" . $ct->id . ")");

          // override all this by actually doing a backup first
          // this ensures that the backup will exist and is as current as it can be
          // and gets round timing issues over scheduled backup

          $prefs = array(
            'backup_users' => 1,
            'backup_logs' => 0,
            'backup_user_files' => 1,
            'backup_course_files' => 1,
            'backup_site_files' => 0,
            'userdata' => 1);
          if (backup_course_silently($cf->id,$prefs)) {
            mtrace("      creating new course backup file");
          } else {
            mtrace("      unable to create new course backup file");
          }

          /*
          // check for a scheduled backup first
          $backup_config = backup_get_config();
          $autoBackupDir = $backup_config->backup_sche_destination;
          //if (check_dir_exists($autoBackupDir)) {
            // get list of standard backup files
            $backupFiles = get_directory_list($autoBackupDir,'',TRUE,FALSE,TRUE);
            $autoBackupDate = '00000000-0000';
            // need to get latest one
            foreach ($backupFiles as $f) {
              // only get the backup for this course as all others are in list too
              // match on name
              if (strpos($f,strtolower($cf->shortname)) > 0) {
                // get date from filename $courseBackupDate
                $theDate = substr(substr($f,0,-4),-13);
                if ($theDate > $autoBackupDate) {
                  // just in case there are more than one - though this shouldn't happen
                  $backupFile =  $autoBackupDir . '/' . $f;
                }
                $autoBackupDate = $theDate;
              }
            }
            if ($backupFile != '') {
              mtrace ("      found latest auto backup: {$backupFile}");
            } else {
              mtrace ("      no auto backups found in: {$autoBackupDir}");
            }
          //}    */

          // what about user generated backup in course files area?
          // look for it and use this if it is more recent
          $courseBackupDir = $CFG->dataroot . "/" . $cf->id . "/backupdata";
          if (check_dir_exists($courseBackupDir)) {
            mtrace ("      user backup folder exists: {$courseBackupDir}");
            // get list of standard backup files
            $backupFiles = get_directory_list($courseBackupDir);
            $manualBackupDate = '';
            // need to get latest one
            foreach ($backupFiles as $f) {
              // check it is an official backup
              // check for shortname and date in zip filename starting with 'backup-'
              // e.g. backup-shortname-20110126-1225.zip
              if ( (strpos($f,str_replace(' ','_',strtolower($cf->shortname))) > 0) && (substr($f,0,7) == 'backup-') && (substr($f,-3) == 'zip') ) {
                // get date from filename $courseBackupDate
                $theDate = substr(substr($f,0,-4),-13);
                if (preg_match("/^20[0-9]{6}[\-][0-2][0-9][0-5][0-9]/",$theDate)) {
                  // only use this if it is more recent than scheduled backup
                  if ($theDate > $autoBackupDate) {
                    $backupFile =  $courseBackupDir . '/' . $f;
                    mtrace ("      found more recent user backup: {$backupFile}");
                  }
                  $manualBackupDate = $theDate;
                } else {
                  mtrace ("      found more recent user backup: {$backupFile} but date format ({$theDate}) invalid");
                }
              }
            }
            if ($manualBackupDate == '') mtrace ("      ... but no valid user backup files found");
          } else {
            mtrace ("      no user backup folder exists: {$courseBackupDir}");
          }

          // OK, so we think we've got a file to backup from, lets try it!
          if ($backupFile != '') {
            mtrace ("      using backup file: {$backupFile}");
            //import_backup_file_silently($zip,$dest,$emptyfirst,$user,array($prefs))
            // $user FALSE means no users or user data
            $emptyFirst = FALSE;
            if ($course->empty_first == 1) $emptyFirst = TRUE;
            // before we do this synchronise number of sections and format between donor and recipient course
            mtrace ("      synching number of sections to {$cf->numsections}");
            set_field("course", "numsections", $cf->numsections , "id", $ct->id);
            mtrace ("      synching course format to {$cf->format}");
            set_field("course", "format", $cf->format , "id", $ct->id);

            // just add backupfile to log table
            set_field("course_rollover", "backup_source", $backupFile , "id", $course->id);

            $prefs = array(
            //'restore_metacourse' => 0,
            'restore_logs' => 0,
            'restore_course_files' => 1,
            'restore_messages' => 0);

            if (import_backup_file_silently($backupFile,$course->course_to,$emptyFirst,FALSE,$prefs)) {
              $rolloverSuccess = TRUE;
              mtrace ("      import from backup has succeeded - NOTE Turnitin assignments are NOT restored");
              mtrace ("      recipient course " . ($course->empty_first == 1 ? 'was cleared first' : 'was not cleared'));
            } else {
              mtrace ("      there was a problem with import_backup_file_silently - see above");
              $rolloverSuccess = FALSE;
              // just add error_msg to log table
              set_field("course_rollover", "error_msg", "problem with import_backup_file_silently" , "id", $course->id);
            }
          }  else {
            mtrace ("      there is no backup file!");
            $rolloverSuccess = FALSE;
            // just add error_msg to log table
            set_field("course_rollover", "error_msg", "no valid backup file" , "id", $course->id);
          }
        }
        // status reporting
        if ($rolloverSuccess) {
          mtrace("      *** rollover was successful ***");
          // so, all is well, lets change rollover status to 0
          // and change name if necessary
          if (! set_field("course_rollover", "rollover_status", 0 , "id", $course->id)) {
            mtrace("      database could not be updated for some reason, course will keep rolling over!");
          } else {
            mtrace("      database has been updated, rollover status set to 0");
          }

          if ($course->new_fullname != '') {
            if (! set_field("course", "fullname", addslashes($course->new_fullname) , "id", $course->course_to)) {
              mtrace("      course name could not be changed to '{$course->new_fullname}'");
            } else {
              mtrace("      course name has been changed to '{$course->new_fullname}'");
            }
          } else {
            mtrace("      course name has not been changed");
          }

          // basic rollover has been done - course contents, layout
          // course files only restored if they are linked to in courses
          // so css files won't get carried over - need to do it manually
          // if requested (yes by default)

          if ($course->copy_css == 1) {
            if (check_dir_exists($CFG->dataroot . "/" . $course->course_from . "/css")) {
              if (backup_copy_dir($CFG->dataroot . "/" . $course->course_from . "/css",$CFG->dataroot . "/" . $course->course_to . "/css")) {
                mtrace("      CSS files exist and have been copied over");
              } else {
                mtrace("      CSS files exist but could not be copied over");
              }
            } else {
              mtrace("      CSS files were requested but not found");
            }
          } else {
            if (check_dir_exists($CFG->dataroot . "/" . $course->course_from . "/css")) {
              mtrace("      CSS files exist in donor course but not requested");
            } else {
              mtrace("      CSS files not required, they don't exist anyway");
            }
          }

          if ($course->copy_enrolments == 1) {
            // now, since rollover leaves behind all user data we must
            // copy role assignments for assistant teacher and above - not Student though
            // student enrolments should have been made via SITS

            // list roles to backup
            $rolesToBackup = array('observer','auditor','assistant','teacher','progadmin','elearnadmin','libraryadmin');
            // if any of these roles have assignments at the course context level for this course
            // then copy over assignments to recipient course

            // get donor course context
            $cfContext = get_record_sql("SELECT id FROM {$CFG->prefix}context WHERE contextlevel=50 and instanceid={$course->course_from}");
            // get recipient course context
            $ctContext = get_record_sql("SELECT id FROM {$CFG->prefix}context WHERE contextlevel=50 and instanceid={$course->course_to}");
            // no point going on context can't be found
            if (($cfContext->id > 0) && ($ctContext->id > 0)) {
              // loop through roles and look for role assignments
              foreach ($rolesToBackup as $r) {
                if ($role = get_record("role","shortname",$r)) {
                  // get assignments for this role in this course context
                  if ($roleass = get_records_sql("SELECT * FROM {$CFG->prefix}role_assignments WHERE contextid={$cfContext->id} AND ROLEID={$role->id}")) {
                    // now re-write them to role_assignments table but with new context id
                    foreach ($roleass as $ra) {
                      //mtrace($ra->userid.":".$role->id." ".$role->shortname." ".$cfContext->id."->".$ctContext->id);
                      role_assign($role->id,$ra->userid,FALSE,$ctContext->id,$ra->timestart,$ra->timeend,$ra->hidden,$ra->enrol,time());
                    }
                    mtrace("      copying role assignments for {$role->shortname} succeeded");
                  }
                }
              }
            } else {
              mtrace("      copying role assignments failed, one or both course contexts not found");
            }
          } else {
            mtrace("      copying of role assignments not required");
          }
        } else {
          mtrace("      *** rollover failed :-( ***");
          // so, set status to -1 to indicate problem in db table and prevent further attempts
          if (! set_field("course_rollover", "rollover_status", -1 , "id", $course->id)) {
            mtrace("      database could not be updated for some reason");
            mtrace("      rollover of this course will be attempted next time");
          } else {
            mtrace("      database has been updated, rollover status set to -1");
            mtrace("      rollover of this course won't be attempted again");
          }
        }
        $totalTime = time() - $startTime;
        mtrace ("      ... rollover took {$totalTime} secs");
      }
    } else {
      mtrace("Looking for courses that need rollover ... none found");
    }
  } else {
    mtrace("Tried rollover but couldn't find the table course_rollover or it is empty");
  }
  flush();
  

  // Stats generation
  if (!empty($CFG->enablestats) and empty($CFG->disablestatsprocessing)) {
        require_once($CFG->dirroot.'/lib/statslib.php');
        // check we're not before our runtime
        $timetocheck = stats_get_base_daily() + $CFG->statsruntimestarthour*60*60 + $CFG->statsruntimestartminute*60;

        if (time() > $timetocheck) {
            // process configured number of days as max (defaulting to 31)
            $maxdays = empty($CFG->statsruntimedays) ? 31 : abs($CFG->statsruntimedays);
            if (stats_cron_daily($maxdays)) {
                if (stats_cron_weekly()) {
                    if (stats_cron_monthly()) {
                        stats_clean_old();
                    }
                }
            }
            @set_time_limit(0);
        } else {
            mtrace('Next stats run after:'. userdate($timetocheck));
        }
    }

    /// Unset session variables and destroy it
	@session_unset();
	@session_destroy();

	mtrace("Stats and Rollover cron script completed correctly");

	$difftime = microtime_diff($starttime, microtime());
	mtrace("Execution took ".$difftime." seconds");

/// finish the IE hack
	if (check_browser_version('MSIE')) {
		echo "</xmp>";
	}