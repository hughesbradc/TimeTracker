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
        foreach($units as $unit){
            if(overlaps($unit->timein, $unit->timeout, $worker->id, $unit->id)){
                echo ("**ERROR**\nUnit id: $unit->id\n*********");
            }
        }
    }
    //echo "Done checking courseid: $cid\n\n\n";
}
