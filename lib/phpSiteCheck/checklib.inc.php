<?php
/*********************************************************************************
 *       Filename: checklib.php
 *       Version 1.0
 *       Copyrights 2004-2005 (c) phpSiteTools.com
 *       Powered by ServiceUptime.com
 *       Last modified: 08/09/2010 by Amanda Doughty
 *********************************************************************************/


function pscValidateHost($host) {
    if(preg_match("/^((([0-9]{1,3}\.){3}[0-9]{1,3})|([0-9a-z-.]{0,61})?\.[a-z]{2,4})$/i", $host)) 
        return 1;
    else
        return 0;
}

function pscDoCheck($host, $service, $port='', $timeout=20) {
    $host = strtolower($host);

    switch($service) {
         case 'http': 
            $Request = "HEAD / HTTP/1.0\r\nUser-Agent: phpSiteCheck 1.0. Powered by ServiceUptime.com\r\nHost: $host\r\n\r\n";
            $OkResults = array("200\D+OK", "200\D+Document\s+Follows", "302", "301");
            if(!is_numeric($port)) $port = 80;
            $host = ($port == 443)? 'ssl://' . $host: $host;
            break;

         case 'ftp': 
            $OkResults = array("220");
            $Request = '';
            if(!is_numeric($port)) $port = 21;
            break;

         case 'smtp': 
            $OkResults = array("220");
            $Request = '';
            if(!is_numeric($port)) $port = 25;
            break;

         case 'pop3': 
            $OkResults = array("\\+OK");
            $Request = '';
            if(!is_numeric($port)) $port = 110;
            break;
    }

      list($MSec, $Sec) = explode(" ", microtime());
      $TimeBegin = (double) $MSec + (double) $Sec;

      $Socket = @fsockopen($host, $port, $error_number, $error, $timeout);

      list($MSec, $Sec) = explode(" ", microtime());
      $TimeEnd = (double) $MSec + (double) $Sec;
	  $Time = number_format($TimeEnd - $TimeBegin, 3);
      // Check port

	  if (is_resource($Socket))
      {
             if ($Request != "") { fputs($Socket, $Request); }
             if (!feof($Socket)) { $Response = fgets($Socket, 4096); }
            
             $Result = "Failed";
             $Error  = $Response;

             foreach($OkResults as $exp_result) {
                if (preg_match("/$exp_result/",$Response)) {
                   $Error = "";
                   $Result = "Ok";
                }
             }
         fclose($Socket);
      }
      else 
	  { 
          $Result = "Failed";
		  $Error = ((!$error) ? "Time out" : $error);
	  }

      if ($Result == "Ok") {
          return 1;
      }
      else {
          error_log("Host: $host, Port: $port, Time: $Time, Error: $Error", 0);
          return 0;
      }

//      return array(
//            'host'   => $host,
//            'service'=> $service,
//            'port'   => $port,
//            'result' => $Result,
//            'time'   => $Time,
//            'error'  => $Error
//          );

}

function pscDatabaseCheck($username, $password, $host) {

    // create connection
    // Returns a connection identifier or FALSE on error
    $connection = @oci_connect($username, $password, $host);

    // test connection
    if (!$connection) {
     $e = oci_error();
     print htmlentities($e['message']);
     error_log($e['message'], 0);
     return 0;
    }

    // else there weren't any errors
//    else
//    {
//     echo 'I am an Oracle daddy.';
//    }

    // create SQL statement
    $sql = "select sysdate from dual";

    // parse SQL statement
    // Returns a statement handle on success, or FALSE on error
    $sql_statement = oci_parse($connection, $sql);

    if (!$sql_statement) {
       $e = oci_error($connection);  // For oci_parse errors pass the connection handle
       trigger_error(htmlentities($e['message']), E_USER_ERROR);
       error_log($e['message'], 0);
       return 0;
    }

    // execute SQL query
    // Returns TRUE on success or FALSE on failure
    $sql_result = oci_execute($sql_statement);
    if (!$sql_result) {
        $e = oci_error($sql_statement);  // For oci_execute errors pass the statement handle
        print htmlentities($e['message']);
        print "\n<pre>\n";
        print htmlentities($e['sqltext']);
        printf("\n%".($e['offset']+1)."s", "^");
        print  "\n</pre>\n";
        error_log($e['message'], 0);
        return 0;
    } 


    // free resources and close connection
    oci_free_statement($sql_statement);
    oci_close($connection);

    return 1;

}



?>