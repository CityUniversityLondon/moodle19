<?

// moodle-apache-check.php
// Guy Waugh, 15/09/2010
// Used by F5s etc. to check that apache is up and able to talk to the Moodle database

	// Read config.php and get the database variables
	$fp = fopen("/moodle/application-current/config.php", "r");
	$end_of_db_vars = FALSE;
	while(!$end_of_db_vars) {
		$confdata = fgets($fp);
		if(!strpos($confdata, 'wwwroot')) {
			if(strpos($confdata, "CFG")) {
				eval("$confdata");
			}
		} else {
			$end_of_db_vars = TRUE;
		}
	}
	fclose($fp);

	require_once("/moodle/application-current/lib/setuplib.php");
	preconfigure_dbconnection();
	require_once("/moodle/application-current/lib/adodb/adodb.inc.php");

	$db = &ADONewConnection($CFG->dbtype);
	if(!isset($CFG->dbpersist) or !empty($CFG->dbpersist)) {    // Use persistent connection (default)
		$dbconnected = $db->PConnect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
	} else {    // Use single connection
		$dbconnected = $db->Connect($CFG->dbhost,$CFG->dbuser,$CFG->dbpass,$CFG->dbname);
	}

	if(!$dbconnected) {
		// Database is down, so send a "503 Service Unavailable"
		header("HTTP/1.0 503 Service Unavailable");
	} else {
		// Connected to database, so run a test query
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $db->Execute("select * from dual");

		if(!$result) {
			// Nothing was returned from the query
			header("HTTP/1.0 503 Service Unavailable");
		} else {
			// No problemo
			header("HTTP/1.0 200 OK");
		}
	}
?>
