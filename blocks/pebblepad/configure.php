<?php
require_once('../../config.php');
include_once('../../pebblepad/include.php');

global $CFG, $USER, $COURSE;

setBranding();

//remove clone access attempt
if (!empty($USER->realuser)) {
    header('Location: ""');
    exit;
}

require_login(0, false, null, true);
$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);


// TODO: security for oAuth then remove
//user access 
if (!has_capability('moodle/site:config', $context)) {
    header('Location: ""');
    exit;
}


$SITE = get_site();
print_header($SITE->fullname.' to '.get_string($CFG->pebbleExportBrand, 'block_pebblepad'), $SITE->fullname.' to '.get_string($CFG->pebbleExportBrand, 'block_pebblepad'), 'home', '',
	'<meta name="description" content="'. strip_tags(format_text($SITE->summary, FORMAT_HTML)) .'" />',
	true, '', user_login_string($SITE));

$content = '';

/*********** user view ***********/
if (!has_capability('moodle/site:config', $context)) {
     
/*********** admin view **********/
} else {
	include_once($CFG->dirroot . '/lib/dmllib.php');
	
	if ($_POST) {

        $errFound = false;
        $errText = "";

        $inst_details = explode(' | ', $_POST['pp_institution']);
        $data = new Object();
        $data->userid = $USER->id;
        if ( ( strlen($inst_details[0]) > 0) && (strlen($inst_details[1]) > 0) ){
            $data->name = trim($inst_details[0]);
            $data->url = trim($inst_details[1]);
        }else{
            $errFound = true;
            $errText = "Invalid Institution format";
        }

        if (strlen($_POST['pp_sharedsecret']) > 5 ){
            $data->sharedsecret = trim($_POST['pp_sharedsecret']);
        }else{
            $data->sharedsecret = "";
        }

        $data->linktype = 'INST';
        $data->priority = 0;

        if (!empty($data->name) && !empty($data->url)){
            //delete_records('block_pebblepad', 'linktype', 'INST', 'userid', $USER->id);
            delete_records('block_pebblepad', 'linktype', 'INST');
        }
        if (  (!$errFound) && (insert_record('block_pebblepad', $data)) ) {
            $content .= '<p id="pp_institution_added" style="color: green">Institution successfully selected.</p>';            
        } else {
            $content .= '<p id="pp_institution_added" style="color: red">Failed to select institution. '.$errText.' </p>';
        }
	}

	if ($current_institutions = get_records('block_pebblepad', 'linktype', 'INST')) {
		$content .= '<p>You currently have the following institution selected: </p>
		<ol>';
		foreach ($current_institutions as $inst) {
			$content .= '<li>'.$inst->name.'</li>';
		}
		$content .= '</ol><br />';
	}

	$content .= '
	<script type="text/javascript" src="'.$CFG->wwwroot.'/pebblepad/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="'.$CFG->wwwroot.'/pebblepad/jquery.highlight-3.js"></script>
	<script>
		$(document).ready(function(){				
			$("#pp_institution").keyup(function(e) {
				
				var q = $(this).val()
				if ($.trim(q).length > 0) {
					$.ajax({
						beforeSend: function(){
							$("#ajax_loader").show();
						},
						type: "GET",
						url: "'.$CFG->wwwroot.'/blocks/pebblepad/institutions.php",
						data: "str=" + $(this).val(),
						success: function(html){
							if (html.length > 0) {
								$("#pp_institution_results").html(html);
								$("#pp_institution_results").show();
								$("#pp_institution_results td").each(function() {
									$(this).highlight(q);
								});
							}
						},
						complete: function(){
							$("#ajax_loader").hide();
						}
					});
				} else {
					$("#pp_institution_results").html("");
					$("#pp_institution_results").hide();
				}
			});
			
			$("#pp_institution_results td").live("click", function() {
				var q = $(this).text();
				$("#pp_institution_results").hide();
				$("#pp_institution").val(q);
			});
			
			<!--$("#pp_institution_added").hide(8000);-->
		});
	</script>

	<p>You can enter an institution (name or url) for a '.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' account here:</p>
	
	<form action="'.$_SERVER['PHP_SELF'].'" method="post">
		<input type="text" id="pp_institution" name="pp_institution" value="" style="margin-top: 10px; width: 500px;" />
		<img id="ajax_loader" src="'.$CFG->wwwroot.'/pebblepad/images/ajax-loader.gif" style="width: 20px; height: 20px; display: none;"/>
		<style type="text/css">
			#pp_institution_results {
				display: none;
				position: absolute;
				width: 505px;
				z-index: 100;
				background: #fff;
				border: 1px solid #3D7BAD;
				border-left: 1px solid #A4C9E3;
				border-right: 1px solid #A4C9E3;
				margin-left: 1px;
				font-size: 12px;
			}
			
			#pp_institution_results tr {
				padding: 3px;
				margin: 20px 0px;
			}
			
			#pp_institution_results tr:hover {
				background: #ccc;
				cursor: pointer;
			}
			
			#pp_institution_results .highlight {
				background: #ccc;
			}
		</style>
		<table id="pp_institution_results">
			
		</table>
		<br />
		<br />
		Sharedsecret: <input type="password" name="pp_sharedsecret" id="pp_sharedsecret" />
		<input type="submit" name="submit_pp_institution" id="submit_pp_institution" value="Select" />
		<p style="font-style: italic; font-size: 11px;">Note: You need to enter a shared secret in order for your users to authenticate with '.get_string($CFG->pebbleExportBrand, 'block_pebblepad').'</p>
	</form>';
}
	
print($content);

print_footer();

?>
