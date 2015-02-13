<?php


/**
 * City Modified version of function get_users_by_capability, for Quickmail
 *
 * I preferred the option of altering the existing function by adding an extra
 * parameter to determine visibility of hidden roles. But I think that this is
 * the most future proof way of maintaining City changes. Though it pains me to
 * be so anti-DRY. AD
 *
 * Who has this capability in this context?
 *
 * This can be a very expensive call - use sparingly and keep
 * the results if you are going to need them again soon.
 *
 * Note if $fields is empty this function attempts to get u.*
 * which can get rather large - and has a serious perf impact
 * on some DBs.
 *
 * @param $context - object
 * @param $capability - string capability, or an array of capabilities, in which
 *               case users having any of those capabilities will be returned.
 *               For performance reasons, you are advised to put the capability
 *               that the user is most likely to have first.
 * @param $fields - fields to be pulled. The user table is aliased to 'u'. u.id MUST be included.
 * @param $sort - the sort order. Default is lastaccess time.
 * @param $limitfrom - number of records to skip (offset)
 * @param $limitnum - number of records to fetch
 * @param $groups - single group or array of groups - only return
 *               users who are in one of these group(s).
 * @param $exceptions - list of users to exclude
 * @param view - set to true when roles are pulled for display only
 *               this is so that we can filter roles with no visible
 *               assignment, for example, you might want to "hide" all
 *               course creators when browsing the course participants
 *               list.
 * @param boolean $useviewallgroups if $groups is set the return users who
 *               have capability both $capability and moodle/site:accessallgroups
 *               in this context, as well as users who have $capability and who are
 *               in $groups.
 */
