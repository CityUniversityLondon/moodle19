<?php  // $Id: index.php, for moodle v1.9.5 1/6/2009 09:29:06 Buck,S $

    /*
    *   @pageData = the output for the user selection images and header
    *   @selectData = the current string that will display the dynamically built select boxes
    */

    require_once(dirname(__FILE__) . '/../config.php');
    require_once($CFG->dirroot     . '/pebblepad/include.php');
    require_once($CFG->dirroot     . '/pebblepad/include/functions.php');
    require_once($CFG->dirroot     . '/pebblepad/assetform.php');
    require_once($CFG->dirroot     . '/pebblepad/assetInput.php');
    require_once($CFG->dirroot     . '/pebblepad/reflectiondata.php');
    require_once($CFG->dirroot     . '/pebblepad/entry.php');
    require_once($CFG->dirroot     . '/pebblepad/lib.php');
    include_once($CFG->dirroot     . '/blocks/pebblepad/portfolio_manager.php');

    require_login(); 
    if (!empty($USER->realuser)) {
        header('Location: ""');
        exit;
    }

    if (empty($SITE)) {
        redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
    }
	
    $strmymoodle = "Moodle to ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." Export";
    
    if (isguest()) {
        print_header($strmymoodle);
        notice_yesno(get_string('noguest', 'pebblepad') . '<br /><br />' .
                get_string('liketologin'), get_login_url(), $CFG->wwwroot);
        print_footer();
        die;
    }

    //get post data
    $postData = null;
    $input = null;
    $acc = get_default_portfolio();
    $result = "";
    $zip = false;
    if (isset($_GET['asset']) ){
        $assetType = $_GET['asset'];

    }else{
        $assetType = null;
    }

