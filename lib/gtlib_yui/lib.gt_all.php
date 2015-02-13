<?php
include_once('config_gtlib.php');

if (isset($CFG->gtlib->exportfunctions) && intval($CFG->gtlib->exportfunctions)==1){
    echo ('GTLib_ExportFuncs=true;'."\n");
}

$minstr=$CFG->gtlib->loadjs_source ? '' : '-min';
readfile('lib.gt_all'.$minstr.'.js');
?>