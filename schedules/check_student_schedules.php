
<?php

require_once('../lib.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');



function check_worker_hours_for_conflicts($workerid, $from, $to){
    //error_log("checking for class conflicts");
    global $CFG, $DB, $USER;
    $myconflicts = array();

    $datetimeformat='%m/%d/%y %I:%M %p';

    $day_names = array();
    $day_names['m'] = 'Monday';
    $day_names['t'] = 'Tuesday';
    $day_names['w'] = 'Wednesday';
    $day_names['r'] = 'Thursday';
    $day_names['f'] = 'Friday';
    $day_names['s'] = 'Saturday';

    $worker = $DB->get_record('block_timetracker_workerinfo',
        array('id'=>$workerid)); 

    if(!$worker) {
        error_log('This worker does not exist in workerinfo');
        return $myconflicts;;
    }

    $mdlworker = $DB->get_record('user', array('id' =>$worker->mdluserid));

    if(!$mdlworker){
        error_log('This worker does not exist in mdl_user');
        return $myconflicts;
    }


    $scheduleitems = $DB->get_records('block_timetracker_schedules',
        array('studentid'=>strtolower($mdlworker->username)));

    if(!$scheduleitems){
        error_log('no schedule items for '.$mdlworker->username);
        return $myconflicts;
    }

    foreach($scheduleitems as $item){


        if(strtolower($item->days) == 'tba') continue;
        if(strtolower($item->days) == 'mtof') $item->days='MTWRF';


        $days_array = preg_split('//', $item->days, -1, PREG_SPLIT_NO_EMPTY); 

        foreach($days_array as $day){
            $day = strtolower($day);
            //echo "Checking $day\n";
            if(!isset($day_names[$day])) {
                echo "Day $day does not exist in day_names array\n";
                continue;
            }
    
            $iterator = strtotime("Next $day_names[$day]", $from);
            if($item->begin_time >=1200){
                $dispstarttime = $item->begin_time;
                if($item->begin_time >=1300)
                    $dispstarttime -= 1200;
                $dispstarttime .='pm';
            } else {
                $dispstarttime = $item->begin_time.'am';
            }
    
            if($item->end_time >= 1200){
                $dispendtime = $item->end_time;
                if($item->end_time >=1300)
                    $dispendtime -= 1200;
                $dispendtime .= 'pm';
            } else {
                $dispendtime = $item->end_time.'am';
            }
    
            while($iterator < $to){


                if($iterator > $item->end_date){
                   break; 
                }

                if($iterator < $item->begin_date){
                    $iterator = strtotime('+1 week', $iterator);
                    continue;
                }

                $tdate = usergetdate($iterator);
                
                //class start
                if(strlen($item->begin_time)==4){
                    $tdate['hours'] = substr($item->begin_time, 0, 2);
                    $tdate['minutes'] = substr($item->begin_time, 2, 2);
                    $tdate['minutes'] = $tdate['minutes'] + 5;
                } else {
                    $tdate['hours'] = substr($item->begin_time, 0, 1);
                    $tdate['minutes'] = substr($item->begin_time, 1, 2);
                    $tdate['minutes'] = $tdate['minutes'] + 5;
                }
    
                $in = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                    $tdate['hours'], $tdate['minutes']);
    
                //end time of class
                if(strlen($item->end_time)==4){
                    $tdate['hours'] = substr($item->end_time,0,2);
                    $tdate['minutes'] = substr($item->end_time,2,2);
                    $tdate['minutes'] = $tdate['minutes'] - 5;
                } else {
                    $tdate['hours'] = substr($item->end_time,0,1);
                    $tdate['minutes'] = substr($item->end_time,1,2);
                    $tdate['minutes'] = $tdate['minutes'] - 5;
                }
    
                $out = make_timestamp($tdate['year'], $tdate['mon'], $tdate['mday'],
                    $tdate['hours'], $tdate['minutes']);
                
                //check to see if this class was during a break; if so, skip it.
                $duringbreak = $DB->count_records_select('block_timetracker_holiday', 
                    'start <= '.$in.' AND end >= '.$out);
                //error_log($duringbreak);
                if(!$duringbreak) {
                    $conflicts = find_conflicts($in, $out, $worker->id, -1,
                        $worker->courseid, false, true);
                    if(sizeof($conflicts) > 0){
                        foreach($conflicts as $conflict){
                            $conflict->conflictcourse = 
                                "$item->course_code $item->days $dispstarttime to ".
                                $dispendtime;
                            $myconflicts[] = $conflict;
                            /*
                                echo "\"$worker->lastname\",\"$worker->firstname\",".
                                "\"$item->course_code $item->days ". 
                                "$dispstarttime to $dispendtime\",\"".
                                userdate($iterator,'%m/%d/%y %A',99,false).'","'.
                                $conflict->display.'"'."\n";
                            */
                        }
                    }
                }
                $iterator = strtotime('+1 week', $iterator);
            }
        }
    }
    return $myconflicts;
}
