<?php

class block_quickfindlist extends block_base {

    function init() {
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2009060305;
        $this->title = get_string('quickfindlist','block_quickfindlist');
        $this->content->footer = '';  
    }

    function applicable_formats() {
        if (has_capability('block/quickfindlist:use', get_context_instance(CONTEXT_SYSTEM))) {
            return (array('all' => false, 'site'=>true, 'my'=>true));
        } else {
            return (array('all' => false));
        }
    }

    function instance_allow_multiple() {
        return true;
    }



    function get_content() {
        global $CFG;
        global $COURSE;
        global $HTTPSPAGEREQUIRED;

        if (!empty($HTTPSPAGEREQUIRED)) {
            $wwwroot = $CFG->httpswwwroot;
        } else {
            $wwwroot = $CFG->wwwroot;
        }

        if(empty($this->config->role)){
            if($thispageqflblocks=get_records_sql('SELECT * FROM '.$CFG->prefix.'block JOIN '.$CFG->prefix.'block_instance instance ON '.$CFG->prefix.'block.id=blockid WHERE name=\'quickfindlist\' AND pagetype=\''.$this->instance->pagetype.'\' AND pageid='.$this->instance->pageid.' AND instance.id<'.$this->instance->id)){
                foreach ($thispageqflblocks as $thispageqflblock){
                    //don't give a warning for blocks without a role configured
                    if(@unserialize(base64_decode($thispageqflblock->configdata))->role<1){$this->content->text=get_string('multiplenorole','block_quickfindlist');return $this->content;}
                }
            }

            $this->config->role=-1;
        }
        if ($role=get_record('role','id',$this->config->role)){
            $roleid=$role->id;
            $this->title = $role->name.get_string('list','block_quickfindlist');
        }else{
            $roleid='-1';
            $this->title=get_string('allusers','block_quickfindlist').get_string('list','block_quickfindlist');
        }

        global $CFG, $USER, $COURSE; 
        if (has_capability('block/quickfindlist:use', get_context_instance(CONTEXT_BLOCK,$this->instance->id))) {
            $this->content->text="<input type='test' onkeyup='quickfindsearch($roleid)' id='quickfindlistsearch$roleid'><br><p id='quickfindlist$roleid'>";

            $people=$this->search_users();

            if(!$people){
                $this->content->text=get_string('nousers','block_quickfindlist');
            }else{
                if(empty($this->config->userfields)){$this->config->userfields=get_string('userfieldsdefault','block_quickfindlist');}
                foreach ($people as $person) {
                    $userstring=str_replace('[[firstname]]',$person->firstname,$this->config->userfields);
                    $userstring=str_replace('[[lastname]]',$person->lastname,$userstring);
                    $userstring=str_replace('[[username]]',$person->username,$userstring);
                    // edited by MH - only show ID if exists
                    if (($person->idnumber) > 0) {
                        $userstring=str_replace('[[idnumber]]','('.$person->idnumber.')',$userstring);
                    } else {
                        $userstring=str_replace('[[idnumber]]','',$userstring);
                    }
                    // end edit
                    if(empty($this->config->url)){
                        $this->content->text .= "<a style='display:none' href='$wwwroot/user/view.php?id=$person->id&course=$COURSE->id'>$userstring</a>";
                    }else{
                        $this->content->text .= '<a style="display:none" href='.$this->config->url.$person->id.'>'.$userstring.'</a>';
                    }

                }

               $this->content->text .="</p>";
            }
            require_js($wwwroot.'/blocks/quickfindlist/quickfindlist.js');
            $this->content->text.='<script type="text/javascript">quickfindsearch('.$roleid.');</script>';
        }

        $this->content->footer='';

        return $this->content;

    }

    function search_users() {

        global $CFG, $USER, $COURSE;

      
            $query='SELECT id,idnumber,firstname,lastname,username
                FROM '.$CFG->prefix.'user WHERE deleted=0';
            if($this->config->role!=-1){
                $query.=' AND (SELECT COUNT(*)
                               FROM '.$CFG->prefix.'role_assignments
                                   JOIN '.$CFG->prefix.'context ON '.$CFG->prefix.'context.id=contextid
                               WHERE '.$CFG->prefix.'role_assignments.userid='.$CFG->prefix.'user.id
                                   AND '.$CFG->prefix.'role_assignments.roleid='.$this->config->role.'
                                   AND '.$CFG->prefix.'role_assignments.hidden=0';
                if($COURSE->format!='site'){$query.=' AND contextlevel=50 AND instanceid='.$COURSE->id;}
                $query.=' )>0';
            }
            $query.=' ORDER BY lastname';

            return get_records_sql($query);

    }



}
?>
