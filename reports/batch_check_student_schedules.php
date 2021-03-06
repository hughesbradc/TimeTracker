
<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');
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
//$from=1314849600; //Sept 1
//$from=1317441600; //Oct 1
$from=1320119998; //nov 1
$to=time();
$datetimeformat='%m/%d/%y %I:%M %p';


$day_names = array();
$day_names['m'] = 'Monday';
$day_names['t'] = 'Tuesday';
$day_names['w'] = 'Wednesday';
$day_names['r'] = 'Thursday';
$day_names['f'] = 'Friday';
$day_names['s'] = 'Saturday';

if(($handle = fopen("../2011Fall_student_schedules.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email = $data[0].'@mhc.edu';
        //if($email != 's000168502@mhc.edu') continue;
        $coursename = $data[1];
        $days_string = $data[2];
        $starttime = $data[3];
        $endtime = $data[4];


        if(strtolower($days_string) == 'tba') continue;
        if(strtolower($days_string) == 'mtof') $days_string='MTWRF';

        $workers = $DB->get_records('block_timetracker_workerinfo',
            array('email'=>$email)); 
        //echo "About to through workers\n";
        foreach ($workers as $worker){ //more than 1 course?
            //echo "Iterating through worker $worker->firstname $worker->lastname\n";

            if($worker->courseid <= 76 && $worker->courseid >=73) 
                continue; //courses that don't count

            $course = $DB->get_record('course',array('id'=>$worker->courseid));
            $context = get_context_instance(CONTEXT_COURSE, $worker->courseid);
           
            $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
            if(!$teachers){
                error_log("no teachers for $course->shortname");
                continue;
            }
            
            $supervisor = '';
            
            foreach ($teachers as $teacher) {
                if(is_enrolled($context, $teacher->id)){
                    $supervisor .= $teacher->firstname.' '.
                        $teacher->lastname .' ' .$teacher->email
                        .',';
                }
            }

            $supervisor = substr($supervisor,0,-1);

            $days_array = preg_split('//', $days_string, -1, PREG_SPLIT_NO_EMPTY); 
            //print_object($days_array);
            foreach($days_array as $day){
                $day = strtolower($day);
                //echo "Checking $day\n";
                if(!isset($day_names[$day])) {
                    echo "Day $day does not exist in day_names array\n";
                    continue;
                }

                $iterator = strtotime("Next $day_names[$day]", $from);
                //echo "Next ".$day_names[$day]."\n";
                //echo "Starting at ".userdate($iterator, $datetimeformat)."\n";
                if($starttime > 1200){
                    $dispstarttime = $starttime - 1200;
                    $dispstarttime .='pm';
                } else {
                    $dispstarttime = $starttime.'am';
                }

                if($endtime > 1200){
                    $dispendtime = $endtime - 1200;
                    $dispendtime .= 'pm';
                } else {
                    $dispendtime = $endtime.'am';
                }

                while($iterator < $to){
                    //echo userdate($iterator, $datetimeformat)."\n";
                    $tdate = usergetdate($iterator);
                    
                    //class start
                    if(strlen($starttime)==4){
                        $tdate['hours'] = substr($starttime, 0, 2);
                        $tdate['minutes'] = substr($starttime, 2, 2);
                        $tdate['minutes'] = $tdate['minutes'] + 5;
                    } else {
                        $tdate['hours'] = substr($starttime, 0, 1);
                        $tdate['minutes'] = substr($starttime, 1, 2);
                        $tdate['minutes'] = $tdate['minutes'] + 5;
                    }

                    $in = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                        $tdate['hours'], $tdate['minutes']);
                    //echo "In: ".userdate($in, $datetimeformat)."\n";

                    //end time of class
                    if(strlen($endtime)==4){
                        $tdate['hours'] = substr($endtime,0,2);
                        $tdate['minutes'] = substr($endtime,2,2);
                        $tdate['minutes'] = $tdate['minutes'] - 5;
                    } else {
                        $tdate['hours'] = substr($endtime,0,1);
                        $tdate['minutes'] = substr($endtime,1,2);
                        $tdate['minutes'] = $tdate['minutes'] - 5;
                    }

                    $out = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                        $tdate['hours'], $tdate['minutes']);

                    //echo "Out: ".userdate($out, $datetimeformat)."\n";

                    $conflicts = find_conflicts($in, $out, $worker->id, -1,
                        $worker->courseid);
                    if(sizeof($conflicts) > 0){
                        foreach($conflicts as $conflict){
                            echo "\"$worker->lastname\",\"$worker->firstname\",".
                                "\"$coursename $days_string ". 
                                "$dispstarttime to $dispendtime\",\"".
                                userdate($iterator,'%m/%d/%y %A',99,false).'","'.
                                $conflict->display.'","'.
                                $course->shortname.'","'.$supervisor."\"\n";
                        }
                    }
                    //$iterator = $iterator + (7 * 86400);
                    $iterator = strtotime('+1 week', $iterator);
                }
            }
            break;
        } 
    }
    fclose($handle);
} else {
    error_log("Error opening file");
}
