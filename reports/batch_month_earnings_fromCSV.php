<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

//foreach($workers as $worker){
if(($handle = fopen("sept_to_BO.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $fileid = $data[2]; 
        $email = $fileid.'@mhc.edu';
    
        $workers = $DB->get_records('block_timetracker_workerinfo', array('email'=>$email));
    
        if(!$workers){
            error_log("No record for $email\n");
        }
        foreach($workers as $worker){

            //demo courses, et. al.
            if($worker->courseid >= 73 && $worker->courseid <= 76){
                continue;
            }

            $earnings = get_hours_this_month($worker->id,$worker->courseid, 9, 2011);
        
            $course = $DB->get_record('course', array('id'=>$worker->courseid));
            $id = str_replace('@mhc.edu', '', $worker->email);
        
            echo '"'.$id.'","'.$earnings.'","'.$worker->lastname.'","'.
                $worker->firstname.'","'.$course->shortname.'"'."\n";
    
        }
    }
}
