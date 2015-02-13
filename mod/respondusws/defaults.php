<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
if (empty($CFG->respondusws_initialdisable)) {
    if (count_records("respondusws") == 0) {
		set_field("modules", "visible", 0, "name", "respondusws");
        set_config("respondusws_initialdisable", 1);
    }
}
?>
