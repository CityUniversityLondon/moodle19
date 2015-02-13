<?php

include_once($CFG->dirroot . '/lib/dmllib.php');
include_once($CFG->dirroot . '/pebblepad/include.php');

setBranding();

class block_pebblepad extends block_base {

	function init() {
            global $CFG;
            $this->title   = get_string($CFG->pebbleExportBrand, 'block_pebblepad');
            $this->version = 2009052600;
            $this->page = $CFG->wwwroot . '/blocks/pebblepad/signin.php';
	}

	function get_content() {
            global $CFG, $COURSE, $USER;

            if ($this->content !== NULL) {
                    return $this->content;
            }

            $pagebrand = get_string($CFG->pebbleExportBrand, 'block_pebblepad');
            $pebbleLink = $CFG->wwwroot."/pebblepad/";

            // make sure its a valid user
            if (!empty($USER->realuser)) {
                $this->content         = new stdClass;
                $this->content->text   = '<p style="margin: auto;">Disabled<br />whilst you are logged in<br />"AS User View"</p>';
                $this->content->footer = '';
                return $this->content;
            }

            //guest
            if (isguest()){
                $this->content         = new stdClass;
                $this->content->text   = '<p style="margin: 0 auto; padding:1px">Login to use<br />';
                $this->content->footer = '<img src="'.$pebbleLink.'images/'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').'-128x36-logo.png" alt="'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' Block" title="'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' Block" /></p>';
                return $this->content;
            }

            //show logo
            $text = '<p style="margin:0 auto; padding:0px;"><img src="'.$pebbleLink.'images/'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').'-128x36-logo.png" alt="'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' Block" title="'.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' Block" /></p>';

            $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);

            //admin view
            if (has_capability('moodle/site:config', $context)) {
                $foot = '<a href="'.$CFG->wwwroot.'/blocks/pebblepad/configure.php"><img src="'.$CFG->wwwroot.'/pix/t/edit.gif" /> Edit '.get_string($CFG->pebbleExportBrand, 'block_pebblepad').' Settings</a>';
            //user view
            }else{
            
                $current_personal = get_records('block_pebblepad', 'userid', $USER->id);
                $current_institutions = get_records('block_pebblepad', 'linktype', 'INST');
                if ( !empty($current_personal) || !empty($current_institutions) ) {
                    $defaultUrl = "";

                    // if personal account not set to default we wil use the intitutions
                    if (!empty($current_personal)){
                       foreach($current_personal as $personal){
                            if ($personal->priority){
                                $defaultUrl =  $personal->url;
                            }
                       }
                    }
                    if(empty($defaultUrl)){
                        foreach($current_institutions as $intitution){
                                $defaultUrl =  $intitution->url;
                        }
                    }

                    $text.='<a href="javascript:;" onclick="window.open(\''.$this->page.'\',\''.$pagebrand.'\', \'width=800,height=600,scrollbars=yes,resizable=1\'); return false;"><p style="margin: 0 auto; padding:1px; font-size:96%; ">Launch '.$pagebrand.'</p></a>';

                    $text.= '<a href="'.$pebbleLink.'"><p style="margin: 0 auto; padding:1px; font-size:96%; ">Export Moodle assets</p></a>';

                    $text.= '<form style="margin: 0 auto; padding:1px; font-size:96%;" name="asset" method="POST">';


                    $page = $pebbleLink."newppa.php?asset=";

                    $text.= '<a  href="javascript:;" onclick="window.open(\''.$page.'\',\'assetpopup\', \'width=10,height=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes\'); return false;">Create</a>&nbsp;<select id="asset" onchange="window.open(\''.$page.'\' + this.value,\'assetpopup\', \'width=10,height=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=yes\'); this.selectedIndex = 0; " >';
                    $text.= '<option value="">Select...</option>';
                    $text.= '<option value="thought">thought</option>';
                    $text.= '<option value="activity">activity</option>';
                    $text.= '<option value="ability">ability</option>';
                    $text.= '<option value="file">file</option>';
                    $text.= '</select> ';

                    $text.= '</form>';
                }
            $foot = '';
            }
		
            $this->content         = new stdClass;
            $this->content->text   = $text;
            $this->content->footer = $foot;

            return $this->content;
	}
}
?>
