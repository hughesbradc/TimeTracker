<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courses = get_courses(2, 'fullname ASC', 'c.id, c.shortname');
//find all workers

foreach($courses as $course){
    $workers = $DB->get_records('block_timetracker_workerinfo', 
        array('courseid'=>$course->id), 'lastname');

    foreach($workers as $worker){
        //demo courses, et. al.
        if($worker->courseid >= 73 && $worker->courseid <= 76){
            continue;
        }

        $course = $courses[$worker->courseid];
        if(!$course) continue;
    
        $earnings = get_hours_this_month($worker->id, $worker->courseid, 9, 2011);
        //$earnings = get_earnings_this_term($worker->id, $worker->courseid);
        
        //$course = $DB->get_record('course', array('id'=>$worker->courseid));
        $id = str_replace('@mhc.edu', '', $worker->email);
        $id = str_replace('s000', '', strtolower($id));
    
        /*
        $remain = $worker->maxtermearnings - $earnings;
        if($remain <= 0){
            $hours_remain = 0;
        } else {
            $hours_remain = round($remain/$worker->currpayrate,2);
        }
        */

        echo '"'.$id.'","'.$earnings.'","'.$worker->lastname.'","'.
            $worker->firstname.'","'.$course->shortname.'"'."\n";
    
        /*
        echo '"'.$worker->lastname.'","'.$worker->firstname.'","'.
            $course->shortname.'","'.$earnings.'","'.$worker->maxtermearnings.'","'.
            $worker->currpayrate.'","'.
            $remain.'","'.$hours_remain.'"'.
            "\n";
        
        }
        */

    }
}
