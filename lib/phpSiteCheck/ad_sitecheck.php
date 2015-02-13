<?php

require_once("./checklib.inc.php");

set_time_limit(90);

$host = 'moodle-dev.city.ac.uk';
//$host = 'moodle197.localhost';

$hostnameok = pscValidateHost($host);
$httpok = pscDoCheck($host, 'http');
$httpsok = pscDoCheck($host, 'http', 443);

$databaseok = pscDatabaseCheck('mdb', 'lsjef!n9oWI', 'moodle-p.mis1.city.ac.uk:1521/moodlep');

//echo "Host name ok: $hostnameok, http ok: $httpok, https ok: $httpsok, Database ok: $databaseok";

?>