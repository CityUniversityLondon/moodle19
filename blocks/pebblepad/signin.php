<?php
require_once(dirname(__FILE__) . '../../../config.php');

global $CFG, $USER;
include_once($CFG->dirroot     . '/blocks/pebblepad/portfolio_manager.php');
if (!empty($USER->realuser)) {
    $text = '<p style="margin: auto; text-align:center;">Disabled<br />whilst you are logged in<br />"As User View"</p>';
    echo $text;
    exit ();
}
$acc = get_default_portfolio();
$sharedsecret = $acc->sharedsecret;
$username = $USER->username;
$timestamp = time();
$md5_hash = md5($timestamp . $username . $sharedsecret);
$forward_url = $acc->url."/pebblepad.aspx?username=$username&timestamp=$timestamp&MAC=$md5_hash";;
header("Location: ".$forward_url);
?>
