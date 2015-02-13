<?php
define('SECURITY_CONSTANT', 1);

require_once('../config.php');

require_login();

$error = false;

if (function_exists('has_capability')) {
    if (!has_capability('moodle/legacy:admin', get_context_instance(CONTEXT_SYSTEM))) {
        $error = true;
    }
} else if (!function_exists('isadmin') or !isadmin($USER->id)) {
    $error = true;
}

if (!$error) {
    require_once('./install/kernel/init.php');
    require_once('./install/components/header.php');
    require_once('./install/components/center.php');
    require_once('./install/components/footer.php');
} else {
    redirect($CFG->wwwroot);
}