<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courses = get_courses(2, 'fullname ASC', 'c.id, c.shortname');
$MONTH = 12;
$YEAR = 2011;
$FILE='/tmp/december.csv';

//foreach($workers as $worker){
if(($handle = fopen($FILE, "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $fileid = $data[2];  //student ID field
        $fileid = strtolower($fileid);

        $email = $fileid.'@mhc.edu';

    
        $workers = $DB->get_records('block_timetracker_workerinfo', array('email'=>$email));
    
        if(!$workers){
            error_log("No record for $email\n");
        }
        foreach($workers as $worker){

            $course = $courses[$worker->courseid];
            if(!$course){ //not a course in this category;
                continue;
            }

            $earnings = get_hours_this_month($worker->id, $worker->courseid, $MONTH, $YEAR);
        

            //$id = str_replace('@mhc.edu', '', $worker->email);
        
            echo '"'.$fileid.'","'.$earnings.'","'.$worker->lastname.'","'.
                $worker->firstname.'","'.$course->shortname.'"'."\n";
    
        }
    }
}
