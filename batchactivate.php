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
    3 - courseID, optional. If not supplied, assumed user only exists in one course.

    ****WORKER WITH THIS ID MUST EXIST!
*/

if(($handle = fopen("/tmp/sss_activate.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email = $data[0].'@mhc.edu';
        $currpay = $data[1];
        $maxterm = $data[2];
        $courseid = -1;
        if(sizeof($data) > 3){
            $courseid = $data[3];
        }

        if($courseid < 0){
            //TODO -- hack because some users already enrolled in SSS course
            $currentworker = $DB->count_records_select('block_timetracker_workerinfo', 
                'email=\''.$email.'\' AND courseid<>76');
        } else {
            $currentworker = $DB->count_records('block_timetracker_workerinfo', 
                array('email'=>$email,'courseid'=>$courseid));
        }

        if($currentworker > 1){
            echo '****More than 1 matching record. Skipping '."$email\n";
        } else if($currentworker == 0){
            echo '****No matching record. Skipping '."$email\n";
        } else {
            if($courseid < 0){
                //TODO -- hack because some users already enrolled in SSS course
                $worker = $DB->get_record_select('block_timetracker_workerinfo', 
                    'email=\''.$email.'\' AND courseid<>76');
            } else {
                $worker = $DB->get_record('block_timetracker_workerinfo', 
                    array('email'=>$email,'courseid'=>$courseid));
            }
            if($worker){
                $worker->currpayrate = $currpay;
                $worker->maxtermearnings = $maxterm;

                print_object($worker);

                /*
                $res = $DB->update_record('block_timetracker_workerinfo', $worker);
    
                if(!$res){
                    echo 'ERROR activating worker: '."$data[0]\n";
                } else {
                    echo 'Successfully activated: '."$data[0]\n";
			    }
                */
            } else {

                echo 'Activation failed for '.$data[0]."\n";
            }

        }

    }
    fclose($handle);
}

