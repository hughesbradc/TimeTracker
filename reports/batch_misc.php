<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

//find all workers
//$workers = $DB->get_records('block_timetracker_workerinfo', array(), 'lastname');

//foreach($workers as $worker){
if(($handle = fopen("2011_11_08FINAL_SUBMISSION.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
    
        $id = $data[0];
        $hours = $data[1];
        $userid = $data[2];
        $last = $data[3];
        $first = $data[4];
        $dept = $data[5];
    
        $email = $userid.'@mhc.edu';
    
        $sql = "SELECT * FROM ".$CFG->prefix."block_timetracker_workerinfo WHERE ".
            'email=\''.$email.'\' AND courseid NOT IN (73, 74, 75, 76)';
        //error_log($sql);
        $worker = $DB->get_record_sql($sql);
        if($worker){
            echo '"'.$id.'","'.$hours.'","'.
                $worker->currpayrate.
                '","'.
                $last.
                '","'.
                $first.
                '","'.
                $dept.'"'."\n";
        } else {
            echo "NOT FOUND!! $email\n";
        }
    }
    
} else {
    echo("Error opening file\n");
}
