<?php

    /**
    * shows a short and sortable list of appointments with usefull links
    *
    * @package mod-scheduler
    * @category mod
    * @author Valery Fremaux > 1.8
    *
    */

    /**
    * Requires and includes
    */    
    include_once "$CFG->libdir/tablelib.php";

    /**
    * Security traps
    */
    if (!defined('MOODLE_INTERNAL')){
        error("This file cannot be loaded directly");
    }

    if (has_capability('mod/scheduler:canseeotherteachersbooking', $context)) {
        $teacherid = optional_param('teacherid', $USER->id, PARAM_INT);
        $select = " teacherid = $teacherid ";
        $tutor = get_record('user', 'id', $teacherid);
        $teachers = get_users_by_capability($context, 'mod/scheduler:attend', 'u.id, lastname, firstname,email,picture', 'lastname, firstname');
    
        foreach($teachers as $teacher){
            $teachermenu[$teacher->id] = fullname($teacher);
        }
        ?>
        <form name="teacherform">
        <input type="hidden" name="id" value="<?php p($cm->id) ?>" />
        <input type="hidden" name="what" value="datelist" />
        <?php choose_from_menu($teachermenu, 'teacherid', $teacherid, '', 'Choose'); ?>
        <input type="submit" value="Go" />
        </form>      
        <hr />
        <?php
    }
    else{
        $select = " teacherid = $USER->id ";
    }

/// getting date list
    
    $sql = "
        SELECT 
        	a.id AS id,
        	u1.id AS studentid,
            u1.email AS studentmail,
            u1.lastname AS studentlastname, 
            u1.firstname AS studentfirstname,
            u1.department AS studentdepartment, 
            a.appointmentnote,
            a.grade,
            sc.name,
            sc.id as schedulerid,
            c.shortname as courseshort,
            c.id as courseid,
            u2.email,
            u2.lastname, 
            u2.firstname, 
            s.id as sid,
            s.starttime, 
            s.duration, 
            s.appointmentlocation, 
            s.notes 
        FROM 
            {$CFG->prefix}course c, 
            {$CFG->prefix}scheduler sc, 
            {$CFG->prefix}scheduler_appointment a, 
            {$CFG->prefix}scheduler_slots s, 
            {$CFG->prefix}user u1, 
            {$CFG->prefix}user u2 
        WHERE 
        	c.id = sc.course AND
        	sc.id = s.schedulerid AND
            a.slotid = s.id AND 
            u1.id = a.studentid AND
            u2.id = s.teacherid AND 
            u2.id = '$teacherid' 
    ";

    $sqlcount = "
        SELECT
            COUNT(*)
        FROM 
            {$CFG->prefix}course c, 
            {$CFG->prefix}scheduler sc, 
            {$CFG->prefix}scheduler_appointment a, 
            {$CFG->prefix}scheduler_slots s, 
            {$CFG->prefix}user u1, 
            {$CFG->prefix}user u2 
        WHERE 
        	c.id = sc.course AND
        	sc.id = s.schedulerid AND
            a.slotid = s.id AND 
            u1.id = a.studentid AND
            u2.id = s.teacherid AND 
            u2.id = '$teacherid' 
    ";
    $numrecords = count_records_sql($sqlcount);
    
    $limit = 30;
    
    if ($numrecords){

/// make table result

	   $coursestr = get_string('course','scheduler');
	   $schedulerstr = get_string('scheduler','scheduler');
	   $whenstr = get_string('when','scheduler');
       $wherestr = get_string('where','scheduler'); 
       $whostr = get_string('who','scheduler');
       $wherefromstr = get_string('department','scheduler');
       $whatstr = get_string('what','scheduler');
       $whatresultedstr = get_string('whatresulted','scheduler');
       $whathappenedstr = get_string('whathappened','scheduler');


        $tablecolumns = array('courseshort', 'schedulerid', 'starttime', 'appointmentlocation', 'CONCAT(studentlastname,studentfirstname)', 'department', 'notes', 'grade', 'appointmentnote');
        $tableheaders = array("<b>$coursestr</b>", "<b>$schedulerstr</b>", "<b>$whenstr</b>", "<b>$wherestr</b>", "<b>$whostr</b>", "<b>$wherefromstr</b>", "<b>$whatstr</b>", "<b>$whatresultedstr</b>", "<b>$whathappenedstr</b>");
        
        $table = new flexible_table('mod-scheduler-datelist');
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        
        $table->define_baseurl($CFG->wwwroot.'/mod/scheduler/view.php?what=datelist&amp;id='.$cm->id);
        
        $table->sortable(true, 'when'); //sorted by date by default
        $table->collapsible(true);
        $table->initialbars(true);
        
        // allow column hiding
        $table->column_suppress('course');
        $table->column_suppress('starttime');
        
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'dates');
        $table->set_attribute('class', 'datelist');
        $table->set_attribute('width', '100%');
        
        $table->column_class('course', 'datelist_course');
        $table->column_class('scheduler', 'datelist_scheduler');
        $table->column_class('starttime', 'timelabel');
        
        $table->setup();
            
        /// get extra query parameters from flexible_table behaviour
        $where = $table->get_sql_where();
        $sort = $table->get_sql_sort();
        $table->pagesize($limit, count($numrecords));
        
        
        if (!empty($sort)){
            $sql .= " ORDER BY $sort";
        }

        $results = get_records_sql($sql);

        // display implementes a "same value don't appear again" filter
        $coursemem = '';
        $schedulermem = '';
        $whenmem = '';
        $whomem = '';
        $whatmem = '';
        foreach($results as $id => $row){
          $coursedata = ($coursemem != $row->courseshort) ? "<a href=\"{$CFG->wwwroot}/course/view.php?id={$row->courseid}\">".$row->courseshort.'</a>' : '';
          $coursemem = $row->courseshort;
          $schedulerdata = ($schedulermem != $row->name) ? "<a href=\"{$CFG->wwwroot}/mod/scheduler/view.php?a={$row->schedulerid}\">".$row->name.'</a>' : '';
          $schedulermem = $row->name;
          $whendata = ($whenmem != "$row->starttime $row->duration") ? '<strong>'.date("d M Y G:i", $row->starttime).' '.get_string('for','scheduler')." $row->duration ".get_string('mins', 'scheduler').'</strong>' : '';
          $whenmem = "$row->starttime $row->duration";
          $whodata = ($whomem != $row->studentmail) ? "<a href=\"{$CFG->wwwroot}/mod/scheduler/view.php?what=viewstudent&a={$row->schedulerid}&amp;studentid=$row->studentid&amp;course=$row->courseid\">".$row->studentfirstname.' '.$row->studentlastname.'</a>' : '';
          $whomem = $row->studentmail;
          $whatdata = ($whatmem != $row->notes) ? format_string($row->notes) : '';
          $whatmem = $row->notes;
          $dataset = array(
          			     $coursedata,
          			     $schedulerdata,
          			     $whendata, 
          			     $row->appointmentlocation, 
          			     $whodata, 
          			     $row->studentdepartment, 
          			     $whatdata, 
          			     $row->grade, 
          			     $row->appointmentnote);
		    $table->add_data($dataset);		
        }
        $table->print_html();
        print_continue($CFG->wwwroot."/mod/scheduler/view.php?id=".$cm->id.'&amp;page='.$page);
    }
    else{
        notice(get_string('noresults', 'scheduler'), $CFG->wwwroot."/mod/scheduler/view.php?id=".$cm->id);
    }
?>