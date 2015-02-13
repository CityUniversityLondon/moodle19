<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * cularc_service_mnet class definition.
 *
 * @since      1.9
 * @package    cularc
 * @subpackage service_mnet
 * @copyright  2013 Tim Gagen <Tim dot Gagen dot 1 at city dot ac dot uk>
 */
class cularc_service_mnet {
    /**
     * cularc_service_mnet::mnet_publishes()
     * Provides the allowed RPC services from this class as an array.
     * @return array  Allowed RPC services.
     */
    function mnet_publishes() {
        $service = array();
        $service['name']       = 'cularc'; // Max 6 characters - written to {mnet_rpc}.parent_type.
        $service['apiversion'] = 1;
        $service['methods']    = array('get_courselist', 'test_response');
        return array($service);
    }


    /**
     * cularc_service_mnet::get_courselist()
     *
     * @param string $username
     * @return mixed
     */
    function get_courselist($username) {
        global $MNET_REMOTE_CLIENT, $USER;

        include_once($CFG->dirroot . '/course/lib.php');

        $courselist = array();
        $courselist['status']     = '';
        $courselist['statusmsg']  = '';
        $courselist['coursedata'] = array();

        $userdata = array();
        if (!$userdata = get_complete_user_data('username', $username, $MNET_REMOTE_CLIENT->id)) {
            $courselist['status']    = 'NOUSER';
            $courselist['statusmsg'] = "Username '{$username}' was not found";
            return serialize($courselist);
        }

        // Get User access data.
        if ($userdata->id === $USER->id && isset($USER->access)) {
            $accessdata = $USER->access;
        } else {
            $accessdata = get_user_access_sitewide($userdata->id);
        }

        $cap     = 'moodle/course:view';
        $sort    = 'c.sortorder ASC';
        $fields  = array('fullname', 'visible');
        $courses = array();

        if (!$courses = get_user_courses_bycap($userdata->id, $cap, $accessdata, false, $sort, $fields)) {
            $courselist['status']    = 'NOCOURSE';
            $courselist['statusmsg'] = "No courses were returned for user id '{$userdata->id}'";
            return serialize($courselist);
        }

        $coursemap = array();
        foreach ($courses as $coursevals) {
            $courseid = $coursevals->id;
            $coursemap[$courseid] = array('id'           => $courseid,
                                          'shortname'    => $coursevals->shortname,
                                          'fullname'     => $coursevals->fullname,
                                          'categorypath' => $coursevals->categorypath,
                                          'wantsurl'     => '/course/view.php%3Fid=' . $coursevals->id,
                                          'visible'      => $coursevals->visible,
                                          'cansee'       => $coursevals->visible);

            if (empty($coursevals->visible)) {
                if (has_capability('moodle/course:viewhiddencourses', $coursevals->context, $userdata->id)) {
                    $coursemap[$courseid]['cansee'] = 1;
                }
            }
        }

        $categoryids = array();
        $coursetree  = array();

        /// Extract category ids from the combined category paths, and build a category/course tree.
        foreach ($coursemap as $courseid => $coursevals) {
            $pathelements = explode('/', substr($coursevals['categorypath'], 1));
            $categoryids  = array_merge($categoryids, $pathelements);

            $temp = &$coursetree;
            foreach($pathelements as $categoryid) {
                $temp = &$temp[$categoryid];
            }

            $temp['courses'][] = $coursevals;
            unset($temp);
        }
        $categoryids = array_unique($categoryids);

        /// Get category details.
        $categories = get_records_list('course_categories',
                                       'id',
                                       implode(',', $categoryids),
                                       'id',
                                       'id,name,description,visible');

        foreach ($categories as $catkey => $catval) {
            $catval->cansee = $catval->visible;

            if (empty($catval->visible)) {
                $catcontext = get_context_instance(CONTEXT_SYSTEM);
                if (has_capability('moodle/category:viewhiddencategories', $catcontext, $userdata->id)) {
                    $catval->cansee = 1;
                }
            }
        }

        $coursedata = array('coursemap'  => $coursemap,
                            'coursetree' => $coursetree,
                            'categories' => $categories);

        $courselist = array('status'     => 'OK',
                            'statusmsg'  => '',
                            'coursedata' => $coursedata);

        return serialize($courselist);
    }


    /**
     * cularc_service_mnet::test_response()
     * Returns the parameter/s provided to test web service data transfer.
     *
     * @param  mixed $input
     * @param  mixed $input2
     * @return mixed
     */
    function test_response($input, $input2 = null)
    {
        if ($input2) {
            $input = array($input, $input2);
        }

        return array('response' => $input, 'host' => array('wwwroot' => $CFG->wwwroot));
    }
 }
