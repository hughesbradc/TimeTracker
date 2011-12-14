<?php

//define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

require_login();

$catid = required_param('catid', PARAM_INTEGER);
$active = optional_param('active', 0, PARAM_INTEGER);

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

$categoryinfo = $DB->get_records('course_categories', array('id'=>$catid));

$filename = date("Y_m_d").'_'.$categoryinfo->name.'_Earnings.csv';
$header = "Department ,Last Name ,First Name ,Earnings ,Max Term Earnings \n";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

$headers = "Department,Last Name,First Name,Email,Earnings,Max Term Earnings,Remaining \n";
echo $headers;

$courses = get_courses($catid, 'fullname ASC', 'c.id, c.shortname');
foreach($courses as $course){

    if($active){
        //find all active workers
        $workers = $DB->get_records('block_timetracker_workerinfo', array('active'=>1,
            'courseid'=>$course->id));
    } else {
        //find all workers
        $workers = $DB->get_records('block_timetracker_workerinfo',
            array('courseid'=>$course->id));
    }
    
    
    foreach($workers as $worker){
        //demo courses, et. al.
        if($worker->courseid >= 73 && $worker->courseid <= 76){
            continue;
        }
    
        $earnings = get_earnings_this_term($worker->id, $course->id);
        $remaining = $worker->maxtermearnings - $earnings; 
        $contents = "$course->shortname,$worker->lastname,$worker->firstname,$worker->email,"
            ."$earnings,$worker->maxtermearnings,$remaining \n";
    
        //Export Data to Simple XLS file
        echo $contents;
    }

}
?>
