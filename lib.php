<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This block will display a summary of hours and earnings for the worker.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

/**
* Tell whether a unit can be editable, based on the following:
* A unit may be edited until the 5th day of the next month
*/
function expired($timein, $now=-1){
    if($now == -1) $now = time();

    $currdateinfo = usergetdate($now);

    $unitdateinfo = usergetdate($timein);
    if($now - $timein > (86400 * 35) || 
        (($currdateinfo['month'] != $unitdateinfo['month'] || 
        $currdateinfo['year'] != $unitdateinfo['year']) &&
        $currdateinfo['mday'] > 5)){
        return true;
    }
    return false;

}

/**
* Given an object that holds all of the values necessary from block_timetracker_workunit,
* Add it to the workunit table, splitting across multiple days if necessary
* @return true if worked, false if failed
*/
function add_unit($unit,$hourlog=false){
    global $DB;

    if(!is_object($unit)) return 0;    
    if(isset($unit->id)) unset($unit->id);
    $nowtime = time();
    $timein = usergetdate($unit->timein);
    $timeout = usergetdate($unit->timeout);

    //check to see if in and out are on the same day
    if($timein['year'] == $timeout['year'] && 
        $timein['month'] == $timeout['month'] &&
        $timein['mday'] == $timeout['mday']){

        $unitid = $DB->insert_record('block_timetracker_workunit', $unit);
        if($unitid){
            if($hourlog)
                add_to_log($unit->courseid, '', 'add work unit', '', 'TimeTracker hourlog');
            else
                add_to_log($unit->courseid, '', 'add clock-out', '', 'TimeTracker clock-out');
        } else {
            if($hourlog){
                add_to_log($unit->courseid, '', 
                    'error adding work unit', '', 'ERROR:  User hourlog failed.');
            } else {
                add_to_log($unit->courseid, '', 
                    'error clocking-out', '', 'ERROR:  User clock-out failed.');
            }
            return false; 
        }
        return true;
    } else { //spans multiple days

        $origtimein = $unit->timein;
        $checkout = $unit->timeout;
        $endofday = (86400+(usergetmidnight($unit->timein)-1));

        $usersdate = usergetdate($endofday);
        if($usersdate['hours'] == 22){ 
            $endofday += 60 * 60;
        } else if ($usersdate['hours'] == 0){
            $endofday -= 60 * 60;
        }

        while ($unit->timein < $checkout){
        
            //add to $DB
            $unit->timeout = $endofday;
            $worked = $DB->insert_record('block_timetracker_workunit', $unit);
            if(!$worked){
                add_to_log($unit->courseid, '', 'error adding work unit', 
                    '', 'TimeTracker add work unit failed.');
                return false; 
            } else {
                add_to_log($unit->courseid, '', 'add work unit', 
                    '', 'TimeTracker work unit added.');
            }
        
            $unit->timein = $endofday + 1;

            //find next 23:59:59
            $endofday = 86400 + (usergetmidnight($unit->timein)-1);
        
            //because I can't get dst_offset_on to work!
            $usersdate = usergetdate($endofday);
            if($usersdate['hours'] == 22){ 
                $endofday += 60 * 60;
            } else if ($usersdate['hours'] == 0){
                $endofday -= 60 * 60;
            }
        
            //if not a full day, don't go to 23:59:59 
            //but rather checkout time
            if($endofday > $checkout){
                $endofday = $unit->timein + ($checkout - $unit->timein);
            } 
        }
        return true;
    }

}

/**
* Given an object that holds all of the values necessary from block_timetracker_workunit,
* add update it in the DB, splitting it across multiple days if necessary
* @return the true if updated successfully, false if not
*
*/
function update_unit($unit){
    global $DB;
    $id = $unit->id;
    $result = add_unit($unit);
    if($result){
        $deleteresult = $DB->delete_records('block_timetracker_workunit', 
            array('id'=>$id));
        if(!$deleteresult){
            //log error in deleting?
            return false;
        }
    } else {
        return false;    
    }
    return true;
}

