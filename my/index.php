<?php  // $Id: index.php,v 1.16.2.8 2009/11/13 05:35:09 andyjdavis Exp $

    // this is the 'my moodle' page

    require_once('../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_once('pagelib.php');
    
    require_login();
    
    // things to do:
    
    // 1. js/css outside
    // 2. get school block ids
    // 3. show empty categories with message?
    
    
    //  what about this:
    // $courses = get_my_courses($USER->id, 'visible DESC, fullname ASC'


    $mymoodlestr = get_string('mymoodle','my');

    if (isguest()) {
        $wwwroot = $CFG->wwwroot.'/login/index.php';
        if (!empty($CFG->loginhttps)) {
            $wwwroot = str_replace('http:','https:', $wwwroot);
        }

        print_header($mymoodlestr);
        notice_yesno(get_string('noguest', 'my').'<br /><br />'.get_string('liketologin'),
                     $wwwroot, $CFG->wwwroot);
        print_footer();
        die();
    }

    // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);

    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $blockaction = optional_param('blockaction', '', PARAM_ALPHA);

    $PAGE = page_create_instance($USER->id);

    // get the list of sticky mymoodle blocks from m_blocks_pinned
    $pageblocksx = blocks_setup($PAGE,BLOCKS_PINNED_BOTH);
    

    // get variables from $USER global
    // $userStatus = trim($USER->UDBcategoryutype); 
    // $userSchool = trim($USER->UDBschoolid);
    // $userDept =   trim($USER->UDBdepartmentid);
    // UDB not getting updated, so in meantime use these
    $userStatus = '';
    $userSchool = trim($USER->institution);
    $userDept =   trim($USER->department);
    
    $showHiddenCats = $CFG->allowvisiblecoursesinhiddencategories;
    $schoolCode = '';
    
    
    // ****************************************************************************************************
    // START CUSTOM SECTION FOR SCHOOL BLOCKS
    // list block ID's in order in which they appear down the side, this overrides
    // weighting set on site admin > modules > blocks > sticky > mymoodle

    if ($_SERVER['SERVER_NAME'] == 'moodle-dev.city.ac.uk')  {
    
      $auditorRoleID = 221;
      $observerRoleID = 222;
      $elearnARoleID = 241;
      $progARoleID = 142;
      $libraryARoleID = 0;
      $rolesFullList = array(142,221,222,241);  // these roles see ALL modules in categories they have RA for
            
      // Default blocks if $userSchool/UDBschoolid not set
      $allowedBlocks_l = array(21);
      $allowedBlocks_r = array(261,22);
      
      // Blocks for Law School   (done))
      if (($userSchool == 'LLILAW') OR (substr($userDept,0,2) == 'LL')) {
        $allowedBlocks_l = array(21,161,22); 
        $allowedBlocks_r = array(261,62,181,182);
      }
      
      // Blocks for Cass Business School  (done)
      if (($userSchool == 'BBCASS') OR (substr($userDept,0,2) == 'BB')) {
        $allowedBlocks_l = array(101,102,183); 
        $allowedBlocks_r = array(261,241,22);
        $schoolCode = 'CASS';
      }   
       
      // Blocks for School of Arts (done)
      if (($userSchool == 'AASOAR') OR (substr($userDept,0,2) == 'AA')) {
        $allowedBlocks_l = array(); 
        $allowedBlocks_r = array(261,21,161,144,22);
      }
  
      // Blocks for School of Social Sciences  (done)
      if (($userSchool == 'SSSOSS') OR (substr($userDept,0,2) == 'SS')) {
        $allowedBlocks_l = array(); 
        $allowedBlocks_r = array(261,21,161,144,22);
      }
  
      // Blocks for School of Engineering and Maths (done)
      if (($userSchool == 'EESEMS') OR (substr($userDept,0,2) == 'EE')) {
        $allowedBlocks_l = array(103,61); 
        $allowedBlocks_r = array(261,21,105);
      }
      
      // Blocks for School of Community and Health Sciences 
      if (($userSchool == 'HASAHS') OR ($userSchool == 'HNSONM') OR (substr($userDept,0,2) == 'HN') OR (substr($userDept,0,4) == 'BARTS') OR (substr($userDept,0,2) == 'HA')) {
        $allowedBlocks_l = array(21); 
        $allowedBlocks_r = array(261,22);
        $schoolCode = 'SHS';
      }
      
      // Blocks for School of Information Sciences
      if (($userSchool == 'IISOIN') OR (substr($userDept,0,2) == 'II')) {
        $allowedBlocks_l = array(261,201); 
        $allowedBlocks_r = array();
      }
                  
      // Blocks for Central Services
      if (($userSchool == 'UUCITY') OR (substr($userDept,0,2) == 'UU')) {
        $allowedBlocks_l = array(21); 
        $allowedBlocks_r = array(261,22);
      }
      
      // Blocks for Externals
      if (substr($userDept,0,2) == 'XX') {
        $allowedBlocks_l = array(21); 
        $allowedBlocks_r = array(261,22);
      }
      
      // override for admins
      if (isadmin()) {
        $allowedBlocks_l = array(22,162); 
        $allowedBlocks_r = array(261,108);
      }
    
    }
    
        
    // Blocks for LIVE
    
    if (($_SERVER['SERVER_NAME'] == 'moodle-archive.city.ac.uk') || ($_SERVER['SERVER_NAME'] == 'moodle-test.city.ac.uk'))  {
      
      $auditorRoleID = 161;
      $observerRoleID = 262;
      $elearnARoleID = 61;
      $progARoleID = 261;
      $libraryARoleID = 281;
      $rolesFullList = array(161,261,262,281,61);  // these roles see ALL modules in categories they have RA for
      
      // Default blocks if $userSchool/UDBschoolid not set
      $allowedBlocks_l = array(41);
      $allowedBlocks_r = array(161,42);
      
      // Blocks for Central Services
      if (($userSchool == 'UUCITY') OR (substr($userDept,0,1) == 'U')) {
        $allowedBlocks_l = array(41); 
        $allowedBlocks_r = array(161,42);
      }
      
      // Blocks for Law School   (done))
      if ((trim($userSchool) == 'LLILAW') OR (substr(trim($userDept),0,2) == 'LL') OR (trim($userDept) == 'INSLAW') OR (trim($userDept) == 'LAW')) {
        $allowedBlocks_l = array(41,67,42); 
        $allowedBlocks_r = array(161,62,63,64);
      }
      
      // Blocks for Cass Business School  (done)
      if ((trim($userSchool) == 'BBCASS') OR (substr(trim($userDept),0,2) == 'BB')) {
        $allowedBlocks_l = array(181,121,81,65); 
        $allowedBlocks_r = array(161,201,67,41,202,221);
        $schoolCode = 'CASS';
      }   
       
      // Blocks for School of Arts (done)
      if ((trim($userSchool) == 'AASOAR') OR (substr(trim($userDept),0,2) == 'AA')) {
        $allowedBlocks_l = array(); 
        $allowedBlocks_r = array(161,41,67,61,42);
      }
  
      // Blocks for School of Social Sciences  (done)
      if ((trim($userSchool) == 'SSSOSS') OR (substr(trim($userDept),0,2) == 'SS') OR (trim($userDept) == 'SoSS')) { 
        $allowedBlocks_l = array(); 
        $allowedBlocks_r = array(161,41,67,61,42);
      }
  
      // Blocks for School of Engineering and Maths (done)
      if ((trim($userSchool) == 'EESEMS') OR (substr(trim($userDept),0,1) == 'E')) {
          $allowedBlocks_l = array(70,71); 
        $allowedBlocks_r = array(161,41,72);
      }
      
      // Blocks for School of Community and Health Sciences 
      if ((trim($userSchool) == 'HASAHS') OR (trim($userSchool) == 'HNSONM') OR (substr(trim($userDept),0,2) == 'HP') OR (substr(trim($userDept),0,2) == 'HN') OR (substr(trim($userDept),0,2) == 'HM') OR (substr(trim($userDept),0,2) == 'HH') OR (substr(trim($userDept),0,2) == 'HA') OR (trim($userDept) == 'BARTS')) {
        $allowedBlocks_l = array(142,41); 
        $allowedBlocks_r = array(161,42,141);
        $schoolCode = 'SHS';
      }
      
      // Blocks for School of Information Sciences
      if ((trim($userSchool) == 'IISOIN') OR (substr(trim($userDept),0,2) == 'II')) {
        $allowedBlocks_l = array(161,73); 
        $allowedBlocks_r = array();
      }
      
      // Blocks for Externals
      // need to put this at end to override any erroneous school/dept codes in UDB
      if ((trim($userSchool) == 'External') OR (trim($userSchool) == 'NOSCHL')) {
        $allowedBlocks_l = array(41); 
        $allowedBlocks_r = array(161,42);
      }
      
      // override for admins
      if (isadmin()) {
        $allowedBlocks_l = array(42,69); 
        $allowedBlocks_r = array(161,68);
      }
    
    }

    
    // now we need to check if user has own blocks on my page and add ids to arrays
    // must add now so they only appear beneath sticky blocks   
    $qry = "SELECT id,position FROM {$CFG->prefix}block_instance WHERE pagetype='my-index' AND pageid={$USER->id} ORDER BY weight ASC";
    if ($myblocks = get_records_sql($qry)) {
      // now look through and add to appropriate array
      foreach ($myblocks as $block) {
        if ($block->position == 'l') $allowedBlocks_l[] = $block->id;
        if ($block->position == 'r') $allowedBlocks_r[] = $block->id;  
      }
    } else {
      // user has no custom blocks so do nothing
    }
    
    
    // get array of all sticky blocks (including users own) left and right and merge
    $pageblocks_array = array_merge ($pageblocksx['l'], $pageblocksx['r']);
    //$pageblocks_left = $pageblocksx['l'];    // array of objects for left blocks
    //$pageblocks_right = $pageblocksx['r'];   // array of objects for right blocks
    
    // set up new arrays for new sets of blocks left and right
    $apageblocks_left = array();
    $apageblocks_right = array();
  
    // seek allowed blocks for left side from array of all sticky blocks
    foreach ($allowedBlocks_l as $a) {
      foreach ($pageblocks_array as $obj) {
        if ($a == $obj->id) {
          //$obj->weight = $w;
          $apageblocks_left[] = $obj;
          //print_r ($obj);
          //$w += 1;
        }
      }
    }
    
    // seek allowed blocks for right side from array of all sticky blocks
    foreach ($allowedBlocks_r as $a) {
      foreach ($pageblocks_array as $obj) {
        if ($a == $obj->id) {
          $apageblocks_right[] = $obj;
        }
      }
    }
    
    // re-construct $pageblocks array
    $pageblocks = array("l"=>$apageblocks_left,"r"=>$apageblocks_right);
    

    // END CUSTOM SECTION FOR SCHOOL BLOCKS
    // ****************************************************************************************************


    if (($edit != -1) and $PAGE->user_allowed_editing()) {
        $USER->editing = $edit;
    }
  
    $PAGE->print_header($mymoodlestr);
    
    echo '<link rel="stylesheet" type="text/css" href="styles.css" />';
    
    if (!isset($_REQUEST['courses']) || ($_REQUEST['courses']='')) $_REQUEST['courses']='my';
    if (!isset($_REQUEST['academicyear'])) $_REQUEST['academicyear']='';
?>

<script type="text/javascript" src="./javascript/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="./javascript/ddaccordion.js"></script>
<script type="text/javascript">


ddaccordion.init({
	headerclass: "expandable", //Shared CSS class name of headers group that are expandable
	contentclass: "categoryitems", //Shared CSS class name of contents group
	revealtype: "click", //Reveal content when user clicks or onmouseover the header? Valid value: "click" or "mouseover
	mouseoverdelay: 200, //if revealtype="mouseover", set delay in milliseconds before header expands onMouseover
	collapseprev: true, //Collapse previous content (so only one open at any time)? true/false 
	defaultexpanded: [], //index of content(s) open by default [index1, index2, etc]. [] denotes no content
	onemustopen: false, //Specify whether at least one header should be open always (so never all headers closed)
	animatedefault: false, //Should contents open by default be animated into view?
	persiststate: false, //persist state of opened contents within browser session?
	toggleclass: ["", "openheader"], //Two CSS classes to be applied to the header when it's collapsed and expanded, respectively ["class1", "class2"]
	togglehtml: ["prefix", "", ""], //Additional HTML added to the header when it's collapsed and expanded, respectively  ["position", "html1", "html2"] (see docs)
	animatespeed: "fast", //speed of animation: integer in milliseconds (ie: 200), or keywords "fast", "normal", or "slow"
	oninit:function(headers, expandedindices){ //custom code to run when headers have initalized
		//do nothing
	},
	onopenclose:function(header, index, state, isuseractivated){ //custom code to run whenever a header is opened or closed
		//do nothing
	}
})

</script>

<?   

    echo '<table id="layout-table">';
    echo '<tr valign="top">';

    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':

    $blocks_preferred_width = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), BLOCK_L_MAX_WIDTH);

    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing()) {        
        echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="left-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        print_container_end();
        echo '</td>';
    }
    
            break;
            case 'middle':
    
    echo '<td valign="top" id="middle-column">';
    print_container_start(TRUE);

