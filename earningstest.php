<?php

require_once('../../config.php');
require_once('lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/

global $CFG, $DB, $USER;

//Print results in a table
echo '<table cellspacing="10" cellpadding="5" width="85%">';
    echo '<tr>';
    echo '<td><b>Department</b></td>';
    echo '<td><b>Worker Name</b></td>';
    echo '<td><b>Earnings This Term</b></td>';
    echo '<td><b>Maximum Term Earnings</b></td>';   
    echo '<td><b>Amount Remaining</b></td>';
    echo '</tr>';

    //find all workers
    $workers = $DB->get_records('block_timetracker_workerinfo');

    foreach($workers as $worker){
        //demo courses, et. al.
        if($worker->courseid >= 73 && $worker->courseid <= 76){
            continue;
        }

        $earnings = get_earnings_this_term($worker->id,$worker->courseid);
    
        $course = $DB->get_record('course', array('id'=>$worker->courseid));
    
        $remaining = $worker->maxtermearnings - $earnings;

        $course = $DB->get_record('course',array('id'=>$worker->courseid));
        $PAGE->set_course($course);
        $context = $PAGE->context;
           
        $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
        if(!$teachers){
            echo ('No supervisor is enrolled in the course.');
        }
            
        $supervisor = '';
            
        foreach ($teachers as $teacher) {
            if(is_enrolled($context, $teacher->id)){
                $supervisor .= $teacher->firstname.' '.$teacher->lastname .' ' .$teacher->email
                .',';
            }
        }
        $supervisor = substr($supervisor,0,-1);
        
        echo '<tr><td>'.$course->shortname.'</td><td>'.$supervisor .'</td><td>'.$worker->lastname.', '
            .$worker->firstname.'</td><td>'.$earnings.'</td><td>'.$worker->maxtermearnings
            .'</td><td>'.$remaining.'</td></tr>';
    }

echo '</table>';

?>
