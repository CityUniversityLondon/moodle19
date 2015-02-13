<?php
// build the individual Scorm preview
function getScormOutput($courseId, $scormId){

        $output = "";
        if (!empty($courseId) && !empty($scormId)) {
            if (! $scorm = get_record('scorm', 'id', $scormId)) {
                error('Course module is incorrect');
            }
        } else {
            error('A required parameter is missing');
        }
        if(!$scoes = get_records_select('scorm_scoes',"scorm='$scorm->id' ORDER BY id")){
            error('Missing script parameter');
        }

        foreach($scoes as $sco){
            if ($sco->launch!='') {
                $scoId = $sco->id;
            }
        }
        
        $trackData = getTrackData($scoId, 1); //todo check for attempts as current demos have only 1 and overwrite on update
        $scorm->status = getScormStatus($trackData);
        
        $output.='<div style="width: 99%; background-color: #EFEFEF; margin: auto; text-align: center; font-weight:bold; padding:3px; margin-bottom:5px;">'.$scorm->name.'</div>';
        $output.='<div style="width: 99%; background-color: #FFFFFF; padding:0px; border-style: solid; border: 1px #EFEFEF solid; margin: 0px 3px 10px 3px; "><div style="background-color: #EFEFEF; padding: 0px 3px 3px 3px; text-align: center; font-weight: bold;">Summary</div>';
        $output.='<div style="padding:3px;">'.$scorm->summary.'</div></div>';
        $output.='<div style="margin: 0 auto; background-color: #EFEFEF; padding: 10px;">Attempted: '.userdate($trackData->timemodified).'<br />';
        $output.='Status: '.$scorm->status.'<br />';
        $output.='Time taken: '.$trackData->total_time.'<br />';
        $output.='Score: '.$trackData->score_raw.'/'.$scorm->maxgrade.' ('.(($trackData->score_raw / $scorm->maxgrade) * 100).'%)</div>';

        return $output;
}

function getTrackData($id, $attempt){
        global $USER;
        $trackdata = scorm_get_tracks($id, $USER->id);
        return $trackdata;
}

function getScormStatus($trackdata){
        $result = "notattempted";
        
        if (isset($trackdata->status)){
            if ($trackdata->status != '') {
               $result = $trackdata->status;
            }
            $strstatus = get_string($result,'scorm');
        }
        return $result;
}

//build an object for export
function getScormObject($courseId, $scormId){

        if (!empty($courseId) && !empty($scormId)) {
            if (! $scorm = get_record('scorm', 'id', $scormId)) {
                error('Course module is incorrect');
            }
        } else {
            error('A required parameter is missing');
        }
        if(!$scoes = get_records_select('scorm_scoes',"scorm='$scorm->id' ORDER BY id")){
            error('Missing script parameter');
        }

        foreach($scoes as $sco){
            if ($sco->launch!='') {
                $scoId = $sco->id;
            }
        }

        //may need try catch around following
        $trackData = getTrackData($scoId, 1); //todo check for attempts as current demos have only 1 and overwrite on update
        $scorm->status = getScormStatus($trackData);

        $resource = new stdClass();

        $resource->id = $scorm->id;
        $resource->course = $courseId;
        $resource->name = $scorm->name;
        $resource->type = "html";
        $resource->summary = $scorm->summary;
        $resource->alltext = ""; // add the output to this on return;
        $resource->timemodified = $trackData->timemodified;

        return $resource;

}
?>
