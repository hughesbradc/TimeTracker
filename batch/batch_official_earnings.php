<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find the official earnings over a given time period
*/
global $CFG, $DB, $USER;

$STARTMONTH = 1;
$STARTYEAR = 2012;
$STARTDAY = 1;

$ENDMONTH = 6;
$ENDYEAR = 2012;
$ENDDAY = 6;

$START=mktime(null,null,null,$STARTMONTH,$STARTDAY,$STARTYEAR);
$END=mktime(null,null,null,$ENDMONTH,$ENDDAY,$ENDYEAR);

$courses = get_courses(2, 'fullname ASC', 'c.id, c.shortname');

$str = implode (',', array_keys($courses));

$users = $DB->get_records_select('block_timetracker_workerinfo',
    'courseid in ('.$str.')');

$timesheets = $DB->get_records_select('block_timetracker_timesheet',
    'courseid in ('.$str.') AND workersignature between '.$START.' AND '.$END);

foreach ($timesheets as $timesheet){
    $worker = $users[$timesheet->userid];
    echo "$worker->firstname,$worker->lastname,$worker->email,";
    echo "$timesheet->submitted,".($timesheet->regpay+$timesheet->otpay)."\n";
}
