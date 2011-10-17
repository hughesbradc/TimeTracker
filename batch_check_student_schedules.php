
<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once('lib.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

global $CFG, $DB, $USER;

/*
    0 - username
    1 - course name
    2 - day(s)
    3 - start time (24H time)
    4 - end time (24H time)

    Checks for work units that occured during classes
*/

//start/stop time for search
$from=1314849600;
$to=time();
$datetimeformat='%m/%d/%y, %I:%M %p';


$day_names = array();
$day_names['m'] = 'Monday';
$day_names['t'] = 'Tuesday';
$day_names['w'] = 'Wednesday';
$day_names['r'] = 'Thursday';
$day_names['f'] = 'Friday';

if(($handle = fopen("/tmp/student_schedules.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email = $data[0].'@mhc.edu';
        $coursename = $data[1];
        $days_string = $data[2];
        $starttime = $data[3];
        $endtime = $data[4];

        $workers = $DB->get_records('block_timetracker_workerinfo',
            array('email'=>$email)); 
        //echo "About to through workers\n";
        foreach ($workers as $worker){ //more than 1 course?
            //echo "Iterating through worker $worker->firstname $worker->lastname\n";

            if($worker->courseid <= 76 && $worker->courseid >=73) 
                continue; //courses that don't count

            $days_array = preg_split('//', $days_string, -1, PREG_SPLIT_NO_EMPTY); 
            foreach($days_array as $day){
                $day = strtolower($day);
                if(!isset($day_names[$day])) {
                    echo "Day $day does not exist in day_names array\n";
                    continue;
                }

                $iterator = strtotime("Next $day_names[$day]", $from);
                while($iterator < $to){
                    //echo userdate($iterator, $datetimeformat)."\n";
                    $tdate = usergetdate($iterator);
                    
                    //class start
                    if(strlen($starttime)==4){
                        $tdate['hours'] = substr($starttime, 0, 2);
                        $tdate['minutes'] = substr($starttime, 2, 2);
                    } else {
                        $tdate['hours'] = substr($starttime, 0, 1);
                        $tdate['minutes'] = substr($starttime, 1, 2);
                    }

                    $in = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                        $tdate['hours'], $tdate['minutes']);
                    //echo "In: ".userdate($in, $datetimeformat)."\n";

                    //end time of class
                    if(strlen($endtime)==4){
                        $tdate['hours'] = substr($endtime,0,2);
                        $tdate['minutes'] = substr($endtime,2,2);
                    } else {
                        $tdate['hours'] = substr($endtime,0,1);
                        $tdate['minutes'] = substr($endtime,1,2);
                    }
                    $out = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                        $tdate['hours'], $tdate['minutes']);
                    //echo "Out: ".userdate($out, $datetimeformat)."\n";

                    $conflicts = find_conflicts($in, $out, $worker->id, -1,
                        $worker->courseid);
                    if(sizeof($conflicts) > 0){
                        foreach($conflicts as $conflict){
                            echo "\n";
                            echo "******************************************************\n";
                            echo "$worker->lastname, $worker->firstname\n";
                            echo "Conflict on ".userdate($iterator, '%A %m/%d/%y')."\n";
                            echo "Class: $coursename, meets $days_string from $starttime to $endtime\n";
                            echo "Work unit: ".$conflict->display."\n";
                            echo "******************************************************\n\n";

                        }
                    }
                    $iterator = $iterator + (7 * 86400);
                }
            }

        } 
    }
    fclose($handle);
}
