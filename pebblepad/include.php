<?php
function update_mypebble_icon() {

    global $CFG, $USER;

    if (!empty($USER->editing)) {
        $string = get_string('updatemymoodleoff');
        $edit = '0';
    } else {
        $string = get_string('updatemymoodleon');
        $edit = '1';
    }    
    return "&nbsp";
}

function setBranding(){
    global $CFG;
    $CFG->pebbleExportBrand = 'pebblepad';
    if ($current_inst = get_records('block_pebblepad')) {
	foreach ($current_inst as $inst) {
		if ($inst->name == 'Institute for Learning (IFL)') {
			$CFG->pebbleExportBrand = 'reflect';
		}
	}
    }
}
?>
