<?php

/// cron_automated-course-backups.php
/// sbbd864, 11/10/2010
/// This script runs only the automated course backups part of Moodle.
/// This script exists because we didn't want to do automated course backups
/// as part of the normal Moodle cronjob.
/// This script is basically the backup bits of <moodle>/admin/cron.php
///
/// This file is run by the script /usr/local/sbin/run-moodle-course-backups.

	set_time_limit(0);
	$starttime = microtime();

	define('FULLME', 'cron');

	$nomoodlecookie = true;

/// The current directory in PHP version 4.3.0 and above isn't necessarily the
/// directory of the script when run from the command line. The require_once()
/// would fail, so we'll have to chdir()

	if (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['argv'][0])) {
		chdir(dirname($_SERVER['argv'][0]));
	}

	require_once(dirname(__FILE__) . '/../config.php');
///	require_once($CFG->libdir.'/adminlib.php');
///	require_once($CFG->libdir.'/gradelib.php');

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
	@raise_memory_limit('1024M');

/// Start output log
	$timenow  = time();

	mtrace("Server Time: ".date('r',$timenow)."\n\n");

/// Now run the backups
	if (empty($CFG->disablescheduledbackups)) {   // Defined in config.php
		if (function_exists('apache_child_terminate')) {
			// if we are running from Apache, give httpd a hint that 
			// it can recycle the process after it's done. Apache's 
			// memory management is truly awful but we can help it.
			@apache_child_terminate();
		}
		if (file_exists("$CFG->dirroot/backup/backup_scheduled.php") and
				file_exists("$CFG->dirroot/backup/backuplib.php") and
				file_exists("$CFG->dirroot/backup/lib.php") and
				file_exists("$CFG->libdir/blocklib.php")) {
			include_once("$CFG->dirroot/backup/backup_scheduled.php");
			include_once("$CFG->dirroot/backup/backuplib.php");
			include_once("$CFG->dirroot/backup/lib.php");
			require_once ("$CFG->libdir/blocklib.php");
			mtrace("Running backups if required...");
    
			if (! schedule_backup_cron()) {
				mtrace("ERROR: Something went wrong while performing backup tasks!!!");
			} else {
				mtrace("Backup tasks finished.");
			}
		}
	}

/// Unset session variables and destroy it
	@session_unset();
	@session_destroy();

	mtrace("Automated course backups cron script completed correctly");

	$difftime = microtime_diff($starttime, microtime());
	mtrace("Execution took ".$difftime." seconds"); 

/// finish the IE hack
	if (check_browser_version('MSIE')) {
		echo "</xmp>";
	}

?>
