<?
  /// Update course visibility according to startdate
  /// If startdate within last $updatePeriod hours and visibility=0 then set to visibility=1
  
  $startDateUpdate = true; // set to false to turn this off
  $updatePeriod = 48; // in hours
  if ($startDateUpdate == true) {    
    // first of all get list of courses to update
    mtrace("\n  Searching for courses to make visible ...");
    if ($courses = get_records_select("course", " visible=0 AND ((startdate < " . time() . ") AND (startdate > " . (time() - (60 * 60 * $updatePeriod)) . ")) ")) {
      foreach ($courses as $course) {
        if (! set_field("course", "visible", 1 , "id", $course->id)) {
          mtrace("    " . $course->id . ": " . $course->shortname . " could not be updated for some reason");
        } else {
          mtrace("    " . $course->id . ": " . $course->shortname . " is now visible");
        }
      }
    } else {
      mtrace("  Nothing to do");
    }
  }
  flush();
  

?>