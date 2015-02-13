<?php 

/**
 * @Author: Kris Popat
 */

	// Designed to be redirected from moodle/login/index.php
	$headers = getallheaders ( );
	require('../../config.php');
	
    if (isloggedin() && $USER->username != 'guest') {      // Nothing to do
        if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
            $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
            unset($SESSION->wantsurl);

        } else {
            $urltogo = $CFG->wwwroot.'/';      /// Go to the standard home page
            unset($SESSION->wantsurl);         /// Just in case
        }
		
        redirect($urltogo);
    }
	
	$iv_user = $headers['iv-user'];
	$auth_pass = $headers['authorization'];
	
	if ( empty($iv_user) or empty($auth_pass) ) {
		error_log ( "iv_user: $iv_user    Authorization: $auth_pass");
		error_log ( "headers: ".print_r($headers, true ));
		print_error('auth_tamldap_externalaccess', 'auth_tamldap' );
	}
		
	$auth_params = explode(":" , base64_decode(substr($auth_pass, 6)));
	//error_log ( print_r ($auth_params,true) );
	$pwd = $auth_params[1];
	//error_log ( 'password: '.$pwd.' ... '.$_SERVER['PHP_AUTH_PW'] );
    $pluginconfig   = get_config('auth/tamldap');
    $tamldapauth = get_auth_plugin('tamldap');
	
	
    
    // Check whether tamldap is configured properly
    //if (empty($pluginconfig->user_attribute)) {
    //    print_error('shib_not_set_up_error', 'auth');
     //}
    if ($tamldapauth->user_login($iv_user, $pwd)) {
		
		$u = $tamldapauth->authenticate_user($iv_user );
        if ( $u === false ) {
			add_to_log(0, 'login', 'error', 'index.php', $username);
			if (debugging('', DEBUG_ALL)) {
				error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Failed Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
			}
			print_error ( "auth_tamldap_user_authentication_failed", 'auth_tamldap' );
			exit;
        }
		$USER = $u;
		$USER->loggedin = true;
		$USER->site     = $CFG->wwwroot; // for added security, store the site in the 
		
		update_user_login_times();
		
		// Don't show username on login page
		set_moodle_cookie('nobody');
		
		set_login_session_preferences();
		
		unset($SESSION->lang);
		$SESSION->justloggedin = true;
		
		add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID, $USER->id, 0, $USER->id);
		
		if (isset($SESSION->wantsurl) and (strpos($SESSION->wantsurl, $CFG->wwwroot) === 0)) {
		    $urltogo = $SESSION->wantsurl;    /// Because it's an address in this site
		    unset($SESSION->wantsurl);
		
		} else {
		    $urltogo = $CFG->wwwroot.'/';      /// Go to the standard home page
		    unset($SESSION->wantsurl);         /// Just in case
		}

	    /// Go to my-moodle page instead of homepage if mymoodleredirect enabled
	    if (!has_capability('moodle/site:config',get_context_instance(CONTEXT_SYSTEM)) and !empty($CFG->mymoodleredirect) and !isguest()) {
	        if ($urltogo == $CFG->wwwroot or $urltogo == $CFG->wwwroot.'/' or $urltogo == $CFG->wwwroot.'/index.php') {
	            $urltogo = $CFG->wwwroot.'/my/';
	        }
	    }
	
	    check_enrolment_plugins($USER);
	    load_all_capabilities();     /// This is what lets the user do anything on the site  :-)
	
	    redirect($urltogo);
	    
	    exit;
	} 
	
	else {
		error_log ( "Authentication of authorization account failed: ".$pluginconfig->authorization_name );
		print_error ( 'auth_tamldap_config_account_failed', 'auth_tamldap', '', $pluginconfig->authorization_name );
	}


	

	/**
	 * 
	 * @return string or null the string of the located header or null if not found
	 * @param array $header_array - this must be the results of a call to header_list()
	 * @param string $match
	 */
	function find_header ($header_array, $match )
	{
		foreach ( $header_array as $h ) {
			$colon_position = strpos ( $h, ':' );
			if ( $colon_position !== false ) {
				$first_part = substr ( $h, 0, $colon_position );
				if ( strcasecmp ( $first_part, $match ) == 0 ) {
					return substr ( $h, $colon_position + 2 );
				}
			}
		}
		return null;
	}


?>