/**
* Attempts to see if this workunit overlaps with any other workunits already submitted
* for user $userid in $COURSE
* @return T if overlaps
*/
function overlaps($timein, $timeout, $userid, $unitid=-1, $courseid=-1){

    global $CFG, $COURSE, $DB;
    if($courseid == -1) $courseid = $COURSE->id;
    
    $sql = 'SELECT COUNT(*) FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        "$userid = userid AND $courseid = courseid AND (".
        "($timein >= timein AND $timein < timeout) OR ".
        "($timeout > timein AND $timeout <= timeout) OR ".
        "(timein >= $timein AND timein < $timeout))";
        
    if($unitid != -1){
      $sql.=" AND id != $unitid"; 
    }

    $numexistingunits = $DB->count_records_sql($sql);

    $sql = 'SELECT COUNT(*) FROM '.$CFG->prefix.'block_timetracker_pending WHERE '.
        "$userid = userid AND $courseid = courseid AND ".
        "timein BETWEEN $timein AND $timeout";

    $numpending = $DB->count_records_sql($sql);

    //error_log("numpending is $numpending");

    if($numexistingunits == 0 && $numpending == 0) return false;
    return true;
}

/**
* Returns an array of stdobjects that have the following:
* obj->display (how to display the work unit)
* obj->editlink (a url to edit the work unit)
* obj->deletelink (a url to delete the unit)
* obj->alertlink (a url to create an alert)
* obj->timein (a timestamp for this clock-in)
* obj->timeout (a timestamp for this clock-out, if applicable. if
* obj->id (the id of the offending unit)
* it is a pending clock-in, this value will be the same as the clock-in value)
* If the array is empty, there are no overlapping units
*/
function find_conflicts($timein, $timeout, $userid, $unitid=-1, $courseid=-1){

    global $CFG, $COURSE, $DB;
    if($courseid == -1) $courseid = $COURSE->id;
    
    //check workunit table first
    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        "$userid = userid AND $courseid = courseid AND (".
        "($timein >= timein AND $timein < timeout) OR ".
        "($timeout > timein AND $timeout <= timeout) OR ".
        "(timein >= $timein AND timein < $timeout))";
        
    if($unitid != -1){
      $sql.=" AND id != $unitid"; 
    }

    $conflictingunits = $DB->get_records_sql($sql);

    $conflicts  = array();
    $baseurl = $CFG->wwwroot.'/blocks/timetracker';
    foreach ($conflictingunits as $unit){
        $entry = new stdClass();
        $disp = userdate($unit->timein,
            get_string('datetimeformat', 'block_timetracker')).
            ' to '.userdate($unit->timeout,
            get_string('timeformat', 'block_timetracker'));
        $entry->display = $disp;
        $entry->deletelink = $baseurl.'/deleteworkunit.php?id='.$unit->courseid.
            '&userid='.$unit->userid.'&unitid='.$unit->id.
            '&sesskey='.sesskey();
        $entry->editlink = $baseurl.'/editunit.php?id='.$unit->courseid.
            '&userid='.$unit->userid.'&unitid='.$unit->id;
        $entry->alertlink = $baseurl.'/alert.php?id='.$unit->courseid.
            '&userid='.$unit->userid.'&unitid='.$unit->id;
        $entry->timein = $unit->timein;
        $entry->timeout = $unit->timeout;
        $entry->id = $unit->id;

        $conflicts[] = $entry;
    }

    //pending units
    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_pending WHERE '.
        "$userid = userid AND $courseid = courseid AND ".
        "timein BETWEEN $timein AND $timeout";

    $pendingconflicts = $DB->get_records_sql($sql);
    foreach ($pendingconflicts as $pending){
        $entry = new stdClass();
        $disp = 'Pending clock-in time: '.userdate($pending->timein,
            get_string('datetimeformat', 'block_timetracker'));
        $entry->display = $disp;
        $entry->deletelink = $baseurl.'/deleteworkunit.php?id='.$pending->courseid.
            '&userid='.$pending->userid.'&unitid='.$pending->id;
        $entry->editlink =  '#';
        $entry->timein = $entry->timeout = $pending->timein;
        $entry->alertlink = $baseurl.'/alert.php?id='.$pending->courseid.
            '&userid='.$pending->userid.'&unitid='.$pending->id.'&ispending=true';
        $entry->id = $pending->id;

        $conflicts[] = $entry;

    }
   
    return $conflicts;
}

