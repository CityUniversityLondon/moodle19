<?php
$CFG->pebbleExportBrand = 'pebblepad';
		
if ($current_inst = get_records('block_pebblepad')) {
        foreach ($current_inst as $inst) {
                if ($inst->name == 'Institute for Learning (IFL)') {
                        $CFG->pebbleExportBrand = 'reflect';
                }
        }
}

function print_pebble_header($pebbleTags){
    global $CFG;
    $header  = "";
    $header .="<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
    $header .="<html xmlns='http://www.w3.org/1999/xhtml' dir='ltr' lang='en' xml:lang='en'>";
    $header .="<head>";
    $header .="<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    if ($CFG->pebbleExportBrand == 'reflect'){
        $header .="<link rel='stylesheet' type='text/css' href='".$CFG->wwwroot."/pebblepad/reflect.css' />\n"; // reflect only
    }else{
        $header .="<link rel='stylesheet' type='text/css' href='".$CFG->wwwroot."/pebblepad/styles.css' />\n";
    }
	$header .="<script type='text/javascript' lang='javascript' src='http://jqueryjs.googlecode.com/files/jquery-1.3.2.min.js'></script>";
    $header .="<SCRIPT type='text/javascript' language='Javascript' SRC='".$CFG->wwwroot."/pebblepad/pebblepad.js'></SCRIPT>";
    $header .="</head>";
    $header .="<div id='wrapper'>";
    $header .="<div class='pebbleElement'>";
    $header .="<div id='pebbleHeadElement'>Please Note: Only content within the solid border will be exported as an asset</div>"; // the pebble logo
    $header .="</div>";
	
	$header .="
	<script type='text/javascript' lang='javascript'>
		$(document).ready(function() {
			var input = $('input:checkbox:not(#select_all)');
			if (input.length == 0) {
				$('#export_options').css('display', 'none');
			}
			
			$('#select_all').click(function() {
				var checked_status = this.checked;
				$('input:checkbox:not(#select_all)').each(function() {
					this.checked = checked_status;
				});
			});
			
			$('input:checkbox:not(#select_all)').click(function() {
				$('#select_all').attr('checked', '');
			});
		});  
        ";
        if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
           include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
           $header.= get_script();
        }
        $header.="
	</script>

	<style type='text/css'>
		#export_options ul {
			list-style: none;
			display: inline;
			margin-left: 0;
			padding-left: 0;
		}
		
		#export_options ul li {
			display: inline;
			margin-left: 15px;
		}
	</style>";
	
	
	$header .="
	<div id='export_options'>
		<ul>
			<li>
				<input type='checkbox' id='select_all' /><label>Select All</label>
			</li>
			<li>
				<a href='#export_anchor'>Jump to export</a>
			</li>
		</ul>
	</div>";
	
    $header .= "<form method='post' action=''>";

    echo $header;
}

function print_element_start(){
    $data  = "";
    $data .= "<div class='element'>";
    echo $data;
}

function print_element_end(){
    $data  = "";
    $data .= "</div>";
    $data .= "<div class='clearer'></div>";
    echo $data;
}

//@exported bool has the leap2a object being exported
function print_export($exported, $pebbleTags, $assetType="", $ppselected=null){
    global $CFG;

    if (empty($ppselected)){
        $ppselected = $CFG->pebblerootid;
    }
    
    $data  = "";
    $data .="<div class='pebbleElement'>
	<a name='export_anchor'></a>";

    if (!$exported){

        $helpUrl = $CFG->wwwroot."/pebblepad/exportassethelp.php";

        // should we see blog link
        if (isset($_GET['blogid'])){
            $data .= "<input style='padding:0px;' type='checkbox'";
            if ($assetType == 'blog'){
                 $data .= " checked ";
            }
            $data .= "name='assetType' value='blog' />Export as ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." blog with thoughts  <img onClick='popupsized( \"$helpUrl\" )' alt='Help info' title='Help info' src='images/help.gif' /><br />";
        }
         
        $data .= get_html_portfolio_accounts($ppselected);

        $data .= "<br />";
        $data .= "<input style='width:400px;' id='create' name='export' type='submit' value='Send to Portfolio'>";
        $data .= "</form>";
    }else{

        $data.="<div id='exportdone'>";
        
        $data.="<form action='' method='post'>";
        $data.="<div id='closebtn'><a href='javascript:window.close();'>Close</a></div>";
        $data.="</form></div>";
    }
    $data .="</div>";
    echo $data;
}


function print_pebble_footer(){
        global $CFG;

        $footer = "";
        if ($CFG->pebbleExportBrand == 'pebblepad'){
            $footer.= "<div class='pebbleElement'>Copyright © 2009 Pebble Learning Ltd. | PebblePad is a product of <a target='_blank' href='http://www.pebblelearning.co.uk'>Pebble Learning Ltd.</a> Please review our <a target='_blank' href='http://www.pebblelearning.co.uk/terms_of_use.htm'>terms of use</a> which includes our privacy and acceptable use polices.</div>";
            $footer.= "</div>";
            $footer.= "</html>";
        }else{
            $footer.= "<div class='pebbleElement'>Copyright © 2009 ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')."</div>";
            $footer.= "</div>";
            $footer.= "</html>";
        }
        echo $footer;
}

// @$leap to moodle array object
// @$zip bool create zip only
function doExport($leap, $zip){

         global $CFG, $USER;
         $feedback = "";
         $exported = false;

         $result = ($leap->build_zip($zip));

         if ($result == 200){
             $exported = true;
         }

         if ($exported){
            $feedback="<div id='success'>Export was successfull</div>";
         }else{
             if ($zip){
                $feedback="<div id='zip'>".$result."</div>";
             }else{
                $feedback="<div id='fail'>Export failed due to Unknown Error</div>";
             }
         }

         return $feedback;

}

function getCorrect($question, $state){
        global $CFG;
        require_once("$CFG->dirroot/question/type/".$question->qtype."/questiontype.php");
        return $result;
}
?>
