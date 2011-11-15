<?php

//define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

require_login();

$catid = required_param('catid', PARAM_INTEGER);

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;


//$courseid = required_param('id', PARAM_INTEGER);
//$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = get_context_instance(CONTEXT_COURSECAT, $catid);
$PAGE->set_context($context);

if (!has_capability('block/timetracker:manageworkers', $context)) { 
    print_error('You do not have permission to run this report.');
}

//find all workers
$workers = $DB->get_records('block_timetracker_workerinfo');

$filename = date("Y_m_d").'_Earnings.csv';
$header = "Department ,Last Name ,First Name ,Earnings ,Max Term Earnings \n";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

$headers = "Department,Last Name,First Name,Email,Earnings,Max Term Earnings,Remaining \n";
echo $headers;

foreach($workers as $worker){
    //demo courses, et. al.
    if($worker->courseid >= 73 && $worker->courseid <= 76){
        continue;
    }

    $earnings = get_earnings_this_term($worker->id, $worker->courseid);
    $course = $DB->get_record('course', array('id'=>$worker->courseid));
    $remaining = $worker->maxtermearnings - $earnings; 
    $contents = "$course->shortname,$worker->lastname,$worker->firstname,$worker->email,"
        ."$earnings,$worker->maxtermearnings,$remaining \n";

    //Export Data to Simple XLS file
    echo $contents;
}
?>