/// The main overview in the middle of the page

    include_once('announce.php');

	print_heading_block('MyMoodle');


// what academic year are we?
// only show years from 2009-10, cos Moodle was empty before that right!
$nYears = 4; // how many past years to show, including next academic year
$yearArray = array();
for ($j = 0; $j<$nYears; $j+=1) {  
  if ((date("Y")+($j-3))>2008) $yearArray[]= date("Y")+($j-3) . "-" . date("y",mktime(0,0,0,1,1,date("Y")+($j-2)));
}
$yearArray[] = 'ALL';


// set academic year
if (!isset($_REQUEST['academicyear']) || ($_REQUEST['academicyear']=='')) {
  // set to current year if unset
  if (date("n") > 8) {
    $acYr = date("Y") . '-' . date("y",mktime(0,0,0,1,1,date("Y")+1));
  } else {
    $acYr = date("Y")-1 . '-' . date("y");
  }
} elseif (preg_match('/[0-9]{4}-[0-9]{2}/',$_REQUEST['academicyear']))  {
  $acYr = $_REQUEST['academicyear'];
}	else {
  $acYr = 'ALL';
}

// override for CASS and SHS, set to ALL by default
// CMDL-1542

if ( ($schoolCode == 'SHS') || ($schoolCode == 'CASS')) {
  if (!isset($_REQUEST['academicyear']) || ($_REQUEST['academicyear']=='')) {
    $acYr = 'ALL';
  }

}

