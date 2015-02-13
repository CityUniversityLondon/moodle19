<?php

include_once('config_gtlib.php');

if ($CFG->gtlib->exportfunctions){
    if (isset($OUTPUT)){
        $PAGE->requires->js('/lib/gtlib_yui/lib.gt_all_expfuncs.js');
    } else {
        require_js($CFG->wwwroot.'/lib/gtlib_yui/lib.gt_all_expfuncs.js');
    }
}
if (isset($OUTPUT)){
    $PAGE->requires->js('/lib/gtlib_yui/lib.gt_all.js');
} else {
    require_js($CFG->wwwroot.'/lib/gtlib_yui/lib.gt_all.js');
}

?>
