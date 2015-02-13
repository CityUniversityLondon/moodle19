<?php
function xmldb_enhancedfile_upgrade($oldversion=0){
    global $CFG;
    $result=true;
    if ($result && $oldversion < 2010030301){
        $file=$CFG->dirroot.'/mod/enhancedfile/db/upgrade_2010030301.xml';
        $result=install_from_xmldb_file($file);
    }
    return ($result);
}
?>