if (isset($_REQUEST['period'])) {
  $period = $_REQUEST['period'];
  if (($period != 'PRD1') && ($period != 'PRD2') && ($period != 'PRD3')) $period ='ALL';
} else {
  $period = 'ALL';
}

if ($_REQUEST['courses'] == 'my') {
   echo "<p>Listed below are " . (($acYr =='ALL' && $period == 'ALL') ? ' <strong>ALL</strong> ':'') . "your modules" . (($acYr != '') && ($acYr != 'ALL') ? ' for <strong>'.$acYr.'</strong>' : '') . (($period != '') && ($period != 'ALL') ? ' for period <strong>'.$period.'</strong>' : '') . ". Click a category heading to expand the list. 
 Use the dropdown lists to filter by academic year and period.</p>\n";
} else {
  echo "<p>Listed below are ALL modules" . (($acYr != '') && ($acYr != 'ALL') ? ' for '.$acYr : '') . ". Click a category heading to expand the list. 
 Use the dropdown lists to filter by academic year and period.</p>\n";
}


echo "</p>\n";

// new code using form dropdowns instead and no year restriction
echo "<form method=\"get\" action=\"/my/index.php\">\n";

echo "<strong>filter by</strong> academic year <select name=\"academicyear\">\n";
foreach ($yearArray as $y) {
  if ($y == $acYr) {
    echo "<option selected=\"selected\" value=\"{$y}\">{$y}</option>\n";  
  } else {
    echo "<option value=\"{$y}\">{$y}</option>\n"; 
  }
}                 
echo "</select>\n";