/////curl

    if ($_POST){
        //check if user had to select asset type
        if (isset($_POST['asset']) ){
            $assetType = $_POST['asset'];
        }else{            
            //get data and check inputs // horrible but functional
            $input = new assetInput();
            $input->set_title(filter($_POST['title'],'TEXT'));
            $input->set_description(filter($_POST['description'],'TEXT'));
            $input->set_type($assetType);
            if(isset($_POST['startdate'])){
                $input->set_startDate(filter($_POST['startdate'],'DATE'));
            }
            if(isset($_POST['enddate'])){
                $input->set_endDate(filter($_POST['enddate'],'DATE'));
            }
            if(isset($_POST['reason'])){ 
                $input->set_reason(filter($_POST['reason'], 'TEXT'));
            }
            if(isset($_POST['knowledge'])){
                $input->set_knowgledge(filter($_POST['knowledge'], 'TEXT'));
            }
            if(isset($_POST['impact'])){
                $input->set_impact(filter($_POST['impact'], 'TEXT'));
            }
            if(isset($_POST['evidence'])){
                $input->set_evidence(filter($_POST['evidence'], 'TEXT'));
            }
            if(isset($_POST['reflection'])){
                $input->reflection = filter($_POST['reflection'], 'TEXT');
            }
            if(isset($_POST['hrs'])){
                $input->hrs = filter($_POST['hrs'], 'NUM');
            }
            if(isset($_POST['mins'])){
                $input->mins = filter($_POST['mins'], 'NUM');
            }
            if(isset($_POST['points'])){
                $input->points = filter($_POST['points'], 'NUM');
            }
            if(isset($_POST['reminder'])){
                $input->reminder = filter($_POST['reminder'], 'DATE');
            }

            if(isset($_FILES)){
                $input->file = $_FILES;
            }

            if(isset($_POST['portfolio'])){
                $acc = get_portfolio(filter($_POST['portfolio'], 'NUM'));
            }else{                
                $acc = get_default_portfolio();
            }

            if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
               if(empty($acc)){
                   include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
                   $acc = get_acc_data($acc);
                   $zip = true;
               }else{
                   $CFG->pebbleroot = $acc->url;
                   $CFG->sharedsecret = $acc->sharedsecret;
               }
            }else{
               $CFG->pebbleroot = $acc->url;
               $CFG->sharedsecret = $acc->sharedsecret;
            }
            
            switch ($input->type){
                case 'thought':
                    $result = build_thought($input, $zip);
                    break;
                case 'activity':
                    $result = build_activity($input, $zip);
                    break;
                case 'ability':
                    $result = build_ability($input, $zip);;
                    break;
                case 'file':
                    $result = build_file($input, $zip);;
                    break;
                default:
                    $result = "Sorry an error occured, no asset was selected.";
            }

        }
    }
    


    $pageData = '';

    $header = '<html lang="en" xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en">
                <head>
                    <SCRIPT type="text/javascript" language="javascript" src="'.$CFG->wwwroot.'/pebblepad/pebblepad.js"></SCRIPT>
                    <script type="text/javascript" lang="javascript" src="'.$CFG->wwwroot.'/pebblepad/jquery-1.3.2.min.js"></script>
                    <!-- jQuery -->
                    <!-- <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script> -->
                    <script type="text/javascript" src="scripts/date.js"></script>
                    <!--[if IE]><script type="text/javascript" src="scripts/jquery.bgiframe.js"></script><![endif]-->

                    <!-- jquery.datePicker.js -->
                    <script type="text/javascript" src="scripts/jquery.datePicker.js"></script>
                    
                    <link rel="stylesheet" type="text/css" href="scripts/datePicker.css" />

                    <title>Create Asset</title>

                    <style type="text/css" >
                        *{
                        font-family:Arial,Verdana,Sans-serif;
                        font-size:14px;
                        /*background-color: #EEE;*/
                        }
                        #courselist ul {
                            list-style-type:none;
                        }
                        .odd{
                            background-color:#fff; display:block; padding:2px;  margin-bottom:3px;
                        }
                        .even{
                            background-color:#FDFFF5; display:block; padding:2px;  margin-bottom:3px;
                        }
                        .assettable{
                            /*background-color:#DFDFFF;*/
                            border:1px solid #9999DD;
                            border-collapse: collapse;  
                        }
                        .assettable td{
                            padding: 2 10 2 10px ;
                            text-align:left;
                            background-color:#E9E9F5;
                            color:#005;
                        }
                        .assettable th{
                            margin:auto 0px;
                            padding: 3px;
                            background-color:#D7D7D7;
                            color:#005;
                        }
                        input, textarea{
                            background-color:#FFF;
                            color:#222;
                        }

                        a.dp-choose-date {
                            float: left;
                            width: 16px;
                            height: 16px;
                            padding: 0;
                            margin: 3px 3px 0;
                            display: block;
                            text-indent: -2000px;
                            overflow: hidden;
                            background: url(images/calendar.gif) no-repeat;
                            margin-left: -20px;
                        }
                        a.dp-choose-date.dp-disabled {
                            background-position: 0 -20px;
                            cursor: default;
                        }
                        /* makes the input field shorter once the date picker code
                         * has run (to allow space for the calendar icon)
                         */
                        input.dp-applied {
                            width: 100px;
                            float: left;
                        }

                    </style>

                    <script language="javascript">
                        $(function() {
                            $(".date-pick").datePicker(
                                {clickInput:true})
                        });

                        function set_screen_size(asset){
                            switch (asset){
                                case "thought":
                                    window.resizeTo(450,540);
                                    break;
                                case "activity":
                                    window.resizeTo(450,800);
                                    break;
                                case "ability":
                                    window.resizeTo(450,615);
                                    break;
                                case "file":
                                    window.resizeTo(450,615);
                                    break;
                                default:
                                    window.resizeTo(450,400);
                            }
                        }

                        ';
    
                        if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
                           include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
                           $header.= get_script();
                        }

                        $header.= '
                        function toggle(obj){
                            var row = document.getElementById(obj.id);
                            var rowa = document.getElementById(obj.id+"1");
                            
                            if (rowa.style.display == ""){
                                rowa.style.display = "none";
                                row.title = "expand";
                                row.src = "images/plus.gif";
                            }else{
                                rowa.style.display = "";
                                row.title = "hide";
                                row.src = "images/minus.gif";
                            }
                        }

                        function hide_multi_submit(obj){
                                var btn = document.getElementById("create");
                                btn.style.display = "none";
                                var loader = document.getElementById("sending");
                                loader.style.display = "";
                        }

                        $(document).ready(function() {
                            $(".pimg").click(function(){
                                swap_image($(this));
                                $(this).next(".item_div").toggle("slow");
                            });

                           function swap_image(this_item) {
                                if ($(this_item).attr("src") == "images/plus.gif") {
                                    $(this_item).attr("src", "images/minus.gif");
                                } else {
                                   $(this_item).attr("src", "images/plus.gif");
                                }
                            }
                        });
                    </script>

                </head><body onload=set_screen_size("'.$assetType.'")>';


    $footer = '</body></html>';


    $fields = null;

    $formData = "<form enctype='multipart/form-data' style='text-align:center;' method='POST' action='?asset=".$assetType."'>";
    $formData.= "<table style='width:400px;' align='center' class='assettable' >";
    if (!empty($assetType)){  
        $formData.= "<tr><th>new ".$assetType."</th></tr>";
    }else{
        $formData.= "<tr><th>create new ???</th></tr>";
    }
    if ($assetType == null){
       $formData.= "<tr><td style='text-align:center;'>Please select an asset type</td></tr>";
       $formData.= "<tr><td style='text-align:center;'>";
       $formData.= '<select name="asset" >';
       $formData.= "<option value='thought'>thought</option>";
       $formData.= "<option value='activity'>activity</option>";
       $formData.= "<option value='ability'>ability</option>";
       $formData.= "<option value='file'>file</option>";
       $formData.= "</select>";
       $formData.= "</td></tr>";
       $formData.= "<tr><td style='text-align:center;'><input style='background-color:#CCC; color:#222;' type='submit' name='create' value='Select Asset Type'></td></tr>";
       
    }else{

        if ($result != ""){
           $formData.= "<tr><td class='error' style='text-align:center; color:#F00;' >".$result."</td></tr>";
        }

        //what do we show in form
        switch ($assetType) {
            case "thought":
                $fields = getThoughtFields();
                break;
            case "activity":
                $fields = getActivityFields();
                break;
            case "ability":
                $fields = getAbilityFields();
                break;
            case "file":
                $fields = getFileFields();
                break;
        }

        $formData.= getForm($fields, $input);
        $formData.= '<tr><td style="text-align:center;">';
        $formData.= get_html_portfolio_accounts($acc->id);
        $formData.= '</td></tr>';
       
        if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
           include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
           $formData.= get_button_status($acc);
        }else{
            $formData.= "<tr><td style='text-align:center;'><br /><img id='sending' style='display:none;' src='images/ajax-loader.gif' alt='sending' title='sending' /><input onClick='hide_multi_submit(this)' align='center' type='submit' id='create' value='Send to Portfolio'><br />&nbsp;</td></tr>";
        }
               
    }

    $formData.= "</table>";
    $formData.= "</form>";

    $pageData.= $header;
    $pageData.= $formData;
    $pageData.= $footer;

    // page structure    
    
    $loggedinas = user_login_string();

    if (empty($CFG->langmenu)) {
        $langmenu = '';
    } else {
        $currlang = current_language();
        $langs = get_list_of_languages();
        $langlabel = get_accesshide(get_string('language'));
        $langmenu = popup_form($CFG->wwwroot . '/pebblepad/index.php?lang=', $langs,
                'chooselang', $currlang, '', '', '', true, 'self', $langlabel);
    }
    
    //render page
    print($pageData);
   