/**
*@return array of tabobjects 
*/
function get_tabs($urlparams, $canmanage = false, $courseid = -1){
    global $CFG;
    $tabs = array();
    $tabs[] = new tabobject('home',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams),
        'Main');
    $tabs[] = new tabobject('reports',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',
        $urlparams),'Reports');
    $numalerts = '';
    if($canmanage){
        $tabs[] = new tabobject('manage',
            new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php', $urlparams),
            'Manage Workers');
        $tabs[] = new tabobject('terms',
            new moodle_url($CFG->wwwroot.'/blocks/timetracker/terms.php', $urlparams),
            'Terms');
        if($courseid != -1){
            //getnumalerts from $courseid
            $n = has_course_alerts($courseid);
            if($n > 0){
                $numalerts = '('.$n.')';
            }
        }
    }
    $tabs[] = new tabobject('alerts',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/managealerts.php', $urlparams),
        'Alerts '.$numalerts);

    return $tabs;
}

function add_enrolled_users($context){
    global $COURSE,$DB;

    //before displaying anything, add any enrolled users NOT in the WORKERINFO table.
    //consider moving this to a 'refresh' link or something so it doesn't do it everytime?
    //TODO
    $config = get_timetracker_config($COURSE->id);
    $students = get_users_by_capability($context, 'mod/assignment:submit');
    foreach ($students as $student){
        if(!$DB->record_exists('block_timetracker_workerinfo',array('mdluserid'=>$student->id,
            'courseid'=>$COURSE->id))){
            $student->mdluserid = $student->id;
            unset($student->id);
            $student->courseid = $COURSE->id;
            $student->idnum = $student->username;
            $student->address = '0';
            $student->position = $config['position'];
            $student->currpayrate = $config['curr_pay_rate'];
            $student->timetrackermethod = $config['trackermethod'];
            $student->dept = $config['department'];
            $student->budget = $config['budget'];
            $student->supervisor = $config['supname'];
            $student->institution = $config['institution'];
            $student->maxtermearnings = $config['default_max_earnings'];
            $res = $DB->insert_record('block_timetracker_workerinfo', $student);
            if(!$res){
                print_error("Error adding $student->firstname $student->lastname to TimeTracker");
            }
        }
    }

}



/*
* rounds to nearest 15 minutes (900 secs)
*/
function round_time($totalsecs=0){
    if($totalsecs <=0) return 0;
    
    $temp = $totalsecs % 3600;
    $distto900 = $temp % 900;
    if($distto900 > 449) 
        $totalsecs = $totalsecs + (900 - $distto900); //round up
    else 
        $totalsecs = $totalsecs - $distto900; //round down

    return $totalsecs;

    
}

/*
* @return number of hours in decimal format, rounded to the nearest .25 hour
*/
function get_hours($totalsecs=0){
    $totalsecs = round_time($totalsecs);
    return ($totalsecs/3600);
}


/**
* returns the $totalsecs as 'xx hour(s) xx minute(s)', rounded to the nearest 15 min
*/
function format_elapsed_time($totalsecs=0){
    if($totalsecs <= 0){
        return '0 hours 0 minutes';
    }

    $totalsecs = round_time($totalsecs);
    $hours = floor($totalsecs/3600);
    $minutes = ($totalsecs % 3600)/60;
    
    return $hours.' hour(s) and '.$minutes. ' minute(s)'; 
}

/**
* Calculate Total Earnings 
* @param $userid, $courseid
* @return total money earned
*/
function get_total_earnings($userid, $courseid){
    global $CFG, $DB;
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $workerunits = $DB->get_recordset_sql($sql);

    if(!$workerunits) return 0;

    $earnings = 0;
    foreach($workerunits as $subunit){
        $earnings += get_hours(round_time($subunit->timeout - $subunit->timein))*$subunit->payrate;
    }

    return round($earnings,2);

}

/**
* Determine if the course has alerts waiting
* @param $courseid id of the course
* @return 0 if no alerts are pending, # of alerts if they exist.
*/
function has_course_alerts($courseid){
    global $CFG,$DB;
    //check the alert* tables to see if there are any outstanding alerts:
    //$sql = 'SELECT COUNT('.$CFG->prefix.'block_timetracker_alertunits.*) FROM '.
    $sql = 'SELECT COUNT(*) FROM '.
        $CFG->prefix.'block_timetracker_alertunits'.
        ' WHERE courseid='.$courseid.
            ' ORDER BY alerttime';

    //error_log($sql);
    $numalerts = $DB->count_records_sql($sql);
    //error_log($numalerts." alerts found! CID: $courseid");
    return $numalerts;

}

