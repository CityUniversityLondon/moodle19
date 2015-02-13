<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
$RWSIGSLOG = FALSE;
$r_mcfg = dirname(dirname(dirname(__FILE__))) . "/config.php";
if (is_readable($r_mcfg))
    require_once($r_mcfg);
else  
	RWSSErr("2002");
$r_sf = TRUE;
if ($r_sf)
	$r_sf = is_readable("$CFG->dirroot/version.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/moodlelib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/datalib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/accesslib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/dmllib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/questionlib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/grouplib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->libdir/gradelib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->dirroot/mod/quiz/lib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->dirroot/course/lib.php");
if ($r_sf)
	$r_sf = is_readable("$CFG->dirroot/mod/quiz/editlib.php");
if (!$r_sf) {
	RWSSErr("2003");
}
require_once("$CFG->dirroot/version.php");
require_once("$CFG->libdir/moodlelib.php");
require_once("$CFG->libdir/datalib.php");
require_once("$CFG->libdir/accesslib.php");
require_once("$CFG->libdir/dmllib.php");
require_once("$CFG->libdir/questionlib.php");
require_once("$CFG->libdir/grouplib.php");
require_once("$CFG->libdir/gradelib.php");
require_once("$CFG->dirroot/mod/quiz/lib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/mod/quiz/editlib.php");
$RWSLB = new stdClass();
$RWSLB->atts = 0; 
$RWSLB->revs = 0; 
$RWSLB->pw = ""; 
$RWSLB->mok = FALSE; 
$RWSLB->bok = FALSE; 
$RWSLB->gerr = FALSE; 
$RWSLB->perr = FALSE; 
$RWSLB->mex = 
  is_readable("$CFG->dirroot/mod/lockdown/locklib.php");
$RWSLB->bex = 
  is_readable("$CFG->dirroot/blocks/lockdownbrowser/locklib.php");
