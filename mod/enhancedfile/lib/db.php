<?php
class enhanced_file_db{
    /**
     * delete anything older than 1 day or canceled.
     */
    public static function remove_old_file_keys(){
        $cutoff=time()-86400; // anything older than 1 day
        $select='uploadcanceled=1 OR uploadstart<'.$cutoff;
        delete_records_select('enhancedfile_uploads_auth', $select);
    }

    /**
     * create upload key
     * @param string $filename
     * @param string $sesskey
     * @param integer $courseid
     * @param integer $userid
     * @param string $securitykey
     * @return stdObject database insert result
     */
    public static function create_upload_key($filename, $sesskey, $courseid, $userid, $securitykey){
        // check existing record
        $row=self::get_upload_key($sesskey, $courseid, $userid, $securitykey);
        if ($row){
            // delete existing record
            delete_records('enhancedfile_uploads_auth','id', $row->id);
        }
        // create new record
        $do=(object) array(
            'sesskey'=>$sesskey,
            'userid'=>$userid,
            'courseid'=>$courseid,
            'filename'=>htmlentities($filename),
            'securitykey'=>$securitykey
        );
        $insok=insert_record('enhancedfile_uploads_auth', $do);
        return ($insok);
    }

    /**
     * return upload key
     * @param string $sesskey
     * @param string $courseid
     * @param string $userid
     * @param string $securitykey
     * @return stdObject
     */
    public static function get_upload_key($sesskey, $courseid, $userid, $securitykey){
        return (get_record('enhancedfile_uploads_auth', 'userid', $userid, 'sesskey', $sesskey, 'securitykey', $securitykey));
    }

    /**
     * return live upload keys - i.e. ones that have not been compelted or canceled
     * @param string $sesskey
     * @param string $courseid
     * @param string $userid
     * @param string $securitykey
     * @return stdObject
     */
    public static function get_upload_key_live($sesskey, $courseid, $userid, $securitykey){
        $select='userid='.$userid.' AND sesskey=\''.$sesskey.'\' AND securitykey=\''.$securitykey.'\' AND uploadcomplete IS NULL and uploadcanceled=0';
        return (get_record_select('enhancedfile_uploads_auth', $select));
    }

    /**
     * set upload as complete so that key can't be used again
     * @param string $sesskey
     * @param string $courseid
     * @param string $userid
     * @param string $securitykey
     * @return stdObject
     */
    public static function set_upload_key_complete($sesskey, $courseid, $userid, $securitykey){
        $upkey=self::get_upload_key_live($sesskey, $courseid, $userid, $securitykey);
        $do=(object) array(
            'id'=>$upkey->id,
            'uploadcomplete'=>time()
        );
        return (update_record('enhancedfile_uploads_auth', $do));
    }
}

?>
