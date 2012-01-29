<?php


define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('check_student_schedules.php');



$conflicts = check_worker_hours_for_conflicts(4, 1325438560, 1328030560);

if($conflicts){
    foreach ($conflicts as $conflict){
        print_object($conflict);
    }
}

?>
