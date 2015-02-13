<?php
include_once($CFG->dirroot . '/lib/dmllib.php');

//@returns just one account as an object
function get_institutional_account(){
    global $CFG, $USER;
    $account = null;
    $account = get_record_sql('SELECT * FROM '.$CFG->prefix.'block_pebblepad WHERE linktype = \'INST\'');
    return $account;
}

//@returns an array of accounts objects priority first
function get_personal_accounts(){
    global $CFG, $USER;
    $accounts =  get_records_sql('SELECT * FROM '.$CFG->prefix.'block_pebblepad WHERE userid = '.$USER->id.' ORDER BY priority DESC');
    return $accounts;
}

function get_portfolio($id){
    global $CFG, $USER;
    $sql = 'SELECT * FROM '.$CFG->prefix.'block_pebblepad WHERE id = '.$id;
    $account =  get_record_sql($sql);
    return $account;
}

function is_portfolio_owner($id){
    global $CFG, $USER;
    if (get_portfolio($id)){
        return true;
    }else{
        return false;
    }
}

function set_default_portfolio($id){
    global $CFG, $USER;
    $upSql = 'UPDATE `'.$CFG->prefix.'block_pebblepad` SET priority = 1 WHERE id = '.$id.' userid = '.$USER->id;
    if (execute_sql('UPDATE `'.$CFG->prefix.'block_pebblepad` SET priority = 0 WHERE userid = '.$USER->id, false)){
       execute_sql('UPDATE `'.$CFG->prefix.'block_pebblepad` SET priority = 1 WHERE id = '.$id.' AND userid = '.$USER->id, false);
    }
}

function update_portfolio($data){
    global $CFG, $USER;
    $upSql = 'UPDATE `'.$CFG->prefix.'block_pebblepad` SET name="'.$data->name.'", url="'.$data->url.'", priority = '.$data->priority.' WHERE id = '.$data->id.' AND userid = '.$USER->id;
    if (execute_sql($upSql, false)){
        return true;
    }else{
        return false;
    }
}

// @returns html selection list with priority account selected as default
// @selected - the portfolio id
function get_html_portfolio_accounts($selected=null){
    global $CFG;
    if (empty($selected)){
       $selected = $CFG->pebblerootid;
    }
    $data = '';
    $accounts = get_all_portfolio_accounts();

    if ( !file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php') && (count($accounts) == 1)){
           //hide select option for user
           $data.='<select style="display:none;" name="portfolio" id="portfolio">';
           foreach($accounts as $acc){
                $data.='<option value='.$acc->id.'>'.$acc->name.'</option>';
           }
    }else{
        $data.='send to: <select onChange="update_button(this)" name="portfolio" id="portfolio">';
            foreach($accounts as $acc){
                            if (is_object($acc)) {
                                    if (intVal($acc->id) == intVal($selected)){
                                       $data.='<option value='.$acc->id.' selected >'.$acc->name.'</option>';
                                    }else{
                                       $data.='<option value='.$acc->id.'>'.$acc->name.'</option>';
                                    }
                            }
            }
            
        if (file_exists($CFG->dirroot . '/blocks/pebblepad/leap2azip.php')){
            include_once($CFG->dirroot . '/blocks/pebblepad/leap2azip.php');
            $data .= get_zip_selection($selected);
        }
    }
    $data .= '</select>';
    return $data;
}

function get_all_portfolio_accounts(){
    $personal = get_personal_accounts();
    $institutional = get_institutional_account();

    $accounts = array();

    if (!empty($personal)){
        foreach ($personal as $acc){
            array_push($accounts, $acc);
        }
    }
    //add the institution
    array_push($accounts, $institutional);

    return $accounts;
}

function get_default_portfolio(){
    $account = null;
    $personal = get_personal_accounts();
    if (!empty($personal)){
        foreach ($personal as $acc){
            if ($acc->priority == 1){
                $account = $acc;
            }
        }
    }

    if (empty($account)){ //else get the standard instititional account
        $account = get_institutional_account();
    }

    return $account;
}

?>
