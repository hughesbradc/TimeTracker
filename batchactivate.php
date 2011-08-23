<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

/**
 The purpose of this script is to update maxtermhours, currpay, and active status of
 a CSV list of students
*/
global $CFG, $DB, $USER;

/*
    0 - ID (first part of email, e.g. s000111111)
    1 - current pay rate
    2 - max term pay
*/

if(($handle = fopen("/tmp/workers.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email = $data[0].'@mhc.edu';
        $currpay = $data[1];
        $maxterm = $data[2];

        $currentworker = $DB->count_records('block_timetracker_workerinfo', 
            array('email'=>$email));

        if($currentworker != 0){
            print_r('ERROR'.$data);
        } else {
            $worker = $DB->get_record('block_timetracker_workerinfo', 
                array('email'=>$email);
            $worker->currpayrate = $currpay;
            $worker->maxtermearnings = $maxterm;

            print_object($worker);

            /*
            $res = $DB->update_record('block_timetracker_workerinfo', $worker);

            if(!$res){
                echo 'ERROR updating worker';
                print_r($data);
            }
            */

        }

    }
    fclose($handle);
}