if ($RWSLB->mex) {
	include_once("$CFG->dirroot/mod/lockdown/locklib.php");
	$RWSLB->mok = lockdown_module_status();
} else if ($RWSLB->bex) {
	include_once("$CFG->dirroot/blocks/lockdownbrowser/locklib.php");
	$RWSLB->bok = (!empty($CFG->customscripts)
	  && is_readable("$CFG->customscripts/mod/quiz/attempt.php")
	  && count_records("block_lockdownbrowser_tokens") > 0);
}
define("RWSATT", "rwsatt");
define("RWSRSV", "rwsrsv");
define("RWSUNK", "rwsunk");
define("RWSRXP", "regexp");
function RWSRHCom()
{
	header("Cache-Control: private, must-revalidate"); 
	header("Expires: -1");
	header("Pragma: no-cache");
}
function RWSRHXml()
{
	RWSRHCom();
	header("Content-Type: text/xml");
}
function RWSRHBin($r_fn, $r_clen)
{
	RWSRHCom();
	header("Content-Type: application/octet-stream");
	header("Content-Length: " . $r_clen);
	header(
	  "Content-Disposition: attachment; filename=\""
	  . htmlspecialchars(trim($r_fn)) . "\""
	  );
	header("Content-Transfer-Encoding: binary");
}
function RWSSWarn($r_wm)
{
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<service_warning>";
	if (!empty($r_wm))
		echo utf8_encode(htmlspecialchars($r_wm));
	else
		echo "3004"; 
	echo "</service_warning>\r\n";
	exit;
}
function RWSSStat($r_sm)
{
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<service_status>";
	if (!empty($r_sm))
		echo utf8_encode(htmlspecialchars($r_sm));
	else
		echo "1007"; 
	echo "</service_status>\r\n";
	exit;
}
function RWSSErr($r_errm)
{
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<service_error>";
	if (!empty($r_errm))
		echo utf8_encode(htmlspecialchars($r_errm));
	else
		echo "2004"; 
	echo "</service_error>\r\n";
	exit;
}
function RWSLMUser()
{
	require_logout();
}
function RWSCMBVer()
{
	$r_rv = RWSGSOpt("version");
	if ($r_rv === FALSE || strlen($r_rv) == 0) {
		return;
	}
	$r_bv = intval($r_rv);
	if ($r_bv == 2009093000		
	  || $r_bv == 2010042801	
	  || $r_bv == 2010063001	
	  || $r_bv == 2010063002	
	  ) {
		return; 
	}
	RWSSErr("2106");
}
function RWSCMVer()
{
	global $CFG;
	$r_req = "";
	$r_vf = RWSGMPath() . "/version.php";
	if (is_readable($r_vf))
		include($r_vf);
	if ($module) {
		if (!empty($module->requires))
			$r_req = $module->requires;
	}
	if (empty($r_req)) {
		RWSSErr("2005");
	}
	$r_res = RWSFCmp($CFG->version, $r_req, 2);
	if ($r_res == -1) {
		RWSSErr("2006,$CFG->version,$r_req");
	}
	else if ($r_res == 1) {
	}
}
function RWSCMInst()
{
	$r_ins = get_records("respondusws", "course", SITEID, "id");
	$r_ok = ($r_ins !== false);
	if ($r_ok)
		$r_ok = (count($r_ins) == 1);
	if (!$r_ok) {
		RWSSErr("2007");
	}
}
function RWSATLog($r_cid, $r_ac, $r_inf="")
{
	add_to_log($r_cid, "respondusws", $r_ac,
	 "index.php?id=$r_cid", $r_inf);
}
function RWSGMPath()
{
	$r_mp = dirname(__FILE__); 
	if (DIRECTORY_SEPARATOR != '/') 
	  $r_mp = str_replace('\\', '/', $r_mp);
	return $r_mp;
}
function RWSGTPath()
{
	global $CFG;
	$r_tp = "$CFG->dataroot/temp";
	return $r_tp;
}
function RWSGSUrl()
{
	$r_hs = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE);
	if ($r_hs)
		$r_su = 'https://';
	else
		$r_su = 'http://';
	$r_su .= $_SERVER['HTTP_HOST'];
	$r_su .= $_SERVER['PHP_SELF'];
	return $r_su;
}
function RWSAMUser($r_usr, $r_pw)
{
	$r_nm = addslashes($r_usr);
	$r_ps = addslashes($r_pw);
	$r_user = authenticate_user_login($r_nm, $r_ps);
	if ($r_user)
		complete_user_login($r_user);
	if (!isloggedin())
		RWSSErr("2008"); 
}
function RWSCMMaint()
{
	global $CFG;
	$r_sctx = get_context_instance(CONTEXT_SYSTEM);
	if (has_capability("moodle/site:doanything", $r_sctx))
		return;
    if (has_capability("moodle/site:config", $r_sctx))
		return;
	if (file_exists($CFG->dataroot . "/" . SITEID . "/maintenance.html")) {
		RWSSErr("2009");
	}
}
function RWSCMAuth()
{
	if (!isloggedin()) {
		RWSSErr("2010");
	}
}
function RWSCMUCourse($r_cid, $r_cqa=FALSE)
{
	$r_rcd = get_record("course", "id", $r_cid);
	if (!$r_rcd) 
		RWSSErr("2011");
	if ($r_cqa && !course_allowed_module($r_rcd, "quiz")) {
		RWSSErr("2012");
	}
	if (!RWSIUMCourse($r_cid)) {
		RWSSErr("2013");
	}
	return $r_rcd; 
}
function RWSCMUQuiz($r_qzi)
{
	$r_rcd = get_record("course_modules", "id", $r_qzi);
	if (!$r_rcd)
		RWSSErr("2014"); 
	if (!RWSIUMQuiz($r_qzi)) {
		RWSSErr("2015");
	}
	return $r_rcd; 
}
function RWSGUQCats($r_cid)
{
	$r_ctxi = array();
	$r_bv = intval(RWSGSOpt("version"));
	if ($r_bv >= 2010063001) { 
		$r_sctx = get_context_instance(CONTEXT_SYSTEM);
		if (has_capability("moodle/site:doanything", $r_sctx)
		  || has_capability("moodle/site:config", $r_sctx)) {
			$r_ctxi[] = $r_sctx->id;
		}
	}
	$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
	$r_ctxi[] = $r_ctx->id;
	$r_qzs = RWSGUVQList($r_cid);
	if (count($r_qzs) > 0) {
		foreach ($r_qzs as $r_q) {
			$r_ctx = get_context_instance(CONTEXT_MODULE, $r_q->id);
			if (!in_array($r_ctx->id, $r_ctxi))
				$r_ctxi[] = $r_ctx->id;
		}
	}
	if (count($r_ctxi) == 0) {
		return array();
	}
	else if (count($r_ctxi) == 1) {
		$r_qcs = get_categories_for_contexts($r_ctxi[0]);
		if (!$r_qcs)
			return array();
	}
	else {
		$r_ctxl = join($r_ctxi, ", ");
		$r_qcs = get_categories_for_contexts($r_ctxl);
		if (!$r_qcs)
			return array();
	}
	return $r_qcs;
}
function RWSGUVSList($r_cid)
{
	$r_vs = array();
	$r_secs = get_all_sections($r_cid);
	if (!$r_secs || count($r_secs) == 0)
		return $r_vs;
	$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
	$r_vh = has_capability("moodle/course:viewhiddensections", $r_ctx);
	if (!$r_vh) { 
		$r_sctx = get_context_instance(CONTEXT_SYSTEM);
		$r_vh = has_capability("moodle/site:doanything", $r_sctx);
		if (!$r_vh)
			$r_vh = has_capability("moodle/site:config", $r_sctx);
	}
	foreach ($r_secs as $r_s) {
		if ($r_s->visible || $r_vh)
			$r_vs[] = $r_s;
	}
	return $r_secs;
}
function RWSGUVQList($r_cid)
{
	$r_vq = array();
	$r_qzs = get_coursemodules_in_course("quiz", $r_cid);
	if (!$r_qzs || count($r_qzs) == 0)
		return $r_vq;
	foreach ($r_qzs as $r_q) {
		if (coursemodule_visible_for_user($r_q))
			$r_vq[] = $r_q;
    }
	return $r_vq;
}
function RWSGUMQList($r_qzs)
{
	$r_mq = array();
	if (!$r_qzs || count($r_qzs) == 0)
		return $r_mq;
	foreach ($r_qzs as $r_q) {
		if (RWSIUMQuiz($r_q->id))
			$r_mq[] = $r_q;
    }
	return $r_mq;
}
function RWSIUMQuiz($r_qzi)
{
	$r_ctx = get_context_instance(CONTEXT_MODULE, $r_qzi);
	$r_ok = $r_ctx;
	if ($r_ok)
		$r_ok = has_capability("mod/quiz:view", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("mod/quiz:preview", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("mod/quiz:manage", $r_ctx);
	if (!$r_ok) {
		$r_sctx = get_context_instance(CONTEXT_SYSTEM);
		$r_ok = has_capability("moodle/site:doanything", $r_sctx);
		if (!$r_ok)
			$r_ok = has_capability("moodle/site:config", $r_sctx);
	}
	return $r_ok;
}
function RWSGUMCList()
{
    global $USER;
	$r_mc = array();
	$r_crss = get_my_courses($USER->id, NULL, NULL, TRUE);
	if (!$r_crss || count($r_crss) == 0)
		return $r_mc;
    if (array_key_exists(SITEID, $r_crss))
        unset($r_crss[SITEID]);
	if (count($r_crss) == 0)
		return $r_mc;
	foreach ($r_crss as $r_c) {
		if (RWSIUMCourse($r_c->id))
			$r_mc[] = $r_c;
    }
	return $r_mc;
}
function RWSCMUSvc()
{
}
function RWSIUMCourse($r_cid)
{
	$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
	$r_ok = $r_ctx;
	if ($r_ok)
		$r_ok = has_capability("moodle/site:viewfullnames", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:activityvisibility", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:view", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:viewhiddencourses", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:viewhiddenactivities", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:viewhiddensections", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:update", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:manageactivities", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/course:managefiles", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:managecategory", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:add", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:editmine", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:editall", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:viewmine", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:viewall", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:usemine", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:useall", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:movemine", $r_ctx);
	if ($r_ok)
		$r_ok = has_capability("moodle/question:moveall", $r_ctx);
	if (!$r_ok) {
		$r_sctx = get_context_instance(CONTEXT_SYSTEM);
		$r_ok = has_capability("moodle/site:doanything", $r_sctx);
		if (!$r_ok)
			$r_ok = has_capability("moodle/site:config", $r_sctx);
	}
	return $r_ok;
}
function RWSGSOpt($r_nm)
{
	if (isset($_POST[$r_nm])) {
		if (get_magic_quotes_gpc())
			return stripslashes_safe($_POST[$r_nm]);
		else
			return $_POST[$r_nm];
	}
	if (isset($_FILES[$r_nm]))
	{
		if ($_FILES[$r_nm]['error'] == UPLOAD_ERR_OK )
		{
			$r_fl = new stdClass();
			$r_fl->filename = $_FILES[$r_nm]['name'];
			$r_fl->filedata = file_get_contents($_FILES[$r_nm]['tmp_name']);
			return $r_fl;
		}
	}
	return FALSE;
}
function RWSSQDLocal(&$r_qz)
{
	$r_qz->intro = ""; 
	$r_qz->timeopen = 0; 
	$r_qz->timeclose = 0; 
	$r_qz->timelimitenable = 0; 
	$r_qz->timelimit = 0; 
	$r_qz->delay1 = 0; 
	$r_qz->delay2 = 0; 
	$r_qz->questionsperpage = 0; 
	$r_qz->shufflequestions = 0; 
	$r_qz->shuffleanswers = 1; 
	$r_qz->attempts = 0; 
	$r_qz->attemptonlast = 0; 
	$r_qz->adaptive = 1; 
	$r_qz->grade = 10; 
	$r_qz->grademethod = 1; 
	$r_qz->penaltyscheme = 1; 
	$r_qz->decimalpoints = 2; 
	$r_qz->responsesimmediately = 1;		
	$r_qz->answersimmediately = 1;			
	$r_qz->feedbackimmediately = 1;			
	$r_qz->generalfeedbackimmediately = 1;	
	$r_qz->scoreimmediately = 1;			
	$r_qz->overallfeedbackimmediately = 1;	
	$r_qz->responsesopen = 1;		
	$r_qz->answersopen = 1;			
	$r_qz->feedbackopen = 1;		
	$r_qz->generalfeedbackopen = 1;	
	$r_qz->scoreopen = 1;			
	$r_qz->overallfeedbackopen = 1;	
	$r_qz->responsesclosed = 1;				
	$r_qz->answersclosed = 1;				
	$r_qz->feedbackclosed = 1;				
	$r_qz->generalfeedbackclosed = 1;		
	$r_qz->scoreclosed = 1;					
	unset($r_qz->overallfeedbackclosed);	
	$r_qz->popup = 0; 
	$r_qz->quizpassword = ""; 
	$r_qz->subnet = ""; 
	$r_qz->groupmode = 0; 
	$r_qz->visible = 1; 
	$r_qz->cmidnumber = ""; 
	$r_qz->gradecat = 1; 
	$r_qz->feedbacktext[0] = "";		
	$r_qz->feedbackboundaries[0] = "";	
	$r_qz->feedbacktext[1] = "";
	$r_qz->feedbackboundaries[1] = "";
	$r_qz->feedbacktext[2] = "";
	$r_qz->feedbackboundaries[2] = "";
	$r_qz->feedbacktext[3] = "";
	$r_qz->feedbackboundaries[3] = "";
	$r_qz->feedbacktext[4] = "";
}
function RWSSQDMoodle(&$r_qz)
{
	global $CFG;
	$r_qz->intro = ""; 
	$r_qz->timeopen = 0;  
	$r_qz->timeclose = 0; 
	if ($CFG->quiz_timelimit != 0)
		$r_qz->timelimitenable = 1;
	else
		$r_qz->timelimitenable = 0;
	$r_qz->timelimit = $CFG->quiz_timelimit;
	$r_qz->delay1 = $CFG->quiz_delay1;
	$r_qz->delay2 = $CFG->quiz_delay2;
	$r_qz->questionsperpage = $CFG->quiz_questionsperpage;
	$r_qz->shufflequestions = $CFG->quiz_shufflequestions;
	$r_qz->shuffleanswers = $CFG->quiz_shuffleanswers;
	$r_qz->attempts = $CFG->quiz_attempts;
	$r_qz->attemptonlast = $CFG->quiz_attemptonlast;
	$r_qz->adaptive = $CFG->quiz_optionflags & QUESTION_ADAPTIVE;
	$r_qz->grade = $CFG->quiz_maximumgrade;
	switch ($CFG->quiz_grademethod) {
	case 1: 
	case 2: 
	case 3: 
	case 4: 
		$r_qz->grademethod = $CFG->quiz_grademethod;
		break;
	default:
		$r_qz->grademethod = 1;
		break;
	}
	$r_qz->penaltyscheme = $CFG->quiz_penaltyscheme;
	$r_qz->decimalpoints = $CFG->quiz_decimalpoints;
	$r_qz->responsesimmediately = $CFG->quiz_review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->responsesimmediately)
		unset($r_qz->responsesimmediately);
	$r_qz->answersimmediately = $CFG->quiz_review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->answersimmediately)
		unset($r_qz->answersimmediately);
	$r_qz->feedbackimmediately = $CFG->quiz_review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->feedbackimmediately)
		unset($r_qz->feedbackimmediately);
	$r_qz->generalfeedbackimmediately = $CFG->quiz_review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->generalfeedbackimmediately)
		unset($r_qz->generalfeedbackimmediately);
	$r_qz->scoreimmediately = $CFG->quiz_review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->scoreimmediately)
		unset($r_qz->scoreimmediately);
	$r_qz->overallfeedbackimmediately = $CFG->quiz_review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_IMMEDIATELY;
	if (!$r_qz->overallfeedbackimmediately)
		unset($r_qz->overallfeedbackimmediately);
	$r_qz->responsesopen = $CFG->quiz_review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_OPEN;
	if (!$r_qz->responsesopen)
		unset($r_qz->responsesopen);
	$r_qz->answersopen = $CFG->quiz_review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_OPEN;
	if (!$r_qz->answersopen)
		unset($r_qz->answersopen);
	$r_qz->feedbackopen = $CFG->quiz_review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_OPEN;
	if (!$r_qz->feedbackopen)
		unset($r_qz->feedbackopen);
	$r_qz->generalfeedbackopen = $CFG->quiz_review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_OPEN;
	if (!$r_qz->generalfeedbackopen)
		unset($r_qz->generalfeedbackopen);
	$r_qz->scoreopen = $CFG->quiz_review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_OPEN;
	if (!$r_qz->scoreopen)
		unset($r_qz->scoreopen);
	$r_qz->overallfeedbackopen = $CFG->quiz_review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_OPEN;
	if (!$r_qz->overallfeedbackopen)
		unset($r_qz->overallfeedbackopen);
	$r_qz->responsesclosed = $CFG->quiz_review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->responsesclosed)
		unset($r_qz->responsesclosed);
	$r_qz->answersclosed = $CFG->quiz_review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->answersclosed)
		unset($r_qz->answersclosed);
	$r_qz->feedbackclosed = $CFG->quiz_review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->feedbackclosed)
		unset($r_qz->feedbackclosed);
	$r_qz->generalfeedbackclosed = $CFG->quiz_review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->generalfeedbackclosed)
		unset($r_qz->generalfeedbackclosed);
	$r_qz->scoreclosed = $CFG->quiz_review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->scoreclosed)
		unset($r_qz->scoreclosed);
	$r_qz->overallfeedbackclosed = $CFG->quiz_review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_CLOSED;
	if (!$r_qz->overallfeedbackclosed)
		unset($r_qz->overallfeedbackclosed);
	$r_qz->popup = $CFG->quiz_popup;
	$r_qz->quizpassword = $CFG->quiz_password;
	$r_qz->subnet = $CFG->quiz_subnet;
	$r_qz->groupmode = 0;	
	$r_qz->visible = 1;		
	$r_qz->cmidnumber = "";	
	$r_qz->gradecat = 1;	
	$r_qz->feedbacktext[0] = "";		
	$r_qz->feedbackboundaries[0] = "";	
	$r_qz->feedbacktext[1] = "";		
	$r_qz->feedbackboundaries[1] = "";	
	$r_qz->feedbacktext[2] = "";		
	$r_qz->feedbackboundaries[2] = "";	
	$r_qz->feedbacktext[3] = "";		
	$r_qz->feedbackboundaries[3] = "";	
	$r_qz->feedbacktext[4] = "";		
}
function RWSSQDefs(&$r_qz, $r_po=FALSE)
{
	global $RWSLB;
		RWSSQDMoodle($r_qz);
	$RWSLB->atts = 0; 
	$RWSLB->revs = 0; 
	$RWSLB->pw = ""; 
	if ($r_po)
		quiz_process_options($r_qz);
}
function RWSIQSet(&$r_qz, $r_sfl, $r_sd, $r_ecd, $r_po=FALSE)
{
	$r_clnid = FALSE;
	$r_clnif = FALSE;
	$r_cloif = FALSE;
	if ($r_ecd) {
		$r_dcd = base64_decode($r_sd);
		if ($r_dcd === FALSE) {
			RWSSErr("2017");
		}
	}
	else { 
		$r_dcd = $r_sd;
	}
	$r_imd = RWSMTFldr();
	$r_ok = ($r_imd !== FALSE);
	$r_clnid = $r_ok;
	if (!$r_ok) 
		$r_err = "2018";
	if ($r_ok) {
		$r_ok = RWSDIData($r_dcd, $r_imd);
		if (!$r_ok) 
			$r_err = "2019";
	}
	if ($r_ok) {
		$r_p = strrpos($r_sfl, ".");
		$r_ok = ($r_p !== 0);
		if (!$r_ok) 
			$r_err = "2020"; 
	}
	if ($r_ok) {
		$r_imf = "$r_imd/";
		if ($r_p === FALSE) 
			$r_imf .= $r_sfl;
		else 
			$r_imf .= substr($r_sfl, 0, $r_p);
		$r_imf .= ".dat";
		$r_ok = file_exists($r_imf);
		$r_clnif = $r_ok;
		if (!$r_ok)
			$r_err = "2020"; 
	}
	if ($r_ok) {
		$r_hdl = fopen($r_imf, "rb");
		$r_ok = ($r_hdl !== FALSE);
		$r_cloif = $r_ok;
		if (!$r_ok)
			$r_err = "2021"; 
	}
	if ($r_ok) {
		$r_ok = RWSCSFSig($r_hdl);
		if (!$r_ok)
			$r_err = "2022"; 
	}
	if ($r_ok) {
		$r_ok = RWSCSFVer($r_hdl);
		if (!$r_ok)
			$r_err = "2023"; 
	}	
	if ($r_ok) {
		$r_rcd = RWSRSRec($r_hdl);
		$r_ok = ($r_rcd !== FALSE);
		if (!$r_ok)
			$r_err = "2024"; 
	}
	if ($r_ok) {
		$r_ok = RWSISRec($r_qz, $r_rcd, $r_po);
		if (!$r_ok)
			$r_err = "2025"; 
	}
	if ($r_cloif)
		fclose($r_hdl);
	if ($r_clnif)
		unlink($r_imf);
	if ($r_clnid)
		rmdir($r_imd);
	if (!$r_ok)
		RWSSErr($r_err);
}
function RWSEQSet($r_qz, &$r_sfl, $r_w64)
{
		$r_fv = 0; 
	$r_fnc = "rwsexportsdata.zip";
	$r_fnu = "rwsexportsdata.dat";
	$r_sfl = "";
	$r_clned = FALSE;
	$r_clnef = FALSE;
	$r_clncf = FALSE;
	$r_cloef = FALSE;
	$r_ok = TRUE;
	if ($r_ok) {
		$r_exd = RWSMTFldr();
		$r_ok = ($r_exd !== FALSE);
		$r_clned = $r_ok;
		if (!$r_ok) 
			$r_err = "2026";
	}
	if ($r_ok) {
		$r_exf = "$r_exd/$r_fnu";
		$r_hdl = fopen($r_exf, "wb"); 
		$r_ok = ($r_hdl !== FALSE);
		$r_clnef = $r_ok;
		$r_cloef = $r_ok;
		if (!$r_ok)
			$r_err = "2027"; 
	}
	if ($r_ok) {
			$r_dat = pack("C*", 0x21, 0xfd, 0x65, 0x0d, 0x6e, 0xae, 0x4d, 0x01,
			  0x86, 0x78, 0xf5, 0x13, 0x00, 0x86, 0x99, 0x2a);
		$r_dat .= pack("n", $r_fv);
		$r_by = fwrite($r_hdl, $r_dat);
		$r_ok = ($r_by !== FALSE);
		if (!$r_ok)
			$r_err = "2028"; 
	}
	if ($r_ok) {
		$r_rcd = RWSESRec($r_qz);
		$r_ok = ($r_rcd !== FALSE);
		if (!$r_ok)
			$r_err = "2029"; 
    }
	if ($r_ok) {
		$r_ok = RWSWSRec($r_hdl, $r_rcd);
		if (!$r_ok)
			$r_err = "2028"; 
	}
	if ($r_cloef)
		fclose($r_hdl);
	if ($r_ok) {
		$r_cf = "$r_exd/$r_fnc";
		$r_ok = RWSCEData($r_exf, $r_cf);
		$r_clncf = $r_ok;
		if (!$r_ok)
			$r_err = "2031"; 
	}
	if ($r_ok) {
		$r_cpr = file_get_contents($r_cf);
		$r_ok = ($r_cpr !== FALSE);
		if (!$r_ok)
			$r_err = "2032"; 
	}
	if ($r_ok && $r_w64)
		$r_ecd = base64_encode($r_cpr);
	if ($r_clnef)
		unlink($r_exf);
	if ($r_clncf)
		unlink($r_cf);
	if ($r_clned)
		rmdir($r_exd);
	if (!$r_ok)
		RWSSErr($r_err);
	$r_sfl = $r_fnc;
	if ($r_w64)
		return $r_ecd;
	else
		return $r_cpr;
}
function RWSIQues($r_cid, $r_qci, $r_qfl, $r_qd, $r_ecd, &$r_drp, &$r_ba)
{
	$r_imp = 0;
	$r_drp = 0;
	$r_ba = 0;
	$r_br = 0;
	$r_clnid = FALSE;
	$r_clnif = FALSE;
	$r_cloif = FALSE;
	if ($r_ecd) {
		$r_dcd = base64_decode($r_qd);
		if ($r_dcd === FALSE) {
			RWSSErr("2033");
		}
	}
	else { 
		$r_dcd = $r_qd;
	}
	$r_imd = RWSMTFldr();
	$r_ok = ($r_imd !== FALSE);
	$r_clnid = $r_ok;
	if (!$r_ok)
		$r_err = "2034"; 
	if ($r_ok) {
		$r_ok = RWSDIData($r_dcd, $r_imd);
		if (!$r_ok)
			$r_err = "2035"; 
	}
	if ($r_ok) {
		$r_p = strrpos($r_qfl, ".");
		$r_ok = ($r_p !== 0);
		if (!$r_ok) 
			$r_err = "2036"; 
	}
	if ($r_ok) {
		$r_imf = "$r_imd/";
		if ($r_p === FALSE) 
			$r_imf .= $r_qfl;
		else 
			$r_imf .= substr($r_qfl, 0, $r_p);
		$r_imf .= ".dat";
		$r_ok = file_exists($r_imf);
		$r_clnif = $r_ok;
		if (!$r_ok)
			$r_err = "2036"; 
	}
	if ($r_ok) {
		$r_hdl = fopen($r_imf, "rb");
		$r_ok = ($r_hdl !== FALSE);
		$r_cloif = $r_ok;
		if (!$r_ok)
			$r_err = "2037"; 
	}
	if ($r_ok) {
		$r_ok = RWSCQFSig($r_hdl);
		if (!$r_ok)
			$r_err = "2038"; 
	}
	if ($r_ok) {
		$r_ok = RWSCQFVer($r_hdl);
		if (!$r_ok)
			$r_err = "2039"; 
	}	
	if ($r_ok) {
		$r_qsti = array();
		$r_rcd = RWSRNQRec($r_hdl);
		while ($r_rcd !== FALSE) {
			$r_typ = RWSGQRType($r_rcd);
			switch ($r_typ) {
			case RWSATT:
				$r_sbp = RWSIARec($r_cid, $r_qci, $r_rcd);
				break;
			case SHORTANSWER:
				$r_qi = RWSISARec($r_cid, $r_qci, $r_rcd);
				break;
			case TRUEFALSE:
				$r_qi = RWSITFRec($r_cid, $r_qci, $r_rcd);
				break;
			case MULTICHOICE:
				$r_qi = RWSIMCRec($r_cid, $r_qci, $r_rcd);
				break;
			case MATCH:
				$r_qi = RWSIMRec($r_cid, $r_qci, $r_rcd);
				break;
			case DESCRIPTION:
				$r_qi = RWSIDRec($r_cid, $r_qci, $r_rcd);
				break;
			case ESSAY:
				$r_qi = RWSIERec($r_cid, $r_qci, $r_rcd);
				break;
			case CALCULATED:
				$r_qi = RWSICRec($r_cid, $r_qci, $r_rcd);
				break;
			case MULTIANSWER: 
				$r_qi = RWSIMARec($r_cid, $r_qci, $r_rcd);
				break;
			case RWSRSV:
				$r_res = RWSIRRec($r_cid, $r_qci, $r_rcd);
				break;
			case RANDOM:
			case NUMERICAL:
			case RANDOMSAMATCH:
			case RWSUNK:
			default:
				$r_qi = FALSE;
				break;
			}
			if ($r_typ == RWSATT) {
				if ($r_sbp === FALSE)
					$r_ba++;
			}
			else if ($r_typ == RWSRSV) {
				if ($r_res === FALSE)
					$r_br++;
			}
			else { 
				if ($r_qi === FALSE)
					$r_drp++;
				else {
					$r_imp++;
					$r_qsti[] = $r_qi;
				}
			}
			$r_rcd = RWSRNQRec($r_hdl);
		}
	}
	if ($r_cloif)
		fclose($r_hdl);
	if ($r_clnif)
		unlink($r_imf);
	if ($r_clnid)
		rmdir($r_imd);
	if (!$r_ok)
		RWSSErr($r_err);
	if ($r_imp == 0) {
		if ($r_drp == 0) 
			RWSSErr("2040");
		else 
			RWSSErr("2041");
	}
	return $r_qsti;
}
function RWSCQFSig($r_hdl)
{
	$r_es =	array(0xe1, 0x8a, 0x3b, 0xaf, 0xd0, 0x30, 0x4d, 0xce,
	  0xb4, 0x75, 0x8a, 0xdf, 0x1e, 0xa9, 0x08, 0x36);
	if (feof($r_hdl))
		return FALSE;
	$r_bf = fread($r_hdl, 16);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_as = array_values(unpack("C*", $r_bf));
	$r_ct = count($r_es);
	if ($r_ct != count($r_as))
		return FALSE;
	for($r_i = 0; $r_i < count; $r_i++) {
		if ($r_as[$r_i] != $r_es[$r_i])
			return FALSE;		
	}
	return TRUE;
}
function RWSCSFSig($r_hdl)
{
	$r_es =	array(0x07, 0x0b, 0x28, 0x3a, 0x98, 0xfa, 0x4c, 0xcd,
	  0x8a, 0x62, 0x14, 0xa7, 0x97, 0x33, 0x84, 0x37);
	if (feof($r_hdl))
		return FALSE;
	$r_bf = fread($r_hdl, 16);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_as = array_values(unpack("C*", $r_bf));
	$r_ct = count($r_es);
	if ($r_ct != count($r_as))
		return FALSE;
	for($r_i = 0; $r_i < count; $r_i++) {
		if ($r_as[$r_i] != $r_es[$r_i])
			return FALSE;		
	}
	return TRUE;
}
function RWSCQFVer($r_hdl)
{
	$r_ev = 0; 
	if (feof($r_hdl))
		return FALSE;
	$r_bf = fread($r_hdl, 2);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_dat = unpack("n", $r_bf);
	$r_av = $r_dat[1];
	if ($r_av == $r_ev)
		return TRUE;
	else
		return FALSE;
}
function RWSCSFVer($r_hdl)
{
	$r_ev = 0; 
	if (feof($r_hdl))
		return FALSE;
	$r_bf = fread($r_hdl, 2);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_dat = unpack("n", $r_bf);
	$r_av = $r_dat[1];
	if ($r_av == $r_ev)
		return TRUE;
	else
		return FALSE;
}
function RWSRSRec($r_hdl)
{
	if (feof($r_hdl))
		return FALSE;
	$r_cpos = ftell($r_hdl);
	if(fseek($r_hdl, 0, SEEK_END) != 0)
		return FALSE;
	$r_ep = ftell($r_hdl);
	$r_sz = $r_ep - $r_cpos;
	if(fseek($r_hdl, $r_cpos, SEEK_SET) != 0)
		return FALSE;
	$r_rcd = fread($r_hdl, $r_sz);
	if ($r_rcd === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	for ($r_i = 0; $r_i < $r_sz; $r_i++) {
		$r_dat = unpack("C", $r_rcd[$r_i]);
		$r_n = (intval($r_dat[1]) ^ 0x55) - 1;
		if ($r_n < 0)
			$r_n = 255;
		$r_rcd[$r_i] = pack("C", $r_n);
	}
	return $r_rcd;
}
function RWSWSRec($r_hdl, $r_rcd)
{
	$r_ok = TRUE;
	$r_l = strlen($r_rcd);
	for ($r_i = 0; $r_i < $r_l; $r_i++) {
		$r_dat = unpack("C", $r_rcd[$r_i]);
			$r_n = intval($r_dat[1]) - 1;
			if ($r_n < 0)
				$r_n = 255;
			$r_n ^= 0xaa;
		$r_rcd[$r_i] = pack("C", $r_n);
	}
	if ($r_l > 0) {
		$r_by = fwrite($r_hdl, $r_rcd);
		$r_ok = ($r_by !== FALSE);
	}
	return $r_ok;
}
function RWSRNQRec($r_hdl)
{
	$r_rcd = "";
	if (feof($r_hdl))
		return FALSE;
	$r_bf = fread($r_hdl, 1);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_rcd .= $r_bf;
	$r_bf = fread($r_hdl, 4);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_rcd .= $r_bf;
	$r_sz = strlen($r_bf);
	for ($r_i = 0; $r_i < $r_sz; $r_i++) {
		$r_dat = unpack("C", $r_bf[$r_i]);
		$r_n = (intval($r_dat[1]) ^ 0x55) - 1;
		if ($r_n < 0)
			$r_n = 255;
		$r_bf[$r_i] = pack("C", $r_n);
	}
	$r_dat = unpack("N", $r_bf);
	$r_sz = $r_dat[1];
	if ($r_sz < 1)
		return FALSE;
	$r_bf = fread($r_hdl, $r_sz);
	if ($r_bf === FALSE)
		return FALSE;
	if (feof($r_hdl))
		return FALSE;
	$r_rcd .= $r_bf;
	$r_sz = strlen($r_rcd); 
	for ($r_i = 0; $r_i < $r_sz; $r_i++) {
		$r_dat = unpack("C", $r_rcd[$r_i]);
		$r_n = (intval($r_dat[1]) ^ 0x55) - 1;
		if ($r_n < 0)
			$r_n = 255;
		$r_rcd[$r_i] = pack("C", $r_n);
	}
	return $r_rcd;
}
function RWSWNQRec($r_hdl, $r_rcd)
{
	$r_ok = TRUE;
	$r_l = strlen($r_rcd);
	for ($r_i = 0; $r_i < $r_l; $r_i++) {
		$r_dat = unpack("C", $r_rcd[$r_i]);
			$r_n = intval($r_dat[1]) - 1;
			if ($r_n < 0)
				$r_n = 255;
			$r_n ^= 0xaa;
		$r_rcd[$r_i] = pack("C", $r_n);
	}
	if ($r_l > 0) {
		$r_by = fwrite($r_hdl, $r_rcd);
		$r_ok = ($r_by !== FALSE);
	}
	return $r_ok;
}
function RWSGQRType($r_rcd)
{
	$r_dat = unpack("C", $r_rcd[0]);
	$r_typ = intval($r_dat[1]);
	switch ($r_typ) {
	case 0:
		return RWSATT;
	case 1:
		return MULTICHOICE;
	case 2:
		return TRUEFALSE;
	case 3:
		return SHORTANSWER;
	case 4:
		return ESSAY;
	case 5:
		return MATCH;
	case 6:
		return DESCRIPTION;
	case 7:
		return CALCULATED;
	case 8:
		return NUMERICAL;
	case 9:  
		return MULTIANSWER;
	case 10: 
		return RANDOM;
	case 11:
		return RANDOMSAMATCH;
	case 12:
		return RWSRSV;
	default:
		return RWSUNK;
	}
}
function RWSGDIMon($r_mo, $r_y)
{
	switch ($r_mo) {
	case 1:
	case 3:
	case 5:
	case 7:
	case 8:
	case 10:
	case 12:
		return 31;
	case 4:
	case 6:
	case 9:
	case 11:
		return 30;
	case 2:
		if ($r_y % 400 == 0)
			return 29;
		else if ($r_y % 100 == 0)
			return 28;
		else if ($r_y % 4 == 0)
			return 29;
		else
			return 28;
	default:
		return FALSE;
	}
}
function RWSISRec(&$r_qz, $r_rcd, $r_po=FALSE)
{
	global $RWSLB;
	$r_p = 0;
	$r_sz = strlen($r_rcd);
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qz->intro = trim($r_f); 
	$r_ct = 2;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("n", $r_f);
	$r_y = $r_dat[1];
	if ($r_y != 0 && ($r_y < 1970 || $r_y > 2020))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_mo = intval($r_dat[1]);
	if ($r_y != 0 && ($r_mo < 1 || $r_mo > 12))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_da = intval($r_dat[1]);
	if ($r_y != 0 && ($r_da < 1 || $r_da > RWSGDIMon($r_mo, $r_y)))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_hr = intval($r_dat[1]);
	if ($r_y != 0 && ($r_hr < 0 || $r_hr > 23))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_mt = intval($r_dat[1]);
	if ($r_y != 0 && ($r_mt < 0 || $r_mt > 55 || $r_mt % 5 != 0))
		return FALSE;
	if ($r_y == 0)
		$r_qz->timeopen = 0;
	else
		$r_qz->timeopen = make_timestamp($r_y, $r_mo, $r_da, $r_hr, $r_mt);
	$r_ct = 2;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("n", $r_f);
	$r_y = $r_dat[1];
	if ($r_y != 0 && ($r_y < 1970 || $r_y > 2020))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_mo = intval($r_dat[1]);
	if ($r_y != 0 && ($r_mo < 1 || $r_mo > 12))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_da = intval($r_dat[1]);
	if ($r_y != 0 && ($r_da < 1 || $r_da > RWSGDIMon($r_mo, $r_y)))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_hr = intval($r_dat[1]);
	if ($r_y != 0 && ($r_hr < 0 || $r_hr > 23))
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_mt = intval($r_dat[1]);
	if ($r_y != 0 && ($r_mt < 0 || $r_mt > 55 || $r_mt % 5 != 0))
		return FALSE;
	if ($r_y == 0)
		$r_qz->timeclose = 0;
	else
		$r_qz->timeclose = make_timestamp($r_y, $r_mo, $r_da, $r_hr, $r_mt);
	if ($r_qz->timeopen != 0 && $r_qz->timeclose != 0
	  && $r_qz->timeopen > $r_qz->timeclose)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->timelimitenable = intval($r_dat[1]);
	if ($r_qz->timelimitenable != 0 && $r_qz->timelimitenable != 1)
		return FALSE;
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qz->timelimit = $r_dat[1];
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qz->delay1 = $r_dat[1];
	switch ($r_qz->delay1) {
	case 0: 
	case 1800: 
	case 3600: 
	case 7200: 
	case 10800: 
	case 14400: 
	case 18000: 
	case 21600: 
	case 25200: 
	case 28800: 
	case 32400: 
	case 36000: 
	case 39600: 
	case 43200: 
	case 46800: 
	case 50400: 
	case 54000: 
	case 57600: 
	case 61200: 
	case 64800: 
	case 68400: 
	case 72000: 
	case 75600: 
	case 79200: 
	case 82800: 
	case 86400: 
	case 172800: 
	case 259200: 
	case 345600: 
	case 432000: 
	case 518400: 
	case 604800: 
		break;
	default:
		return FALSE;
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qz->delay2 = $r_dat[1];
	switch ($r_qz->delay2) {
	case 0: 
	case 1800: 
	case 3600: 
	case 7200: 
	case 10800: 
	case 14400: 
	case 18000: 
	case 21600: 
	case 25200: 
	case 28800: 
	case 32400: 
	case 36000: 
	case 39600: 
	case 43200: 
	case 46800: 
	case 50400: 
	case 54000: 
	case 57600: 
	case 61200: 
	case 64800: 
	case 68400: 
	case 72000: 
	case 75600: 
	case 79200: 
	case 82800: 
	case 86400: 
	case 172800: 
	case 259200: 
	case 345600: 
	case 432000: 
	case 518400: 
	case 604800: 
		break;
	default:
		return FALSE;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->questionsperpage = intval($r_dat[1]);
	if ($r_qz->questionsperpage < 0 || $r_qz->questionsperpage > 50)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->shufflequestions = intval($r_dat[1]);
	if ($r_qz->shufflequestions != 0 && $r_qz->shufflequestions != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->shuffleanswers = intval($r_dat[1]);
	if ($r_qz->shuffleanswers != 0 && $r_qz->shuffleanswers != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->attempts = intval($r_dat[1]);
	if ($r_qz->attempts < 0 || $r_qz->attempts > 10)
		return FALSE;
	if (RWSFCmp($CFG->version, 2007101546, 2) == -1
	  && $r_qz->attempts > 6) {
		$r_qz->attempts = 6;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->attemptonlast = intval($r_dat[1]);
	if ($r_qz->attemptonlast != 0 && $r_qz->attemptonlast != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->adaptive = intval($r_dat[1]);
	if ($r_qz->adaptive != 0 && $r_qz->adaptive != 1)
		return FALSE;
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qz->grade = $r_dat[1];
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->grademethod = intval($r_dat[1]);
	switch ($r_qz->grademethod) {
	case 1: 
	case 2: 
	case 3: 
	case 4: 
		break;
	default:
		return FALSE;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->penaltyscheme = intval($r_dat[1]);
	if ($r_qz->penaltyscheme != 0 && $r_qz->penaltyscheme != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->decimalpoints = intval($r_dat[1]);
	switch ($r_qz->decimalpoints) {
	case 0:
	case 1:
	case 2:
	case 3:
		break;
	default:
		return FALSE;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->responsesimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->responsesimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->answersimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->answersimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->feedbackimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->feedbackimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->generalfeedbackimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->generalfeedbackimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->scoreimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->scoreimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->overallfeedbackimmediately = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->overallfeedbackimmediately);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->responsesopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->responsesopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->answersopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->answersopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->feedbackopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->feedbackopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->generalfeedbackopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->generalfeedbackopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->scoreopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->scoreopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->overallfeedbackopen = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->overallfeedbackopen);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->responsesclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->responsesclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->answersclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->answersclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->feedbackclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->feedbackclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->generalfeedbackclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->generalfeedbackclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->scoreclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->scoreclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_stg = intval($r_dat[1]);
	if ($r_stg == 1)
		$r_qz->overallfeedbackclosed = $r_stg;
	else if ($r_stg == 0)
		unset($r_qz->overallfeedbackclosed);
	else
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->popup = intval($r_dat[1]);
	if ($r_qz->popup != 0 && $r_qz->popup != 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qz->quizpassword = trim($r_f); 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qz->subnet = trim($r_f); 
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->groupmode = intval($r_dat[1]);
	switch ($r_qz->groupmode) {
	case 0: 
	case 1: 
	case 2: 
		break;
	default:
		return FALSE;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_qz->visible = intval($r_dat[1]);
	if ($r_qz->visible != 0 && $r_qz->visible != 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qz->cmidnumber = trim($r_f); 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qz->gradecat = trim($r_f); 
	if ($r_qz->gradecat != "1") 
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_nf = intval($r_dat[1]);
	$r_fds = array();
	for ($r_i = 0; $r_i < $r_nf; $r_i++) {
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_fds[] = trim($r_f); 
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_nb = intval($r_dat[1]);
	$r_bds = array();
	for ($r_i = 0; $r_i < $r_nb; $r_i++) {
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_bd = trim($r_f); 
		$r_l = strlen($r_bd);
		if ($r_l == 0)
			return FALSE;
		if (is_numeric($r_bd)) {
			if ($r_bd <= 0 || $r_bd >= $r_qz->grade)
				return FALSE;
			if ($r_i > 0 && $r_bd >= $r_lb)
				return FALSE;
			$r_lb = $r_bd;
		}
		else {
			if ($r_bd[$r_l-1] != '%')
				return FALSE;
			$r_pct = trim(substr($r_bd, 0, -1));
			if (!is_numeric($r_pct))
				return FALSE;
			if ($r_pct <= 0 || $r_pct >= 100)
				return FALSE;
			if ($r_i > 0 && $r_bd >= $r_lb)
				return FALSE;
			$r_lb = $r_bd * $r_qz->grade / 100.0;
		}
		$r_bds[] = $r_bd;
	}
	$r_nf = count($r_fds);
	$r_nb = count($r_bds);
	if ($r_nf > 0) {
		if ($r_nf != $r_nb + 1)
			return FALSE;
		$r_qz->feedbacktext = $r_fds;
		$r_qz->feedbackboundaries = $r_bds;
	}
	else { 
		$r_qz->feedbacktext = array();
		$r_qz->feedbackboundaries = array();
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_lbq = intval($r_dat[1]);
	if ($r_lbq != 0 && $r_lbq != 1)
		return FALSE;
	$RWSLB->atts = $r_lbq;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_lbr = intval($r_dat[1]);
	if ($r_lbr != 0 && $r_lbr != 1)
		return FALSE;
	$RWSLB->revs = $r_lbr;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$RWSLB->pw = trim($r_f); 
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	if ($r_po)
		quiz_process_options($r_qz);
	return TRUE;
}
function RWSSLBSet(&$r_qz)
{
	global $RWSLB;
	$RWSLB->perr = FALSE;
	if ($RWSLB->mok) {
		$r_ok = lockdown_set_settings($r_qz->instance, $RWSLB->atts,
		  $RWSLB->revs, $RWSLB->pw);
		if (!$r_ok)
			$RWSLB->perr = TRUE;
	} else if ($RWSLB->bok) {
		$r_upq = FALSE;
		if ($RWSLB->atts == 1) {
			$r_ok = lockdown_set_quiz_options($r_qz->instance);
			if (!$r_ok)
				$RWSLB->perr = TRUE;
			if ($r_ok) {
				$r_qz->name .= get_string("requires_ldb",
				  "block_lockdownbrowser");
				$r_upq = TRUE;
			}
		} else {
			$r_rcd = lockdown_get_quiz_options($r_qz->instance);
			if ($r_rcd !== FALSE) {
				lockdown_delete_options($r_qz->instance);
				$r_suf = get_string("requires_ldb", "block_lockdownbrowser");
				$r_qz->name = str_replace($r_suf, "", $r_qzn);
				$r_upq = TRUE;
			}
		}
		if ($r_upq) {
			$r_res = quiz_update_instance(addslashes_recursive($r_qz));
			if (!$r_res || is_string($r_res))
				$RWSLB->perr = TRUE;
		}
	} 
}
function RWSIARec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	if (RWSGQRType($r_rcd) != RWSATT)
		return FALSE;
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_ff = $r_f; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_fn = $r_f; 
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_fdat = $r_f; 
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_ff = clean_filename($r_ff);
	$r_drpth = "$r_cid/$r_ff";
	$r_ok = make_upload_directory($r_drpth);
	if (!$r_ok)
		return FALSE;
	$r_fn = clean_filename($r_fn);
	$r_crpth = "$r_ff/$r_fn";
	$r_drpth .= "/$r_fn";
	$r_fp = "$CFG->dataroot/$r_drpth";
	if (file_exists($r_fp))
		return FALSE;
	$r_hdl = fopen($r_fp, "wb");
	if ($r_hdl === FALSE)
		return FALSE;
	$r_by = fwrite($r_hdl, $r_fdat);
	fclose($r_hdl);
	if ($r_by === FALSE)
		return FALSE;
	return $r_crpth;
}
function RWSIRRec($r_cid, $r_qci, $r_rcd)
{
	if (RWSGQRType($r_rcd) != RWSRSV)
		return FALSE;
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	return TRUE;
}
function RWSISARec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != SHORTANSWER)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = SHORTANSWER;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty < 0 || $r_qst->penalty > 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_op = new stdClass();
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_op->usecase = intval($r_dat[1]);
	if ($r_op->usecase != 0 && $r_op->usecase != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_na = intval($r_dat[1]);
	if ($r_na < 1)
		return FALSE;
	$r_asrs = array();
	$r_mf = -1;
	for ($r_i = 0; $r_i < $r_na; $r_i++) {
		$r_asr = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->answer = trim($r_f); 
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->fraction = strval(RWSDblIn($r_f));
		switch ($r_asr->fraction) {
			case "1":
			case "0.9":
			case "0.8":
			case "0.75":
			case "0.7":
			case "0.66666":
			case "0.6":
			case "0.5":
			case "0.4":
			case "0.33333":
			case "0.3":
			case "0.25":
			case "0.2":
			case "0.16666":
			case "0.142857":
			case "0.125":
			case "0.11111":
			case "0.1":
			case "0.05":
			case "0":
				break;
			case "0.83333":
				if (RWSFCmp($CFG->version, 2007101546, 2) == -1)
					$r_asr->fraction = "0.8";
				break;
			default:
				$r_asr->fraction = "0";
				break;
		}
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->feedback = trim($r_f); 
		if (strlen($r_asr->feedback) > 0) {
			$r_asr->feedback = str_replace("%%COURSEPATH%%",
			  $r_cpth, $r_asr->feedback);
		}
		if (strlen($r_asr->answer) == 0)
			continue;
		$r_asrs[] = $r_asr;
		if ($r_asr->fraction > $r_mf)
			$r_mf = $r_asr->fraction;
	}
	if (count($r_asrs) < 1)
		return FALSE;
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_aid = array();
	foreach ($r_asrs as $r_an) {
		$r_an->question = $r_qst->id;
		$r_an->id = insert_record("question_answers",
		  addslashes_recursive($r_an));
		if ($r_an->id === FALSE) {
			delete_records("question", "id", $r_qst->id);
			delete_records("question_answers", "question", $r_qst->id);
			return FALSE;
		}
		$r_aid[] = $r_an->id;
	}
	$r_op->question = $r_qst->id;
	$r_op->answers = implode(",", $r_aid);
	$r_ok = insert_record("question_shortanswer",
	  addslashes_recursive($r_op));
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "question", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSITFRec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != TRUEFALSE)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = TRUEFALSE;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty != 1) 
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_tru = new stdClass();
	$r_tru->answer = get_string("true", "quiz");
	$r_fal = new stdClass();
	$r_fal->answer = get_string("false", "quiz");
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_cor = intval($r_dat[1]);
	if ($r_cor != 0 && $r_cor != 1)
		return FALSE;
	$r_tru->fraction = $r_cor;
	$r_fal->fraction = 1 - $r_cor;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_tru->feedback = trim($r_f); 
	if (strlen($r_tru->feedback) > 0) {
		$r_tru->feedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_tru->feedback);
	}
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_fal->feedback = trim($r_f); 
	if (strlen($r_fal->feedback) > 0) {
		$r_fal->feedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_fal->feedback);
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_tru->question = $r_qst->id;
	$r_tru->id = insert_record("question_answers",
	  addslashes_recursive($r_tru));
	if ($r_tru->id === FALSE) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_fal->question = $r_qst->id;
	$r_fal->id = insert_record("question_answers",
	  addslashes_recursive($r_fal));
	if ($r_fal->id === FALSE) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "id", $r_tru->id);
		return FALSE;
	}
	$r_op = new stdClass();
	$r_op->question = $r_qst->id;
	$r_op->trueanswer = $r_tru->id;
	$r_op->falseanswer = $r_fal->id;
	$r_ok = insert_record("question_truefalse",
	  addslashes_recursive($r_op));
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "id", $r_tru->id);
		delete_records("question_answers", "id", $r_fal->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSIMARec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != MULTIANSWER)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = MULTIANSWER;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty < 0 || $r_qst->penalty > 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_chn = array();
	$r_qst->defaultgrade = 0;
	$r_clzf = RWSGCFields($r_qst->questiontext);
	if ($r_clzf === FALSE)
		return FALSE;
	$r_chc = count($r_clzf);
	for ($r_i = 0; $r_i < $r_chc; $r_i++) {
		$r_chd = RWSCCChild($r_qst, $r_clzf[$r_i]);
		if ($r_chd === FALSE)
			return FALSE;
		$r_chn[] = $r_chd;
		$r_qst->defaultgrade += $r_chd->defaultgrade;
		$r_pk = $r_i+1;
		$r_qst->questiontext = implode("{#$r_pk}",
		  explode($r_clzf[$r_i], $r_qst->questiontext, 2));
	}
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_chid = array();
	foreach ($r_chn as $r_chd) {
		$r_chd->parent = $r_qst->id;
		$r_chd->id = RWSCChild($r_chd);
		if ($r_chd->id === FALSE) {
			delete_question($r_qst->id);
			return FALSE;
		}
		$r_chid[] = $r_chd->id;
	}
	if (count($r_chid) > 0) {
		$r_op = new stdClass();
		$r_op->question = $r_qst->id;
		$r_op->sequence = implode(",", $r_chid);
		$r_op->id = insert_record("question_multianswer",
		  addslashes_recursive($r_op));
		if ($r_op->id === FALSE) {
			delete_question($r_qst->id);
			return FALSE;
		}
	}
	return $r_qst->id;
}
function RWSCCChild($r_qst, $r_f)
{
	global $CFG;
	global $QTYPES;
	$r_rxpt = FALSE;
	foreach ($QTYPES as $r_qtyp) {
		if (strcasecmp($r_qtyp->name(), RWSRXP) == 0) {
			$r_rxpt = TRUE;
			break;
		}
	}
	$r_rxpc = FALSE;
	$r_pth = "$CFG->dirroot/question/type/multianswer/questiontype.php";
	$r_dat = file_get_contents($r_pth);
	if ($r_dat !== FALSE
	  && strpos($r_dat, "ANSWER_REGEX_ANSWER_TYPE_REGEXP") !== FALSE)
		$r_rxpc = TRUE;
	$r_rxps = ($r_rxpt && $r_rxpc);
	$r_chd = new stdClass();
	$r_chd->name = $r_qst->name;
	$r_chd->category = $r_qst->category;
	$r_chd->questiontext = $r_f;
	$r_chd->questiontextformat = $r_qst->questiontextformat;
	$r_chd->answer = array();
	$r_chd->fraction = array();
	$r_chd->feedback = array();
	$r_chd->defaultgrade = "1";
	$r_st = 1;
	$r_ofs = strpos(substr($r_f, $r_st), ":");
	if ($r_ofs === FALSE)
		return FALSE;
	if ($r_ofs > 0) {
		$r_sbf = trim(substr($r_f, $r_st, $r_ofs));
		if (strlen($r_sbf) > 0 && ctype_digit($r_sbf))
			$r_chd->defaultgrade = $r_sbf;
	}
	$r_st += $r_ofs;
	$r_sbf = substr($r_f, $r_st);
	if (strncmp($r_sbf, ":NUMERICAL:", 11) == 0
	  || strncmp($r_sbf, ":NM:", 4) == 0) {
        $r_chd->qtype = NUMERICAL;
		$r_chd->tolerance = array();
        $r_chd->multiplier = array();
        $r_chd->units = array();
	} else if (strncmp($r_sbf, ":SHORTANSWER:", 13) == 0
	  || strncmp($r_sbf, ":SA:", 4) == 0
	  || strncmp($r_sbf, ":MW:", 4) == 0) {
        $r_chd->qtype = SHORTANSWER;
		$r_chd->usecase = 0;
	} else if (RWSFCmp($CFG->version, 2007101546, 2) >= 0
	  && (strncmp($r_sbf, ":SHORTANSWER_C:", 15) == 0
		|| strncmp($r_sbf, ":SAC:", 5) == 0
		|| strncmp($r_sbf, ":MWC:", 5) == 0)) {
        $r_chd->qtype = SHORTANSWER;
		$r_chd->usecase = 1;
	} else if (strncmp($r_sbf, ":MULTICHOICE:", 13) == 0
	  || strncmp($r_sbf, ":MC:", 4) == 0) {
        $r_chd->qtype = MULTICHOICE;
		$r_chd->single = 1;
		$r_chd->answernumbering = 0;
		$r_chd->shuffleanswers = 1;
		$r_chd->correctfeedback = "";
		$r_chd->partiallycorrectfeedback = "";
		$r_chd->incorrectfeedback = "";
		$r_chd->layout = 0;
	} else if (RWSFCmp($CFG->version, 2007101546, 2) >= 0
	  && (strncmp($r_sbf, ":MULTICHOICE_V:", 15) == 0
		|| strncmp($r_sbf, ":MCV:", 5) == 0)) {
        $r_chd->qtype = MULTICHOICE;
		$r_chd->single = 1;
		$r_chd->answernumbering = 0;
		$r_chd->shuffleanswers = 1;
		$r_chd->correctfeedback = "";
		$r_chd->partiallycorrectfeedback = "";
		$r_chd->incorrectfeedback = "";
		$r_chd->layout = 1;
	} else if (RWSFCmp($CFG->version, 2007101546, 2) >= 0
	  && (strncmp($r_sbf, ":MULTICHOICE_H:", 15) == 0
		|| strncmp($r_sbf, ":MCH:", 5) == 0)) {
        $r_chd->qtype = MULTICHOICE;
		$r_chd->single = 1;
		$r_chd->answernumbering = 0;
		$r_chd->shuffleanswers = 1;
		$r_chd->correctfeedback = "";
		$r_chd->partiallycorrectfeedback = "";
		$r_chd->incorrectfeedback = "";
		$r_chd->layout = 2;
	} else if ($r_rxps
	  && strncmp($r_sbf, ":REGEXP:", 8) == 0) {
        $r_chd->qtype = RWSRXP;
        $r_chd->usehint = 0;
	} else {
		return FALSE;
	}
	$r_st++;
	$r_ofs = strpos(substr($r_f, $r_st), ":");
	$r_st += $r_ofs;
	$r_st++;
	$r_fln = strlen($r_f);
	while ($r_st < $r_fln) {
		if ($r_f[$r_st] == '}') { 
			break;
		}
		if ($r_f[$r_st] == '~') { 
			$r_st++;
		}
		$r_fra = "0";
		if ($r_f[$r_st] == '=') { 
			$r_fra = "1";
			$r_st++;
		}
		if ($r_f[$r_st] == '%') { 
			$r_st++;
			$r_pct = "";
			while ($r_st < $r_fln) {
				if ($r_f[$r_st] == '%')
					break;
				$r_pct .= $r_f[$r_st];
				$r_st++;
			}
			$r_pct = trim($r_pct);
			if (strlen($r_pct) == 0 || !ctype_digit($r_pct))
				return FALSE;
			$r_fra = .01 * $r_pct;
			$r_st++;
		}
		$r_asr = "";
		if ($r_chd->qtype == NUMERICAL) {
			$r_tol = "";
			$r_fnd = FALSE;
			while ($r_st < $r_fln) {
				if ($r_f[$r_st] == '#'
				  || $r_f[$r_st] == '~'
				  || $r_f[$r_st] == '}') {
					break;
				} else if ($r_f[$r_st] == ':') {
					$r_fnd = TRUE;
					$r_st++;
					continue;
				}
				if ($r_fnd)
					$r_tol .= $r_f[$r_st];
				else
					$r_asr .= $r_f[$r_st];
				$r_st++;
			}
			$r_asr = trim($r_asr);
			if (strlen($r_asr) == 0)
				return FALSE;
			if (($r_asr != strval(floatval($r_asr))) && $r_asr != "*")
				return FALSE;
			$r_tol = trim($r_tol);
			if (strlen($r_tol) == 0
			  || ($r_tol != strval(floatval($r_tol)))
			  || $r_asr == "*")
				$r_tol = 0;
		} else { 
			$r_itg = FALSE;
			while ($r_st < $r_fln) {
				if ($r_f[$r_st] == '<')
					$r_itg = TRUE;
				else if ($r_f[$r_st] == '>')
					$r_itg = FALSE;
				else if (!$r_itg &&
				  ($r_f[$r_st] == '#'
					|| $r_f[$r_st] == '~'
					|| $r_f[$r_st] == '}')) {
					$r_st--;
					$r_esc = ($r_f[$r_st] == '\\');
					$r_st++;
					if (!$r_esc)
						break;
				}
				$r_asr .= $r_f[$r_st];
				$r_st++;
			}
			$r_asr = trim($r_asr);
			if (strlen($r_asr) == 0)
				return FALSE;
			$r_asr = str_replace("\#", "#", $r_asr);
			$r_asr = str_replace("\}", "}", $r_asr);
			$r_asr = str_replace("\~", "~", $r_asr);
		}
		$r_fb = "";
		if ($r_f[$r_st] == '#') { 
			$r_st++;
			$r_fb = "";
			$r_itg = FALSE;
			while ($r_st < $r_fln) {
				if ($r_f[$r_st] == '<')
					$r_itg = TRUE;
				else if ($r_f[$r_st] == '>')
					$r_itg = FALSE;
				else if (!$r_itg &&
				  ($r_f[$r_st] == '~'
					|| $r_f[$r_st] == '}')) {
					$r_st--;
					$r_esc = ($r_f[$r_st] == '\\');
					$r_st++;
					if (!$r_esc)
						break;
				}
				$r_fb .= $r_f[$r_st];
				$r_st++;
			}
			$r_fb = trim($r_fb);
			$r_fb = str_replace("\#", "#", $r_fb);
			$r_fb = str_replace("\}", "}", $r_fb);
			$r_fb = str_replace("\~", "~", $r_fb);
		}
		$r_chd->answer[] = $r_asr;
		$r_chd->fraction[] = $r_fra;
		$r_chd->feedback[] = $r_fb;
		if ($r_chd->qtype == NUMERICAL)
			$r_chd->tolerance[] = $r_tol;
	}
	$r_na = count($r_chd->answer);
	if ($r_na == 0)
		return FALSE;
	if (count($r_chd->fraction) != $r_na)
		return FALSE;
	if (count($r_chd->feedback) != $r_na)
		return FALSE;
	if ($r_chd->qtype == NUMERICAL && count($r_chd->tolerance) != $r_na)
		return FALSE;
	return $r_chd;
}
function RWSGCFields($r_qstx)
{
	$r_p = 0;
	$r_l = strlen($r_qstx);
	$r_itg = FALSE;
	$r_ifd = FALSE;
	$r_fs = array();
	while ($r_p < $r_l) {
		if ($r_qstx[$r_p] == '<')
			$r_itg = TRUE;
		else if ($r_qstx[$r_p] == '>')
			$r_itg = FALSE;
		else if (!$r_ifd && !$r_itg && $r_qstx[$r_p] == '{') {
			$r_esc = FALSE;
			if ($r_p > 0) {
				$r_p--;
				$r_esc = ($r_qstx[$r_p] == '\\');
				$r_p++;
			}
			if (!$r_esc) {
				$r_f = "";
				$r_ifd = TRUE;
			}
		}
		else if ($r_ifd && !$r_itg && $r_qstx[$r_p] == '}') {
			$r_p--;
			$r_esc = ($r_qstx[$r_p] == '\\');
			$r_p++;
			if (!$r_esc) {
				$r_f .= $r_qstx[$r_p];
				$r_fs[] = $r_f;
				$r_ifd = FALSE;
			}
		}		
		if ($r_ifd)
			$r_f .= $r_qstx[$r_p];
		$r_p++;
	}
	return $r_fs;
}
function RWSCChild($r_chd)
{
	global $CFG;
	global $USER;
	$r_chd->hidden = 0;
	$r_chd->length = 1;
	$r_chd->stamp = make_unique_id_code();
	$r_chd->createdby = $USER->id;
	$r_chd->modifiedby = $USER->id;
	$r_chd->image = "";
	$r_chd->penalty = 0;
	$r_chd->generalfeedback = "";
	$r_chd->timecreated = time();
	$r_chd->timemodified = time();
	if ($r_chd->qtype == NUMERICAL) {
		$r_chd->id = insert_record("question",
		  addslashes_recursive($r_chd));
		if ($r_chd->id === FALSE)
			return FALSE;
		$r_h = question_hash($r_chd);
		$r_ok = set_field("question", "version", $r_h, "id", $r_chd->id);
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			return FALSE;
		}
		$r_na = count($r_chd->answer);
		for ($r_i = 0; $r_i < $r_na; $r_i++) {
			$r_an = new stdClass();
			$r_an->answer = $r_chd->answer[$r_i]; 
			$r_an->fraction = $r_chd->fraction[$r_i];
			$r_an->feedback = $r_chd->feedback[$r_i];
			$r_an->question = $r_chd->id;
			$r_an->id = insert_record("question_answers",
			  addslashes_recursive($r_an));
			if ($r_an->id === FALSE) {
				delete_records("question", "id", $r_chd->id);
				delete_records("question_answers", "question", $r_chd->id);
				delete_records("question_numerical", "question", $r_chd->id);
				return FALSE;
			}
			$r_op = new stdClass();
			$r_op->question = $r_chd->id;
			$r_op->answer = $r_an->id;
			$r_op->tolerance = $r_chd->tolerance[$r_i]; 
			$r_ok = insert_record("question_numerical",
			  addslashes_recursive($r_op));
			if (!$r_ok) {
				delete_records("question", "id", $r_chd->id);
				delete_records("question_answers", "question", $r_chd->id);
				delete_records("question_numerical", "question", $r_chd->id);
				return FALSE;
			}
		}
	} else if ($r_chd->qtype == SHORTANSWER) {
		$r_chd->id = insert_record("question",
		  addslashes_recursive($r_chd));
		if ($r_chd->id === FALSE)
			return FALSE;
		$r_h = question_hash($r_chd);
		$r_ok = set_field("question", "version", $r_h, "id", $r_chd->id);
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			return FALSE;
		}
		$r_aid = array();
		$r_na = count($r_chd->answer);
		for ($r_i = 0; $r_i < $r_na; $r_i++) {
			$r_an = new stdClass();
			$r_an->answer = $r_chd->answer[$r_i];
			$r_an->fraction = $r_chd->fraction[$r_i];
			$r_an->feedback = $r_chd->feedback[$r_i];
			$r_an->question = $r_chd->id;
			$r_an->id = insert_record("question_answers",
			  addslashes_recursive($r_an));
			if ($r_an->id === FALSE) {
				delete_records("question", "id", $r_chd->id);
				delete_records("question_answers", "question", $r_chd->id);
				return FALSE;
			}
			$r_aid[] = $r_an->id;
		}
		$r_op = new stdClass();
		$r_op->usecase = $r_chd->usecase;
		$r_op->question = $r_chd->id;
		$r_op->answers = implode(",", $r_aid);
		$r_ok = insert_record("question_shortanswer",
		  addslashes_recursive($r_op));
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			delete_records("question_answers", "question", $r_chd->id);
			return FALSE;
		}
	} else if ($r_chd->qtype == MULTICHOICE) {
		$r_chd->id = insert_record("question",
		  addslashes_recursive($r_chd));
		if ($r_chd->id === FALSE)
			return FALSE;
		$r_h = question_hash($r_chd);
		$r_ok = set_field("question", "version", $r_h, "id", $r_chd->id);
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			return FALSE;
		}
		$r_aid = array();
		$r_na = count($r_chd->answer);
		for ($r_i = 0; $r_i < $r_na; $r_i++) {
			$r_an = new stdClass();
			$r_an->answer = $r_chd->answer[$r_i];
			$r_an->fraction = $r_chd->fraction[$r_i];
			$r_an->feedback = $r_chd->feedback[$r_i];
			$r_an->question = $r_chd->id;
			$r_an->id = insert_record("question_answers",
			  addslashes_recursive($r_an));
			if ($r_an->id === FALSE) {
				delete_records("question", "id", $r_chd->id);
				delete_records("question_answers", "question", $r_chd->id);
				return FALSE;
			}
			$r_aid[] = $r_an->id;
		}
		$r_op = new stdClass();
		$r_op->question = $r_chd->id;
		$r_op->answers = implode(",", $r_aid);
		$r_op->single = $r_chd->single;
		$r_op->answernumbering = $r_chd->answernumbering;
		$r_op->shuffleanswers = $r_chd->shuffleanswers;
		$r_op->correctfeedback = $r_chd->correctfeedback;
		$r_op->partiallycorrectfeedback = $r_chd->partiallycorrectfeedback;
		$r_op->incorrectfeedback = $r_chd->incorrectfeedback;
		$r_op->layout = $r_chd->layout;
		$r_ok = insert_record("question_multichoice",
		  addslashes_recursive($r_op));
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			delete_records("question_answers", "question", $r_chd->id);
			return FALSE;
		}
	} else if ($r_chd->qtype == RWSRXP) {
		$r_chd->id = insert_record("question",
		  addslashes_recursive($r_chd));
		if ($r_chd->id === FALSE)
			return FALSE;
		$r_h = question_hash($r_chd);
		$r_ok = set_field("question", "version", $r_h, "id", $r_chd->id);
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			return FALSE;
		}
		$r_aid = array();
		$r_na = count($r_chd->answer);
		for ($r_i = 0; $r_i < $r_na; $r_i++) {
			$r_an = new stdClass();
			$r_an->answer = $r_chd->answer[$r_i];
			$r_an->fraction = $r_chd->fraction[$r_i];
			$r_an->feedback = $r_chd->feedback[$r_i];
			$r_an->question = $r_chd->id;
			$r_an->id = insert_record("question_answers",
			  addslashes_recursive($r_an));
			if ($r_an->id === FALSE) {
				delete_records("question", "id", $r_chd->id);
				delete_records("question_answers", "question", $r_chd->id);
				return FALSE;
			}
			$r_aid[] = $r_an->id;
		}
		$r_op = new stdClass();
		$r_op->question = $r_chd->id;
		$r_op->answers = implode(",", $r_aid);
		$r_ok = insert_record("question_regexp",
		  addslashes_recursive($r_op));
		if (!$r_ok) {
			delete_records("question", "id", $r_chd->id);
			delete_records("question_answers", "question", $r_chd->id);
			return FALSE;
		}
	} else {
		return FALSE;
	}
	return $r_chd->id;
}
function RWSICRec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != CALCULATED)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = CALCULATED;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty < 0 || $r_qst->penalty > 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_na = intval($r_dat[1]);
	if ($r_na != 1)
		return FALSE;
	$r_asrs = array();
	$r_tf = 0;
	for ($r_i = 0; $r_i < $r_na; $r_i++) {
		$r_an = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_an->formula = trim($r_f); 
		if (strlen($r_an->formula) == 0)
			return FALSE;
		if (!RWSCFSyn($r_an->formula))
			return FALSE;
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_an->fraction = strval(RWSDblIn($r_f));
		switch ($r_an->fraction) {
			case "1":
			case "0.9":
			case "0.8":
			case "0.75":
			case "0.7":
			case "0.66666":
			case "0.6":
			case "0.5":
			case "0.4":
			case "0.33333":
			case "0.3":
			case "0.25":
			case "0.2":
			case "0.16666":
			case "0.142857":
			case "0.125":
			case "0.11111":
			case "0.1":
			case "0.05":
			case "0":
				break;
			case "0.83333":
				if (RWSFCmp($CFG->version, 2007101546, 2) == -1)
					$r_an->fraction = "0.8";
				break;
			default:
				$r_an->fraction = "0";
				break;
		}
		if ($r_an->fraction != "1")
			return FALSE;
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_an->feedback = trim($r_f); 
		if (strlen($r_an->feedback) > 0) {
			$r_an->feedback = str_replace("%%COURSEPATH%%",
			  $r_cpth, $r_an->feedback);
		}
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_an->tolerance = RWSDblIn($r_f);
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_an->tolerancetype = intval($r_dat[1]);
		switch ($r_an->tolerancetype) {
		case 1: 
		case 2: 
		case 3: 
			break;
		default:
			return FALSE;
		}
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_an->correctanswerlength = intval($r_dat[1]);
		if ($r_an->correctanswerlength < 0 || $r_an->correctanswerlength > 9)
			return FALSE;
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_an->correctanswerformat = intval($r_dat[1]);
		switch ($r_an->correctanswerformat) {
		case 1: 
		case 2: 
			break;
		default:
			return FALSE;
		}
		$r_asrs[] = $r_an;
		$r_tf += $r_an->fraction;
	}
	if (count($r_asrs) != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_nu = intval($r_dat[1]);
	if ($r_nu < 0 || $r_nu > 1)
		return FALSE;
	$r_uts = array();
	$r_fbu = FALSE;
	for ($r_i = 0; $r_i < $r_nu; $r_i++) {
		$r_ut = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ut->name = trim($r_f); 
		if (strlen($r_ut->name) == 0)
			return FALSE;
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ut->multiplier = RWSDblIn($r_f);
		if ($r_ut->multiplier == 1)
			$r_fbu = TRUE;
		if ($r_ut->multiplier != 1)
			return FALSE;
		$r_uts[] = $r_ut;
	}
	if (count($r_uts) > 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_nd = intval($r_dat[1]);
	if ($r_nd < 1)
		return FALSE;
	$r_dset = array();
	for ($r_i = 0; $r_i < $r_nd; $r_i++) {
		$r_ds = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ds->name = trim($r_f); 
		if (strlen($r_ds->name) == 0)
			return FALSE;
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ds->distribution = trim($r_f); 
		switch ($r_ds->distribution) {
		case "uniform":
		case "loguniform":
			break;
		default:
			return FALSE;
		}
		if ($r_ds->distribution != "uniform")
			return FALSE;
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ds->min = RWSDblIn($r_f);
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_ds->max = RWSDblIn($r_f);
		if ($r_ds->max < $r_ds->min)
			return FALSE;
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_ds->precision = intval($r_dat[1]);
		if ($r_ds->precision < 0 || $r_ds->precision > 10)
			return FALSE;
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_ds->type = intval($r_dat[1]);
		if ($r_ds->type != 1)
			return FALSE;
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_ds->status = intval($r_dat[1]);
		if ($r_ds->status != 0 && $r_ds->status != 1)
			return FALSE;
		$r_ct = 1;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_dat = unpack("C", $r_f);
		$r_ds->itemcount = intval($r_dat[1]);
		if ($r_ds->itemcount < 1)
			return FALSE;
		$r_ds->items = array();
		$r_map = array_fill(1, $r_ds->itemcount, 0);
		for ($j = 0; $j < $r_ds->itemcount; $j++) {
			$r_it = new stdClass();
			$r_ct = 1;
			if ($r_sz < $r_ct)
				return FALSE;
			$r_f = substr($r_rcd, $r_p, $r_ct);
			$r_p += $r_ct;
			$r_sz -= $r_ct;
			$r_dat = unpack("C", $r_f);
			$r_it->itemnumber = intval($r_dat[1]);
			if ($r_it->itemnumber < 1 || $r_it->itemnumber > $r_ds->itemcount)
				return FALSE;
			if ($r_map[$r_it->itemnumber] == 1) 
				return FALSE;
			$r_map[$r_it->itemnumber] = 1;
			$r_ct = 8;
			if ($r_sz < $r_ct)
				return FALSE;
			$r_f = substr($r_rcd, $r_p, $r_ct);
			$r_p += $r_ct;
			$r_sz -= $r_ct;
			$r_it->value = RWSDblIn($r_f);
			$r_ds->items[] = $r_it;
		}
		if (array_sum($r_map) != $r_ds->itemcount)
			return FALSE;
		$r_dset[] = $r_ds;
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	foreach ($r_asrs as $r_a) {
		$r_an = new stdClass();
		$r_an->answer = $r_a->formula;
		$r_an->fraction = $r_a->fraction;
		$r_an->feedback = $r_a->feedback;
		$r_an->question = $r_qst->id;
		$r_an->id = insert_record("question_answers",
		  addslashes_recursive($r_an));
		if ($r_an->id === FALSE) {
			$r_ok = FALSE;
			break;
		}
		$r_o = new stdClass();
		$r_o->tolerance = $r_a->tolerance;
		$r_o->tolerancetype = $r_a->tolerancetype;
		$r_o->correctanswerlength = $r_a->correctanswerlength;
		$r_o->correctanswerformat = $r_a->correctanswerformat;
		$r_o->question = $r_qst->id;
		$r_o->answer = $r_an->id;
		$r_o->id = insert_record("question_calculated",
		  addslashes_recursive($r_o));
		if ($r_o->id === FALSE) {
			$r_ok = FALSE;
			break;
		}
	}
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "question", $r_qst->id);
		delete_records("question_calculated", "question", $r_qst->id);
		return FALSE;
	}
	foreach ($r_uts as $r_u) {
		$r_ut = new stdClass();
		$r_ut->unit = $r_u->name;
		$r_ut->multiplier = $r_u->multiplier;
		$r_ut->question = $r_qst->id;
		$r_ut->id = insert_record("question_numerical_units",
		  addslashes_recursive($r_ut));
		if ($r_ut->id === FALSE) {
			$r_ok = FALSE;
			break;
		}
	}
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "question", $r_qst->id);
		delete_records("question_calculated", "question", $r_qst->id);
		delete_records("question_numerical_units", "question", $r_qst->id);
		return FALSE;
	}
	foreach ($r_dset as $r_ds) {
		$r_df = new stdClass();
		$r_df->name = $r_ds->name;
		$r_df->options =
		  "$r_ds->distribution:$r_ds->min:$r_ds->max:$r_ds->precision";
		$r_df->itemcount = $r_ds->itemcount;
		$r_df->type = $r_ds->type;
		if ($r_ds->status == 0)
			$r_df->category = 0; 
		else 
			$r_df->category = $r_qst->category;
		$r_df->id = insert_record("question_dataset_definitions",
		  addslashes_recursive($r_df));
		if ($r_df->id === FALSE) {
			$r_ok = FALSE;
			break;
		}
		$r_qds = new stdClass();
		$r_qds->question = $r_qst->id;
		$r_qds->datasetdefinition = $r_df->id;
		$r_qds->id = insert_record("question_datasets",
		  addslashes_recursive($r_qds));
		if ($r_qds->id === FALSE) {
			$r_ok = FALSE;
			break;
		}
		foreach ($r_ds->items as $r_di) {
			$r_it = new stdClass();
			$r_it->itemnumber = $r_di->itemnumber;
			$r_it->value = $r_di->value;
			$r_it->definition = $r_df->id;
			$r_it->id = insert_record("question_dataset_items",
			  addslashes_recursive($r_it));
			if ($r_it->id === FALSE) {
				$r_ok = FALSE;
				break;
			}
		}
		if (!$r_ok)
			break;
	}
	if (!$r_ok) { 
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "question", $r_qst->id);
		delete_records("question_calculated", "question", $r_qst->id);
		delete_records("question_numerical_units", "question", $r_qst->id);
		$r_rcs = get_records("question_datasets", "question", $r_qst->id);
		foreach ($r_rcs as $r_rc) {
			delete_records("question_dataset_definitions", "id",
			  $r_rc->datasetdefinition);
			delete_records("question_dataset_items", "definition",
			  $r_rc->datasetdefinition);
		}
		delete_records("question_datasets", "question", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSCFSyn($r_for)
{
    while (ereg('\\{[[:alpha:]][^>} <{"\']*\\}', $r_for, $r_rgs)) {
        $r_for = str_replace($r_rgs[0], '1', $r_for);
    }
    $r_for = strtolower(str_replace(' ', '', $r_for));
	$r_soc = '-+/*%>:^~<?=&|!';
	$r_oon = "[$r_soc.0-9eE]";
    while (ereg("(^|[$r_soc,(])([a-z0-9_]*)\\(($r_oon+(,$r_oon+((,$r_oon+)+)?)?)?\\)",
            $r_for, $r_rgs)) {
		switch ($r_rgs[2]) {
            case '':
                if ($r_rgs[4] || strlen($r_rgs[3])==0) {
                    return FALSE; 
                }
                break;
            case 'pi':
                if ($r_rgs[3]) {
                    return FALSE; 
                }
                break;
            case 'abs': case 'acos': case 'acosh': case 'asin': case 'asinh':
            case 'atan': case 'atanh': case 'bindec': case 'ceil': case 'cos':
            case 'cosh': case 'decbin': case 'decoct': case 'deg2rad':
            case 'exp': case 'expm1': case 'floor': case 'is_finite':
            case 'is_infinite': case 'is_nan': case 'log10': case 'log1p':
            case 'octdec': case 'rad2deg': case 'sin': case 'sinh': case 'sqrt':
            case 'tan': case 'tanh':
                if ($r_rgs[4] || empty($r_rgs[3])) {
                    return FALSE; 
                }
                break;
            case 'log': case 'round':
                if ($r_rgs[5] || empty($r_rgs[3])) {
                    return FALSE; 
                }
                break;
            case 'atan2': case 'fmod': case 'pow':
                if ($r_rgs[5] || empty($r_rgs[4])) {
                    return FALSE; 
                }
                break;
            case 'min': case 'max':
                if (empty($r_rgs[4])) {
                    return FALSE; 
                }
                break;
            default:
                return FALSE; 
        }
        if ($r_rgs[1]) {
            $r_for = str_replace($r_rgs[0], $r_rgs[1] . '1', $r_for);
        } else {
            $r_for = ereg_replace("^$r_rgs[2]\\([^)]*\\)", '1', $r_for);
        }
    }
	if (ereg("[^$r_soc.0-9eE]+", $r_for, $r_rgs)) {
		return FALSE; 
    } else {
        return TRUE; 
    }
}
function RWSIMCRec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != MULTICHOICE)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = MULTICHOICE;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty < 0 || $r_qst->penalty > 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_op = new stdClass();
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_op->single = intval($r_dat[1]);
	if ($r_op->single != 0 && $r_op->single != 1)
		return FALSE;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_flg = intval($r_dat[1]);
	if ($r_flg != 0 && $r_flg != 1)
		return FALSE;
	$r_op->shuffleanswers = (bool)$r_flg;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_op->answernumbering = trim($r_f); 
	switch ($r_op->answernumbering) {
	case "abc":
	case "ABCD":
	case "123":
	case "none":
		break;
	default:
		return FALSE;
	}
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_na = intval($r_dat[1]);
	if ($r_na < 2)
		return FALSE;
	$r_asrs = array();
	$r_tf = 0;
	$r_mf = -1;
	for ($r_i = 0; $r_i < $r_na; $r_i++) {
		$r_asr = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->answer = trim($r_f); 
		if (strlen($r_asr->answer) > 0) {
			$r_asr->answer = str_replace("%%COURSEPATH%%",
			  $r_cpth, $r_asr->answer);
		}
		$r_ct = 8;
		if ($r_sz < $r_ct)
			return FALSE;
		$r_f = substr($r_rcd, $r_p, $r_ct);
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->fraction = strval(RWSDblIn($r_f));
		switch ($r_asr->fraction) {
			case "1":
			case "0.9":
			case "0.8":
			case "0.75":
			case "0.7":
			case "0.66666":
			case "0.6":
			case "0.5":
			case "0.4":
			case "0.33333":
			case "0.3":
			case "0.25":
			case "0.2":
			case "0.16666":
			case "0.142857":
			case "0.125":
			case "0.11111":
			case "0.1":
			case "0.05":
			case "0":
			case "-0.05":
			case "-0.1":
			case "-0.11111":
			case "-0.125":
			case "-0.142857":
			case "-0.16666":
			case "-0.2":
			case "-0.25":
			case "-0.3":
			case "-0.33333":
			case "-0.4":
			case "-0.5":
			case "-0.6":
			case "-0.66666":
			case "-0.7":
			case "-0.75":
			case "-0.8":
			case "-0.9":
			case "-1":
				break;
			case "0.83333":
				if (RWSFCmp($CFG->version, 2007101546, 2) == -1)
					$r_asr->fraction = "0.8";
				break;
			case "-0.83333":
				if (RWSFCmp($CFG->version, 2007101546, 2) == -1)
					$r_asr->fraction = "-0.8";
				break;
			default:
				$r_asr->fraction = "0";
				break;
		}
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_asr->feedback = trim($r_f); 
		if (strlen($r_asr->feedback) > 0) {
			$r_asr->feedback = str_replace("%%COURSEPATH%%",
			  $r_cpth, $r_asr->feedback);
		}
		if (strlen($r_asr->answer) == 0)
			continue;
		$r_asrs[] = $r_asr;
		if ($r_asr->fraction > 0)
			$r_tf += $r_asr->fraction;
		if ($r_asr->fraction > $r_mf)
			$r_mf = $r_asr->fraction;
	}
	if (count($r_asrs) < 2)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_op->correctfeedback = trim($r_f); 
	if (strlen($r_op->correctfeedback) > 0) {
		$r_op->correctfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_op->correctfeedback);
	}
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_op->partiallycorrectfeedback = trim($r_f); 
	if (strlen($r_op->partiallycorrectfeedback) > 0) {
		$r_op->partiallycorrectfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_op->partiallycorrectfeedback);
	}
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_op->incorrectfeedback = trim($r_f); 
	if (strlen($r_op->incorrectfeedback) > 0) {
		$r_op->incorrectfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_op->incorrectfeedback);
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_aid = array();
	foreach ($r_asrs as $r_an) {
		$r_an->question = $r_qst->id;
		$r_an->id = insert_record("question_answers",
		  addslashes_recursive($r_an));
		if ($r_an->id === FALSE) {
			delete_records("question", "id", $r_qst->id);
			delete_records("question_answers", "question", $r_qst->id);
			return FALSE;
		}
		$r_aid[] = $r_an->id;
	}
	$r_op->question = $r_qst->id;
	$r_op->answers = implode(",", $r_aid);
	$r_ok = insert_record("question_multichoice",
	  addslashes_recursive($r_op));
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_answers", "question", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSIMRec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != MATCH)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = MATCH;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	$r_ct = 8;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->penalty = RWSDblIn($r_f);
	if ($r_qst->penalty < 0 || $r_qst->penalty > 1)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_op = new stdClass();
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_flg = intval($r_dat[1]);
	if ($r_flg != 0 && $r_flg != 1)
		return FALSE;
	$r_op->shuffleanswers = (bool)$r_flg;
	$r_ct = 1;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("C", $r_f);
	$r_np = intval($r_dat[1]);
	if ($r_np < 3)
		return FALSE;
	$r_prs = array();
	$r_sbqct = 0;
	for ($r_i = 0; $r_i < $r_np; $r_i++) {
		$r_sbq = new stdClass();
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_sbq->questiontext = trim($r_f); 
		if (strlen($r_sbq->questiontext) > 0) {
			$r_sbq->questiontext = str_replace("%%COURSEPATH%%",
			  $r_cpth, $r_sbq->questiontext);
		}
		if ($r_sz < 1)
			return FALSE;
		$r_ct = strpos(substr($r_rcd, $r_p), "\0");
		if ($r_ct === FALSE)
			return FALSE;
		if ($r_ct > 0)
			$r_f = substr($r_rcd, $r_p, $r_ct);
		else
			$r_f = "";
		$r_ct++; 
		$r_p += $r_ct;
		$r_sz -= $r_ct;
		$r_sbq->answertext = trim($r_f); 
		if (strlen($r_sbq->answertext) == 0)
			continue;
		if (strlen($r_sbq->questiontext) != 0)
			$r_sbqct++;
		$r_prs[] = $r_sbq;
	}
	if (count($r_prs) < 3)
		return FALSE;
	if (RWSFCmp($CFG->version, 2007101546, 2) == -1) {
		if ($r_sbqct < 3)
			return FALSE;
	}
	else {
		if ($r_sbqct < 2)
			return FALSE;
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_pis = array();
	foreach ($r_prs as $r_pr) {
        $r_pr->code = rand(1, 999999999);
        while (record_exists('question_match_sub', 'code', $r_pr->code,
		  'question', $r_qst->id)) {
            $r_pr->code = rand(1, 999999999);
        }
		$r_pr->question = $r_qst->id;
		$r_pr->id = insert_record("question_match_sub",
		  addslashes_recursive($r_pr));
		if ($r_pr->id === FALSE) {
			delete_records("question", "id", $r_qst->id);
			delete_records("question_match_sub", "question", $r_qst->id);
			return FALSE;
		}
		$r_pis[] = $r_pr->id;
	}
	$r_op->question = $r_qst->id;
	$r_op->subquestions = implode(",", $r_pis);
	$r_ok = insert_record("question_match",
	  addslashes_recursive($r_op));
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		delete_records("question_match_sub", "question", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSIDRec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != DESCRIPTION)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = DESCRIPTION;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_qst->defaultgrade = 0;
	$r_qst->penalty = 0;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSIERec($r_cid, $r_qci, $r_rcd)
{
	global $CFG;
	global $USER;
	if (RWSGQRType($r_rcd) != ESSAY)
		return FALSE;
	$r_qst = new stdClass();
	$r_qst->qtype = ESSAY;
	$r_qst->parent = 0;
	$r_qst->hidden = 0;
	$r_qst->length = 1;
	$r_qst->category = $r_qci;
	$r_qst->stamp = make_unique_id_code();
	$r_qst->createdby = $USER->id;
	$r_qst->modifiedby = $USER->id;
	$r_qst->penalty = 0;
	$r_cpth = "$CFG->wwwroot/file.php";
	if ($CFG->slasharguments)
		$r_cpth .= "/$r_cid";
	else
		$r_cpth .= "?file=/$r_cid";
	$r_p = 1;
	$r_ct = 4;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_sz = $r_dat[1];
	if (strlen($r_rcd) != $r_p + $r_sz)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE || $r_ct < 1)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->name = trim($r_f); 
	if (strlen($r_qst->name) == 0)
		return FALSE;
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->questiontext = trim($r_f); 
	if (strlen($r_qst->questiontext) > 0) {
		$r_qst->questiontext = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->questiontext);
	}
	$r_qst->questiontextformat = 1; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->image = $r_f; 
	if (strlen($r_qst->image) > 0) {
		$r_qst->image = str_replace("%%COURSEPATH%%/",
		  "", $r_qst->image); 
	}
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_qst->defaultgrade = $r_dat[1];
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->generalfeedback = trim($r_f); 
	if (strlen($r_qst->generalfeedback) > 0) {
		$r_qst->generalfeedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_qst->generalfeedback);
	}
	$r_asr = new stdClass();
	$r_asr->fraction = 0; 
	if ($r_sz < 1)
		return FALSE;
	$r_ct = strpos(substr($r_rcd, $r_p), "\0");
	if ($r_ct === FALSE)
		return FALSE;
	if ($r_ct > 0)
		$r_f = substr($r_rcd, $r_p, $r_ct);
	else
		$r_f = "";
	$r_ct++; 
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_asr->feedback = trim($r_f); 
	if (strlen($r_asr->feedback) > 0) {
		$r_asr->feedback = str_replace("%%COURSEPATH%%",
		  $r_cpth, $r_asr->feedback);
	}
	$r_asr->answer = $r_asr->feedback;
	$r_ct = 4;
	if ($r_sz < $r_ct)
		return FALSE;
	$r_f = substr($r_rcd, $r_p, $r_ct);
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_dat = unpack("N", $r_f);
	$r_ct = $r_dat[1];
	if ($r_sz < $r_ct)
		return FALSE;
	$r_p += $r_ct;
	$r_sz -= $r_ct;
	$r_qst->timecreated = time();
	$r_qst->timemodified = time();
	$r_qst->id = insert_record("question",
	  addslashes_recursive($r_qst));
	if ($r_qst->id === FALSE)
		return FALSE;
	$r_h = question_hash($r_qst);
	$r_ok = set_field("question", "version", $r_h, "id", $r_qst->id);
	if (!$r_ok) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	$r_asr->question = $r_qst->id;
	$r_asr->id = insert_record("question_answers",
	  addslashes_recursive($r_asr));
	if ($r_asr->id === FALSE) {
		delete_records("question", "id", $r_qst->id);
		return FALSE;
	}
	return $r_qst->id;
}
function RWSESRec($r_qz)
{
	global $RWSLB;
	$r_f = $r_qz->intro;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	if ($r_qz->timeopen == 0) {
		$r_y = 0;
		$r_mo = 0;
		$r_da = 0;
		$r_hr = 0;
		$r_mt = 0;
	}
	else {
		$r_std = usergetdate($r_qz->timeopen);
		$r_y = $r_std['year'];
		$r_mo = $r_std['mon'];
		$r_da = $r_std['mday'];
		$r_hr = $r_std['hours'];
		$r_mt = $r_std['minutes'];
	}
	$r_f = pack("nC*", $r_y, $r_mo, $r_da, $r_hr, $r_mt);
	$r_rcd .= $r_f;
	if ($r_qz->timeclose == 0) {
		$r_y = 0;
		$r_mo = 0;
		$r_da = 0;
		$r_hr = 0;
		$r_mt = 0;
	}
	else {
		$r_end = usergetdate($r_qz->timeclose);
		$r_y = $r_end['year'];
		$r_mo = $r_end['mon'];
		$r_da = $r_end['mday'];
		$r_hr = $r_end['hours'];
		$r_mt = $r_end['minutes'];
	}
	$r_f = pack("nC*", $r_y, $r_mo, $r_da, $r_hr, $r_mt);
	$r_rcd .= $r_f;
	$r_en = ($r_qz->timelimit == 0) ? 0 : 1;
	$r_mts = $r_qz->timelimit;
	$r_f = pack("CN", $r_en, $r_mts);
	$r_rcd .= $r_f;
	$r_f = $r_qz->delay1;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->delay2;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->questionsperpage;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->shufflequestions;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->shuffleanswers;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->attempts;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->attemptonlast;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->adaptive;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->grade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->grademethod;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->penaltyscheme;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->decimalpoints;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_rsp = ($r_qz->review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_asrs = ($r_qz->review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_fb = ($r_qz->review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_gen = ($r_qz->review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_sc = ($r_qz->review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_ov = ($r_qz->review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_IMMEDIATELY) ? 1 : 0;
	$r_f = pack("C*", $r_rsp, $r_asrs, $r_fb, $r_gen, $r_sc, $r_ov);
	$r_rcd .= $r_f;
	$r_rsp = ($r_qz->review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_asrs = ($r_qz->review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_fb = ($r_qz->review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_gen = ($r_qz->review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_sc = ($r_qz->review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_ov = ($r_qz->review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_OPEN) ? 1 : 0;
	$r_f = pack("C*", $r_rsp, $r_asrs, $r_fb, $r_gen, $r_sc, $r_ov);
	$r_rcd .= $r_f;
	$r_rsp = ($r_qz->review & QUIZ_REVIEW_RESPONSES & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_asrs = ($r_qz->review & QUIZ_REVIEW_ANSWERS & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_fb = ($r_qz->review & QUIZ_REVIEW_FEEDBACK & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_gen = ($r_qz->review & QUIZ_REVIEW_GENERALFEEDBACK & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_sc = ($r_qz->review & QUIZ_REVIEW_SCORES & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_ov = ($r_qz->review & QUIZ_REVIEW_OVERALLFEEDBACK & QUIZ_REVIEW_CLOSED) ? 1 : 0;
	$r_f = pack("C*", $r_rsp, $r_asrs, $r_fb, $r_gen, $r_sc, $r_ov);
	$r_rcd .= $r_f;
	$r_f = $r_qz->popup;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->password;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->subnet;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->groupmode;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->visible;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->cmidnumber;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qz->gradecat;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_fbt = array();
	$r_fbb = array();
	$r_qzf = get_records("quiz_feedback", "quizid", $r_qz->id,
	  "mingrade DESC");
	if ($r_qzf !== FALSE) {
		foreach ($r_qzf as $r_qf) {
			$r_fbt[] = $r_qf->feedbacktext;
            if ($r_qf->mingrade > 0) {
				$r_bd = (100.0 * $r_qf->mingrade / $r_qz->grade) . "%";
				$r_fbb[] = $r_bd;
			}
		}
	}
	$r_f = count($r_fbt);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_fbt as $r_fd) {
		$r_f = $r_fd;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
	}
	$r_f = count($r_fbb);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_fbb as $r_bd) {
		$r_f = $r_bd;
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
	}
	RWSLLBSet($r_qz);
	$r_f = $RWSLB->atts;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $RWSLB->revs;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $RWSLB->pw;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	return $r_rcd;
}
function RWSLLBSet($r_qz)
{
	global $RWSLB;
	$RWSLB->atts = 0; 
	$RWSLB->revs = 0; 
	$RWSLB->pw = ""; 
	$RWSLB->gerr = FALSE;
	if ($RWSLB->mok) {
		$r_op = lockdown_get_quiz_options($r_qz->instance);
		if (!$r_op)
			$RWSLB->gerr = TRUE;
		else {
			$RWSLB->atts = $r_op->attempts;
			$RWSLB->revs = $r_op->reviews;
			$RWSLB->pw = $r_op->password;
		}
	} else if ($RWSLB->bok) {
		$r_op = lockdown_get_quiz_options($r_qz->instance);
		if (!$r_op)
			$RWSLB->gerr = TRUE;
		else {
			$RWSLB->atts = $r_op->attempts;
		}
	}
}
function RWSERRec($r_dat)
{
	$r_rcd = "";
	$r_l = strlen($r_dat);
	if ($r_l > 0)
		$r_rcd .= $r_dat;
	if ($r_l > 0) {
		$r_f = crc32($r_dat);
		$r_f = pack("N", $r_f);
		$r_rcd .= $r_f;
	}
	$r_rd  = pack("C", 12); 
	$r_rd .= pack("N", strlen($r_rcd)); 
	$r_rd .= $r_rcd; 
	return $r_rd;
}
function RWSESARec($r_qst)
{
	if ($r_qst->qtype != SHORTANSWER)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_op = get_record("question_shortanswer", "question", $r_qst->id);
	if ($r_op === FALSE)
		return FALSE;
	$r_f = $r_op->usecase;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_asrs = array();
	$r_aid = explode(",", $r_op->answers);
	foreach($r_aid as $r_id) {
		$r_asr = get_record("question_answers", "id", $r_id);
		if ($r_asr === FALSE)
			return FALSE;
		$r_asrs[] = $r_asr;
	}
	$r_f = count($r_asrs);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_asrs as $r_asr) {
		$r_f = $r_asr->answer;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_asr->fraction;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_asr->feedback;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
	}
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 3); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSETFRec($r_qst)
{
	if ($r_qst->qtype != TRUEFALSE)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_op = get_record("question_truefalse", "question", $r_qst->id);
	if ($r_op === FALSE)
		return FALSE;
	$r_tru = get_record("question_answers", "id", $r_op->trueanswer);
	if ($r_tru === FALSE)
		return FALSE;
	$r_fal = get_record("question_answers", "id", $r_op->falseanswer);
	if ($r_fal === FALSE)
		return FALSE;
	$r_f = $r_tru->fraction;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_tru->feedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_fal->feedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 2); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSEMARec($r_qst)
{
	if ($r_qst->qtype != MULTIANSWER)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_clzf = RWSGCFields($r_qst->questiontext);
	if ($r_clzf === FALSE)
		return FALSE;
	$r_op = get_record("question_multianswer", "question", $r_qst->id);
	if ($r_op === FALSE)
		return FALSE;
	$r_chid = explode(",", $r_op->sequence);
	$r_chc = count($r_chid);
	if ($r_chc != count($r_clzf))
		return FALSE;
	for ($r_i = 0; $r_i < $r_chc; $r_i++) {
		$r_chd = get_record("question", "id", $r_chid[$r_i]);
		if ($r_chd === FALSE)
			return FALSE;
		$r_pk = $r_i+1;
		$r_qst->questiontext = implode($r_chd->questiontext,
		  explode($r_clzf[$r_i], $r_qst->questiontext, 2));
	}
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 9); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSCRFISort($r_rc1, $r_rc2)
{
	if ($r_rc1->id == $r_rc2->id)
		return 0;
	return ($r_rc1->id < $r_rc2->id) ? -1 : 1;
}
function RWSECRec($r_qst)
{
	if ($r_qst->qtype != CALCULATED)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_asrs = get_records("question_answers", "question", $r_qst->id);
	if ($r_asrs === FALSE)
		return FALSE;
	if (count($r_asrs) > 1)
		usort($r_asrs, "RWSCRFISort");
	$r_f = count($r_asrs);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_asrs as $r_an) {
		$r_f = $r_an->answer;
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_an->fraction;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_an->feedback;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_o = get_record("question_calculated", "answer", $r_an->id);
		if ($r_o === FALSE)
			return FALSE;
		$r_f = $r_o->tolerance;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_o->tolerancetype;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_o->correctanswerlength;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_o->correctanswerformat;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
	}
	$r_uts = get_records("question_numerical_units",
	  "question", $r_qst->id);
	if ($r_uts !== FALSE && count($r_uts) > 1)
		usort($r_uts, "RWSCRFISort");
	if ($r_uts !== FALSE)
		$r_f = count($r_uts);
	else
		$r_f = 0;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	if ($r_uts !== FALSE)
	{
		foreach($r_uts as $r_ut) {
			$r_f = $r_ut->unit;
			$r_f = pack("a*x", $r_f);
			$r_rcd .= $r_f;
			$r_f = $r_ut->multiplier;
			$r_f = RWSDblOut($r_f);
			$r_rcd .= $r_f;
		}
	}
	$r_dset = get_records("question_datasets", "question", $r_qst->id);
	if ($r_dset === FALSE)
		return FALSE;
	$r_f = count($r_dset);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_dset as $r_qds) {
		$r_df = get_record("question_dataset_definitions",
		  "id", $r_qds->datasetdefinition);
		if ($r_df === FALSE)
			return FALSE;
		$r_f = $r_df->name;
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		list($r_dstr, $r_mi, $r_mx, $r_pre) =
		  explode(":", $r_df->options, 4);
		$r_f = $r_dstr;
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_mi;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_mx;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_pre;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_df->type;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		if ($r_df->category == 0)
			$r_f = 0; 
		else 
			$r_f = 1;
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		$r_its = get_records("question_dataset_items",
		  "definition", $r_df->id);
		if ($r_its === FALSE)
			return FALSE;
		$r_f = count($r_its);
		$r_f = pack("C", $r_f);
		$r_rcd .= $r_f;
		foreach($r_its as $r_it) {
			$r_f = $r_it->itemnumber;
			$r_f = pack("C", $r_f);
			$r_rcd .= $r_f;
			$r_f = $r_it->value;
			$r_f = RWSDblOut($r_f);
			$r_rcd .= $r_f;
		}
	}
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 7); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSEMCRec($r_qst)
{
	if ($r_qst->qtype != MULTICHOICE)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_op = get_record("question_multichoice", "question", $r_qst->id);
	if ($r_op === FALSE)
		return FALSE;
	$r_f = $r_op->single;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_op->shuffleanswers;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_op->answernumbering;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_asrs = array();
	$r_aid = explode(",", $r_op->answers);
	foreach($r_aid as $r_id) {
		$r_asr = get_record("question_answers", "id", $r_id);
		if ($r_asr === FALSE)
			return FALSE;
		$r_asrs[] = $r_asr;
	}
	$r_f = count($r_asrs);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_asrs as $r_asr) {
		$r_f = $r_asr->answer;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_asr->fraction;
		$r_f = RWSDblOut($r_f);
		$r_rcd .= $r_f;
		$r_f = $r_asr->feedback;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
	}
	$r_f = $r_op->correctfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_op->partiallycorrectfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_op->incorrectfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 1); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSEMRec($r_qst)
{
	if ($r_qst->qtype != MATCH)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->penalty;
	$r_f = RWSDblOut($r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_op = get_record("question_match", "question", $r_qst->id);
	if ($r_op === FALSE)
		return FALSE;
	$r_f = $r_op->shuffleanswers;
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	$r_prs = array();
	$r_pis = explode(",", $r_op->subquestions);
	foreach($r_pis as $r_id) {
		$r_pr = get_record("question_match_sub", "id", $r_id);
		if ($r_pr === FALSE)
			return FALSE;
		$r_prs[] = $r_pr;
	}
	$r_f = count($r_prs);
	$r_f = pack("C", $r_f);
	$r_rcd .= $r_f;
	foreach($r_prs as $r_pr) {
		$r_f = $r_pr->questiontext;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
		$r_f = $r_pr->answertext;
		if (!RWSIVUtf8($r_f))
			$r_f = utf8_encode($r_f);
		$r_f = pack("a*x", $r_f);
		$r_rcd .= $r_f;
	}
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 5); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSEDRec($r_qst)
{
	if ($r_qst->qtype != DESCRIPTION)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 6); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSEERec($r_qst)
{
	if ($r_qst->qtype != ESSAY)
		return FALSE;
	if ($r_qst->parent != 0)
		return FALSE;
	$r_f = $r_qst->name;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd = $r_f;
	$r_f = $r_qst->questiontext;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->image;
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->defaultgrade;
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = $r_qst->generalfeedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_asr = get_record("question_answers", "question", $r_qst->id);
	if ($r_asr === FALSE)
		return FALSE;
	$r_f = $r_asr->feedback;
	if (!RWSIVUtf8($r_f))
		$r_f = utf8_encode($r_f);
	$r_f = pack("a*x", $r_f);
	$r_rcd .= $r_f;
	$r_f = 8; 
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = time();
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_f = crc32($r_rcd);
	$r_f = pack("N", $r_f);
	$r_rcd .= $r_f;
	$r_qd  = pack("C", 4); 
	$r_qd .= pack("N", strlen($r_rcd)); 
	$r_qd .= $r_rcd; 
	return $r_qd;
}
function RWSCEData($r_uf, $r_cf)
{
	$r_sps = array($r_uf);
	$r_ok = zip_files($r_sps, $r_cf);
	return $r_ok;
}
function RWSDIData($r_fdat, $r_imd)
{
	$r_clntf = FALSE;
	$r_tpp = RWSGTPath();
	$r_tpf = tempnam($r_tpp, "rws");
	$r_ok = ($r_tpf !== FALSE);
	if ($r_ok) {
		$r_pp = pathinfo($r_tpf);
		$r_ext = $r_pp['extension'];
		if (empty($r_ext)) {
			$r_old = $r_tpf;
			$r_tpf .= ".tmp";
			unlink($r_tpf);
			$r_ok = rename($r_old, $r_tpf);
		}
	}
	if ($r_ok) {
		$r_tmp = fopen($r_tpf, "wb"); 
		$r_ok = ($r_tmp !== FALSE);
		$r_clntf = $r_ok;
	}
	if ($r_ok) {
		$r_by = fwrite($r_tmp, $r_fdat);
		$r_ok = ($r_by !== FALSE);
	}
	if ($r_clntf)
		fclose($r_tmp);
	if ($r_ok)
		$r_ok = unzip_file($r_tpf, $r_imd, false);
	if ($r_clntf)
		unlink($r_tpf);
	return $r_ok;
}
function RWSMTFldr()
{
	$r_tpp = RWSGTPath();
	$r_ok = ($r_tpp !== FALSE);
	if ($r_ok) {
		$r_tpf = tempnam($r_tpp, "rws");
		$r_ok = ($r_tpf !== FALSE);
	}
	if ($r_ok)
		$r_ok = unlink($r_tpf);
	if ($r_ok)
		$r_ok = mkdir($r_tpf);
	if ($r_ok)
		return $r_tpf;
	else
		return FALSE;
}
function RWSEQCQues($r_qci, &$r_qfl, &$r_drp, $r_w64)
{
    $r_drp = 0;
	$r_qd = "";
	$r_qtps = array();
	$r_qsts = get_records("question", "category", $r_qci);
	if ($r_qsts !== FALSE) {
		foreach ($r_qsts as $r_q) {
			if ($r_q->parent == 0)
				$r_qtps[] = $r_q;
		}
	}
	if (count($r_qtps) < 1) {
		RWSSErr("2102");
	}
	$r_qd = RWSEQues($r_qtps, $r_qfl, $r_drp, $r_w64);
	return $r_qd;
}
function RWSEQQues($r_qzi, &$r_qfl, &$r_drp, &$r_ran, $r_w64)
{
    $r_drp = 0;
    $r_ran = 0;
	$r_mss = 0;
	$r_cmod = get_record("course_modules", "id", $r_qzi);
	if (!$r_cmod)
		RWSSErr("2042"); 
	$r_mr = get_record("modules", "id", $r_cmod->module);
    if (!$r_mr ) 
        RWSSErr("2043");
 	$r_qz = get_record($r_mr->name, "id", $r_cmod->instance);
	if (!$r_qz ) 
        RWSSErr("2044");
    $r_qd = "";
    $r_qis = explode(",", $r_qz->questions);
	$r_qsts = array();
	if ($r_qis !== FALSE) {
		foreach ($r_qis as $r_id) {
			if ($r_id == "0")
				continue; 
			$r_q = get_record("question", "id", $r_id);
			if ($r_q !== FALSE)
				$r_qsts[] = $r_q;
			else
				$r_mss++;
		}
	}
	if (count($r_qsts) < 1) {
		RWSSErr("2103");
	}
	$r_qd = RWSEQues(
	  $r_qsts, $r_qfl, $r_drp, $r_ran, $r_w64);
	$r_drp += $r_mss;
	return $r_qd;
}
function RWSEQues($r_qsts, &$r_qfl, &$r_drp, &$r_ran, $r_w64)
{
		$r_fv = 0; 
	$r_fnc = "rwsexportqdata.zip";
	$r_fnu = "rwsexportqdata.dat";
	$r_qfl = "";
	$r_exp = 0;
	$r_drp = 0;
	$r_ran = 0;
	$r_clned = FALSE;
	$r_clnef = FALSE;
	$r_clncf = FALSE;
	$r_cloef = FALSE;
	$r_ok = (count($r_qsts) > 0);
	if (!$r_ok)
		return "";
	if ($r_ok) {
		$r_exd = RWSMTFldr();
		$r_ok = ($r_exd !== FALSE);
		$r_clned = $r_ok;
		if (!$r_ok)
			$r_err = "2045"; 
	}
	if ($r_ok) {
		$r_exf = "$r_exd/$r_fnu";
		$r_hdl = fopen($r_exf, "wb"); 
		$r_ok = ($r_hdl !== FALSE);
		$r_clnef = $r_ok;
		$r_cloef = $r_ok;
		if (!$r_ok)
			$r_err = "2046"; 
	}
	if ($r_ok) {
			$r_dat = pack("C*", 0xc7, 0x89, 0xf0, 0x4c, 0xa4, 0x03, 0x47, 0x9b,
			  0xa3, 0x7b, 0x29, 0xc6, 0xad, 0xd5, 0x30, 0x81);
		$r_dat .= pack("n", $r_fv);
		$r_by = fwrite($r_hdl, $r_dat);
		$r_ok = ($r_by !== FALSE);
		if (!$r_ok)
			$r_err = "2047"; 
	}
	if ($r_ok) {
		$r_i = 0;
		foreach ($r_qsts as $r_q) {
			$r_i++;
			if ($r_i % 10 == 0) {
				$r_rcd = RWSERRec(time());
				$r_ok2 = ($r_rcd !== FALSE);
				if ($r_ok2)
					RWSWNQRec($r_hdl, $r_rcd);
			}
			switch ($r_q->qtype) {
			case SHORTANSWER:
				$r_rcd = RWSESARec($r_q);
				break;
			case TRUEFALSE:
				$r_rcd = RWSETFRec($r_q);
				break;
			case MULTICHOICE:
				$r_rcd = RWSEMCRec($r_q);
				break;
			case MATCH:
				$r_rcd = RWSEMRec($r_q);
				break;
			case DESCRIPTION:
				$r_rcd = RWSEDRec($r_q);
				break;
			case ESSAY:
				$r_rcd = RWSEERec($r_q);
				break;
			case CALCULATED:
				$r_rcd = RWSECRec($r_q);
				break;
			case MULTIANSWER: 
				$r_rcd = RWSEMARec($r_q);
				break;
			case RANDOM:
				$r_ran++;
				$r_rcd = FALSE;
				break;
			case NUMERICAL:
			case RANDOMSAMATCH:
			default:
				$r_rcd = FALSE;
				break;
			}
			$r_ok2 = ($r_rcd !== FALSE);
			if ($r_ok2)
				$r_ok2 = RWSWNQRec($r_hdl, $r_rcd);
			if ($r_ok2)
				$r_exp++;
			else
				$r_drp++;
		}
    }
	if ($r_cloef)
		fclose($r_hdl);
	if ($r_ok && $r_exp > 0) {
		$r_cf = "$r_exd/$r_fnc";
		$r_ok = RWSCEData($r_exf, $r_cf);
		$r_clncf = $r_ok;
		if (!$r_ok)
			$r_err = "2048"; 
	}
	if ($r_ok && $r_exp > 0) {
		$r_cpr = file_get_contents($r_cf);
		$r_ok = ($r_cpr !== FALSE);
		if (!$r_ok)
			$r_err = "2049"; 
	}
	if ($r_ok && $r_exp > 0 && $r_w64)
		$r_ecd = base64_encode($r_cpr);
	if ($r_clnef)
		unlink($r_exf);
	if ($r_clncf)
		unlink($r_cf);
	if ($r_clned)
		rmdir($r_exd);
	if (!$r_ok)
		RWSSErr($r_err);
	if ($r_exp == 0) {
		RWSSErr("2104");
	}
	$r_qfl = $r_fnc;
	if ($r_w64)
		return $r_ecd;
	else
		return $r_cpr;
}
function RWSUQGrades($r_qz)
{
	$r_gi = grade_item::fetch(array('itemtype'=>'mod',
	  'itemmodule'=>$r_qz->modulename, 'iteminstance'=>$r_qz->instance,
	  'itemnumber'=>0, 'courseid'=>$r_qz->course));
     if ($r_gi && $r_gi->idnumber != $r_qz->cmidnumber) {
         $r_gi->idnumber = $r_qz->cmidnumber;
         $r_gi->update();
     }
    $r_its = grade_item::fetch_all(array('itemtype'=>'mod',
	  'itemmodule'=>$r_qz->modulename, 'iteminstance'=>$r_qz->instance,
	  'courseid'=>$r_qz->course));
    if ($r_its && isset($r_qz->gradecat)) {
        if ($r_qz->gradecat == -1) {
            $grade_category = new grade_category();
            $grade_category->courseid = $r_qz->course;
            $grade_category->fullname = $r_qz->name;
            $grade_category->insert();
            if ($r_gi) {
                $parent = $r_gi->get_parent_category();
                $grade_category->set_parent($parent->id);
            }
            $r_qz->gradecat = $grade_category->id;
        }
        foreach ($r_its as $r_iti=>$r_un) {
            $r_its[$r_iti]->set_parent($r_qz->gradecat);
            if ($r_iti == $r_gi->id)
                $r_gi = $r_its[$r_iti]; 
        }
    }
    if ($r_ocs = grade_outcome::fetch_all_available($r_qz->course)) {
        $r_gis = array();
        $r_mit = 999;
        if ($r_its) {
            foreach($r_its as $r_it) {
                if ($r_it->itemnumber > $r_mit)
                    $r_mit = $r_it->itemnumber;
            }
        }
        foreach($r_ocs as $r_oc) {
            $r_eln = 'outcome_'.$r_oc->id;
            if (array_key_exists($r_eln, $r_qz) and $r_qz->$r_eln) {
                if ($r_its) {
                    foreach($r_its as $r_it) {
                        if ($r_it->outcomeid == $r_oc->id)
                            continue 2; 
                    }
                }
                $r_mit++;
                $r_oi = new grade_item();
                $r_oi->courseid     = $r_qz->course;
                $r_oi->itemtype     = 'mod';
                $r_oi->itemmodule   = $r_qz->modulename;
                $r_oi->iteminstance = $r_qz->instance;
                $r_oi->itemnumber   = $r_mit;
                $r_oi->itemname     = $r_oc->fullname;
                $r_oi->outcomeid    = $r_oc->id;
                $r_oi->gradetype    = GRADE_TYPE_SCALE;
                $r_oi->scaleid      = $r_oc->scaleid;
                $r_oi->insert();
                if ($r_gi) {
                    $r_oi->set_parent($r_gi->categoryid);
                    $r_oi->move_after_sortorder($r_gi->sortorder);
                } else if (isset($r_qz->gradecat)) {
                    $r_oi->set_parent($r_qz->gradecat);
                }
            }
        }
    }
}
function RWSDQCat($r_qci)
{
	$r_chn = get_records("question_categories", "parent", $r_qci);
	if ($r_chn !== FALSE) {
		foreach ($r_chn as $r_chd)
			RWSDQCat($r_chd->id);
	}
	$r_qsts = get_records("question", "category", $r_qci);
	if ($r_qsts !== FALSE) {
		foreach ($r_qsts as $r_q)
			delete_question($r_q->id);
		delete_records("question", "category", $r_qci);
	}
	delete_records("question_categories", "id", $r_qci);
}
function RWSIQCUsed($r_qci)
{
	$r_chn = get_records("question_categories", "parent", $r_qci);
	if ($r_chn !== FALSE) {
		foreach ($r_chn as $r_chd) {
			if (RWSIQCUsed($r_chd->id))
				return TRUE;
		}
	}
	$r_qsts = get_records("question", "category", $r_qci);
	if ($r_qsts !== FALSE) {
		foreach ($r_qsts as $r_q) {
			if (count(question_list_instances($r_q->id)) > 0)
				return TRUE;
		}
	}
	return FALSE;	
}
function RWSIVUtf8($r_str)
{ 
	$r_l = strlen($r_str);
	$r_i = 0;
	while ($r_i < $r_l) {
		$r_c0 = ord($r_str[$r_i]);
		if ($r_i+1 < $r_l)
			$r_c1 = ord($r_str[$r_i+1]);
		if ($r_i+2 < $r_l)
			$r_c2 = ord($r_str[$r_i+2]);
		if ($r_i+3 < $r_l)
			$r_c3 = ord($r_str[$r_i+3]);
		if ($r_c0 >= 0x00 && $r_c0 <= 0x7e) {
			$r_i++;
		}
		else if ($r_i+1 < $r_l
		  && $r_c0 >= 0xc2 && $r_c0 <= 0xdf
		  && $r_c1 >= 0x80 && $r_c1 <= 0xbf) {
			$r_i += 2;
		}
		else if ($r_i+2 < $r_l
		  && $r_c0 == 0xe0
		  && $r_c1 >= 0xa0 && $r_c1 <= 0xbf
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf) {
			$r_i += 3;
		}
		else if ($r_i+2 < $r_l
		  && (($r_c0 >= 0xe1 && $r_c0 <= 0xec) || $r_c0 == 0xee || $r_c0 == 0xef)
		  && $r_c1 >= 0x80 && $r_c1 <= 0xbf
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf) {
			$r_i += 3;
		}
		else if ($r_i+2 < $r_l
		  && $r_c0 == 0xed
		  && $r_c1 >= 0x80 && $r_c1 <= 0x9f
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf) {
			$r_i += 3;
		}
		else if ($r_i+3 < $r_l
		  && $r_c0 == 0xf0
		  && $r_c1 >= 0x90 && $r_c1 <= 0xbf
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf
		  && $r_c3 >= 0x80 && $r_c3 <= 0xbf) {
			$r_i += 4;
		}
		else if ($r_i+3 < $r_l
		  && $r_c0 >= 0xf1 && $r_c0 <= 0xf3
		  && $r_c1 >= 0x80 && $r_c1 <= 0xbf
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf
		  && $r_c3 >= 0x80 && $r_c3 <= 0xbf) {
			$r_i += 4;
		}
		else if ($r_i+3 < $r_l
		  && $r_c0 == 0xf4
		  && $r_c1 >= 0x80 && $r_c1 <= 0x8f
		  && $r_c2 >= 0x80 && $r_c2 <= 0xbf
		  && $r_c3 >= 0x80 && $r_c3 <= 0xbf) {
			$r_i += 4;
		}
		else {
			return FALSE;
		}
	}
	return TRUE;
}
function RWSDSAct($r_ac)
{
	if ($r_ac == "phpinfo")
		RWSAPInfo();
	else if ($r_ac == "serviceinfo")
		RWSASInfo();
	else if ($r_ac == "login")
		RWSAILog();
	else if ($r_ac == "logout")
		RWSAOLog();
	else if ($r_ac == "courselist")
		RWSACList();
	else if ($r_ac == "sectionlist")
		RWSASList();
	else if ($r_ac == "quizlist")
		RWSAQList();
	else if ($r_ac == "qcatlist")
		RWSAQCList();
	else if ($r_ac == "addqcat")
		RWSAAQCat();
	else if ($r_ac == "deleteqcat")
		RWSADQCat();
	else if ($r_ac == "deletequiz")
		RWSADQuiz();
	else if ($r_ac == "addquiz")
		RWSAAQuiz();
	else if ($r_ac == "updatequiz")
		RWSAUQuiz();
	else if ($r_ac == "addqlist")
		RWSAAQList();
	else if ($r_ac == "addqrand")
		RWSAAQRand();
	else if ($r_ac == "importqdata")
		RWSAIQData();
	else if ($r_ac == "getquiz")
		RWSAGQuiz();
	else if ($r_ac == "exportqdata")
		RWSAEQData();
	else if ($r_ac == "uploadfile")
		RWSAUFile();
	else if ($r_ac == "dnloadfile")
		RWSADFile();
	else
		RWSSErr("2050");
}
function RWSAPInfo()
{
	RWSCMAuth();
	RWSCMUSvc();
	$r_sctx = get_context_instance(CONTEXT_SYSTEM);
	$r_ia = has_capability("moodle/site:doanything", $r_sctx);
	if (!$r_ia)
		$r_ia = has_capability("moodle/site:config", $r_sctx);
	if (!$r_ia) {
		RWSSErr("2107");
	}
	phpinfo();
	exit;
}
function RWSASInfo()
{
	global $CFG;
	global $RWSLB;
	$r_bv = intval(RWSGSOpt("version"));
	$r_ilg = isloggedin();
	$r_sctx = get_context_instance(CONTEXT_SYSTEM);
	$r_ia = has_capability("moodle/site:doanything", $r_sctx);
	if (!$r_ia)
		$r_ia = has_capability("moodle/site:config", $r_sctx);
	$r_su = RWSGSUrl();
	$r_ver = "";
	$r_rel = "";
	$r_req = "";
	$r_lt = "";
	$r_vf = RWSGMPath() . "/version.php";
	if (is_readable($r_vf))
		include($r_vf);
	if ($module) {
		if (!empty($module->version))
			$r_ver = $module->version;
		if (!empty($module->rws_release))
			$r_rel = $module->rws_release;
		if (!empty($module->requires))
			$r_req = $module->requires;
		if (!empty($module->requires))
			$r_req = $module->requires;
		if (!empty($module->rws_latest))
			$r_lt = $module->rws_latest;
	}
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<service_info>\r\n";
	if ($r_ia)
		echo "\t<description>Respondus 4.0 Web Service Extension For Moodle</description>\r\n";
	else
		echo "\t<description>(authentication required)</description>\r\n";
	if (!empty($r_ver)) {
		echo "\t<module_version>";
		if ($r_bv >= 2010042801) 
			echo utf8_encode(htmlspecialchars($r_ver));
		else 
			echo "2009093000";
		echo "</module_version>\r\n";
	}
	else
		echo "\t<module_version />\r\n";
	if (!empty($r_rel)) {
		echo "\t<module_release>";
		if ($r_bv >= 2010042801) 
			echo utf8_encode(htmlspecialchars($r_rel));
		else 
			echo "1.0.2";
		echo "</module_release>\r\n";
	}
	else
		echo "\t<module_release />\r\n";
	if ($r_bv >= 2010042801) { 
		echo "\t<module_behavior>";
		echo utf8_encode(htmlspecialchars($r_bv));
		echo "</module_behavior>\r\n";
	}
	if ($r_ia) {
		if (!empty($r_req)) {
			echo "\t<module_requires>";
			echo utf8_encode(htmlspecialchars($r_req));
			echo "</module_requires>\r\n";
		}
		else
			echo "\t<module_requires />\r\n";
	}
	else
		echo "\t<module_requires>(authentication required)</module_requires>\r\n";
	if ($r_ia) {
		if (!empty($r_lt)) {
			echo "\t<module_latest>";
			echo utf8_encode(htmlspecialchars($r_lt));
			echo "</module_latest>\r\n";
		}
		else
			echo "\t<module_latest />\r\n";
	}
	else
		echo "\t<module_latest>(authentication required)</module_latest>\r\n";
	if ($r_ia) {
		echo "\t<endpoint>";
		echo utf8_encode(htmlspecialchars($r_su));
		echo "</endpoint>\r\n";
	}
	else
		echo "\t<endpoint>(authentication required)</endpoint>\r\n";
	if ($r_ia) {
		echo "\t<whoami>";
		echo utf8_encode(htmlspecialchars(exec("whoami")));
		echo "</whoami>\r\n";
	}
	else
		echo "\t<whoami>(authentication required)</whoami>\r\n";
	if ($r_ilg) {
		echo "\t<moodle_version>";
		echo utf8_encode(htmlspecialchars($CFG->version));
		echo "</moodle_version>\r\n";
	}
	else
		echo "\t<moodle_version>(authentication required)</moodle_version>\r\n";
	if ($r_ilg) {
		echo "\t<moodle_release>";
		echo utf8_encode(htmlspecialchars($CFG->release));
		echo "</moodle_release>\r\n";
	}
	else
		echo "\t<moodle_release>(authentication required)</moodle_release>\r\n";
	if ($r_ia) {
		echo "\t<moodle_site_id>";
		echo utf8_encode(htmlspecialchars(SITEID));
		echo "</moodle_site_id>\r\n";
	}
	else
		echo "\t<moodle_site_id>(authentication required)</moodle_site_id>\r\n";
	if ($r_ia) {
		echo "\t<moodle_maintenance>";
		if (file_exists($CFG->dataroot . "/" . SITEID . "/maintenance.html"))
			echo "yes";
		else
			echo "no";
		echo "</moodle_maintenance>\r\n";
	}
	else if ($r_bv >= 2010063001) 
		echo "\t<moodle_maintenance>(authentication required)</moodle_maintenance>\r\n";
	else 
		echo "\t<moodle_maintenance>no</moodle_maintenance>\r\n";
	if ($r_ia) {
		$r_mn = get_list_of_plugins("mod");
		if ($r_mn && count($r_mn) > 0) {
			$r_ml = implode(",", $r_mn);
			echo "\t<moodle_module_types>";
			echo utf8_encode(htmlspecialchars(trim($r_ml)));
			echo "</moodle_module_types>\r\n";
		}
		else
			echo "\t<moodle_module_types />\r\n";
	}
	else
		echo "\t<moodle_module_types>(authentication required)</moodle_module_types>\r\n";
	if ($r_ia) {
		$r_qtn = get_list_of_plugins("question/type");
		$r_irx = FALSE;
		foreach ($r_qtn as $r_qn) {
			if (strcasecmp($r_qn, "regexp") == 0) {
				$r_irx = TRUE;
				break;
			}
		}
		if ($r_qtn && count($r_qtn) > 0) {
			$r_qtl = implode(",", $r_qtn);
			echo "\t<moodle_question_types>";
			echo utf8_encode(htmlspecialchars(trim($r_qtl)));
			echo "</moodle_question_types>\r\n";
		}
		else
			echo "\t<moodle_question_types />\r\n";
	}
	else
		echo "\t<moodle_question_types>(authentication required)</moodle_question_types>\r\n";
	if ($r_ilg) {
		echo "\t<cloze_regexp_support>";
		$r_pth = "$CFG->dirroot/question/type/multianswer/questiontype.php";
		$r_dat = file_get_contents($r_pth);
		if ($r_dat !== FALSE
		  && strpos($r_dat, "ANSWER_REGEX_ANSWER_TYPE_REGEXP") !== FALSE) {
			if ($r_bv >= 2010063001) { 
				if ($r_irx)
					echo "yes";
				else
					echo "no";
			}
			else
				echo "yes";
		}
		else
			echo "no";
		echo "</cloze_regexp_support>\r\n";
	}
	else if ($r_bv >= 2010063001) 
		echo "\t<cloze_regexp_support>(authentication required)</cloze_regexp_support>\r\n";
	else 
		echo "\t<cloze_regexp_support>no</cloze_regexp_support>\r\n";
	if ($r_ilg) {
		echo "\t<ldb_module_detected>";
		if ($RWSLB->mex || $RWSLB->bex)
			echo "yes";
		else
			echo "no";
		echo "</ldb_module_detected>\r\n";
	}
	else if ($r_bv >= 2010063001) 
		echo "\t<ldb_module_detected>(authentication required)</ldb_module_detected>\r\n";
	else 
		echo "\t<ldb_module_detected>no</ldb_module_detected>\r\n";
	if ($r_ilg) {
		echo "\t<ldb_module_ok>";
		if ($RWSLB->mok || $RWSLB->bok)
			echo "yes";
		else
			echo "no";
		echo "</ldb_module_ok>\r\n";
	}
	else if ($r_bv >= 2010063001) 
		echo "\t<ldb_module_ok>(authentication required)</ldb_module_ok>\r\n";
	else 
		echo "\t<ldb_module_ok>no</ldb_module_ok>\r\n";
	echo "</service_info>\r\n";
	exit;
}
function RWSFCmp($r_f1, $r_f2, $r_pre)
{
	if ($r_pre < 0)
		$r_pre = 0;
	$r_eps = 1 / pow(10, $r_pre);
	$r_dif = ($r_f1 - $r_f2);
	if (abs($r_dif) < $r_eps)
		return 0;
	else if ($r_dif < 0)
		return -1;
	else
		return 1;
}
function RWSDblOut($r_val)
{
	$r_t = unpack("C*", pack("S", 256));
	$r_ch = array_values(unpack("C*", pack("d", $r_val)));
	if($r_t[1] == 1) {
		$r_by = $r_ch;
	} else {
		$r_by = array_reverse($r_ch);
	}
	$r_bn = "";
	foreach ($r_by as $r_b)
		$r_bn .= pack("C", $r_b);
	return $r_bn;
}
function RWSDblIn($r_val)
{
	$r_t = unpack("C*", pack("S", 256));
	$r_by = array_values(unpack("C*", $r_val));
	if($r_t[1] == 1) {
		$r_ch = $r_by;
	} else {
		$r_ch = array_reverse($r_by);
	}
	$r_bn = "";
	foreach ($r_ch as $r_c)
		$r_bn .= pack("C", $r_c);
	$r_d = unpack("d", $r_bn);
	return $r_d[1];
}
function RWSGCFCat($r_ctx)
{
	switch($r_ctx->contextlevel) {
	case CONTEXT_COURSE:
		$r_cid = $r_ctx->instanceid;
		break;
	case CONTEXT_MODULE:
		$r_cid = get_field("course_modules", "course", "id",
		  $r_ctx->instanceid);
		break;
	case CONTEXT_COURSECAT:
	case CONTEXT_SYSTEM:
		$r_cid = SITEID;
		break;
	default: 
		RWSSErr("2053");
	}
	return $r_cid;
}
function RWSAILog()
{
	if (!$RWSIGSLOG) {
		if ($CFG->loginhttps) {
			if (empty($_SERVER["HTTPS"])
			  || strcasecmp($_SERVER["HTTPS"], "off") == 0) {
				RWSSErr("4001"); 
			}
		}
	}
	$r_usr = RWSGSOpt("username");
	if ($r_usr === FALSE || strlen($r_usr) == 0)
		RWSSErr("2054"); 
	$r_pw = RWSGSOpt("password");
	if ($r_pw === FALSE || strlen($r_pw) == 0)
		RWSSErr("2055"); 
	if (isloggedin())
		RWSSErr("2056"); 
	RWSAMUser($r_usr, $r_pw);
	RWSSStat("1000"); 
}
function RWSAOLog()
{
	RWSCMAuth();
	RWSLMUser();
	RWSSStat("1001"); 
}
function RWSACList()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_crss = RWSGUMCList();
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	if (count($r_crss) == 0) {
		echo "<courselist />\r\n";
		exit;
	}
	echo "<courselist>\r\n";
	foreach ($r_crss as $r_c) {
		echo "\t<course>\r\n";
		echo "\t\t<name>";
		echo utf8_encode(htmlspecialchars(trim($r_c->fullname)));
		echo "</name>\r\n";
		echo "\t\t<id>";
		echo utf8_encode(htmlspecialchars(trim($r_c->id)));
		echo "</id>\r\n";
		echo "\t</course>\r\n";
	}
	echo "</courselist>\r\n";
	exit;
}
function RWSASList()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	$r_cid = intval($r_pm);
	$r_crs = RWSCMUCourse($r_cid);
	$r_secs = RWSGUVSList($r_cid);
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	if (count($r_secs) == 0) {
		echo "<sectionlist />\r\n";
		exit;
	}
	echo "<sectionlist>\r\n";
	$r_fnm = get_section_name($r_crs->format);
	echo "\t<format_name>";
	echo utf8_encode(htmlspecialchars(trim($r_fnm)));
	echo "</format_name>\r\n";
	foreach ($r_secs as $r_s) {
		echo "\t<section>\r\n";
		$r_sum = trim($r_s->summary);
		if (strlen($r_sum) > 0) {
			echo "\t\t<summary>";
			echo utf8_encode(htmlspecialchars($r_sum));
			echo "</summary>\r\n";
		}
		else
			echo "\t\t<summary />\r\n";
		echo "\t\t<id>";
		echo utf8_encode(htmlspecialchars(trim($r_s->id)));
		echo "</id>\r\n";
		echo "\t\t<relative_index>";
		echo utf8_encode(htmlspecialchars(trim($r_s->section)));
		echo "</relative_index>\r\n";
		echo "\t</section>\r\n";
	}
	echo "</sectionlist>\r\n";
	exit;
}
function RWSAQList()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	$r_cid = intval($r_pm);
	RWSCMUCourse($r_cid);
	$r_vq = RWSGUVQList($r_cid);
	if (count($r_vq) > 0)
		$r_mq = RWSGUMQList($r_vq);
	else
		$r_mq = array();
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	if (count($r_vq) == 0) {
		echo "<quizlist />\r\n";
		exit;
	}
	echo "<quizlist>\r\n";
	foreach ($r_vq as $r_q) {
		echo "\t<quiz>\r\n";
		echo "\t\t<name>";
		echo utf8_encode(htmlspecialchars(trim($r_q->name)));
		echo "</name>\r\n";
		echo "\t\t<id>";
		echo utf8_encode(htmlspecialchars(trim($r_q->id)));
		echo "</id>\r\n";
		echo "\t\t<section_id>";
		echo utf8_encode(htmlspecialchars(trim($r_q->section)));
		echo "</section_id>\r\n";
		echo "\t\t<writable>";
		if (in_array($r_q, $r_mq))
			echo "yes";
		else
			echo "no";
		echo "</writable>\r\n";
		echo "\t</quiz>\r\n";
	}
	echo "</quizlist>\r\n";
	exit;
}
function RWSAQCList()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_bv = intval(RWSGSOpt("version"));
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	$r_cid = intval($r_pm);
	RWSCMUCourse($r_cid);
	$r_qcs = RWSGUQCats($r_cid);
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	if (count($r_qcs) == 0) {
		echo "<qcatlist />\r\n";
		exit;
	}
	echo "<qcatlist>\r\n";
	foreach ($r_qcs as $r_qc) {
		echo "\t<category>\r\n";
		echo "\t\t<name>";
		echo utf8_encode(htmlspecialchars(trim($r_qc->name)));
		echo "</name>\r\n";
		echo "\t\t<id>";
		echo utf8_encode(htmlspecialchars(trim($r_qc->id)));
		echo "</id>\r\n";
		if (!empty($r_qc->parent) && array_key_exists($r_qc->parent, $r_qcs)) {
			echo "\t\t<parent_id>";
			echo utf8_encode(htmlspecialchars(trim($r_qc->parent)));
			echo "</parent_id>\r\n";
		}
		if ($r_bv >= 2010063001) { 
			$r_ctx = get_context_instance_by_id($r_qc->contextid);
			$r_qc_course_id = RWSGCFCat($r_ctx);
			if ($r_qc_course_id == SITEID) 
				echo "\t\t<system>yes</system>\r\n";
			else
				echo "\t\t<system>no</system>\r\n";
		}
		echo "\t</category>\r\n";
	}
	echo "</qcatlist>\r\n";
	exit;
}
function RWSAAQCat()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("name");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2058"); 
	$r_qcn = trim(clean_text(strip_tags($r_pm, "<lang><span>")));
	if (strlen($r_qcn) > 254) {
		RWSSErr("2059");
	}
	$r_cid = FALSE;
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm !== FALSE && strlen($r_pm) > 0)
		$r_cid = intval($r_pm);
	$r_pi = FALSE;
	$r_pm = RWSGSOpt("parentid");
	if ($r_pm !== FALSE && strlen($r_pm) > 0)
		$r_pi = intval($r_pm);
	if ($r_cid === FALSE && $r_pi === FALSE) {
		RWSSErr("2060");
	}
	else if ($r_cid !== FALSE && $r_pi === FALSE) {
		RWSCMUCourse($r_cid);
		$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
		$r_pi = 0;
	}
	else if ($r_cid === FALSE && $r_pi !== FALSE) {
		$r_rcd = get_record("question_categories", "id", $r_pi);
		if (!$r_rcd) 
			RWSSErr("2061");
		$r_ctx = get_context_instance_by_id($r_rcd->contextid);
		$r_cid = RWSGCFCat($r_ctx);
		RWSCMUCourse($r_cid);
		if ($r_cid == SITEID)
			$r_ctx = get_context_instance(CONTEXT_SYSTEM);
		else
			$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
	}
	else 
	{
		RWSCMUCourse($r_cid);
		$r_rcd = get_record("question_categories", "id", $r_pi);
		if (!$r_rcd) 
			RWSSErr("2061");
		$r_ctx = get_context_instance_by_id($r_rcd->contextid);
		$r_qcci = RWSGCFCat($r_ctx);
		if ($r_qcci != $r_cid) {
			$r_sctx = get_context_instance(CONTEXT_SYSTEM);
			if (has_capability("moodle/site:doanything", $r_sctx)
			  || has_capability("moodle/site:config", $r_sctx)) {
				if ($r_qcci != SITEID) {
					RWSSErr("2110");
				}
				else
					$r_ctx = $r_sctx;
			}
			else {
				RWSSErr("2062");
			}
		}
		else
			$r_ctx = get_context_instance(CONTEXT_COURSE, $r_cid);
	}
    $r_qca = new stdClass();
    $r_qca->parent = $r_pi;
    $r_qca->contextid = $r_ctx->id;
    $r_qca->name = $r_qcn;
    $r_qca->info = "Created by Respondus";
    $r_qca->sortorder = 999;
    $r_qca->stamp = make_unique_id_code();
	$r_qci = insert_record("question_categories",
	  addslashes_recursive($r_qca));
    if (!$r_qci) 
        RWSSErr("2063");
	rebuild_course_cache($r_cid);
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<addqcat>\r\n";
	echo "\t<name>";
	echo utf8_encode(htmlspecialchars(trim($r_qcn)));
	echo "</name>\r\n";
	echo "\t<id>";
	echo utf8_encode(htmlspecialchars(trim($r_qci)));
	echo "</id>\r\n";
	if ($r_pi != 0) {
		echo "\t<parent_id>";
		echo utf8_encode(htmlspecialchars(trim($r_pi)));
		echo "</parent_id>\r\n";
	}
	echo "</addqcat>\r\n";
	exit;
}
function RWSADQCat()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("qcatid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2064"); 
	$r_qci = intval($r_pm);
	$r_qca = get_record("question_categories", "id", $r_qci);
	if (!$r_qca) 
		RWSSErr("2065");
	$r_ctx = get_context_instance_by_id($r_qca->contextid);
	$r_cid = RWSGCFCat($r_ctx);
	RWSCMUCourse($r_cid);
	question_can_delete_cat($r_qci);
	if (RWSIQCUsed($r_qci)) {
		RWSSErr("2066");
	}
	RWSDQCat($r_qci);
	rebuild_course_cache($r_cid);
	RWSSStat("1002"); 
}
function RWSADQuiz()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2067"); 
	$r_qzi = intval($r_pm);
	$r_rcd = RWSCMUQuiz($r_qzi);
	$r_cid = $r_rcd->course;
	RWSCMUCourse($r_cid, TRUE);
	if (!quiz_delete_instance($r_rcd->instance)) {
		RWSSErr("2068");
	}
	if (!delete_course_module($r_qzi)) {
		RWSSErr("2069");
	}
	if (!delete_mod_from_section($r_qzi, $r_rcd->section)) {
		RWSSErr("2070");
	}
	if ($RWSLB->mok)
		lockdown_delete_options($r_rcd->instance);
	else if ($RWSLB->bok)
		lockdown_delete_options($r_rcd->instance);
	rebuild_course_cache($r_cid);
	RWSSStat("1003"); 
}
function RWSAAQuiz()
{
	global $CFG;
	global $RWSLB;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	$r_cid = intval($r_pm);
	RWSCMUCourse($r_cid, TRUE);
	$r_si = FALSE;
	$r_pm = RWSGSOpt("sectionid");
	if ($r_pm !== FALSE && strlen($r_pm) > 0)
		$r_si = intval($r_pm);
	if ($r_si === FALSE) {
		$r_sr = 0; 
	}
	else {
		$r_sec = get_record("course_sections", "id", $r_si);
		if (!$r_sec)
			RWSSErr("2071"); 
		if ($r_sec->course != $r_cid) {
			RWSSErr("2072");
		}
		$r_sr = $r_sec->section;
	}
	$r_pm = RWSGSOpt("name");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2073"); 
	$r_qzn = trim(clean_text(strip_tags($r_pm, "<lang><span>")));
	$r_mr = get_record("modules", "name", "quiz");
	if (!$r_mr)
		RWSSErr("2074"); 
    $r_qz = new stdClass();
	$r_qz->name = $r_qzn;
	RWSSQDefs($r_qz);
	$r_sfl = RWSGSOpt("sfile");
	if ($r_sfl === FALSE) {
		$r_sn = RWSGSOpt("sname");
		$r_sd = RWSGSOpt("sdata");
		$r_ecd = TRUE;
	}
	else {
		$r_sn = $r_sfl->filename;
		$r_sd = $r_sfl->filedata;
		$r_ecd = FALSE;
	}
	if ($r_sd !== FALSE && strlen($r_sd) > 0) {
		if ($r_sn === FALSE || strlen($r_sn) == 0) {
			RWSSErr("2075");
		}
		$r_sn = clean_filename($r_sn);
		RWSIQSet($r_qz, $r_sn, $r_sd, $r_ecd);
	}
	$r_qz->course = $r_cid;
	$r_qz->coursemodule = "";	
	$r_qz->instance = "";		
	$r_qz->modulename = $r_mr->name;
	$r_qz->module = $r_mr->id;
	$r_qz->section = $r_sr;
	$r_qz->groupingid = 0;		
	$r_qz->groupmembersonly = 0;
	$r_insi = quiz_add_instance(addslashes_recursive($r_qz));
	if (!$r_insi || is_string($r_insi)) {
		RWSSErr("2076");
	}
	$r_qz->instance = $r_insi;
	$r_qzi = add_course_module($r_qz);
	if (!$r_qzi) 
		RWSSErr("2077");
	$r_qz->coursemodule = $r_qzi;
	$r_siu = add_mod_to_section($r_qz);
	if (!$r_siu) 
		RWSSErr("2078");
    if (!set_field("course_modules", "section", $r_siu, "id",
	  $r_qzi)) {
		RWSSErr("2078");
	}
	if ($r_si !== FALSE
	  && $r_siu != $r_si) {
		RWSSErr("2078");
	}
	RWSSLBSet($r_qz);
    set_coursemodule_visible($r_qzi, $r_qz->visible);
	if (isset($r_qz->cmidnumber))  
		set_coursemodule_idnumber($r_qzi, $r_qz->cmidnumber);
	RWSUQGrades($r_qz);
	rebuild_course_cache($r_cid);
    grade_regrade_final_grades($r_cid);
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<addquiz>\r\n";
	echo "\t<name>";
	echo utf8_encode(htmlspecialchars(trim($r_qz->name)));
	echo "</name>\r\n";
	echo "\t<id>";
	echo utf8_encode(htmlspecialchars(trim($r_qzi)));
	echo "</id>\r\n";
	echo "\t<section_id>";
	echo utf8_encode(htmlspecialchars(trim($r_siu)));
	echo "</section_id>\r\n";
	echo "\t<writable>yes</writable>\r\n";
	if ($RWSLB->mex || $RWSLB->bex) {
		if ($RWSLB->mok) {
			if ($RWSLB->perr) 
				echo "\t<service_warning>3003</service_warning>\r\n";
		} else if ($RWSLB->bok) {
			if ($RWSLB->perr) 
				echo "\t<service_warning>3003</service_warning>\r\n";
		} else { 
			echo "\t<service_warning>3001</service_warning>\r\n";
		}
	} else { 
		echo "\t<service_warning>3000</service_warning>\r\n";
	}
	echo "</addquiz>\r\n";
	exit;
}
function RWSAUQuiz()
{
	global $RWSLB;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2067"); 
	$r_qzi = intval($r_pm);
	$r_cmod = RWSCMUQuiz($r_qzi);
	$r_cid = $r_cmod->course;
	RWSCMUCourse($r_cid, TRUE);
	$r_mr = get_record("modules", "id", $r_cmod->module);
    if (!$r_mr ) 
        RWSSErr("2043");
	$r_qz = get_record($r_mr->name, "id", $r_cmod->instance);
	if (!$r_qz ) 
        RWSSErr("2044");
	$r_sec = get_record("course_sections", "id", $r_cmod->section);
	if (!$r_sec ) 
        RWSSErr("2079");
    $r_qz->coursemodule = $r_cmod->id;
    $r_qz->section = $r_sec->section;
    $r_qz->visible = $r_cmod->visible;
    $r_qz->cmidnumber = $r_cmod->idnumber;
    $r_qz->groupmode = groups_get_activity_groupmode($r_cmod);
    $r_qz->groupingid = $r_cmod->groupingid;
    $r_qz->groupmembersonly = $r_cmod->groupmembersonly;
    $r_qz->course = $r_cid;
    $r_qz->module = $r_mr->id;
    $r_qz->modulename = $r_mr->name;
    $r_qz->instance = $r_cmod->instance;
	$r_its = grade_item::fetch_all(array('itemtype'=>'mod',
	  'itemmodule'=>$r_qz->modulename, 'iteminstance'=>$r_qz->instance,
	  'courseid'=>$r_cid));
	if ($r_its) {
        foreach ($r_its as $r_it) {
            if (!empty($r_it->outcomeid))
                $r_qz->{'outcome_'.$r_it->outcomeid} = 1;
        }
        $r_gc = false;
        foreach ($r_its as $r_it) {
            if ($r_gc === false) {
                $r_gc = $r_it->categoryid;
                continue;
            }
            if ($r_gc != $r_it->categoryid) { 
                $r_gc = false;
                break;
            }
        }
        if ($r_gc !== false) 
            $r_qz->gradecat = $r_gc;
    }
	$r_ren = FALSE;
	$r_pm = RWSGSOpt("rename");
	if ($r_pm !== FALSE && strlen($r_pm) > 0) {
		$r_ren = trim(clean_text(strip_tags($r_pm, "<lang><span>")));
		$r_qz->name = $r_ren;
	}
	$r_sfl = RWSGSOpt("sfile");
	if ($r_sfl === FALSE) {
		$r_sn = RWSGSOpt("sname");
		$r_sd = RWSGSOpt("sdata");
		$r_ecd = TRUE;
	}
	else {
		$r_sn = $r_sfl->filename;
		$r_sd = $r_sfl->filedata;
		$r_ecd = FALSE;
	}
	if ($r_sd !== FALSE && strlen($r_sd) > 0) {
		if ($r_sn === FALSE || strlen($r_sn) == 0) {
			RWSSErr("2075");
		}
		$r_sn = clean_filename($r_sn);
		RWSIQSet($r_qz, $r_sn, $r_sd, $r_ecd);
	}
	if ($r_ren === FALSE) {
		if ($r_sd === FALSE || strlen($r_sd) == 0)
			RWSSErr("2080"); 
	}
	$r_res = quiz_update_instance(addslashes_recursive($r_qz));
	if (!$r_res || is_string($r_res)) {
		RWSSErr("2081");
	}
	RWSSLBSet($r_qz);
	set_coursemodule_visible($r_qzi, $r_qz->visible);
	set_coursemodule_groupmode($r_qzi, $r_qz->groupmode);
    set_coursemodule_groupingid($r_qzi, $r_qz->groupingid);
    set_coursemodule_groupmembersonly($r_qzi, $r_qz->groupmembersonly);
	if (isset($r_qz->cmidnumber))
		set_coursemodule_idnumber($r_qzi, $r_qz->cmidnumber);
	RWSUQGrades($r_qz);
	rebuild_course_cache($r_cid);
    grade_regrade_final_grades($r_cid);
	if ($RWSLB->mex || $RWSLB->bex) {
		if ($RWSLB->mok) {
			if ($RWSLB->perr)
				RWSSWarn("3003"); 
		} else if ($RWSLB->bok) {
			if ($RWSLB->perr)
				RWSSWarn("3003"); 
		} else { 
			RWSSWarn("3001");
		}
	} else { 
		RWSSWarn("3000");
	}
	RWSSStat("1004"); 
}
function RWSAAQList()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2067"); 
	$r_qzi = intval($r_pm);
	$r_cmod = RWSCMUQuiz($r_qzi);
	$r_cid = $r_cmod->course;
	RWSCMUCourse($r_cid, TRUE);
	$r_ql = RWSGSOpt("qlist");
	if ($r_ql === FALSE || strlen($r_ql) == 0)
		RWSSErr("2082"); 
	$r_qis = explode(",", $r_ql);
	if (count($r_qis) == 0 || strlen($r_qis[0]) == 0)
		RWSSErr("2082");
	foreach ($r_qis as $r_k=>$r_val) {
		if ($r_val === FALSE || strlen($r_val) == 0)
			RWSSErr("2108"); 
		$r_qis[$r_k] = intval($r_val);
	}
	$r_mr = get_record("modules", "id", $r_cmod->module);
    if (!$r_mr ) 
        RWSSErr("2043");
	$r_qz = get_record($r_mr->name, "id", $r_cmod->instance);
	if (!$r_qz ) 
        RWSSErr("2044");
	if (!isset($r_qz->instance))
		$r_qz->instance = $r_qz->id; 
	$r_erri = array();
	foreach ($r_qis as $r_id) {
		$r_ok = get_record("question", "id", $r_id);
		if ($r_ok)
			$r_ok = quiz_add_quiz_question($r_id, $r_qz);
		if (!$r_ok)
			$r_erri[] = $r_id;
	}
	if (count($r_erri) > 0) {
		$r_errl = implode(",", $r_erri);
		RWSSErr("2083,$r_errl");
	}
	if (count($r_erri) < count($r_qis))
		delete_records("quiz_attempts", "preview", "1", "quiz", $r_qz->id);
	$r_qz->grades = quiz_get_all_question_grades($r_qz);
	$r_sumg = array_sum($r_qz->grades);
	if (!set_field("quiz", "sumgrades", $r_sumg, "id", $r_qz->id))
		RWSSErr("2105");
	RWSSStat("1005"); 
}
function RWSAAQRand()
{
    global $USER;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2067"); 
	$r_qzi = intval($r_pm);
	$r_cmod = RWSCMUQuiz($r_qzi);
	$r_cid = $r_cmod->course;
	RWSCMUCourse($r_cid, TRUE);
	$r_pm = RWSGSOpt("qcatid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2064"); 
	$r_qci = intval($r_pm);
	$r_qca = get_record("question_categories", "id", $r_qci);
	if (!$r_qca) 
		RWSSErr("2065");
	$r_ctx = get_context_instance_by_id($r_qca->contextid);
	$r_qcci = RWSGCFCat($r_ctx);
	if ($r_qcci != $r_cid) {
		$r_sctx = get_context_instance(CONTEXT_SYSTEM);
		if (has_capability("moodle/site:doanything", $r_sctx)
		  || has_capability("moodle/site:config", $r_sctx)) {
			if ($r_qcci != SITEID) {
				RWSSErr("2109");
			}
		}
		else {
			RWSSErr("2084");
		}
	}
	$r_pm = RWSGSOpt("qcount");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2085"); 
	$r_qct = intval($r_pm);
	if ($r_qct <= 0)
		RWSSErr("2085");
	$r_pm = RWSGSOpt("qgrade");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2086"); 
	$r_qg = round(floatval($r_pm));
	if ($r_qg <= 0)
		RWSSErr("2086");
	$r_mr = get_record("modules", "id", $r_cmod->module);
    if (!$r_mr ) 
        RWSSErr("2043");
	$r_qz = get_record($r_mr->name, "id", $r_cmod->instance);
	if (!$r_qz ) 
        RWSSErr("2044");
	if (!isset($r_qz->instance))
		$r_qz->instance = $r_qz->id; 
	$r_aerr = 0;
	for ($r_i = 0; $r_i < $r_qct; $r_i++) {
		$r_qst = new stdClass();
		$r_qst->qtype = RANDOM;
		$r_qst->parent = 0;
		$r_qst->hidden = 0;
		$r_qst->length = 1;
		$r_qst->name = random_qtype::question_name($r_qca);
		$r_qst->questiontext = 1; 
		$r_qst->questiontextformat = 0;
		$r_qst->penalty = 0;
		$r_qst->defaultgrade = $r_qg;
		$r_qst->image = "";
		$r_qst->generalfeedback = "";
		$r_qst->category = $r_qca->id;
		$r_qst->stamp = make_unique_id_code();
		$r_qst->createdby = $USER->id;
		$r_qst->modifiedby = $USER->id;
		$r_qst->timecreated = time();
		$r_qst->timemodified = time();
		$r_qst->id = insert_record("question",
		  addslashes_recursive($r_qst));
		$r_ok = $r_qst->id;		
		if ($r_ok) {
			$r_ok = set_field("question", "parent", $r_qst->id,
			  "id", $r_qst->id);
		}
		if ($r_ok) {
			$r_h = question_hash($r_qst);
			$r_ok = set_field("question", "version", $r_h,
			  "id", $r_qst->id);
		}
		if ($r_ok)
			$r_ok = quiz_add_quiz_question($r_qst->id, $r_qz);
		if (!$r_ok) {
			delete_records("question", "id", $r_qst->id);
			$r_aerr++;
		}
	}
	if ($r_aerr > 0) {
		RWSSErr("2087,$r_aerr");
	}
	if ($r_aerr < $r_qct)
		delete_records("quiz_attempts", "preview", "1", "quiz", $r_qz->id);
	$r_qz->grades = quiz_get_all_question_grades($r_qz);
	$r_sumg = array_sum($r_qz->grades);
	if (!set_field("quiz", "sumgrades", $r_sumg, "id", $r_qz->id))
		RWSSErr("2105");
	RWSSStat("1006"); 
}
function RWSAIQData()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("qcatid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2064"); 
	$r_qci = intval($r_pm);
	$r_qca = get_record("question_categories", "id", $r_qci);
	if (!$r_qca) 
		RWSSErr("2065");
	$r_ctx = get_context_instance_by_id($r_qca->contextid);
	$r_cid = RWSGCFCat($r_ctx);
	RWSCMUCourse($r_cid);
	$r_qfl = RWSGSOpt("qfile");
	if ($r_qfl === FALSE) {
		$r_qn = RWSGSOpt("qname");
		$r_qd = RWSGSOpt("qdata");
		$r_ecd = TRUE;
	}
	else {
		$r_qn = $r_qfl->filename;
		$r_qd = $r_qfl->filedata;
		$r_ecd = FALSE;
	}
	if ($r_qn === FALSE || strlen($r_qn) == 0)
		RWSSErr("2088"); 
	$r_qn = clean_filename($r_qn);
	if ($r_qd === FALSE || strlen($r_qd) == 0)
		RWSSErr("2089"); 
	RWSATLog($r_cid, "publish", "qcatid=$r_qci");
	$r_drp = 0;
	$r_ba = 0;
	$r_qis = RWSIQues(
	  $r_cid, $r_qci, $r_qn, $r_qd, $r_ecd, $r_drp, $r_ba);
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<importqdata>\r\n";
	echo "\t<category_id>";
	echo utf8_encode(htmlspecialchars(trim($r_qci)));
	echo "</category_id>\r\n";
	echo "\t<dropped>";
	echo utf8_encode(htmlspecialchars(trim($r_drp)));
	echo "</dropped>\r\n";
	echo "\t<badatts>";
	echo utf8_encode(htmlspecialchars(trim($r_ba)));
	echo "</badatts>\r\n";
	$r_ql = implode(",", $r_qis);
	echo "\t<qlist>";
	echo utf8_encode(htmlspecialchars(trim($r_ql)));
	echo "</qlist>\r\n";
	echo "</importqdata>\r\n";
	exit;
}
function RWSAGQuiz()
{
	global $RWSLB;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_fmt = RWSGSOpt("format");
	if (strcasecmp($r_fmt, "base64") == 0)
		$r_w64 = TRUE;
	else if (strcasecmp($r_fmt, "binary") == 0)
		$r_w64 = FALSE;
	else
		RWSSErr("2051"); 
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2067"); 
	$r_qzi = intval($r_pm);
	$r_cmod = RWSCMUQuiz($r_qzi);
	$r_cid = $r_cmod->course;
	RWSCMUCourse($r_cid, TRUE);
	$r_mr = get_record("modules", "id", $r_cmod->module);
    if (!$r_mr ) 
        RWSSErr("2043");
	$r_qz = get_record($r_mr->name, "id", $r_cmod->instance);
	if (!$r_qz ) 
        RWSSErr("2044");
	$r_sec = get_record("course_sections", "id", $r_cmod->section);
	if (!$r_sec ) 
        RWSSErr("2079");
    $r_qz->coursemodule = $r_cmod->id;
    $r_qz->section = $r_sec->section;
    $r_qz->visible = $r_cmod->visible;
    $r_qz->cmidnumber = $r_cmod->idnumber;
    $r_qz->groupmode = groups_get_activity_groupmode($r_cmod);
    $r_qz->groupingid = $r_cmod->groupingid;
    $r_qz->groupmembersonly = $r_cmod->groupmembersonly;
    $r_qz->course = $r_cid;
    $r_qz->module = $r_mr->id;
    $r_qz->modulename = $r_mr->name;
    $r_qz->instance = $r_cmod->instance;
	$r_its = grade_item::fetch_all(array('itemtype'=>'mod',
	  'itemmodule'=>$r_qz->modulename, 'iteminstance'=>$r_qz->instance,
	  'courseid'=>$r_cid));
	if ($r_its) {
        foreach ($r_its as $r_it) {
            if (!empty($r_it->outcomeid))
                $r_qz->{'outcome_'.$r_it->outcomeid} = 1;
        }
        $r_gc = false;
        foreach ($r_its as $r_it) {
            if ($r_gc === false) {
                $r_gc = $r_it->categoryid;
                continue;
            }
            if ($r_gc != $r_it->categoryid) { 
                $r_gc = false;
                break;
            }
        }
        if ($r_gc !== false) 
            $r_qz->gradecat = $r_gc;
    }
	$r_sfl = "";
	$r_sd = RWSEQSet($r_qz, $r_sfl, $r_w64);
	if ($r_w64)
	{
		RWSRHXml();
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		echo "<getquiz>\r\n";
		echo "\t<name>";
		echo utf8_encode(htmlspecialchars(trim($r_qz->name)));
		echo "</name>\r\n";
		echo "\t<id>";
		echo utf8_encode(htmlspecialchars(trim($r_qzi)));
		echo "</id>\r\n";
		echo "\t<section_id>";
		echo utf8_encode(htmlspecialchars(trim($r_qz->section)));
		echo "</section_id>\r\n";
		echo "\t<writable>yes</writable>\r\n";
		echo "\t<sfile>";
		echo utf8_encode(htmlspecialchars(trim($r_sfl)));
		echo "</sfile>\r\n";
		echo "\t<sdata>";
		echo utf8_encode(htmlspecialchars(trim($r_sd)));
		echo "</sdata>\r\n";
		if ($RWSLB->mex || $RWSLB->bex) {
			if ($RWSLB->mok) {
				if ($RWSLB->gerr) 
					echo "\t<service_warning>3002</service_warning>\r\n";
			} else if ($RWSLB->bok) {
				if ($RWSLB->gerr) 
					echo "\t<service_warning>3002</service_warning>\r\n";
			} else { 
				echo "\t<service_warning>3001</service_warning>\r\n";
			}
		} else { 
			echo "\t<service_warning>3000</service_warning>\r\n";
		}
		echo "</getquiz>\r\n";
	}
	else 
	{
		$r_f = "name=\"" . htmlspecialchars(trim($r_qz->name)) . "\"; ";
		$r_chdr = $r_f;
		$r_f = "id=" . htmlspecialchars(trim($r_qzi)) . "; ";
		$r_chdr .= $r_f;
		$r_f = "section_id=" . htmlspecialchars(trim($r_qz->section)) . "; ";
		$r_chdr .= $r_f;
		$r_f = "writable=yes";
		$r_chdr .= $r_f;
		if ($RWSLB->mex || $RWSLB->bex) {
			if ($RWSLB->mok) {
				if ($RWSLB->gerr) {
					$r_f = "; service_warning=3002";
					$r_chdr .= $r_f;
				}
			} else if ($RWSLB->bok) {
				if ($RWSLB->gerr) {
					$r_f = "; service_warning=3002";
					$r_chdr .= $r_f;
				}
			} else { 
				$r_f = "; service_warning=3001";
				$r_chdr .= $r_f;
			}
		} else { 
			$r_f = "; service_warning=3000";
			$r_chdr .= $r_f;
		}
		header("X-GetQuiz: " . $r_chdr);
		RWSRHBin($r_sfl, strlen($r_sd));
		echo $r_sd;
	}
	exit;
}
function RWSAEQData()
{
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_fmt = RWSGSOpt("format");
	if (strcasecmp($r_fmt, "base64") == 0)
		$r_w64 = TRUE;
	else if (strcasecmp($r_fmt, "binary") == 0)
		$r_w64 = FALSE;
	else
		RWSSErr("2051"); 
	$r_qzi = FALSE;
	$r_pm = RWSGSOpt("quizid");
	if ($r_pm !== FALSE && strlen($r_pm) > 0)
		$r_qzi = intval($r_pm);
	$r_qci = FALSE;
	$r_pm = RWSGSOpt("qcatid");
	if ($r_pm !== FALSE && strlen($r_pm) > 0)
		$r_qci = intval($r_pm);
	if ($r_qzi === FALSE && $r_qci === FALSE) {
		RWSSErr("2090");
	}
	else if ($r_qzi !== FALSE && $r_qci === FALSE) {
		$r_cmod = RWSCMUQuiz($r_qzi);
		$r_cid = $r_cmod->course;
	}
	else if ($r_qzi === FALSE && $r_qci !== FALSE) {
		$r_qca = get_record("question_categories", "id", $r_qci);
		if (!$r_qca) 
			RWSSErr("2065");
		$r_ctx = get_context_instance_by_id($r_qca->contextid);
		$r_cid = RWSGCFCat($r_ctx);
	}
	else 
	{
		RWSSErr("2091");
	}
	RWSCMUCourse($r_cid);
	if ($r_qzi !== FALSE)
		RWSATLog($r_cid, "retrieve", "quizid=$r_qzi");
	else 
		RWSATLog($r_cid, "retrieve", "qcatid=$r_qci");
	$r_qfl = "";
	$r_drp = 0;
	$r_ran = 0;
	if ($r_qzi !== FALSE) {
		$r_qd = RWSEQQues(
		  $r_qzi, $r_qfl, $r_drp, $r_ran, $r_w64);
	}
	else { 
		$r_qd = RWSEQCQues(
		  $r_qci, $r_qfl, $r_drp, $r_w64);
	}
	if ($r_w64)
	{
		RWSRHXml();
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		echo "<exportqdata>\r\n";
		if ($r_qzi !== FALSE) {
			echo "\t<quiz_id>";
			echo utf8_encode(htmlspecialchars(trim($r_qzi)));
			echo "</quiz_id>\r\n";
		}
		else 
		{
			echo "\t<category_id>";
			echo utf8_encode(htmlspecialchars(trim($r_qci)));
			echo "</category_id>\r\n";
		}
		echo "\t<dropped>";
		echo utf8_encode(htmlspecialchars(trim($r_drp)));
		echo "</dropped>\r\n";
		if ($r_qzi !== FALSE)	{
			echo "\t<random>";
			echo utf8_encode(htmlspecialchars(trim($r_ran)));
			echo "</random>\r\n";
		}
		echo "\t<qfile>";
		echo utf8_encode(htmlspecialchars(trim($r_qfl)));
		echo "</qfile>\r\n";
		echo "\t<qdata>";
		echo utf8_encode(htmlspecialchars(trim($r_qd)));
		echo "</qdata>\r\n";
		echo "</exportqdata>\r\n";
	}
	else 
	{
		if ($r_qzi !== FALSE)
			$r_f = "quiz_id=" . htmlspecialchars(trim($r_qzi)) . "; ";
		else 
			$r_f = "category_id=" . htmlspecialchars(trim($r_qci)) . "; ";
		$r_chdr = $r_f;
		$r_f = "dropped=" . htmlspecialchars(trim($r_drp));
		$r_chdr .= $r_f;
		if ($r_qzi !== FALSE) {
			$r_f = "; random=" . htmlspecialchars(trim($r_ran));
			$r_chdr .= $r_f;
		}
		header("X-ExportQData: " . $r_chdr);
		RWSRHBin($r_qfl, strlen($r_qd));
		echo $r_qd;
	}
	exit;
}
function RWSAUFile()
{
	global $CFG;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	if (strcasecmp($r_pm, "site") == 0)
		$r_cid = SITEID;
	else
		$r_cid = intval($r_pm);
	RWSCMUCourse($r_cid);
	$r_ff = RWSGSOpt("folder");
	if ($r_ff === FALSE || strlen($r_ff) == 0)
		RWSSErr("2092"); 
	$r_ff = clean_filename($r_ff);
	$r_fbn = RWSGSOpt("filebinary");
	if ($r_fbn === FALSE) {
		$r_fn = RWSGSOpt("filename");
		$r_fdat = RWSGSOpt("filedata");
		$r_ecd = TRUE;
	}
	else {
		$r_fn = $r_fbn->filename;
		$r_fdat = $r_fbn->filedata;
		$r_ecd = FALSE;
	}
	if ($r_fn === FALSE || strlen($r_fn) == 0)
		RWSSErr("2093"); 
	$r_fn = clean_filename($r_fn);
	if ($r_fdat === FALSE || strlen($r_fdat) == 0)
		RWSSErr("2094"); 
	$r_drpth = "$r_cid/$r_ff";
	$r_ok = make_upload_directory($r_drpth);
	if ($r_ok === FALSE) 
		RWSSErr("2095,$r_ff");
	$r_crpth = "$r_ff/$r_fn";
	$r_drpth .= "/$r_fn";
	$r_fp = "$CFG->dataroot/$r_drpth";
	if (file_exists($r_fp)) 
		RWSSErr("2096,$r_crpth");
	if ($r_ecd) {
		$r_dcd_data = base64_decode($r_fdat);
		if ($r_dcd_data === FALSE) {
			RWSSErr("2097");
		}
	}
	else { 
		$r_dcd_data = $r_fdat;
	}
	$r_hdl = fopen($r_fp, "wb");
	if ($r_hdl === FALSE)
		RWSSErr("2098"); 
	$r_by = fwrite($r_hdl, $r_dcd_data);
	if ($r_hdl !== FALSE)
		fclose($r_hdl);
	if ($r_by === FALSE)
		RWSSErr("2098"); 
	RWSRHXml();
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<uploadfile>\r\n";
	echo "\t<course_subpath>";
	echo utf8_encode(htmlspecialchars(trim($r_crpth)));
	echo "</course_subpath>\r\n";
	echo "</uploadfile>\r\n";
	exit;
}
function RWSADFile()
{
	global $CFG;
	RWSCMAuth();
	RWSCMUSvc();
	RWSCMMaint();
	$r_pm = RWSGSOpt("courseid");
	if ($r_pm === FALSE || strlen($r_pm) == 0)
		RWSSErr("2057"); 
	if (strcasecmp($r_pm, "site") == 0)
		$r_cid = SITEID;
	else
		$r_cid = intval($r_pm);
	RWSCMUCourse($r_cid);
	$r_fmt = RWSGSOpt("format");
	if (strcasecmp($r_fmt, "base64") == 0)
		$r_w64 = TRUE;
	else if (strcasecmp($r_fmt, "binary") == 0)
		$r_w64 = FALSE;
	else
		RWSSErr("2051"); 
	$r_fr = RWSGSOpt("fileref");
	if ($r_fr === FALSE || strlen($r_fr) == 0)
		RWSSErr("2099"); 
	$r_p = strrpos($r_fr, "/");
	if ($r_p === FALSE) {
		$r_fn = $r_fr;
	}
	else {
		$r_p++;
		if ($r_p == strlen($r_fr))
			$r_fn = "";
		else
			$r_fn = substr($r_fr, $r_p);
	}
	$r_pth = $r_fr;
	$r_pfx = "$CFG->wwwroot/file.php/";
	$r_p = stripos($r_pth, $r_pfx);
	if ($r_p === 0)
		$r_pth = substr($r_pth, strlen($r_pfx));
	$r_pfx = "$CFG->wwwroot/file.php?file=";
	$r_p = stripos($r_pth, $r_pfx);
	if ($r_p === 0)
		$r_pth = substr($r_fr, strlen($r_pfx));
	$r_pfx = "%%COURSEPATH%%";
	$r_p = stripos($r_pth, $r_pfx);
	if ($r_p === 0)
		$r_pth = substr($r_pth, strlen($r_pfx));
	$r_iru = ($r_p === 0);
	$r_fnd = (!$r_iru && file_exists($r_pth));
	if (!$r_fnd)
		$r_pth = trim($r_pth, " /");
	if (!$r_fnd && !$r_iru) {
		$r_dpth = "$CFG->dataroot/$r_pth";
		$r_fnd = file_exists($r_dpth);
		if ($r_fnd)
			$r_pth = $r_dpth;
	}
	if (!$r_fnd) {
		$r_cpth = "$CFG->dataroot/$r_cid/$r_pth";
		$r_fnd = file_exists($r_cpth);
		if ($r_fnd)
			$r_pth = $r_cpth;
	}
	if (!$r_fnd && !$r_iru) {
		$r_sitp = "$CFG->dataroot/" . SITEID . "/$r_pth";
		$r_fnd = file_exists($r_sitp);
		if ($r_fnd)
			$r_pth = $r_sitp;
	}
	if (!$r_fnd)
		RWSSErr("2100"); 
	$r_fdat = file_get_contents($r_pth);
	if ($r_fdat === FALSE)
		RWSSErr("2101"); 
	if ($r_w64)
	{
		RWSRHXml();
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		echo "<dnloadfile>\r\n";
		echo "\t<filename>";
		echo utf8_encode(htmlspecialchars(trim($r_fn)));
		echo "</filename>\r\n";
		$r_ed = base64_encode($r_fdat);
		echo "\t<filedata>";
		echo utf8_encode(htmlspecialchars(trim($r_ed)));
		echo "</filedata>\r\n";
		echo "</dnloadfile>\r\n";
	}
	else 
	{
		RWSRHBin($r_fn, strlen($r_fdat));
		echo $r_fdat;
	}
	exit;
}
function RWSELog($r_msg)
{
}
?>
