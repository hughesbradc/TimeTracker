<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once('lib.php');

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
    
        $remaining = $worker->maxtermearnings - $earnings;

        $course = $DB->get_record('course',array('id'=>$worker->courseid));
        $context = get_context_instance(CONTEXT_COURSE, $worker->courseid);

        $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
        /*
        if(!$teachers){
            echo ('No supervisor is enrolled in the course.');
        }
        */
            
        $supervisor = '';
        $email = ''; 

        foreach ($teachers as $teacher) {
            if(is_enrolled($context, $teacher->id)){
                $supervisor .= $teacher->firstname.' '.$teacher->lastname .','.$teacher->email.',';
            }
        }
        $supervisor = substr($supervisor,0,-1);
        
        echo $worker->lastname.','.$worker->firstname.','.$earnings.','.$worker->maxtermearnings
            .','.$remaining.','.$course->shortname.','.$supervisor."\n";
    }
?>
