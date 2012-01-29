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

$file='2012SpringHolidays.csv';


$count = 0;
if(($handle = fopen($file, "r")) !== FALSE){
    $holidayitems = array();

    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $holidaydesc    = $data[0]; 
        $start          = $data[1];
        $end            = $data[2];

        $entry              = new stdClass();
        $entry->description = $holidaydesc;
        $entry->start       = $start;
        $entry->end         = $end;

        $holidayitems[] = $entry;
    }

    if(sizeof($holidayitems) > 0){
        echo 'About to process '.sizeof($holidayitems).' holidays'."\n";
        //if we have some, then wipe the old entries, and add the new
        $DB->delete_records('block_timetracker_holiday');
        
        foreach($holidayitems as $item){
            //print_object($item);
            $res = $DB->insert_record('block_timetracker_holiday', $item, true, true);
            if($res) $count++;
        }
    }
}
echo "Handled $count holidays\n";
