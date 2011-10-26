<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;



//find all workers
$workers = $DB->get_records('block_timetracker_workerinfo');

foreach($workers as $worker){
    //demo courses, et. al.
    if($worker->courseid >= 73 && $worker->courseid <= 76){
        continue;
    }

    $earnings = get_earnings_this_term($worker->id,$worker->courseid);
    
    $course = $DB->get_record('course', array('id'=>$worker->courseid));
    echo $course->shortname.','.$worker->lastname.','.$worker->firstname.
        ','.$earnings.','.$worker->maxtermearnings."\n";

}