// fields
function getThoughtFields(){
               
        $formFields = new assetForm();
        $formFields->set_hasTitle(true);
        $formFields->set_hasDescription(true);
        $formFields->set_hasStartDate(true);
        $formFields->set_hasEndDate(true);
        $formFields->set_hasReflection(true);
        
        return $formFields;
}


function getActivityFields(){
        
        $formFields = new assetForm();
        $formFields->set_hasTitle(true);
        $formFields->set_hasDescription(true);
        $formFields->set_hasReason(true);
        $formFields->set_hasKnowgledge(true);
        $formFields->set_hasImpact(true);
        $formFields->set_hasStartDate(true);
        $formFields->set_hasEndDate(true);
        $formFields->set_hasReflection(true);
        
        return $formFields;
}

function getAbilityFields(){
        
        $formFields = new assetForm();
        $formFields->set_hasTitle(true);
        $formFields->set_hasDescription(true);
        $formFields->set_hasEvidence(true);
        $formFields->set_hasReflection(true);
        
        return $formFields;
}

function getFileFields(){
        
        $formFields = new assetForm();
        $formFields->set_hasTitle(true);
        $formFields->set_hasDescription(true);
        $formFields->set_hasFile(true);
        $formFields->set_hasReflection(true);

        return $formFields;
}

function filter($str, $type){
    if ($type == 'TEXT'){
        return strip_tags(trim($str));
    }elseif($type == 'DATE'){
        return $str; //needs safe return format
    }else{
        return intval($str);
    }
}

