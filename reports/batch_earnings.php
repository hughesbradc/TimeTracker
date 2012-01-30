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
    "DET,DETCode,ID,Hours,Amount,Budget,Department,Last Name,First Name,Max Term Earnings,Remaining \n";

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

        $units = get_split_units($start, $end, $worker->id, $course->id);

        $info = break_down_earnings($units);

        $remaining = $worker->maxtermearnings - $info['earnings']; 
        if($remaining < 0) $remaining = 0;

        //print_object($info); 
        
        if ($info['regearnings'] > 0){
            $contents =
                "E,Reg,$worker->idnum,".$info['reghours'].','.$info['regearnings'].','.
                "$worker->budget,$course->shortname,$worker->lastname,$worker->firstname,".
                "$worker->maxtermearnings,$remaining\n";
            echo $contents;
        }

        if($info['ovtearnings'] > 0){
            $contents =
                "E,OT,$worker->idnum,".$info['ovthours'].','.$info['ovtearnings'].','.
                "$worker->budget,$course->shortname,$worker->lastname,$worker->firstname,".
                "$worker->maxtermearnings,$remaining\n";
            echo $contents;
        } 

    }

}
?>
