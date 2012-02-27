<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to add a single work unit to each worker in a course.
*/
global $CFG, $DB, $USER;

$courseid = 95; //residential living
//$courseid = 111; //SGA
//$courseid = 105;//Judicial
//$courseid = 112; //test site
//$courseid = 119; //Dept_Gateway

$duration = 1 * 3600; //1 hour
//$date = 1; //put unit on first day of month

$startmonth=02;
$startyear=2012;

$endmonth=02;
$endyear=2012;

//TODO -- Put where 'deleted=0' eventually here
$courseworkers = $DB->get_records('block_timetracker_workerinfo',
    array('courseid'=>$courseid));

$newunit = new stdClass();
$newunit->courseid = $courseid;
$newunit->lasteditedby = 0;
$newunit->lastedited = time();

$startinfo = get_month_info($startmonth, $startyear);
$endinfo = get_month_info($endmonth, $endyear);

$starttime = $startinfo['firstdaytimestamp'];

if($startinfo['firstdaytimestamp'] <= $endinfo['firstdaytimestamp']){
    do {
        $newunit->timein = $starttime;
        $newunit->timeout = $starttime + $duration;
    
        foreach($courseworkers as $worker){
            $newunit->payrate = $worker->currpayrate;
            $newunit->userid = $worker->id;
            echo "adding a unit for $worker->firstname $worker->lastname\n";
            $res = $DB->insert_record('block_timetracker_workunit', $newunit);
            if(!$res){
                error_log("failed inserting new work unit for ".
                    "$worker->firstname $worker->lastname");
                exit;
            }
        }
    
        $starttime = strtotime('+ 1 month', $starttime);
    } while ($starttime <= $endinfo['firstdaytimestamp']);
} 
