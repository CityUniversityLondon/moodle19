<?php

    /**
     * Returns the proper SQL to do LIKE in a case-insensitive way for oracle
     * and Postgres
     *
     * Note the LIKE are case sensitive for Oracle. Oracle 10g is required to use
     * the caseinsensitive search using regexp_like() or NLS_COMP=LINGUISTIC :-(
     * See http://docs.moodle.org/en/XMLDB_Problems#Case-insensitive_searches
     *
     * @uses $CFG
     * @param string $field Number of records per page
     * @param string $data Number of records per page
     * @param integer $operator Number of records per page
     * @return string
     */
    function sql_olike($field, $data, $operator=0) {
        global $CFG;
        $not = '';

        switch ($operator) {
            case 0: // contains
                $value = "'%$data%'";
                $oci8_value = "'$data'";
                break;
            case 1: // does not contain
                $not = 'NOT';
                $value = "'%$data%'";
                $oci8_value = "'$data'";
                break;
            case 2: // equal to
                $value = "'$data'";
                $oci8_value = "'^$data$'";
                break;
            case 3: // starts with
                $value = "'$data%'";
                $oci8_value = "'^$data'";
                break;
            case 4: // ends with
                $value = "'%$data'";
                $oci8_value = "'$data$'";
                break;
            case 5: // empty
                $value = "''";
                $oci8_value = "''";
                break;
            case 7: // is defined
                break;
        }

        switch ($CFG->dbfamily) {
            case 'postgres':
                 return " $field $not ILIKE $value ";
            case 'oracle':
                return "$not regexp_like($field, $oci8_value, 'i') ";
            default:
                 return " $field $not LIKE $value ";
        }
    }




	 /**
	 * CQU This function is specifically to cater for oracle's limitation
	 * on IN lists. It will accept the array of values and split using
	 * OR and starting at the WHERE.
	 *
	 * @param string $joinVal the value of the query join
	 * @param string $groupSize size of the groupings
	 * @param string $array The values as array or as string seperated ,
	 *
	 * @return the sql query with the split in clause
	 */
	function split_query_in_list($joinVal, $groupSize, $array, $in=true) {

        if ($in) {
            $operator = 'OR';
            $not = '';
        }
        else {
            $operator = 'AND';
            $not = 'NOT';
        }
        //convert to array if not already one
        if (is_string($array)) {
                $array = explode(",", $array);
        }

        if ( count($array) == 0 || !isset($array) ) {
            return " $joinVal $not IN ('')";
        }
        elseif ( count($array) > 1000 ) {
			$sql = '';
			$list = '';

			for ($i = 0; $i < count($array); $i++) {

				$list .= $array[$i] . ',';

				if ( (( $i % $groupSize ) == 0 || $i == count($array) -1) && $i != 0 ) {

					if ( $i == $groupSize ) {
						$sql .= " ( $joinVal $not IN (" . substr($list, 0, -1) . ")";
					} else {
						$sql .= " $operator $joinVal $not IN (" . substr($list, 0, -1) . ")";
					}
					$list = '';
				}
			}
			$sql .= " ) ";
		} else {
			$sql = " $joinVal $not IN (" . implode(",", $array) . ")";
		}
		return $sql;

	} //end split_query_in_list()

