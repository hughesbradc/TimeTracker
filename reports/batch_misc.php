<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courses = get_courses(4, 'fullname ASC', 'c.id');
echo sizeof($courses)." courses\n";

foreach($courses as $course){
    $id = $course->id;

    $users = $DB->get_records('block_timetracker_workerinfo', array('courseid'=>$id));

    foreach($users as $user){
        $user->email = strtolower($user->email);
        $user->idnum = strtolower($user->idnum);
        $user->idnum = str_replace('s000', '', $user->idnum);
        //print_object($user);
        $worked = $DB->update_record('block_timetracker_workerinfo', $user);
        if(!$worked){
            echo "Did not update $user->firstname $user->lastname correctly\n";
        }
    }
}