/**
* Determine if the supervisor has alerts waiting
* @param $supervisorid of the supervisor in question
* @param $courseid id of the course
* @return T if alerts are pending, F if not.
* @deprecated. Design decision to show ALL SUPERVISORS ALL ALERTS.
*/
function has_alerts($supervisorid, $courseid){
    /*
    global $CFG,$DB;
    //check the alert* tables to see if there are any outstanding alerts:
    //$sql = 'SELECT COUNT('.$CFG->prefix.'block_timetracker_alertunits.*) FROM '.
    $sql = 'SELECT COUNT(*) FROM '.
        $CFG->prefix.'block_timetracker_alertunits,'.
        ' WHERE courseid='.$courseid.' ORDER BY alerttime';

    $numalerts = $DB->count_records_sql($sql);
    //error_log($numalerts." alerts found! USERID: $supervisorid CID: $courseid");
    return ($numalerts != 0);
    */
    return has_course_alerts($courseid);

}

/**
* Generate the alert links for a supervisor
* @param $supervisorid of the supervisor in question
* @param $courseid id of the course
* @return two-dimensional array of links. First index is the TT worker id, the second
* index is either 'approve', 'deny', or 'change' for each of the corresponding links
* @deprecated DO NOT USE!!!
*/
function get_alert_links($supervisorid, $courseid){
    /*
    global $CFG,$DB;
    //check the alert* tables to see if there are any outstanding alerts:
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_alertunits.* FROM '.
        $CFG->prefix.'block_timetracker_alertunits,'.
        $CFG->prefix.'block_timetracker_alert_com WHERE mdluserid='.
        $supervisorid.' AND courseid='.$courseid.' ORDER BY alerttime';
    //print_object($sql);

    $alerts = $DB->get_recordset_sql($sql);
    $alertlinks = array();
    foreach ($alerts as $alert){
        //print_object($alert);
        $url = $CFG->wwwroot.'/blocks/timetracker/alertaction.php';
        
        $params = "?alertid=$alert->id";
        if($alert->todelete) $params.="&delete=1";

        $alertlinks[$alert->userid]['approve'] = $url.$params."&action=approve";
        $alertlinks[$alert->userid]['deny'] = $url.$params."&action=deny";
        $alertlinks[$alert->userid]['change'] = $url.$params."&action=change";

    }
    */

    return get_course_alert_links($courseid);
}

/**
* Generate the alert links for a course
* @param $courseid id of the course
* @return two-dimensional array of links. First index is the TT worker id, the second
* index is either 'approve', 'deny', or 'change' for each of the corresponding links
*/
function get_course_alert_links($courseid){
    global $CFG,$DB;
    //check the alert* tables to see if there are any outstanding alerts:
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_alertunits.* FROM '.
        $CFG->prefix.'block_timetracker_alertunits '.
        'WHERE courseid='.$courseid.
            ' ORDER BY alerttime';
    //print_object($sql);

    $alerts = $DB->get_recordset_sql($sql);
    $alertlinks = array();
    foreach ($alerts as $alert){
        //print_object($alert);
        $url = $CFG->wwwroot.'/blocks/timetracker/alertaction.php';
        
        $params = "?alertid=$alert->id";
        if($alert->todelete) $params.="&delete=1";

        $alertlinks[$alert->userid]['approve'] = $url.$params."&action=approve";
        $alertlinks[$alert->userid]['deny'] = $url.$params."&action=deny";
        $alertlinks[$alert->userid]['change'] = $url.$params."&action=change";
        $alertlinks[$alert->userid]['delete'] = $url.$params."&action=delete";

    }

    return $alertlinks;
}

/**
* Calculate Total Hours
* @param $workerunits is an array, each $subunit has $subunit->timein and $subunit->timeout
* @return total hous worked in decimal format rounded to the nearest interval.
*/
function get_total_hours($userid, $courseid){
    global $CFG, $DB;
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workerinfo.firstname, '.
        $CFG->prefix.'block_timetracker_workerinfo.lastname, '.$CFG->prefix.
            'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $workerunits = $DB->get_recordset_sql($sql);

    if(!$workerunits) return 0;

    $total = 0;
    foreach($workerunits as $subunit){
        $total += round_time($subunit->timeout - $subunit->timein);
    }

    return get_hours($total);

}

