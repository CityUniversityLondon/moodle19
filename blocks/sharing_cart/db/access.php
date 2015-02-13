<?php

$block_sharing_cart_capabilities = array(

    'block/sharing_cart:use' => array(

        'captype' => 'view',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW,
            'elearnadmin' => CAP_ALLOW,
            'progadmin' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'assistant' => CAP_PREVENT,
            'auditor' => CAP_PREVENT,
            'observer' => CAP_PREVENT,
            'student' => CAP_PREVENT,
        )
    )

);
