<?php

require_once('../../../config.php');
require_once('../lib.php');

require_login();

global $CFG, $DB, $USER;

$cat = required_param('catid', PARAM_INT);
$from = required_param('start', PARAM_INT);

//only get courses in the specified category
$courses = get_courses($cat, 'fullname ASC', 'c.id, c.shortname');
if(!$courses){
    print_error("No courses in category $cat");
}

/*
    0 - username
    1 - course name
    2 - day(s)
    3 - start time (24H time)
    4 - end time (24H time)

    Checks for work units that occured during classes
*/
$context = get_context_instance(CONTEXT_COURSECAT, $cat);
$PAGE->set_context($context);

if (!has_capability('block/timetracker:manageworkers', $context)) { 
    print_error('You do not have permission to run this report.');
}


//start/stop time for search
//$from=1314849600; //Sept 1
//$from=1317441600; //Oct 1

//$from=1320119998; //Nov 1
$to=time();
$datetimeformat='%m/%d/%y %I:%M %p';

$day_names = array();
$day_names['m'] = 'Monday';
$day_names['t'] = 'Tuesday';
$day_names['w'] = 'Wednesday';
$day_names['r'] = 'Thursday';
$day_names['f'] = 'Friday';
$day_names['s'] = 'Saturday';

//CSV File Generation
$filename = date("Y_m_d").'_ScheduleConflicts.csv';
$header = 'Last Name ,First Name ,Conflicting Course ,Conflicting Unit Day ,'
    ."Conflicting Work Unit Date & Time ,Department ,Supervisor(s) \n";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

echo $header;

//if(($handle = fopen("../testfile.csv", "r")) !== FALSE){
if(($handle = fopen("../2011Fall_student_schedules.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 100, ",")) !== FALSE){


        //$email = $data[0];
        $email = $data[0].'@mhc.edu';
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

            if(!array_key_exists($worker->courseid, $courses)){
                continue; //not in this category?
            }

            //$course = $DB->get_record('course',array('id'=>$worker->courseid));
            $course = $courses[$worker->courseid];
            if(!$course) {
                error_log('$course does not exist');
                continue;
            }

            $context = get_context_instance(CONTEXT_COURSE, $worker->courseid);
           
            $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
            if(!$teachers){
                error_log("no teachers for $course->shortname");
                continue;
            }
            
            $supervisor = '';
            
            foreach ($teachers as $teacher) {
                if(is_enrolled($context, $teacher->id)){
                    //$supervisor .= 
                    //$teacher->firstname.' '.$teacher->lastname .' ' .$teacher->email.',';
                    $supervisor .= $teacher->firstname.' '.$teacher->lastname .',';
                }
            }
            $supervisor = substr($supervisor,0,-1);

            $days_array = preg_split('//', $days_string, -1, PREG_SPLIT_NO_EMPTY); 
            foreach($days_array as $day){
                $day = strtolower($day);
                if(!isset($day_names[$day])) {
                    error_log("Day $day does not exist in day_names array\n");
                    continue;
                }

                $iterator = strtotime("Next $day_names[$day]", $from);
                if($starttime >= 1200){
                    $dispstarttime = $starttime;
                    if($starttime >=1300)
                        $dispstarttime -= 1200;
                    $dispstarttime .= 'pm';
                } else {
                    $dispstarttime = $starttime.'am';
                }


                if($endtime >= 1200){
                    $dispendtime = $endtime;
                    if($endtime >= 1300)
                        $dispendtime -= 1200;
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
                            $contents .= "$worker->lastname ,$worker->firstname ,"
                                ."$coursename $days_string $dispstarttime to $dispendtime ,"
                                .userdate($iterator,'%m/%d/%y %A',99,false).","
                                .$conflict->display.",".
                                $course->shortname.",".$supervisor."\n";
                            //echo $contents;
                        }
                    }
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

//echo $header;
echo $contents;

?>
