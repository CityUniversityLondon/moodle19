<?php
    require_once(dirname(__FILE__) . './../config.php');
    global $CFG;
    require_once($CFG->dirroot     . '/pebblepad/include.php');
    setBranding();
    $help ='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
    $help.='<html>';
        $help.='<head>';
            $help.='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            $help.='<title>Export Type Help</title>';
            $help.='<link rel="stylesheet" type="text/css" href="styles.css" />';

        $help.='</head>';
        $help.='<body >';

            $help.='<div id="pebblehelp">';

                $help.="<img alt='".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." logo' title='".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." logo' src='images/".get_string($CFG->pebbleExportBrand, 'block_pebblepad')."-128x36-logo.png' /><br />";

                $help.="<h3>Export to ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." Help</h3>";

                $help.="<h4>Export as ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." blog with thoughts</h4>";
                $help.="<p>If checked (ticked) all selected items will be imported into ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." as thoughts and linked to a new Blog asset</p>";

                $help.="<h4>Send to: [select portfolio]</h4>";
                $help.="<p>All items selected will be imported into ".get_string($CFG->pebbleExportBrand, 'block_pebblepad')." as thoughts.<br />(This option only appears if you have more than one option available to select)</p>";

                $help.="<h4>Create LEAP2a import/export zip</h4>";
                $help.="<p>All items selected will be added to a LEAP2a importable zip file. You will be prompted to download and save the file. (Only available for some Moodle installs)</p>";

                $help.="<div id='pebbleHelpFooter'><div id='closebtn'><a href='javascript:window.close();'>Close</a></div></div>";
             $help.="</div>";
         $help.="</body>";
     $help.="</html>";
     echo $help;
?>