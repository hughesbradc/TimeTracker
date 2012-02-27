<?php


define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB, $USER;

/**
    The purpose of this script is to import all student schedules
    after clearing out the existing schedules

    CSV file needs the following format
        studentID,Course Desc,days*,start time** (Military format, end time (military format)

    *days will be in the format: M or MWF or MW or T or TR or MtoF or R etc
    **start time will be a 3/4 digit number i.e. 1200 130 1330 etc
*/

//$file='2012SpringStudentSchedules.csv';
$file='updatedCourseSchedules.csv';

$count = 0;
if(($handle = fopen($file, "r")) !== FALSE){
    $scheduleitems = array();
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $studentid  = strtolower($data[0]); 
        $coursedesc = $data[1];
        $days       = $data[2];
        $start      = $data[3];
        $end        = $data[4];
        $sdate      = $data[5];
        $edate      = $data[6];

        $entry              = new stdClass();
        $entry->studentid   = $studentid;
        $entry->course_code = $coursedesc;
        $entry->days        = $days;
        $entry->begin_time  = $start;
        $entry->end_time    = $end;
        $entry->begin_date  = $sdate;
        $entry->end_date    = $edate;

        $scheduleitems[] = $entry;
    }

    if(sizeof($scheduleitems) > 0){
        echo 'About to process '.sizeof($scheduleitems).' schedule items'."\n";
        //if we have some, then wipe the old entries, and add the new
        $DB->delete_records('block_timetracker_schedules');
        
        foreach($scheduleitems as $item){
            //print_object($item);
            $res = $DB->insert_record('block_timetracker_schedules', $item, true, true);
            if($res) $count++;
        }
    }
}
echo "Handled $count schedule items\n";
