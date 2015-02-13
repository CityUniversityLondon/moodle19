<?php
///////////////////////////////////////////////////////////////////////////////
// Respondus 4.0 Web Service Extension For Moodle
// Copyright (c) 2009-2010 Respondus, Inc.  All Rights Reserved.
// Date: September 13, 2010
$id = optional_param("id", 0, PARAM_INT);
redirect("view.php?id=" . $id);
?>
