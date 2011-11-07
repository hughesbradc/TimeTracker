<?php

//define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

require_login();

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

//find all workers
$workers = $DB->get_records('block_timetracker_workerinfo');

$filename = date("Y_m_d").'_Earnings.xls';
$header = "Department \tLast Name \tFirst Name \tEarnings \tMax Term Earnings \n";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);
echo $header;

foreach($workers as $worker){
    //demo courses, et. al.
    if($worker->courseid >= 73 && $worker->courseid <= 76){
        continue;
    }

    $earnings = get_earnings_this_term($worker->id,$worker->courseid);
    $course = $DB->get_record('course', array('id'=>$worker->courseid));
    
    $contents = "$course->shortname \t $worker->lastname \t $worker->firstname \t"
        ."$earnings \t $worker->maxtermearnings \n";

    //Export Data to Simple XLS file
    echo $contents;
}
?>
