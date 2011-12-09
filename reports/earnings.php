<?php

require_once('../../../config.php');
require_once('../lib.php');
require_login();

$cat = required_param('catid', PARAM_INT);

/**
 The purpose of this script is to find earnings/max earnings for this term
*/

global $CFG, $DB, $USER;

$catid = 2;
$context = get_context_instance(CONTEXT_COURSECAT), $catid; 
$PAGE->set_context($context);

//$context = $PAGE->context;

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

        echo '<tr><td>'.$course->shortname.'</td><td>'.$worker->lastname.', '
            .$worker->firstname.'</td><td>'.$earnings.'</td><td>'.$worker->maxtermearnings
            .'</td><td>'.$remaining.'</td></tr>';
    }

echo '</table>';

?>
