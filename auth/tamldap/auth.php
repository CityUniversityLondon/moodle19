<?php 

/* Author: Kris Popat
 * Plugin written specifically for City Univeristy's Moodle TAM integration
 * Amended by Mike Hughes for use with custom user fields
 */
 
 

// use ldap plugin's requirement steps
require_once($CFG->dirroot."/auth/ldap/auth.php" );

require_once($CFG->libdir.'/authlib.php');

/**
 * sso_ldap authentication plugin.
 */
class auth_plugin_tamldap extends auth_plugin_ldap {

    var $custom_fields = array(); // Moodle custom fields to sync with

    /**
     * Constructor.
     */       
    function auth_plugin_tamldap() {
        $this->authtype = 'tamldap';
        $this->config = get_config('auth/tamldap');
        if (empty($this->config->ldapencoding)) {
            $this->config->ldapencoding = 'utf-8';
        }
        if (empty($this->config->user_type)) {
            $this->config->user_type = 'default';
        }

        $default = $this->ldap_getdefaults();

        //use defaults if values not given
        foreach ($default as $key => $value) {
            // watch out - 0, false are correct values too
            if (!isset($this->config->{$key}) or $this->config->{$key} == '') {
                $this->config->{$key} = $value[$this->config->user_type];
            }
        }

        // Hack prefix to objectclass
        if (empty($this->config->objectclass)) {
            // Can't send empty filter
            $this->config->objectclass='(objectClass=*)';
        } else if (stripos($this->config->objectclass, 'objectClass=') === 0) {
            // Value is 'objectClass=some-string-here', so just add ()
            // around the value (filter _must_ have them).
            $this->config->objectclass = '('.$this->config->objectclass.')';
        } else if (stripos($this->config->objectclass, '(') !== 0) {
            // Value is 'some-string-not-starting-with-left-parentheses',
            // which is assumed to be the objectClass matching value.
            // So build a valid filter with it.
            $this->config->objectclass = '(objectClass='.$this->config->objectclass.')';
        } else {
            // There is an additional possible value
            // '(some-string-here)', that can be used to specify any
            // valid filter string, to select subsets of users based
            // on any criteria. For example, we could select the users
            // whose objectClass is 'user' and have the
            // 'enabledMoodleUser' attribute, with something like:
            //
            //   (&(objectClass=user)(enabledMoodleUser=1))
            //
            // This is only used in the functions that deal with the
            // whole potential set of users (currently sync_users()
            // and get_user_list() only).
            //
            // In this particular case we don't need to do anything,
            // so leave $this->config->objectclass as is.
        }
        
        if ($custom_fields = get_records('user_info_field', '', '', '', 'shortname')) {

            foreach($custom_fields as $cf) {
                $this->custom_fields[] = $cf->shortname;
            }
        }


    }
	

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG;
		if ( !empty ( $password )) {
	        $authorization_name = $this->config->authorization_name;

			if ( empty($authorization_name) ) {
				print_error ( 'auth_sso_ldap_noauthname', 'auth_tamldap' );
			}
			$textlib = textlib_get_instance();
			$extusername = $textlib->convert(stripslashes($authorization_name), 'utf-8', $this->config->ldapencoding);
			$extpassword = $textlib->convert(stripslashes($password), 'utf-8', $this->config->ldapencoding);
			
			$ldapconnection = $this->ldap_connect();
			if ($ldapconnection) {
				if ( $this->bind_authorization_name ( $ldapconnection, $extusername, $extpassword ) === false ) {
					error_log ( "Binding authorization name $extusername failed" );
					$this->ldap_close();
					return false;
				}
				else {
					$this->ldap_close();
					return true;
				}
			}
 			else {
				$this->ldap_close();
				print_error('auth_ldap_noconnect','auth','',$this->config->host_url);
 			}
			return false;
		}
		else {
			// just lookup $username on ldap

			if ( $this->get_userinfo ( $username ) !== false ) {
				return true;
			}
		}
		return false;
    }
	
	
	/**
	 * 
	 * @return bool
	 * @param object $connection
	 * @param string $auth_name
	 * @param string $password
	 */
	function bind_authorization_name ( $connection, $auth_name, $password )
	{
		/*if (!empty($this->config->version)) {
		    ldap_set_option($connresult, LDAP_OPT_PROTOCOL_VERSION, $this->config->version);
		}
		
		// Fix MDL-10921
		if ($this->config->user_type == 'ad') {
		     ldap_set_option($connresult, LDAP_OPT_REFERRALS, 0);
		}*/
		return ldap_bind($connection, $auth_name,$password);
	}
	

    /**
     * Hook for overriding behavior of logout page.
     * This method is called from login/logout.php page for all enabled auth plugins.
     */
    function logoutpage_hook() {
        global $USER;     // use $USER->auth to find the plugin used for login
        global $redirect; // can be used to override redirect after logout
		
        if ( isset ( $this->config->logout_redirect ) && !empty ( $this->config->logout_redirect ) ) {
        	$redirect  = $this->config->logout_redirect;
        }
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    //function config_form($config, $err, $user_fields) {
    function config_form($config, $err, $user_fields, $custom_fields=array()) {
        include "config.html";
    }
	
	

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        // set to defaults if undefined
		if (!isset($config->authorization_name)) {
			$config->authorization_name = 'moodlesso';
		}
		if ( !isset($config->logout_redirect)) {
			$config->logout_redirect = '/pkmslogout';
		}
		set_config('authorization_name', $config->authorization_name, 'auth/tamldap');
		set_config('logout_redirect', $config->logout_redirect, 'auth/tamldap');

        if (!isset($config->host_url))
            { $config->host_url = ''; }
        if (empty($config->ldapencoding))
            { $config->ldapencoding = 'utf-8'; }
        if (!isset($config->contexts))
            { $config->contexts = ''; }
        if (!isset($config->user_type))
            { $config->user_type = 'default'; }
        if (!isset($config->user_attribute))
            { $config->user_attribute = ''; }
        if (!isset($config->search_sub))
            { $config->search_sub = ''; }
        if (!isset($config->opt_deref))
            { $config->opt_deref = ''; }
        if (!isset($config->preventpassindb))
            { $config->preventpassindb = 0; }
        if (!isset($config->bind_dn))
            {$config->bind_dn = ''; }
        if (!isset($config->bind_pw))
            {$config->bind_pw = ''; }
        if (!isset($config->version))
            {$config->version = '2'; }
        if (!isset($config->objectclass))
            {$config->objectclass = ''; }
        if (!isset($config->memberattribute))
            {$config->memberattribute = ''; }
        if (!isset($config->memberattribute_isdn))
            {$config->memberattribute_isdn = ''; }
        if (!isset($config->creators))
            {$config->creators = ''; }
        if (!isset($config->create_context))
            {$config->create_context = ''; }
        if (!isset($config->expiration))
            {$config->expiration = ''; }
        if (!isset($config->expiration_warning))
            {$config->expiration_warning = '10'; }
        if (!isset($config->expireattr))
            {$config->expireattr = ''; }
        if (!isset($config->gracelogins))
            {$config->gracelogins = ''; }
        if (!isset($config->graceattr))
            {$config->graceattr = ''; }
        if (!isset($config->auth_user_create))
            {$config->auth_user_create = ''; }
        if (!isset($config->forcechangepassword))
            {$config->forcechangepassword = 0; }
        if (!isset($config->stdchangepassword))
            {$config->stdchangepassword = 0; }
        if (!isset($config->passtype))
            {$config->passtype = 'plaintext'; }
        if (!isset($config->changepasswordurl))
            {$config->changepasswordurl = ''; }
        if (!isset($config->removeuser))
            {$config->removeuser = 0; }
        if (!isset($config->ntlmsso_enabled))
            {$config->ntlmsso_enabled = 0; }
        if (!isset($config->ntlmsso_subnet))
            {$config->ntlmsso_subnet = ''; }
        if (!isset($config->ntlmsso_ie_fastpath))
            {$config->ntlmsso_ie_fastpath = 0; }

        // save settings
        set_config('host_url', $config->host_url, 'auth/tamldap');
        set_config('ldapencoding', $config->ldapencoding, 'auth/tamldap');
        set_config('host_url', $config->host_url, 'auth/tamldap');
        set_config('contexts', $config->contexts, 'auth/tamldap');
        set_config('user_type', $config->user_type, 'auth/tamldap');
        set_config('user_attribute', $config->user_attribute, 'auth/tamldap');
        set_config('search_sub', $config->search_sub, 'auth/tamldap');
        set_config('opt_deref', $config->opt_deref, 'auth/tamldap');
        set_config('preventpassindb', $config->preventpassindb, 'auth/tamldap');
        set_config('bind_dn', $config->bind_dn, 'auth/tamldap');
        set_config('bind_pw', $config->bind_pw, 'auth/tamldap');
        set_config('version', $config->version, 'auth/tamldap');
        set_config('objectclass', trim($config->objectclass), 'auth/tamldap');
        set_config('memberattribute', $config->memberattribute, 'auth/tamldap');
        set_config('memberattribute_isdn', $config->memberattribute_isdn, 'auth/tamldap');
        set_config('creators', $config->creators, 'auth/tamldap');
        set_config('create_context', $config->create_context, 'auth/tamldap');
        set_config('expiration', $config->expiration, 'auth/tamldap');
        set_config('expiration_warning', $config->expiration_warning, 'auth/tamldap');
        set_config('expireattr', $config->expireattr, 'auth/tamldap');
        set_config('gracelogins', $config->gracelogins, 'auth/tamldap');
        set_config('graceattr', $config->graceattr, 'auth/tamldap');
        set_config('auth_user_create', $config->auth_user_create, 'auth/tamldap');
        set_config('forcechangepassword', $config->forcechangepassword, 'auth/tamldap');
        set_config('stdchangepassword', $config->stdchangepassword, 'auth/tamldap');
        set_config('passtype', $config->passtype, 'auth/tamldap');
        set_config('changepasswordurl', $config->changepasswordurl, 'auth/tamldap');
        set_config('removeuser', $config->removeuser, 'auth/tamldap');
        set_config('ntlmsso_enabled', (int)$config->ntlmsso_enabled, 'auth/tamldap');
        set_config('ntlmsso_subnet', $config->ntlmsso_subnet, 'auth/tamldap');
        set_config('ntlmsso_ie_fastpath', (int)$config->ntlmsso_ie_fastpath, 'auth/tamldap');

        return true;
    }

	
	/**
	 * 
	 * @return object|false
	 * @param object $username
	 */
	function authenticate_user ( $username ) 
	{
		$auth = 'tamldap';
	    if ($user = get_complete_user_data('username', $username)) {
	        $auth = empty($user->auth) ? 'tamldap' : $user->auth;  // use tamldap if auth not set
	        if ($auth=='nologin' or !is_enabled_auth($auth)) {
	            add_to_log(0, 'login', 'error', 'index.php', $username);
	            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Disabled Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
	            return false;
	        }
	        if (!empty($user->deleted)) {
	            add_to_log(0, 'login', 'error', 'index.php', $username);
	            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Deleted Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
	            return false;
	        }
	
	    } 
		else {
	        $user = new object();
	        $user->id = 0;     // User does not exist
	        $user->auth = $auth;
	    }
    

        if (!$this->user_login($username, '')) {
            return false;
        }

        // successful authentication
        if ($user->id) {                          // User already exists in database
            if (empty($user->auth)) {             // For some reason auth isn't set yet
                set_field('user', 'auth', $auth, 'username', $username);
                $user->auth = $auth;
            }
            if (empty($user->firstaccess)) { //prevent firstaccess from remaining 0 for manual account that never required confirmation
                set_field('user','firstaccess', $user->timemodified, 'id', $user->id);
                $user->firstaccess = $user->timemodified;
            }

            //update_internal_user_password($user, $password); // just in case salt or encoding were changed (magic quotes too one day)

            if (!$this->is_internal()) {            // update user record from external DB
                $user = update_user_record($username, get_auth_plugin($user->auth));
            }
        } 
		else {
            // if user not found, create him
            $user = create_user_record($username, '', $auth);
        }

        $this->sync_roles($user);

    	$authsenabled = get_enabled_auth_plugins();

        foreach ($authsenabled as $hau) {
            $hauth = get_auth_plugin($hau);
            $hauth->user_authenticated_hook($user, $username, '');
        }

        if ($user->id===0) {
            return false;
        }
        return $user;
	}
}
 
 ?>