$periodArray = array ('PRD1','PRD2','PRD3','ALL');
echo "period <select name=\"period\">\n";
foreach ($periodArray as $p) {
  if ($p == $period) {
    echo "<option selected=\"selected\" value=\"{$p}\">{$p}</option>\n";  
  } else {
    echo "<option value=\"{$p}\">{$p}</option>\n"; 
  }
} 
echo "</select>\n";
echo " <input value=\"apply\" type=\"submit\">\n";
echo "</form>\n";

   
      // start course list using accordion method
      // get long list of all course that a user has a role designation on in the course context
      // show hidden categories if $CFG allows
      
      $fromPart = "FROM {$CFG->prefix}COURSE_CATEGORIES 
LEFT JOIN {$CFG->prefix}COURSE ON {$CFG->prefix}COURSE_CATEGORIES.ID={$CFG->prefix}COURSE.CATEGORY 
LEFT JOIN {$CFG->prefix}CONTEXT ON {$CFG->prefix}CONTEXT.INSTANCEID={$CFG->prefix}COURSE.ID 
LEFT JOIN {$CFG->prefix}ROLE_ASSIGNMENTS ON {$CFG->prefix}ROLE_ASSIGNMENTS.CONTEXTID={$CFG->prefix}CONTEXT.ID ";
      if (($_REQUEST['courses'] == 'my') || (!isset($_REQUEST['courses']))) {
        // for 'My' courses user has some kind of enrollment on the course and it is visible
        $wherePart = "WHERE {$CFG->prefix}CONTEXT.CONTEXTLEVEL=50 
        AND (
          (({$CFG->prefix}COURSE.VISIBLE=1) 
            AND ((({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID > 4) AND ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID < 8)) 
            OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$observerRoleID})) 
          )
            
          OR (
            ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = 3 ) 
              OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = 4 )
              OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$elearnARoleID} )
              OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$progARoleID} )
              OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$libraryARoleID} )
              OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$auditorRoleID} ) 
            ) 
        ) AND {$CFG->prefix}ROLE_ASSIGNMENTS.USERID={$USER->id}";

      } else {     // for ALL courses, course must visible OR user must be a teacher or assistant teacher or auditor with a role assignment on the course
        $wherePart = "WHERE {$CFG->prefix}CONTEXT.CONTEXTLEVEL=50 
AND (
( {$CFG->prefix}COURSE.VISIBLE=1 ) 
  OR ((({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = 3) OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = 4) OR ({$CFG->prefix}ROLE_ASSIGNMENTS.ROLEID = {$auditorRoleID}))  
  AND ({$CFG->prefix}ROLE_ASSIGNMENTS.USERID={$USER->id}))
)";    

          // override for admins viewing ALL - so they see everything OK?
          if (isadmin()) {
            $wherePart = "WHERE {$CFG->prefix}CONTEXT.CONTEXTLEVEL=50";
          } 
      }
      
      // apply hiding of hidden categories if global setting says so and if not admin
      if (($showHiddenCats == 1) || (isadmin())) {
        // do nothing
      }  else {
        $wherePart = $wherePart . " AND {$CFG->prefix}COURSE_CATEGORIES.VISIBLE=1";
      }
      
      $fromPart = $fromPart . $wherePart;

      // add academic year to query if relevant
      if (($acYr != '') && ($acYr != 'ALL')) $fromPart = $fromPart . " AND {$CFG->prefix}COURSE.shortname LIKE '%_{$acYr}%'";
      if (($period != '') && ($period != 'ALL')) {
        $fromPart = $fromPart . " AND {$CFG->prefix}COURSE.shortname LIKE '%_{$period}_%'";
      }
      $cats = array();
      // build array of category ids, parents first
      $query_rsCategories1 = "select DISTINCT {$CFG->prefix}COURSE_CATEGORIES.id,{$CFG->prefix}COURSE_CATEGORIES.name " . $fromPart . " AND {$CFG->prefix}COURSE_CATEGORIES.parent=0 ORDER BY {$CFG->prefix}COURSE_CATEGORIES.name ASC";
      if (($Category1 = get_records_sql($query_rsCategories1)) != false) {
        foreach ($Category1 as $cc1) {
          $cats[] = $cc1->id;
        }
      }

      // build array of category ids, now get parent ids of subcats
      $query_rsCategories2 = "select DISTINCT {$CFG->prefix}COURSE_CATEGORIES.parent,{$CFG->prefix}COURSE_CATEGORIES.name " . $fromPart . " AND {$CFG->prefix}COURSE_CATEGORIES.parent>0 AND {$CFG->prefix}COURSE_CATEGORIES.depth=2 ORDER BY {$CFG->prefix}COURSE_CATEGORIES.name ASC";
      if (($Category2 = get_records_sql($query_rsCategories2)) != false) {
        foreach ($Category2 as $cc2) {
          $cats[] = $cc2->parent; 
        }
      }   
  
      // now remove duplicates
      $catsDistinct = array_unique($cats);
      $str = array();
      foreach ($catsDistinct as $cc) {
        $str[] = "id=" . $cc;
      }
      if (count($str) > 0) {
        $orPart = implode(' OR ',$str);   
      } else {
        $orPart = "1=0";  // to ensure null query if no categories
      }
      
      $query_rsCategories = "select DISTINCT id,name,visible FROM {$CFG->prefix}COURSE_CATEGORIES WHERE " . (($showHiddenCats == 1) || (isadmin())?'':'VISIBLE=1 AND ') . "({$orPart}) ORDER BY name ASC";
            
      echo "<div class=\"arrowlistmenu\">";
        
      if (($Categories = get_records_sql($query_rsCategories)) != false) {
       
  		  foreach ($Categories as $c) { // start looping through each major category
  		    
  		    $c->context = get_context_instance(CONTEXT_COURSECAT,$c->id);
          $catCount = "SELECT DISTINCT {$CFG->prefix}COURSE.id " . $fromPart . " AND ({$CFG->prefix}COURSE_CATEGORIES.id={$c->id} OR {$CFG->prefix}COURSE_CATEGORIES.parent={$c->id})";
          $catCountObject = get_records_sql($catCount);
  		    $numberCourses = count($catCountObject);
      
          echo "<h3 class=\"menuheader expandable" . (($c->visible == 0)?' hidden':'') . "\">" . $c->name . " (" . $numberCourses . ")</h3>";   
  		
  		    
          $query_rsSubCategories = "select DISTINCT {$CFG->prefix}COURSE_CATEGORIES.id,{$CFG->prefix}COURSE_CATEGORIES.name,{$CFG->prefix}COURSE_CATEGORIES.parent,{$CFG->prefix}COURSE_CATEGORIES.visible " . $fromPart . " AND {$CFG->prefix}COURSE_CATEGORIES.PARENT={$c->id} AND {$CFG->prefix}COURSE_CATEGORIES.COURSECOUNT > 0 ORDER BY {$CFG->prefix}COURSE_CATEGORIES.name ASC";
          
  				echo "<ul class=\"categoryitems\">";
          
          if (($SubCats = get_records_sql($query_rsSubCategories)) != false) {    // if the category has sub categories  
                    
  					foreach ($SubCats as $c1) {						
           
              $c1->context = get_context_instance(CONTEXT_COURSECAT,$c1->id);
              
              if ($_REQUEST['courses'] == 'all') {
                $catCount = "SELECT DISTINCT {$CFG->prefix}COURSE.id " . $fromPart . " AND {$CFG->prefix}COURSE_CATEGORIES.id={$c1->id}";
  		        } else {
                $catCount = "SELECT DISTINCT {$CFG->prefix}COURSE.id " . $fromPart . " AND {$CFG->prefix}COURSE_CATEGORIES.id={$c1->id}";
  		        }
              //echo $catCount;
              $catCountObject = get_records_sql($catCount);
  		        $numberCourses = count($catCountObject);
  		    
            	echo "<h4 class=\"sub_menuheader" . (($c1->visible == 0)?' hidden':'') . "\">" . $c1->name . " (" . $numberCourses . ") ";
              
              if (isadmin() || (has_capability('moodle/course:create',$c1->context))) { 
                addCourseLink($c1->id);
              }       
              if (isadmin() || (has_capability('moodle/category:manage',$c1->context))) { 
                addCourseMLink($c1->id);
              } 
              echo "</h4>";
              
  						echo "<ul class=\"subcategoryitems\">";
  						
  						//$query_rsSubCourses = "SELECT id, fullname, shortname FROM {$CFG->prefix}course WHERE category = " . $c1->id . " AND visible = 1 ORDER BY sortorder ASC";
  						$query_rsSubCourses = "select DISTINCT {$CFG->prefix}COURSE.id,{$CFG->prefix}COURSE.fullname,{$CFG->prefix}COURSE.shortname,{$CFG->prefix}course.idnumber,{$CFG->prefix}course.visible,{$CFG->prefix}ROLE_ASSIGNMENTS.contextid " . $fromPart . " AND {$CFG->prefix}COURSE.category={$c1->id} ORDER BY {$CFG->prefix}course.fullname ASC";
              //echo $query_rsSubCourses;
              
              if (($SubCourses = get_records_sql($query_rsSubCourses)) != false) {
  								
  							foreach ($SubCourses as $c2) {
               		echo "<li>";
                  printCourseTitle($c2);
                  printTeacherInfo($c2->id,$c2->contextid); 
                  echo "</li>";
              	}
  						}											
  						echo "</ul>";

  					}
  							
  				} // END OF IF-THEN-ELSE FOR $query_rsSubCategories
  					
  				//$query_rsCourses = "SELECT id, fullname, shortname FROM {$CFG->prefix}course WHERE category = " . $c->id . " AND visible = 1 ORDER BY sortorder ASC";
  				$query_rsCourses = "select DISTINCT {$CFG->prefix}course.id,{$CFG->prefix}course.fullname,{$CFG->prefix}course.shortname,{$CFG->prefix}course.idnumber,{$CFG->prefix}course.visible,{$CFG->prefix}ROLE_ASSIGNMENTS.contextid " . $fromPart . " AND {$CFG->prefix}course.category={$c->id} ORDER BY {$CFG->prefix}course.fullname ASC";
          //echo $query_rsCourses;

              
          if (($Courses = get_records_sql($query_rsCourses)) != false) {
  					//echo "<ul class=\"categoryitems\">";
  				  foreach ($Courses as $c3) {
              echo "<li>";
              printCourseTitle($c3);
              printTeacherInfo($c3->id,$c3->contextid); 
              echo "</li>";
  					}
  					//echo "</ul>";		
  				}				
          echo "<br />"	;				

          if (isadmin() || (has_capability('moodle/course:create',$c->context))) { 
            addCourseLink($c->id);
          }       
          if (isadmin() || (has_capability('moodle/category:manage',$c->context))) { 
            addCourseMLink($c->id);
          } 
              
  				echo "</ul>";
  			 									
        } // END OF FOR EACH 
  		} else { // END OF IF-THEN SUB CATEGORIES
  		  if ($_REQUEST['courses']=='all') {
          $errorMsg = "There are no modules listed";
  		  } else{
          $errorMsg = "You are not enrolled on any modules<br />";
        }
        if ($acYr != '') $errorMsg .= " for this " . ((($period!='ALL') && ($period!=''))?'period':'year') . " ({$acYr}" . ((($period!='ALL') && ($period!=''))?" {$period}":"") . ")<br />Try viewing <a href=\"/my/?academicyear=ALL\">ALL</a> your modules";

        notify($errorMsg, 'errorbox', 'center', false);   
    
  		}
  		
  		
  		// check if user has a role assignment that allows whole category listing 
  		// before doing anything!   This list is ordered by path
  		$catList = get_categories('none','cc.path');
  		
  		foreach ($rolesFullList as $role) {
        if (user_has_role_assignment($USER->id,$role)) {
          // OK, so they do, so now lets find out which categories they have access to  
          foreach ($catList as $cat) {
            if (user_has_role_assignment($USER->id,$role,$cat->context->id)) {
              //echo "user " . $USER->id . " has role " . $role . " in category " . $cat->id . " (context " . $cat->context->id . ")<br />";
              $catsArray[] = $cat->path;
            }
          }
        }
      }
      
      
      if (isset($catsArray)) {
        // OK, now we have an array of category paths that the user has access to
        
        // need to unique and re-sort
        $catArrayPaths = array_unique($catsArray);
        
        // now sort in path order
        sort($catArrayPaths);
        
        $parentID = 0;
        $lastParentID = 0;
        
        echo "<p>You have the following category level enrolments:</p>";
        
        // now ready to walk it, and construct lists as we go
        foreach ($catArrayPaths as $catPath) {
          
          // first part is parent, else last part
          $pathSplit = explode("/",$catPath);
          $parentID = $pathSplit[1];
          $lastElement = count($pathSplit) - 1;
          $catID = $pathSplit[$lastElement];
          
          
          
          // do we need a new major heading?
          if ($parentID != $lastParentID) {
            if ($lastParentID > 0) echo "</ul>\n"; // close a previous heading 
            getCatHeading($parentID);
            echo "<ul class=\"categoryitems\">\n";    
          }

          // change of logic needed
          if (count($pathSplit) < 3) {  // major category
            $subcatarray = array();
            getEnrolmentsByCat($catID);
            getCatList($catID,$acYr,$period);
            // now show all sub category courses too ...
            $subcats = get_records_sql("SELECT id,name FROM {$CFG->prefix}course_categories WHERE parent={$catID} ORDER BY name");
            //$s = get_records_select('COURSE_CATEGORIES','parent=' . $catID);
            if ($subcats) {
              foreach ($subcats as $subcatID) {
                getCatSubHeading($subcatID->id, TRUE);  
                echo "  <ul class=\"subcategoryitems\">\n";
                getEnrolmentsByCat($subcatID->id);
                getCatList($subcatID->id,$acYr,$period);
                echo "  </ul>\n";
                // add category to an array for checking in next step
                $subcatarray[] = $subcatID->id;
              }
            }
            
            // now show manage links:
            $c = get_context_instance(CONTEXT_COURSECAT,$catID);
            if (isadmin() || (has_capability('moodle/course:create',$c))) { 
              addCourseLink($catID);
            }       
            if (isadmin() || (has_capability('moodle/category:manage',$c))) { 
              addCourseMLink($catID);
            }
            
          } else { // OK so it's a subcategory, show all courses but ONLY IF this subcat not already been shown!
          // this is needed if only the subcat has an enrolment and not the major cat
            if (!in_array($catID,$subcatarray)) {
              getCatSubHeading($catID, TRUE);
              echo "  <ul class=\"subcategoryitems\">\n";
              getEnrolmentsByCat($catID);
              getCatList($catID,$acYr,$period);
              echo "  </ul>\n";
            }
          }           
          
          /* this section redundant now as we are listing ALL sub cats anyway
          
          // is it a second level category?
          if (count($pathSplit) > 2) {
            getCatSubHeading($catID);
            echo "  <ul class=\"subcategoryitems\">\n";
            getEnrolmentsByCat($catID);
            getCatList($catID,$acYr,$period);
            echo "  </ul>\n";
          } else {
            getEnrolmentsByCat($catID);
            getCatList($catID,$acYr,$period);
          }   
          
          */      

          $lastParentID = $parentID;
          

            
        }
      }
 		
  		echo "</div>";
      // end course lists using accordion method

    if ($userStatus != 'U') {     // only for STAFF   - this is not set now so everyone sees this
      echo "<p class=\"smalltext\"><img src=\"{$CFG->wwwroot}/my/images/switch_arrow.gif\" /> <a href=\"{$CFG->wwwroot}/course/index.php\">Categories List</a></p>";
    } 

    print_course_search();
