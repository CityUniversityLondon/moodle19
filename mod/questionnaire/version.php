<?php // $Id: version.php,v 1.20.10.7 2008/09/02 13:48:02 mchurch Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of NEWMODULE
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2009040606;  // The current module version (Date: YYYYMMDDXX)
//$module->requires = 2007101000;  // Requires this Moodle version
$module->cron     = 60*60*12;    // Period for cron to check this module (secs)

?>
