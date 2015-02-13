<?php

$block_quickfindlist_capabilities = array(

    'block/quickfindlist:use' => array(

        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'admin' => CAP_ALLOW,
            'elearnadmin' => CAP_ALLOW,
            'progadmin' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'assistant' => CAP_PREVENT,
            'auditor' => CAP_ALLOW,
            'observer' => CAP_ALLOW,
            'student' => CAP_PREVENT,
        )
    )

);

?>