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

$start = 1317441600;
$end = time();

$count = 0;
foreach($courses as $course){

    $cid = $course->courseid;
    if($cid >= 73 &&  $cid <= 76) continue; //skip demo courses

    $sql = 
        'SELECT * FROM mdl_block_timetracker_workerinfo WHERE courseid='.$cid.
            ' ORDER BY lastname,firstname';

    //get workers for this course
    $workers = $DB->get_records_sql($sql);

    //each worker for this course
    //echo "Checking courseid: $cid\n";
    foreach($workers as $worker){
        
        //get all workunits for this course for this worker
        $wid = $worker->id;
        
        $sql = 'SELECT * from '.$CFG->prefix.'block_timetracker_workunit '.
            'WHERE courseid='.$cid.' AND userid='.$wid.' AND timeout BETWEEN '.
            $start.' AND '.$end;

        $units  = $DB->get_records_sql($sql);
        //$count = 0;
        foreach($units as $unit){
            //echo ($unit->timein."\t".$unit->timeout."\n");
            if(overlaps($unit->timein, $unit->timeout, $worker->id, $unit->id, $cid)){
                echo ("<br />\n**ERROR**<br />\n");

                //echo ("cid: $cid<br />\n");
                //echo("uid: $worker->id<br />\n");
                echo("Worker ID: $unit->id<br />\n");
                echo("Modified by ID <a href=\"http://moodle.mhc.edu/workstudy".
                    "/user/profile.php?id=$unit->lasteditedby".
                    "\">$unit->lasteditedby</a><br />\n");
                echo("Modified: ". userdate($unit->lastedited,
                    get_string('datetimeformat','block_timetracker'))."<br />\n");
                echo("Worker: ".$worker->lastname.', '.$worker->firstname."<br />\n");
                echo("timein: ".userdate($unit->timein,
                    get_string('datetimeformat','block_timetracker'))."<br />\n");
                echo("timeout: ".userdate($unit->timeout,
                    get_string('datetimeformat','block_timetracker'))."<br />\n");
                echo("<a href=\"http://moodle.mhc.edu/workstudy/blocks/timetracker/".
                    "reports.php?id=$cid&userid=$worker->id\">View Reports Page</a><br />\n");
                echo("*********<br />\n");
                $count++;
            }

        }
        //echo("Checked $count units\n");

    }
    //echo "Done checking courseid: $cid\n\n\n";
}
echo "$count units detected<br />\n";
