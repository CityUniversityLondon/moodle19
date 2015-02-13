<?php

// Announcement for MyMoodle page with time constraints
// shows message only between the dates/times set
// Mike Hughes 3rd August 2010

// message #1 RED
$startDate = "2010-07-29 00:00";     
$endDate   = "2010-07-31 23:59"; 
$msgTitle = "Important Announcement - Moodle downtime on Saturday (31st July)";
$msgBody = "Due to system upgrades Moodle may be unavailable at two separate periods between 9am and 1pm on Saturday 31st July 2010. The first period of downtime may initially last for up to an hour and occur between 9am and 11am and the period following that will be no more than 30 minutes between 11am and 1pm and will not affect Cass PC's. We apologise for any inconvenience caused.";
showAnnouncement($startDate,$endDate,$msgTitle,$msgBody);

// message #2 BLACK
$startDate = "2010-08-06 00:00";
$endDate   = "2010-08-14 12:00";
$msgTitle = "Important Announcement";
$msgBody = "Moodle (Live) will be down for essential maintenance on Saturday August 14 2010 between the hours of 9:00am and 6:00pm.";
showAnnouncement($startDate,$endDate,$msgTitle,$msgBody, 'generalbox');

// for new messages copy, paste and edit message #1 below here 

// PRE read-only message
$startDate = "2015-02-01 00:00";
$endDate   = "2015-03-03 08:00";
$msgTitle = "Important Announcement - Changes to Moodle archive";
$msgBody = "This Moodle site will be put into 'read-only' mode on 3rd March 2015. This means you will no longer be able edit content or participate in activities. You WILL still be able to access all the content that you currently access as well as view and download resources and view reports, grades and activities.<br><br>For more information please visit <a href=\"/mod/resource/view.php?id=1289309\">this page</a>.";
showAnnouncement($startDate,$endDate,$msgTitle,$msgBody, 'generalbox');

// POST read-only message
$startDate = "2015-03-03 08:00";
$endDate   = "2020-12-12 23:59";
$msgTitle = "Important Announcement - Changes to Moodle archive";
$msgBody = "This Moodle site has been put into 'read-only' mode on 3rd March 2015. This means you can no longer edit content or participate in activities. You CAN still access all the content that you could access before as well as view and download resources and view reports, grades and activities.<br><br>For more information please visit <a href=\"/mod/resource/view.php?id=1289309\">this page</a>.";
showAnnouncement($startDate,$endDate,$msgTitle,$msgBody, 'generalbox');

// ###########################################################
// do not edit below here
function showAnnouncement ($start, $end, $title, $body, $class='errorbox') {
  $msg = '';
  $now = date("Y-m-d H:i");
  if ($start == '') $start = date("Y-m-d 00:00");
  if ($end == '')   $end   = date("Y-m-d 23:59");
  // check time now and see if message is current
  if ( ($now > $start) && ($now < $end) )  {
    // show message
    $msg = "<h3>{$title}</h3>\n<p>{$body}</p>\n";
    print_box($msg,$class,'',false);
  } else {
    // do nothing
  }
}
// ###########################################################

?>
