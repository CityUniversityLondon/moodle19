<?php
function enhancedfile_get_types() {
    $types = array();

    $type = new object();
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = "enhancedfile";
    $type->typestr = get_string('modulenameadd', 'enhancedfile');
    $types[] = $type;
    return $types;
}

function enhancedfile_get_participants($fileid) {
//Returns the users with data in one resource
//(NONE, but must exist on EVERY mod !!)

    return false;
}

/**
 * add file
 * @param stdObject $fdata form data
 * @return integer|boolean id in database or false
 */
function enhancedfile_add_instance($resource){
    global $CFG;
    $resource->type = clean_param($resource->type, PARAM_SAFEDIR);   // Just to be safe
    if (!empty($CFG->formatstringstriptags)) {
        $resource->name = clean_param($resource->name, PARAM_TEXT);
    } else {
        $resource->name = clean_param($resource->name, PARAM_CLEAN);
    }

    require_once("$CFG->dirroot/mod/resource/type/$resource->type/resource.class.php");
    $resourceclass = "resource_$resource->type";
    $res = new $resourceclass();

    return $res->add_instance($resource);
}

/**
 * delete file - NOTE: Just return true, this module is just a wrapper for resource/file
 * @param integer $id
 * @return boolean
 */
function enhancedfile_delete_instance($id) {
    return (true);
}

?>