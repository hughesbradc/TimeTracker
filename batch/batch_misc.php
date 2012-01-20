<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

/*
$courses = get_courses(2, 'fullname ASC', 'c.id,c.shortname');


foreach($courses as $course){
    */

    /*
    //delete old alerts
    $alerts = $DB->get_records_select('block_timetracker_alertunits',
        'courseid='.$course->id.' AND alerttime < 1324416245');

    
    if(sizeof($alerts) > 0){

        foreach($alerts as $alert){
            $DB->delete_records('block_timetracker_alert_com',
                array('alertid'=>$alert->id));

            $DB->delete_records('block_timetracker_alertunits',
                array('id'=>$alert->id));
        }

    }
    */

    $workers = $DB->get_records('block_timetracker_workerinfo');
    foreach($workers as $worker){
        
        $worker->email = str_replace('S000','s000', $worker->email);
        //$worker->active = 0;
        $res = $DB->update_record('block_timetracker_workerinfo', $worker);
        if(!$res){
            echo "Error updating $worker->firstname $worker->lastname\n";
        }

    }


    /*
    $workers = $DB->get_records('block_timetracker_workerinfo', 
        array('courseid'=>$course->id));
    */

    /*
    foreach($workers as $worker){
        
        $worker->active = 0;
        $res = $DB->update_record('block_timetracker_workerinfo', $worker);
        if(!$res){
            echo "Error updating $worker->firstname $worker->lastname\n";
        }

    }
    */


    /*
    //Generate a list of workers that have > 0 work units
    $workers = $DB->get_records('block_timetracker_workerinfo', 
        array('courseid'=>$course->id));

    foreach($workers as $worker){
        $num = $DB->count_records('block_timetracker_workunit',
            array('userid'=>$worker->id));
        if($num > 0){
            echo "$worker->id, $num, $worker->firstname, $worker->lastname, $worker->email\n";
        }

    }
    */


    /*
    //change round to 900 seconds
    $config = $DB->get_record('block_timetracker_config',
        array(
            'courseid'=>$course->id,
            'name'=>'block_timetracker_round'));

    $config->value = '900';

    $DB->update_record('block_timetracker_config', $config);
    */

    /*
    update each worker's maxterm to 0
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
    
/*
}
*/
