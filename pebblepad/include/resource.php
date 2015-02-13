<?php
function getResource($acourse, $resourceId){

       if (! $resources = get_all_instances_in_course("resource", $acourse) ) {
         notice(get_string('thereareno', 'moodle', $strresources), "../../course/view.php?id=$acourse->id");
         exit;
       }

       foreach($resources as $r) {
           if ( intVal($r->id) == intVal($resourceId) ){
              return $r;
           }
       }

       return $resources;
}

function getResourceOutput($resource){
        global $CFG, $USER;

        if ($resource->alltext != "" || !empty($resource->alltext)) {
                $output = format_text($resource->alltext, $format=FORMAT_MOODLE, NULL, NULL);
        } else {
                require_once('pp_mime_type.php');
                if ($mimetype = pebble_mime_content_type($resource->reference)) {
                        if (substr_count($mimetype, 'image') > 0) {
                                $output = '<img src="'.$CFG->wwwroot.'/file.php/'.$resource->course.'/'.$resource->reference.'" alt="'.$resource->reference.'" title="'.$resource->reference.'" />';
                        } else {
                                $output = "You are exporting a file of type '$mimetype' called '".$resource->reference."'";
                        }
                }
        }

        return $output;
}
?>
