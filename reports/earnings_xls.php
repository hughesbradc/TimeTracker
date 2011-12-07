<?php

require_once(dirname(__FILE__) . '/../../../config.php');
require_once("$CFG->libdir/excellib.class.php");
require_once('../lib.php');
require_once('../../../lib/moodlelib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/

function generate_xls(){          
    //Would like to ask for term to generate report for.  Can we do this?
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
}//Close Function
