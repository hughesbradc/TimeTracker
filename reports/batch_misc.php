<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB, $USER;

$courses = get_courses(2, 'fullname ASC', 'c.id, c.shortname');

foreach ($courses as $course){
    if($course->id >= 73 && $course->id <=76) continue;
    echo ('"'.$course->shortname.'","');

    //print_object($course);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    $students = get_enrolled_users($context, 'mod/assignment:submit');
    $supervisors = get_enrolled_users($context, 'mod/assignment:grade');

    echo(sizeof($students).'","');
    $sups = '';
    foreach($supervisors as $supervisor){
        $sups .= $supervisor->firstname.' '.$supervisor->lastname.'","'.
            $supervisor->email.'","';
    }

    $sups = substr($sups, 0, strlen($sups)-2);
    $sups .= "\n";
    echo $sups;
}
