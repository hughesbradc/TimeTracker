<?php

require_once('../../../config.php');
require_once('../lib.php');

require_login();

global $CFG, $DB, $USER;

/*
    0 - username
    1 - course name
    2 - day(s)
    3 - start time (24H time)
    4 - end time (24H time)

    Checks for work units that occured during classes
*/
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

if (!has_capability('block/timetracker:manageworkers', $context)) { 
    print_error('You do not have permission to run this report.');
}


//start/stop time for search
//$from=1314849600; //Sept 1
$from=1317441600; //Oct 1
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

if(($handle = fopen("../testfile.csv", "r")) !== FALSE){
//if(($handle = fopen("../2011Fall_student_schedules.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email = $data[0];
        //$email = $data[0].'@mhc.edu';
        $coursename = $data[1];
        $days_string = $data[2];
        $starttime = $data[3];
        $endtime = $data[4];

        if(strtolower($days_string) == 'tba') continue;
        if(strtolower($days_string) == 'mtof') $days_string='mtwrf';

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
                    //$supervisor .= $teacher->firstname.' '.$teacher->lastname .' ' .$teacher->email.',';
                    $supervisor .= $teacher->firstname.' '.$teacher->lastname .',';
                }
            }
            $supervisor = substr($supervisor,0,-1);

            $days_array = preg_split('//', $days_string, -1, PREG_SPLIT_NO_EMPTY); 
            foreach($days_array as $day){
                $day = strtolower($day);
                if(!isset($day_names[$day])) {
                    echo "Day $day does not exist in day_names array\n";
                    continue;
                }

                $iterator = strtotime("Next $day_names[$day]", $from);
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
                            /*
                            //HTML
                            echo "$worker->lastname,$worker->firstname,".
                                "$coursename $days_string $dispstarttime to $dispendtime,".
                                userdate($iterator,'%m/%d/%y %A',99,false).",".
                                $conflict->display.','.$course->shortname.','.$supervisor."\n";
                            */

                            //Simple XLS file
                            
                            $contents = "$worker->lastname ,$worker->firstname ,"
                                ."$coursename $days_string $dispstarttime to $dispendtime ,"
                                .userdate($iterator,'%m/%d/%y %A',99,false).","
                                .$conflict->display.",".$course->shortname.",".$supervisor."\n";
                            echo $contents;

                            /*
                            //Complex XLS
                            function generate_xls($method = 'I', $base-''){
                                $fn = date("Y_m_d").'_ScheduleConflicts.xls';
                                if($method == 'F'){
                                    $workbook = new MoodleExcelWorkbook($base.'/'.$fn);
                                } else {
                                    $workbook = new MoodleExcelWorkbook('-');
                                    $workbook->send($fn);
                                }

                                //Formatting
                                $format_header =& $workbook->add_format();
                                $format_header->set_bold();
                                $format_header->set_bottom(1);

                                //Create worksheet
                                $worksheet = array();
                                $worksheet[1] =& $workbook->add_worksheet('Conflicts');

                                //Set column widths
                                $worksheet[1]->set_column(0,1,11.00);
                                $worksheet[1]->set_column(2,2,26.50);
                                $worksheet[1]->set_column(3,3,16.15);
                                $worksheet[1]->set_column(4,4,29.60);
                                $worksheet[1]->set_column(5,5,26.50);
                                $worksheet[1]->set_column(6,6,30.00);

                                //Write data to spreadsheet
                                $worksheet[1]->write_string(0,0,'Last Name', $format_header);
                                $worksheet[1]->write_string(1,0,'First Name', $format_header);
                                $worksheet[1]->write_string(2,0,'Conflicting Course', $format_header);
                                $worksheet[1]->write_string(3,0,'Conflict Day', $format_header);
                                $worksheet[1]->write_string(4,0,'Conflicting Work Unit Date & Time', $format_header);
                                $worksheet[1]->write_string(5,0,'Department', $format_header);
                                $worksheet[1]->write_string(6,0,'Supervisor', $format_header);

                                $workbook->close();
                                return $fn;
                                */
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
?>