/**
* @return earnings (in dollars) for this month
*
*/
function get_earnings_this_month($userid,$courseid){
    global $CFG, $DB;
    $currtime = usergetdate(time());

    $firstofmonth = time() - ((($currtime['mday']-1) * (24*3600)) + ($currtime['hours']*3600) + 
        ($currtime['minutes']*60) + ($currtime['seconds']));

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.$firstofmonth.' AND '.time().' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $earnings = 0;
    foreach($units as $unit){
        $earnings += get_hours(round_time($unit->timeout - $unit->timein))*$unit->payrate;
    }
    return round($earnings,2);
}

/**
* @param $month The month (1-12) that you're inspecting
* @param $year The year (yyyy) that you're inspecting
* @return array of values
    $monthinfo['firstdaytimestamp'] <= unix time of midnight of first day
    $monthinfo['lastdaytimestamp'] <= unix time of 23:59:59 of last day
    $monthinfo['dayofweek'] <= the index of day of week of first day
    $monthinfo['lastday'] <= the last day of this month
    $monthinfo['monthname'] <= the name of this month
*/
function get_month_info($month,$year){
    $monthinfo = array();
    
    $timestamp = make_timestamp($year,$month); //timestamp of midnight, first day of $month
    $monthinfo['firstdaytimestamp'] = $timestamp;
    $monthinfo['lastday'] = date('t',$timestamp);

    $thistime = usergetdate($timestamp);
    $monthinfo['dayofweek'] = $thistime['wday'];
    $monthinfo['monthname'] = $thistime['month'];

    $timestamp = make_timestamp($year,$month,$monthinfo['lastday'],23,59,59);
    $monthinfo['lastdaytimestamp'] = $timestamp; //23:59:59pm

    return $monthinfo;
}


/**
* @return hours (in decimal) for this month
*
*/
function get_hours_this_month($userid,$courseid){
    global $CFG, $DB;
    $currtime = usergetdate(time());

    $firstofmonth = time() - ((($currtime['mday']-1) * (24*3600)) + ($currtime['hours']*3600) + 
        ($currtime['minutes']*60) + ($currtime['seconds']));

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.$firstofmonth.' AND '.time().' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein);
    }
    return get_hours($total);
}

/**
* @return earnings (in dollars) for this calendar year
*
*/
function get_earnings_this_year($userid,$courseid){
    global $CFG, $DB;

    $currtime = usergetdate(time());
    $firstofyear = time() - ((($currtime['yday']-1) * (24*3600)) + ($currtime['hours']*3600) + 
        ($currtime['minutes']*60) + ($currtime['seconds']));

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.$firstofyear.' AND '.time().' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);


    if(!$units) return 0;
    $earnings = 0;
    foreach($units as $unit){
        $earnings += get_hours(round_time($unit->timeout - $unit->timein)) * $unit->payrate;
    }
    return round($earnings,2);
}

/**
* @return hours (in decimal) for this calendar year
*
*/
function get_hours_this_year($userid,$courseid){
    global $CFG, $DB;
    $currtime = usergetdate(time());
    $firstofyear = time() - ((($currtime['yday']-1) * (24*3600)) + ($currtime['hours']*3600) + 
        ($currtime['minutes']*60) + ($currtime['seconds']));

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.$firstofyear.' AND '.time().' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein);
    }
    return get_hours($total);
}

/**
*
* @return array 'termstart' and 'termend'
*/
function get_term_boundaries($courseid){
    global $DB;
    $currtime = time();
    $year = date("Y");

    $terms = $DB->get_records('block_timetracker_term',array('courseid'=>$courseid), 'month, day');

    $termstart = 0;
    $termend = 0;
    if($terms){

        $term_times = array();
        $counter = 0;
        foreach($terms as $term){
            $tstart = mktime(0,0,0,$term->month,$term->day,$year);
            $term_times[] = $tstart; 
            if($counter == 0){
                $term_times[] = mktime(0,0,0,$term->month,$term->day,$year+1);
            }
            $counter++;
        }
    
        sort($term_times);
    
        foreach($term_times as $termtime){
            if($currtime < $termtime){
                $termend = $termtime - 1;
                break;
            }
            $termstart = $termtime;
        }
    }

    $boundaries = array('termstart'=>$termstart,'termend'=>$termend);
    return $boundaries;
}