/// end main middle column

    print_container_end();
    echo '</td>';
    
            break;
            case 'right':
            
    $blocks_preferred_width = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), BLOCK_R_MAX_WIDTH);

    
    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing()) {
        echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="right-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
        print_container_end();
        echo '</td>';
    }
            break;
        }
    }


    /// Finish the page
    
    echo '</tr></table>';    
    print_footer();

    // functions for formatting course lists
    
    function printCourseTitle($course) {
      global $CFG;
      $shortName = shortName($course->shortname);
      if ($course->visible == '0') $classHidden = "hidden";
      echo "<a class=\"courselink " . (($course->visible == 0) ? 'hidden' : '') . "\" href=\"" . $CFG->wwwroot . "/course/view.php?id={$course->id}\">{$course->fullname}</a>"; 
    }          
  
    function printTeacherInfo($cid,$contextid) {
      global $CFG;
      $t = array();
      $teacherString = '';
      if (isset($contextid) && ($contextid > 0)) {
        $queryUsers = "select {$CFG->prefix}USER.id,{$CFG->prefix}USER.firstname,{$CFG->prefix}USER.lastname FROM {$CFG->prefix}USER LEFT JOIN {$CFG->prefix}ROLE_ASSIGNMENTS ON {$CFG->prefix}USER.id={$CFG->prefix}ROLE_ASSIGNMENTS.userid WHERE {$CFG->prefix}ROLE_ASSIGNMENTS.roleid=3 AND {$CFG->prefix}ROLE_ASSIGNMENTS.contextid={$contextid} AND {$CFG->prefix}ROLE_ASSIGNMENTS.hidden=0"; 
        if (($userList = get_records_sql($queryUsers)) != false) {
          $teacherString = "<li class=\"teacherInfo\">Teachers: ";
          foreach ($userList as $c) {
            $t[] = "<a href=\"/user/view.php?id=" . $c->id . "\">" . $c->firstname . " " . $c->lastname . "</a>";
          }
          $teacherString .= implode(', ',$t) . "</li>";
        }
        echo $teacherString;
      }
    }

    function shortName($shortName) {
      $yr=''; $prd=''; $ccode='';
      $p = explode('_',$shortName);       // split shortname by _ into array
      $n = count($p);                     // count array parts   
      // get year if it exists
      // looks at last part of shortName after last _ and checks for year format YYYY-YY
      if ( preg_match('/[0-9]{4}-[0-9]{2}/',($p[$n-1]))) {
        $yr = $p[$n-1];
      } 
      // get period if it exists
      if (($n>1) && (strtoupper(substr($p[$n-2],0,3)) == 'PRD') ) {
        $prd = strtoupper($p[$n-2]);  
      }
      $carray = array("code" => $ccode,"period" => $prd,"year" => $yr);
      return $carray;
    }
    
    function addCourseLink ($cid) {
       global $CFG;
       echo "<span class=\"smalltext\"><img align=\"texttop\" alt=\"add new module\" src=\"{$CFG->wwwroot}/my/images/switch_plus.gif\" /> <a title=\"add new module\" href=\"{$CFG->wwwroot}/course/edit.php?category={$cid}\">add new</a></span>&nbsp;&nbsp;";  
    }
    function addCourseMLink ($cid) {
       global $CFG;
       echo "<span class=\"smalltext\"><img align=\"texttop\" alt=\"manage category\" src=\"{$CFG->wwwroot}/my/images/switch_arrow.gif\" /> <a title=\"manage category\" href=\"{$CFG->wwwroot}/course/category.php?categoryedit=on&id={$cid}\">manage</a></span>";  
    }

    function getCatHeading ($cid) {
      $cat = get_record('COURSE_CATEGORIES','id',$cid);
      echo "<h3 class=\"menuheader expandable" . (($cat->visible == 0)?' hidden':'') . "\">" . $cat->name . "</h3>";
    }
    
    function getCatSubHeading ($cid,$manage) {
      if (!isset($manage)) $manage = FALSE;
      $cat = get_record('COURSE_CATEGORIES','id',$cid);
      echo "<h4 class=\"sub_menuheader" . (($cat->visible == 0)?' hidden':'') . "\">" . $cat->name . " ";
      if ($manage == TRUE) {
        $c = get_context_instance(CONTEXT_COURSECAT,$cid);
        if (isadmin() || (has_capability('moodle/course:create',$c))) { 
          addCourseLink($cid);
        }       
        if (isadmin() || (has_capability('moodle/category:manage',$c))) { 
          addCourseMLink($cid);
        }
      }
      echo "</h4>";
    }
    
    function getCatList ($cid,$acYr,$period) {
      GLOBAL $CFG;
      //$courses = get_courses($cid, 'c.fullname', 'c.id,c.shortname,c.fullname,c.visible');
      //$courses = get_records('course','category',$cid);
      if (($acYr == '') || ($acYr == 'ALL')) $acYr = '%';
      if (($period == '') || ($period == 'ALL')) {
        $periodx = '%';
      } else {
        $periodx = $period;
      }
      $qry =  "SELECT id,shortname,fullname,visible FROM {$CFG->prefix}course WHERE category={$cid} AND shortname LIKE '%_{$acYr}%' AND shortname LIKE '%_{$periodx}_%' ORDER BY fullname,id"; 
      $courses = get_records_sql($qry);
       
      if ($courses) {
        foreach ($courses as $course) {
          // need to apply year filter here becasue we can'ty do it with 
          echo "<li>";
          printCourseTitle($course);
          echo "</li>";
        } 
      } else {
        if (preg_match('/[0-9]{4}-[0-9]{2}/',$acYr)) {
          echo "<div class=\"errorbox\">No modules found for this " . ((($period!='ALL') && ($period!=''))?'period':'year') . " ({$acYr}" . ((($period!='ALL') && ($period!=''))?" {$period}":"") . ")</div>";
        } else {
          echo "<div class=\"errorbox\">No modules found</div>";
        }
      }
    }
    
    function getEnrolmentsByCat ($cid) {
      global $USER;
      $ctx = get_context_instance(CONTEXT_COURSECAT,$cid);
      $e = get_records_select('ROLE_ASSIGNMENTS','userid=' . $USER->id . ' AND contextid=' . $ctx->id);
      if ($e) {
        echo 'Enrolments: ';
        foreach ($e as $ra) {
          $r = get_record('ROLE','id',$ra->roleid);
          $names[] = $r->name;
        }
        $nameString = implode(' / ',$names);
        echo $nameString;
      }
    }
                                                                                                                                
?>


