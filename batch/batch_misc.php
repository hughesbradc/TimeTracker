<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courses = get_courses(5, 'fullname ASC', 'c.id,c.shortname');


foreach($courses as $course){
    $config = $DB->get_record('block_timetracker_config',
        array(
            'courseid'=>$course->id,
            'name'=>'block_timetracker_round'));

    $config->value = '900';

    $DB->update_record('block_timetracker_config', $config);
    /*
    $workers = $DB->get_records('block_timetracker_workerinfo',
        array('courseid'=>$course->id));

    foreach($workers as $worker){
        //echo $worker->idnum."\n";
        //$worker->idnum = str_replace('s000','', $worker->idnum);
        $worker->maxtermearnings = 0;
        $res = $DB->update_record('block_timetracker_workerinfo', $worker);
        //if(!$res) exit;
        if(!$res){
            echo 'error updateing '.$worker->firstname.' '.$worker->lastname."\n";
        }
        //echo $worker->idnum."\n";
    }
    */
    
}