function quickmail_get_users_by_capability($context, $capability, $fields='', $sort='',
        $limitfrom='', $limitnum='', $groups='', $exceptions='', $doanything=true,
        $view=false, $useviewallgroups=false) {
    global $CFG;

    $ctxids = substr($context->path, 1); // kill leading slash
    $ctxids = str_replace('/', ',', $ctxids);

    // Context is the frontpage
    $isfrontpage = false;
    $iscoursepage = false; // coursepage other than fp
    if ($context->contextlevel == CONTEXT_COURSE) {
        if ($context->instanceid == SITEID) {
            $isfrontpage = true;
        } else {
            $iscoursepage = true;
        }
    }

    // What roles/rolecaps are interesting?
    if (is_array($capability)) {
        $caps = "'" . implode("','", $capability) . "'";
        $capabilities = $capability;
    } else {
        $caps = "'" . $capability . "'";
        $capabilities = array($capability);
    }
    if ($doanything===true) {
        $caps .= ",'moodle/site:doanything'";
        $capabilities[] = 'moodle/site:doanything';
        $doanything_join='';
        $doanything_cond='';
    } else {
        // This is an outer join against
        // admin-ish roleids. Any row that succeeds
        // in JOINing here ends up removed from, $viewhidden=true
        // the resultset. This means we remove
        // rolecaps from roles that also have
        // 'doanything' capabilities.
        $doanything_join="LEFT OUTER JOIN (
                              SELECT DISTINCT rc.roleid
                              FROM {$CFG->prefix}role_capabilities rc
                              WHERE rc.capability='moodle/site:doanything'
                                    AND rc.permission=".CAP_ALLOW."
                                    AND rc.contextid IN ($ctxids)
                          ) dar
                             ON rc.roleid=dar.roleid";
        $doanything_cond="AND dar.roleid IS NULL";
    }

    // fetch all capability records - we'll walk several
    // times over them, and should be a small set

    $negperm = false; // has any negative (<0) permission?
    $roleids = array();

    $sql = "SELECT rc.id, rc.roleid, rc.permission, rc.capability,
                   ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel
            FROM {$CFG->prefix}role_capabilities rc
            JOIN {$CFG->prefix}context ctx on rc.contextid = ctx.id
            $doanything_join
            WHERE rc.capability IN ($caps) AND ctx.id IN ($ctxids)
                  $doanything_cond
            ORDER BY rc.roleid ASC, ctx.depth ASC";
    if ($capdefs = get_records_sql($sql)) {
        foreach ($capdefs AS $rcid=>$rc) {
            $roleids[] = (int)$rc->roleid;
            if ($rc->permission < 0) {
                $negperm = true;
            }
        }
    }

    $roleids = array_unique($roleids);

    if (count($roleids)===0) { // noone here!
        return false;
    }

    // is the default role interesting? does it have
    // a relevant rolecap? (we use this a lot later)
    if (in_array((int)$CFG->defaultuserroleid, $roleids, true)) {
        $defaultroleinteresting = true;
    } else {
        $defaultroleinteresting = false;
    }

    // is the default role interesting? does it have
    // a relevant rolecap? (we use this a lot later)
    if (($isfrontpage or is_inside_frontpage($context)) and !empty($CFG->defaultfrontpageroleid) and in_array((int)$CFG->defaultfrontpageroleid, $roleids, true)) {
        if (!empty($CFG->fullusersbycapabilityonfrontpage)) {
            // new in 1.9.6 - full support for defaultfrontpagerole MDL-19039
            $frontpageroleinteresting = true;
        } else {
            // old style 1.9.0-1.9.5 - much faster + fewer negative override problems on frontpage
            $frontpageroleinteresting = ($context->contextlevel == CONTEXT_COURSE);
        }
    } else {
        $frontpageroleinteresting = false;
    }

    //
    // Prepare query clauses
    //
    $wherecond = array();

    // Non-deleted users. We never return deleted users.
    $wherecond['nondeleted'] = 'u.deleted = 0';

    /// Groups
    if ($groups) {
        if (is_array($groups)) {
            $grouptest = 'gm.groupid IN (' . implode(',', $groups) . ')';
        } else {
            $grouptest = 'gm.groupid = ' . $groups;
        }
        $grouptest = 'ra.userid IN (SELECT userid FROM ' .
            $CFG->prefix . 'groups_members gm WHERE ' . $grouptest . ')';

        if ($useviewallgroups) {
            $viewallgroupsusers = get_users_by_capability($context,
                    'moodle/site:accessallgroups', 'u.id, u.id', '', '', '', '', $exceptions);
            $wherecond['groups'] =  '('. $grouptest . ' OR ra.userid IN (' .
                                    implode(',', array_keys($viewallgroupsusers)) . '))';
        } else {
            $wherecond['groups'] =  '(' . $grouptest .')';
        }
    }

    /// User exceptions
    if (!empty($exceptions)) {
        $wherecond['userexceptions'] = ' u.id NOT IN ('.$exceptions.')';
    }

    /// Set up hidden role-assignments sql
    if ($view) {
        $condhiddenra = 'AND ra.hidden = 0 ';
        $sscondhiddenra = 'AND ssra.hidden = 0 ';
    } else {
        $condhiddenra = '';
        $sscondhiddenra = '';
    }

    // Collect WHERE conditions
    $where = implode(' AND ', array_values($wherecond));
    if ($where != '') {
        $where = 'WHERE ' . $where;
    }

    /// Set up default fields
    if (empty($fields)) {
        if ($iscoursepage) {
            $fields = 'u.*, ul.timeaccess as lastaccess';
        } else {
            $fields = 'u.*';
        }
    } else {
        if (debugging('', DEBUG_DEVELOPER) && strpos($fields, 'u.*') === false &&
                strpos($fields, 'u.id') === false) {
            debugging('u.id must be included in the list of fields passed to get_users_by_capability.', DEBUG_DEVELOPER);
        }
    }

    /// Set up default sort
    if (empty($sort)) { // default to course lastaccess or just lastaccess
        if ($iscoursepage) {
            $sort = 'ul.timeaccess';
        } else {
            $sort = 'u.lastaccess';
        }
    }
    $sortby = $sort ? " ORDER BY $sort " : '';

    // User lastaccess JOIN
    if ((strpos($sort, 'ul.timeaccess') === FALSE) and (strpos($fields, 'ul.timeaccess') === FALSE)) {  // user_lastaccess is not required MDL-13810
        $uljoin = '';
    } else {
        $uljoin = "LEFT OUTER JOIN {$CFG->prefix}user_lastaccess ul
                         ON (ul.userid = u.id AND ul.courseid = {$context->instanceid})";
    }

    //
    // Simple cases - No negative permissions means we can take shortcuts
    //
    if (!$negperm) {

        // at the frontpage, and all site users have it - easy!
        if ($frontpageroleinteresting) {
            return get_records_sql("SELECT $fields
                                    FROM {$CFG->prefix}user u
                                    WHERE u.deleted = 0
                                    ORDER BY $sort",
                                   $limitfrom, $limitnum);
        }

        // all site users have it, anyway
        // TODO: NOT ALWAYS!  Check this case because this gets run for cases like this:
        // 1) Default role has the permission for a module thing like mod/choice:choose
        // 2) We are checking for an activity module context in a course
        // 3) Thus all users are returned even though course:view is also required
        if ($defaultroleinteresting) {
            $sql = "SELECT $fields
                    FROM {$CFG->prefix}user u
                    $uljoin
                    $where
                    ORDER BY $sort";
            return get_records_sql($sql, $limitfrom, $limitnum);
        }

        /// Simple SQL assuming no negative rolecaps.
        /// We use a subselect to grab the role assignments
        /// ensuring only one row per user -- even if they
        /// have many "relevant" role assignments.
        $select = " SELECT $fields";
        $from   = " FROM {$CFG->prefix}user u
                    JOIN (SELECT DISTINCT ssra.userid
                          FROM {$CFG->prefix}role_assignments ssra
                          WHERE ssra.contextid IN ($ctxids)
                                AND ssra.roleid IN (".implode(',',$roleids) .")
                                $sscondhiddenra
                          ) ra ON ra.userid = u.id
                    $uljoin "; 
        return get_records_sql($select.$from.$where.$sortby, $limitfrom, $limitnum);
    }

    //
    // If there are any negative rolecaps, we need to
    // work through a subselect that will bring several rows
    // per user (one per RA).
    // Since we cannot do the job in pure SQL (not without SQL stored
    // procedures anyway), we end up tied to processing the data in PHP
    // all the way down to pagination.
    //
    // In some cases, this will mean bringing across a ton of data --
    // when paginating, we have to walk the permisisons of all the rows
    // in the _previous_ pages to get the pagination correct in the case
    // of users that end up not having the permission - this removed.
    //

    // Prepare the role permissions datastructure for fast lookups
    $roleperms = array(); // each role cap and depth
    foreach ($capdefs AS $rcid=>$rc) {

        $rid       = (int)$rc->roleid;
        $perm      = (int)$rc->permission;
        $rcdepth   = (int)$rc->ctxdepth;
        if (!isset($roleperms[$rc->capability][$rid])) {
            $roleperms[$rc->capability][$rid] = (object)array('perm'  => $perm,
                                                              'rcdepth' => $rcdepth);
        } else {
            if ($roleperms[$rc->capability][$rid]->perm == CAP_PROHIBIT) {
                continue;
            }
            // override - as we are going
            // from general to local perms
            // (as per the ORDER BY...depth ASC above)
            // and local perms win...
            $roleperms[$rc->capability][$rid] = (object)array('perm'  => $perm,
                                                              'rcdepth' => $rcdepth);
        }

    }

    if ($context->contextlevel == CONTEXT_SYSTEM
        || $frontpageroleinteresting
        || $defaultroleinteresting) {

        // Handle system / sitecourse / defaultrole-with-perhaps-neg-overrides
        // with a SELECT FROM user LEFT OUTER JOIN against ra -
        // This is expensive on the SQL and PHP sides -
        // moves a ton of data across the wire.
        $ss = "SELECT u.id as userid, ra.roleid,
                      ctx.depth
               FROM {$CFG->prefix}user u
               LEFT OUTER JOIN {$CFG->prefix}role_assignments ra
                 ON (ra.userid = u.id
                     AND ra.contextid IN ($ctxids)
                     AND ra.roleid IN (".implode(',',$roleids) .")
                     $condhiddenra)
               LEFT OUTER JOIN {$CFG->prefix}context ctx
                 ON ra.contextid=ctx.id
               WHERE u.deleted=0";
    } else {
        // "Normal complex case" - the rolecaps we are after will
        // be defined in a role assignment somewhere.
        $ss = "SELECT ra.userid as userid, ra.roleid,
                      ctx.depth
               FROM {$CFG->prefix}role_assignments ra
               JOIN {$CFG->prefix}context ctx
                 ON ra.contextid=ctx.id
               WHERE ra.contextid IN ($ctxids)
                     $condhiddenra
                     AND ra.roleid IN (".implode(',',$roleids) .")";
    }

    $select = "SELECT $fields ,ra.roleid, ra.depth ";
    $from   = "FROM ($ss) ra
               JOIN {$CFG->prefix}user u
                 ON ra.userid=u.id
               $uljoin ";

    // Each user's entries MUST come clustered together
    // and RAs ordered in depth DESC - the role/cap resolution
    // code depends on this.
    $sort .= ' , ra.userid ASC, ra.depth DESC';
    $sortby .= ' , ra.userid ASC, ra.depth DESC ';

    $rs = get_recordset_sql($select.$from.$where.$sortby);

    //
    // Process the user accounts+RAs, folding repeats together...
    //
    // The processing for this recordset is tricky - to fold
    // the role/perms of users with multiple role-assignments
    // correctly while still processing one-row-at-a-time
    // we need to add a few additional 'private' fields to
    // the results array - so we can treat the rows as a
    // state machine to track the cap/perms and at what RA-depth
    // and RC-depth they were defined.
    //
    // So what we do here is:
    // - loop over rows, checking pagination limits
    // - when we find a new user, if we are in the page add it to the
    //   $results, and start building $ras array with its role-assignments
    // - when we are dealing with the next user, or are at the end of the userlist
    //   (last rec or last in page), trigger the check-permission idiom
    // - the check permission idiom will
    //   - add the default enrolment if needed
    //   - call has_any_capability_from_rarc(), which based on RAs and RCs will return a bool
    //     (should be fairly tight code ;-) )
    // - if the user has permission, all is good, just $c++ (counter)
    // - ...else, decrease the counter - so pagination is kept straight,
    //      and (if we are in the page) remove from the results
    //
    $results = array();

    // pagination controls
    $c = 0;
    $limitfrom = (int)$limitfrom;
    $limitnum = (int)$limitnum;

    //
    // Track our last user id so we know when we are dealing
    // with a new user...
    //
    $lastuserid  = 0;
    //
    // In this loop, we
    // $ras: role assignments, multidimensional array
    // treat as a stack - going from local to general
    // $ras = (( roleid=> x, $depth=>y) , ( roleid=> x, $depth=>y))
    //
    while ($user = rs_fetch_next_record($rs)) {

        //error_log(" Record: " . print_r($user,1));

        //
        // Pagination controls
        // Note that we might end up removing a user
        // that ends up _not_ having the rights,
        // therefore rolling back $c
        //
        if ($lastuserid != $user->id) {

            // Did the last user end up with a positive permission?
            if ($lastuserid !=0) {
                if ($frontpageroleinteresting) {
                    // add frontpage role if interesting
                    $ras[] = array('roleid' => $CFG->defaultfrontpageroleid,
                                   'depth'  => $context->depth);
                }
                if ($defaultroleinteresting) {
                    // add the role at the end of $ras
                    $ras[] = array( 'roleid' => $CFG->defaultuserroleid,
                                    'depth'  => 1 );
                }
                if (has_any_capability_from_rarc($ras, $roleperms, $capabilities)) {
                    $c++;
                } else {
                    // remove the user from the result set,
                    // only if we are 'in the page'
                    if ($limitfrom === 0 || $c >= $limitfrom) {
                        unset($results[$lastuserid]);
                    }
                }
            }

            // Did we hit pagination limit?
            if ($limitnum !==0 && $c >= ($limitfrom+$limitnum)) { // we are done!
                break;
            }

            // New user setup, and $ras reset
            $lastuserid = $user->id;
            $ras = array();
            if (!empty($user->roleid)) {
                $ras[] = array( 'roleid' => (int)$user->roleid,
                                'depth'  => (int)$user->depth );
            }

            // if we are 'in the page', also add the rec
            // to the results...
            if ($limitfrom === 0 || $c >= $limitfrom) {
                $results[$user->id] = $user; // trivial
            }
        } else {
            // Additional RA for $lastuserid
            $ras[] = array( 'roleid'=>(int)$user->roleid,
                            'depth'=>(int)$user->depth );
        }

    } // end while(fetch)

    // Prune last entry if necessary
    if ($lastuserid !=0) {
        if ($frontpageroleinteresting) {
            // add frontpage role if interesting
            $ras[] = array('roleid' => $CFG->defaultfrontpageroleid,
                           'depth'  => $context->depth);
        }
        if ($defaultroleinteresting) {
            // add the role at the end of $ras
            $ras[] = array( 'roleid' => $CFG->defaultuserroleid,
                            'depth'  => 1 );
        }
        if (!has_any_capability_from_rarc($ras, $roleperms, $capabilities)) {
            // remove the user from the result set,
            // only if we are 'in the page'
            if ($limitfrom === 0 || $c >= $limitfrom) {
                if (isset($results[$lastuserid])) {
                    unset($results[$lastuserid]);
                }
            }
        }
    }

    return $results;
}
?>