// make the actual assets
function build_thought($input, $zip = false){

            $leap = new leap2a();

            $ppAsset = new stdClass();

            $ppAsset->id            = time();
            $ppAsset->type          = $input->get_type();
            $ppAsset->title         = $input->get_title();
            $ppAsset->description   = $input->get_description();
            $ppAsset->startdate     = $input->get_startDate();
            $ppAsset->enddate       = $input->get_endDate();
            $ppAsset->timemodified  = $input->timemodified;
            $ppAsset->hrs           = $input->hrs;
            $ppAsset->mins          = $input->mins;
            $ppAsset->points        = $input->points;

            if ( empty($ppAsset->title) || empty($ppAsset->description) ){
                return "Assets must have a title and description";
            }
           
            if (!dblCheckDate($ppAsset)){
                return "Error! Please check start and end dates.";
            }

            $leap->set_objects($ppAsset, 'thought');

            if (!empty($input->reflection) || !empty($input->hrs) || !empty($input->mins) || !empty($input->points) || !empty($input->reminder)){
                $reflection = new reflectiondata();
                $reflection->id = $ppAsset->id;
                $reflection->set_type($input->type);
                $reflection->set_text($input->reflection);
                $reflection->set_reminder($input->reminder);
                $leap->set_objects($reflection, 'reflection');
            }
            
            $result = ($leap->build_zip($zip, 'true'));

            return $result;
}

function build_activity($input, $zip){

            $leap        = new leap2a();
            $ppAsset     = new stdClass();
            $ppAsset->id            = time();
            $ppAsset->type          = $input->get_type();
            $ppAsset->title         = $input->get_title();
            $ppAsset->description   = $input->get_description();
            $ppAsset->startdate     = $input->get_startDate();
            $ppAsset->enddate       = $input->get_endDate();
            $ppAsset->reason        = $input->get_reason();
            $ppAsset->knowledge     = $input->get_knowgledge();
            $ppAsset->impact        = $input->get_impact();
            $ppAsset->timemodified  = $input->timemodified;
            $ppAsset->hrs           = $input->hrs;
            $ppAsset->mins          = $input->mins;
            $ppAsset->points        = $input->points;

            if ( empty($ppAsset->title) || empty($ppAsset->description) ){
                return "Assets must have a title and description";
            }

            $leap->set_objects($ppAsset, 'activity');

            if (!empty($ppAsset->reason)){
                $reasonData = new stdClass();
                $reasonData->id = $ppAsset->id;
                $reasonData->title = 'Reasons';
                $reasonData->type = 'reasons';
                $reasonData->selection_type = $input->get_type();
                $reasonData->text = $ppAsset->reason;
                $reasonEntry = new entry($reasonData);
                $leap->set_objects($reasonEntry, 'reasons');
            }

            if (!empty($ppAsset->impact)){
                $impactdata = new stdClass();
                $impactdata->id = $ppAsset->id;
                $impactdata->title = 'Impact';
                $impactdata->type = 'impact';
                $impactdata->selection_type = $input->get_type();
                $impactdata->text = $ppAsset->impact;
                $impactEntry = new entry($impactdata);
                $leap->set_objects($impactEntry, 'impact');
            }

            if (!empty($ppAsset->knowledge)){
                $knowledgeData = new stdClass();
                $knowledgeData->id = $ppAsset->id;
                $knowledgeData->title = 'Gains';
                $knowledgeData->type = 'gains';
                $knowledgeData->selection_type = $input->get_type();
                $knowledgeData->text = $ppAsset->knowledge;
                $knowledgeEntry = new entry($knowledgeData);
                $leap->set_objects($knowledgeEntry, 'gains');
            }
           
            if (!empty($input->reflection) || !empty($input->hrs) || !empty($input->mins) || !empty($input->points) || !empty($input->reminder)){
                $reflection = new reflectiondata();
                $reflection->id = $ppAsset->id;
                $reflection->set_type($input->type);
                $reflection->set_text($input->reflection);
                $reflection->set_reminder($input->reminder);
                $leap->set_objects($reflection, 'reflection');
            }
           
            $result = ($leap->build_zip($zip, 'true'));

    return $result;
}

