<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
$r_slf = dirname(__FILE__) . "/servicelib.php";
if (is_readable($r_slf)) {
    include_once($r_slf);
}
else { 
	header("Content-Type: text/xml");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
	echo "<service_error>2000</service_error>\r\n";
	exit;
}
RWSCMBVer();
RWSCMVer();
RWSCMInst();
$r_ac = RWSGSOpt("action");
if ($r_ac === FALSE || strlen($r_ac) == 0)
	RWSSErr("2001"); 
else
	RWSDSAct($r_ac);
?>
