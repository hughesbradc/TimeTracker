<?php

//define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

require_login();

$catid = required_param('catid', PARAM_INTEGER);
$start = required_param('start', PARAM_INTEGER);
$end = required_param('end', PARAM_INTEGER);
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


$categoryinfo = $DB->get_record('course_categories', array('id'=>$catid));
$catname = str_replace(' ','', $categoryinfo->name);

$filename = date("Y_m_d").'_'.$catname.'_Earnings.csv';
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

//$headers = "Department,Last Name,First Name,Email,Earnings,Max Term Earnings,Remaining \n";
$headers = 
    "Department,Last Name,First Name,ID,Budget,Hours,Earnings,Max Term Earnings,Remaining \n";

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
    
        $earnings = get_earnings($worker->id, $course->id, $start, $end);
        $hours = get_hours_this_period($worker->id, $course->id, $start, $end);

        $remaining = $worker->maxtermearnings - $earnings; 
        if($remaining < 0) $remaining = 0;

        $contents =
        "$course->shortname,$worker->lastname,$worker->firstname,$worker->idnum,"
            ."$worker->budget,$hours,$earnings,$worker->maxtermearnings,$remaining \n";
    
        //Export Data to Simple XLS file
        echo $contents;
    }

}
?>
