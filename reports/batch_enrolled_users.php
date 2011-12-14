<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find enrolled users in WS category
*/
global $CFG, $DB, $USER;

$courses = get_courses(2, 'fullname ASC', 'c.id, c.shortname');

foreach($courses as $course){
    $id = $course->id;

    $users = $DB->get_records('block_timetracker_workerinfo', array('courseid'=>$id));

    foreach($users as $user){
        echo 
            '"'.$user->lastname.'",'.
            '"'.$user->firstname.'",'.
            '"'.$user->email.'",'.
            '"'.$user->active.'",'.
            '"'.$user->idnum.'",'.
            '"'.$course->shortname.'"'."\n";
    }

}
