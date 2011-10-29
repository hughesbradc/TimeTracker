<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB, $USER;

//iterate through each userid/courseid combo, and check all of their work units for
//overlaps? 
//Might just use brute force as of right now -- n^3 at best, right?

//go on a course-by-course basis
$courses = $DB->get_records_sql(
    'SELECT DISTINCT courseid FROM mdl_block_timetracker_workunit ORDER BY courseid');

foreach($courses as $course){

    $cid = $course->courseid;
    $fullcourse = $DB->get_record('course',array('id'=>$cid));

    if($cid == 73) continue; //skip demo course

    //get workers for this course
    $workers = $DB->get_records_sql(
        'SELECT * FROM mdl_block_timetracker_workerinfo WHERE courseid='.$cid);

    //each worker for this course
    //echo "Checking courseid: $cid\n";
    foreach($workers as $worker){
        
        //get all workunits for this course for this worker
        $wid = $worker->id;

        $units  = $DB->get_records('block_timetracker_workunit',
            array('courseid'=>$cid,'userid'=>$wid));
        //$count = 0;
        foreach($units as $unit){
            //echo ($unit->timein."\t".$unit->timeout."\n");
            //check to see if the unit is between starts after 12am & ends before 7am

            if($unit->timeout <= usergetmidnight($unit->timeout)+(7*3600)){
                /*
                echo ("<br />\n**ERROR**<br />\n");
                echo ("Department: $fullcourse->shortname<br />\n");
                echo("User: $worker->firstname $worker->lastname<br />\n");
                //echo("wuid: $unit->id<br />\n");
                echo('In: '.userdate($unit->timein, get_string('datetimeformat',
                    'block_timetracker')).' Out: '.
                    userdate($unit->timeout, get_string('datetimeformat',
                    'block_timetracker'))."\n<br />");
                echo("<a href=\"http://moodle.mhc.edu/workstudy/blocks/timetracker/".
                    "editunit.php?id=$cid&userid=$worker->id&unitid=$unit->id\">
                    Edit this unit</a><br />\n");
                echo("<a href=\"http://moodle.mhc.edu/workstudy/blocks/timetracker/".
                    "deleteworkunit.php?id=$cid&userid=$worker->id&unitid=$unit->id\">
                    Delete this unit</a><br />\n");
                echo("*********<br />\n");
                */
                echo "$fullcourse->shortname,$worker->firstname $worker->lastname,".
                    userdate($unit->timein, get_string('datetimeformat',
                    'block_timetracker')).','.
                    userdate($unit->timeout, get_string('datetimeformat',
                    'block_timetracker'))."\n";
            }

        }
        //echo("Checked $count units\n");

    }
    //echo "Done checking courseid: $cid\n\n\n";
}
