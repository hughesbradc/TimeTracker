<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courseid = 95; //residential living
$courseid = 112; //test site

$duration = 1 * 3600; //1 hour
$date = 1; //put unit on first day of month

$startmonth=12;
$startyear=2011;

$endmonth=3;
$endyear=2012;

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
            $res = $DB->insert_record('block_timetracker_workunit', $newunit);
            if(!$res){
                error_log("failed inserting new work unit for $worker->firstname $worker->lastname");
                exit;
            }
        }
    
        $starttime = strtotime('+ 1 month', $starttime);
    } while ($starttime <= $endinfo['firstdaytimestamp']);
} 
