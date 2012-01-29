<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

/**
 The purpose of this script is to update maxtermhours, currpay, and active status of
 a CSV list of students
*/
global $CFG, $DB, $USER;

/*
    REQUIRED **
    0 - ID (workerinfo.id)
    OPTIONAL **
    1 - hours horked
    2 - first name
    3 - last name
    4 - email

    ****WORKER WITH THIS ID MUST EXIST!
*/

if(($handle = fopen("toReActivate.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){


        $id = $data[0];

        $worker = $DB->get_record('block_timetracker_workerinfo', 
            array('id'=>$id));

        if(!$worker){
            echo 'Worker '.$data[3].' '.$data[4].' does not exist';
        } else {
            $worker->active = 1;
            $result = $DB->update_record('block_timetracker_workerinfo', $worker);
            if(!$result){
                echo 'Error updating Worker '.$id. ','.$data[3].' '.$data[4];
            }

        }
    }
    fclose($handle);
}