function build_ability($input, $zip){

            $leap       = new leap2a();
            $ppAsset    = new stdClass();

            $ppAsset->id            = time();
            $ppAsset->type          = $input->get_type();
            $ppAsset->title         = $input->get_title();
            $ppAsset->description   = $input->get_description();
            $ppAsset->startdate     = $input->get_startDate();
            $ppAsset->enddate       = $input->get_endDate();
            $ppAsset->evidence      = $input->get_evidence();
            $ppAsset->timemodified  = $input->timemodified;
            $ppAsset->hrs           = $input->hrs;
            $ppAsset->mins          = $input->mins;
            $ppAsset->points        = $input->points;

            if ( empty($ppAsset->title) || empty($ppAsset->description) ){
               return "Assets must have a title and description";
            }

            $leap->set_objects($ppAsset, 'ability');

            if (!empty($ppAsset->evidence)){
               $evidenceData = new stdClass();
               $evidenceData->id = $ppAsset->id;
               $evidenceData->title = 'Evidence';
               $evidenceData->type = 'evidence';
               $evidenceData->selection_type = $input->get_type();
               $evidenceData->text = $ppAsset->evidence;
               $evidenceEntry = new entry($evidenceData);
               $leap->set_objects($evidenceEntry, 'evidence');
            }

            if (!empty($input->reflection) || !empty($input->hrs) || !empty($input->mins) || !empty($input->points) || !empty($input->reminder)){
                $reflection = new reflectiondata();
                $reflection->id = $ppAsset->id;
                $reflection->set_type($input->type);
                $reflection->set_text($input->reflection);
                $reflection->set_reminder($input->reminder);
                $leap->set_objects($reflection, 'reflection');
            }

            $result = ($leap->build_zip($zip, 'true'));

    return $result;
}

function build_file($input, $zip){
            global $CFG;
            $limitname = "";
            $limit = 0;

            $leap       = new leap2a();
            $ppAsset    = new stdClass();

            $ppAsset->id            = time();
            $ppAsset->type          = $input->get_type();
            $ppAsset->title         = $input->get_title();
            $ppAsset->description   = $input->get_description();
            $ppAsset->file          = $_FILES; 
            $ppAsset->name          = $input->get_title();

            if ( empty($ppAsset->title) || empty($ppAsset->description) ){
                return "Assets must have a title and description";
            }

            if (!empty($ppAsset->file)){
               $fileData                    = new stdClass();
               $fileData->id                = $ppAsset->id;
               $fileData->course            = 0;
               $fileData->name              = $ppAsset->title; 
               $fileData->type              = $input->get_type();
               $fileData->description       = $input->get_description();
               $fileData->selection_type    = $input->get_type();
               $fileData->timemodified      = $input->timemodified;
               $fileData->hrs               = $input->hrs;
               $fileData->mins              = $input->mins;
               $fileData->points            = $input->points;
               $fileData->file              = $ppAsset->file;
               $fileData->format            = $input->get_type();
               $fileData->reference         = $fileData->file['file']['tmp_name'];
               $fileData->file_type         = $fileData->file['file']['type'];
               
               //check filesize limit as file has to be uploaded to moodles temp dir before forwarding
               if ($CFG->maxbytes != 0 && ($CFG->maxbytes < (substr(get_cfg_var('upload_max_filesize'), 0, -1)*1024)*1024) ){
                    $limit = $CFG->maxbytes/1024;
                    $limitname = "Moodle";
               }else{
                    $limit = substr(get_cfg_var('upload_max_filesize'), 0, -1)*1024;
                    $limitname = "Server";
               }

               if ($fileData->file['file']['error']){
                   if ($fileData->file['file']['error'] == 4){
                       return "Please select a file to upload";
                   }else{
                        return "File size is greater than your current upload limit. Current ".$limitname." limit = ".$limit."KB or ".($limit/1024)."MB";
                   }
               }
               
               $leap->add_file($fileData->file['file']['tmp_name'], $fileData->file['file']['name'], false);

               $leap->set_objects($fileData, 'file');
            }

            if (!empty($input->reflection) || !empty($input->hrs) || !empty($input->mins) || !empty($input->points) || !empty($input->reminder)){
                    $reflection = new reflectiondata();
                    $reflection->id = $ppAsset->id;
                    $reflection->set_type($input->type);
                    $reflection->set_text($input->reflection);
                    $reflection->set_reminder($input->reminder);
                    $leap->set_objects($reflection, 'reflection');
            }

            $result = ($leap->build_zip($zip, 'true'));

    return $result;
}

function dblCheckDate($asset){    
    try {
        $start = strtotime(str_replace("/", "-", $asset->startdate));
        $end = strtotime(str_replace("/", "-", $asset->enddate));

        if (empty($start) && empty($end)){
            return true;
        }
        if (empty($start) && !empty($end)){
            return false;
        }
        if (!empty($start) && empty($end)){
            return true;
        }else{
            if ($start > $end){
                return false;
            }else{
                return true;
            }
        }
    } catch (Exception $e) {
        return false;
    }
}
?>