/**
* @return hours (in decimal) for the current term
*
*/
function get_hours_this_term($userid, $courseid){

    global $CFG, $DB;
    $boundaries = get_term_boundaries($courseid);

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.
        $boundaries['termstart'].' AND '.$boundaries['termend'].' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein);
    }
    return get_hours($total);
}

function get_earnings_this_term($userid,$courseid){

    global $CFG, $DB;

    $boundaries = get_term_boundaries($courseid);

    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.timein BETWEEN '.
        $boundaries['termstart'].' AND '.$boundaries['termend'].' AND '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.
            'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.
            'block_timetracker_workunit.courseid='.$courseid;

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $earnings = 0;
    foreach($units as $unit){
        $earnings += get_hours(round_time($unit->timeout - $unit->timein))*$unit->payrate;
    }
    return round($earnings,2);
}

/**
* We need
* this month
* this term
* year to date
* total
*/

/**
* Gives an array with worker stats:
* $stats['totalhours']
* $stats['monthhours'
* $stats['yearhours']
* $stats['termhours']
* $stats['totalearnings'] 
* $stats['monthearnings']
* $stats['yearearnings']
* $stats['termearnings']
* @return an array with useful values
*/
function get_worker_stats($userid,$courseid){
    global $DB;

    $stats['totalhours'] = get_total_hours($userid,$courseid);
    $stats['monthhours'] = get_hours_this_month($userid, $courseid);
    $stats['yearhours'] = get_hours_this_year($userid, $courseid);
    $stats['termhours'] = get_hours_this_term($userid, $courseid);

    $stats['totalearnings'] = get_total_earnings($userid,$courseid);
    $stats['monthearnings'] =get_earnings_this_month($userid,$courseid);
    $stats['yearearnings'] = get_earnings_this_year($userid,$courseid);
    $stats['termearnings'] = get_earnings_this_term($userid,$courseid);


    return $stats; 
}

/**
* @see get_worker_stats
* @return an object array like this:
Array{
    [userid as index] => stdClassObject
        {
            [id] = TT user id
            [mdluserid] = moodle user id
            .
            .  (all other workerinfo fields
            .
            [totalhours] = 
            [monthhours] = 
            .
            . (all other from get_worker_stats())
            .
        }
    [next userid as index] => stdClassObject
        {
            etc
}
*/
function get_workers_stats($courseid){
    global $DB; 

    $workers = $DB->get_records('block_timetracker_workerinfo',
        array('courseid'=>$courseid),'lastname ASC, firstname ASC');

    if(!$workers) return null;
    $workerstats = array();
    foreach($workers as $worker){
        
        //XXX this is bad; multiple DB calls. Should do all in one call for efficiency
        $stats = get_worker_stats($worker->id, $courseid);
        foreach($stats as $stat=>$val){
            $worker->$stat = $val;
        }

        $lastunit = $DB->get_records('block_timetracker_workunit',
            array('userid'=>$worker->id, 'courseid'=>$courseid), 'timeout DESC LIMIT 1');
        $lu = '';
        foreach($lastunit as $u){
            $lu = 
                userdate($u->timein,get_string('datetimeformat','block_timetracker')).
                '<br />'.
                userdate($u->timeout,get_string('datetimeformat','block_timetracker')).
                '<br />'.
                number_format(get_hours($u->timeout - $u->timein),2).' hours';
        }

        $worker->lastunit = $lu;
        $workerstats[$worker->id] = $worker;
    }

    return $workerstats;
}



/**
* XXX TODO document this function
* @return an array of config items for this course;
*/
function get_timetracker_config($courseid){
    global $DB;
    $config = array();
    $confs = $DB->get_records('block_timetracker_config',array('courseid'=>$courseid));
    foreach ($confs as $conf){
        $key = preg_replace('/block\_timetracker\_/','',$conf->name);
        $config[$key] = $conf->value;
    }

    return $config;
}

/**
* for admin usage. Find all units that 
*
*/
/*
function find_units($startafter, $endbefore){

}
*/


