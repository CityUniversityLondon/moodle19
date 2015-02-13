<?php
function migrate2utf8_ipodcast_name($recordid){
    global $CFG, $globallang;

/// Some trivial checks
    if (empty($recordid)) {
        log_the_problem_somewhere();
        return false;
    }

    if (!$forum = get_record('ipodcast','id',$recordid)) {
        log_the_problem_somewhere();
        return false;
    }
    if ($globallang) {
        $fromenc = $globallang;
    } else {
        $sitelang   = $CFG->lang;
        $courselang = get_course_lang($ipodcast->course);  //Non existing!
        $userlang   = get_main_teacher_lang($ipodcast->course); //N.E.!!

        $fromenc = get_original_encoding($sitelang, $courselang, $userlang);
    }

/// We are going to use textlib facilities
    
/// Convert the text
    if (($fromenc != 'utf-8') && ($fromenc != 'UTF-8')) {
        $result = utfconvert($ipodcast->name, $fromenc);

        $newipodcast = new object;
        $newipodcast->id = $recordid;
        $newipodcast->name = $result;
        migrate2utf8_update_record('ipodcast',$newipodcast);
    }
/// And finally, just return the converted field
    return $result;
}

?>
