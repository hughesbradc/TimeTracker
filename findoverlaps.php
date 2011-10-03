<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once('lib.php');

global $CFG, $DB, $USER;

//iterate through each userid/courseid combo, and check all of their work units for
//overlaps? 
//Might just use brute force as of right now -- n^3 at best, right?

//go on a course-by-course basis
$courses = $DB->get_records_sql(
    'SELECT DISTINCT courseid FROM mdl_block_timetracker_workunit ORDER BY courseid');

foreach($courses as $course){

    $cid = $course->courseid;
    if($cid == 73) continue; //skip demo course

    //get workers for this course
    $workers = $DB->get_records_sql(
        'SELECT id FROM mdl_block_timetracker_workerinfo WHERE courseid='.$cid);

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
            if(overlaps($unit->timein, $unit->timeout, $worker->id, $unit->id, $cid)){
                echo ("<br />\n**ERROR**<br />\n");
                echo ("cid: $cid<br />\n");
                echo("uid: $worker->id<br />\n");
                echo("wuid: $unit->id<br />\n");
                echo("<a href=\"http://moodle.mhc.edu/workstudy/blocks/timetracker/".
                    "reports.php?id=$cid&userid=$worker->id\">View Reports Page</a><br />\n");
                echo("*********<br />\n");
            }

        }
        //echo("Checked $count units\n");

    }
    //echo "Done checking courseid: $cid\n\n\n";
}
