<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
function respondusws_restore_mods($mod, $restore)
{
    $ok = true; 
	if ($ok) {
        $data = backup_getid($restore->backup_unique_code, $mod->modtype,
		  $mod->id);
		$ok = ($data !== false);
	}
	if ($ok) {
        $info = $data->info;
        if ($restore->course_startdateoffset) {
            restore_log_date_changes("respondusws", $restore,
			  $info['MOD']['#'], array("TIMECREATED", "TIMEMODIFIED"));
		}
        $instance->course = $restore->course_id;
        $instance->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
        $instance->intro = backup_todb($info['MOD']['#']['INTRO']['0']['#']);
        $instance->introformat =
		  backup_todb($info['MOD']['#']['INTROFORMAT']['0']['#']);
        $instance->timecreated = $info['MOD']['#']['TIMECREATED']['0']['#'];
        $instance->timemodified = $info['MOD']['#']['TIMEMODIFIED']['0']['#'];
        $newid = insert_record ("respondusws", $instance);
		$ok = ($newid !== false);
        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>"
			  . get_string("modulename","respondusws")
			  . " \""
			  . format_string(stripslashes($instance->name), true)
			  . "\"</li>";
        }
        backup_flush(300);
	}
	if ($ok) {
        backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id,
		  $newid);
        if (restore_userdata_selected($restore, "respondusws", $mod->id)) {
        }
	}
    return $ok;
}
function respondusws_decode_content_links($content, $restore)
{
    global $CFG;
	$result = $content;
    $searchstring = '/\$@(RESPONDUSWSINDEX)\*([0-9]+)@\$/';
    preg_match_all($searchstring, $result, $foundset);
    if ($foundset[0]) {
		foreach($foundset[2] as $old_id) {
            $searchstring = '/\$@(RESPONDUSWSINDEX)\*(' . $old_id . ')@\$/';
			$rec = backup_getid($restore->backup_unique_code, "course",
			  $old_id);
            if($rec->new_id) {
                $result = preg_replace($searchstring,
				  $CFG->wwwroot
				    . '/mod/respondusws/index.php?id='
					. $rec->new_id,
				  $result);
            } else { 
                $result = preg_replace($searchstring,
				  $restore->original_wwwroot
				    . '/mod/respondusws/index.php?id='
					. $old_id,
				  $result);
            }
        }
    }
    $searchstring = '/\$@(RESPONDUSWSVIEWBYID)\*([0-9]+)@\$/';
    preg_match_all($searchstring, $result, $foundset);
    if ($foundset[0]) {
        foreach($foundset[2] as $old_id) {
            $searchstring = '/\$@(RESPONDUSWSVIEWBYID)\*(' . $old_id . ')@\$/';
            $rec = backup_getid($restore->backup_unique_code, "course_modules",
			  $old_id);
            if($rec->new_id) {
                $result = preg_replace($searchstring,
				  $CFG->wwwroot
				    . '/mod/respondusws/view.php?id='
					. $rec->new_id,
				  $result);
            } else { 
                $result = preg_replace($searchstring,
				  $restore->original_wwwroot
				    . '/mod/respondusws/view.php?id='
					. $old_id,
				  $result);
            }
        }
    }
	return $result;
}
function respondusws_decode_content_links_caller($restore)
{
    $ok = true; 
	if ($ok) {
		$instances = get_records("respondusws", "course",
		  $restore->course_id, "id");
	}
	if ($ok && $instances !== false) {
        $i = 0;
        foreach ($instances as $instance) {
            $i++;
			$update = false;
			$content = $instance->intro;
            $result = restore_decode_content_links_worker($content, $restore);
			if ($result != $content) {
                $instance->intro = addslashes($result);
				$update = true;
			}
			if ($update) {
                $ok = update_record("respondusws", $instance);
				if (!$ok)
					break;
                if ($ok && debugging()) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo "<br /><hr />"
						  . s($content)
						  . "<br />changed to<br />"
						  . s($result)
						  . "<hr /><br />";
                    }
                }
            }
            if (($i+1) % 5 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 100 == 0)
                        echo "<br />";
                }
                backup_flush(300);
            }
		}
	}
    return $ok;
}
function respondusws_restore_wiki2markdown($restore)
{
    $ok = true;
    return $ok;
}
function respondusws_restore_logs($restore, $log)
{
    $ok = true;
	switch ($log->action) {
	case "add":
	case "update":
	case "view":
        if ($log->cmid) {
            $record = backup_getid($restore->backup_unique_code, $log->module,
			  $log->info);
			$ok = ($record !== false);
            if ($ok) {
                $log->url = "view.php?id=$log->cmid";
                $log->info = $record->new_id;
            }
        }
        break;
	case "view all":
        $log->url = "index.php?id=$log->course";
        break;
	case "publish":
		if (strpos($log->info, "qcatid=") === 0) {
			$old_id = substr($log->info, 7);
			$record = backup_getid($restore->backup_unique_code,
			  "question_categories", $old_id);
			$ok = ($record !== false);
			if ($ok)
				$log->info = "qcatid=$record->new_id";
		}
		break;
	case "retrieve":
		if (strpos($log->info, "quizid=") === 0) {
			$old_id = substr($log->info, 7);
			$record = backup_getid($restore->backup_unique_code, "quiz",
			  $old_id);
			$ok = ($record !== false);
			if ($ok)
				$log->info = "quizid=$record->new_id";
		}
		else if (strpos($log->info, "qcatid=") === 0) {
			$old_id = substr($log->info, 7);
			$record = backup_getid($restore->backup_unique_code,
			  "question_categories", $old_id);
			$ok = ($record !== false);
			if ($ok)
				$log->info = "qcatid=$record->new_id";
		}
		break;
	default:
		$ok = false;
        if (!defined('RESTORE_SILENTLY')) {
            echo "action ("
			  . $log->module . "-" . $log->action
			  . ") unknown. Not restored<br />";
        }
        break;
    }
    if ($ok)
		return $log;
	else
		return false;
}
?>
