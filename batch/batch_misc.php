<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$manual = enrol_get_plugin('manual');

$instances = enrol_get_instances(113, false);
/*
foreach($instances as $instance){
    if($instance->enrol == 'manual'){
        $winner = $instance;
        break;
    }
}
*/
//print_r($winner);

//$context = get_context_instance(CONTEXT_COURSE, 113);

if(isset($winner))
    $manual->unenrol_user($winner, 3);


//unenrol_user


/*
$courses = get_courses(5, 'fullname ASC', 'c.id,c.shortname');


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

    //$workers = $DB->get_records('block_timetracker_workerinfo');
    $workers = $DB->get_records('user');
    foreach($workers as $worker){
        
        $worker->email = str_replace('S000','s000', $worker->email);
        if($worker->lastname == 'Hedberg' ||
            $worker->lastname == 'Freeman')
            print_object($worker);
        //$worker->active = 0;
        //$res = $DB->update_record('block_timetracker_workerinfo', $worker);
        //if(!$res){
            //echo "Error updating $worker->firstname $worker->lastname\n";
        //}

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
}
