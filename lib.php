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
*
* Question about this is whethere we should look at timein
* or timeout, now that we're not splitting up work units
* before adding them to the db TODO
* @deprecated as of v2011123100
*/
function expired($timein, $now = -1){
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
* Given a userid, courseid, start time and end time, find all units
* that meet within this time period, and split them up across the day boundary.
* For Example:
* DB work unit 10/01/11 09:00am to 10/02/11 02:00am
* this function would return:
*
* array[0]: 10/01/11 09:00am - 11:59:59
* array[1]: 10/02/11 12:00am - 02:00am
*
* Note: some $unit->id may be the same, since a single unit will be broken
* up across many days.
*
* @return an array of objects, each having all the properties of a workunit
*/
function get_split_units($start, $end, $userid=0, $courseid=0, $timesheetid=-1, $sort='ASC'){
    global $CFG, $DB;

    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        '(timein BETWEEN '.
        $start. ' AND '.$end.' OR timeout BETWEEN '.
        $start. ' AND '.$end.') ';

    if($userid > 0){
        $sql .= ' AND userid='.$userid;
    }
    
    if($courseid > 0){
        $sql .= ' AND courseid='.$courseid;
    }

    if($timesheetid > -1){
        $sql .= ' AND timesheetid='.$timesheetid;
    }

    $sql .= ' ORDER BY timein '.$sort;

    $units = $DB->get_records_sql($sql);

    if(!$units) {
        return;
    }

    $splitunits = array(); 
    $nowtime = time();

    foreach($units as $unit){
        $splits = split_unit($unit);

        $splitunits = array_merge($splitunits, $splits);

    }
    return $splitunits;
}

/**
* If any units straddle the $start or $end boundary, split them into multiple units
* Only consider units that have NOT been submitted (submitted==0)
*/
function split_boundary_units($start, $end, $userid, $courseid){
    global $DB, $CFG;

    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        'userid = '.$userid.' AND courseid = '.$courseid.' AND submitted=0'.
        'timein < '.$start.' AND timeout > '.$start;

    $startunits = $DB->get_records_sql($sql);

    if($startunits){
        //if there are some (only should be 1, right?)
        //split them up to timein->$start and $start->timeout
        foreach($startunits as $unit){
            $origid = $unit->id;
            $timeout = $unit->timeout; 
            $timein = $unit->timein;

            unset($unit->id); 
            $unit->timeout = $start;

            $result = $DB->insert_record('block_timetracker_workunit', $unit);

            if(!$result) {
                print_error("Error splitting boundary work unit");
                return;
            }
            
            unset($unit->id);
            $unit->timein = $start;
            $unit->timeout = $timeout;
            
            $result = $DB->insert_record('block_timetracker_workunit', $unit);

            if(!$result) {
                print_error("Error splitting boundary work unit");
                return;
            }

            //delete the original
            $DB->delete_record('block_timetracker_workunit', array('id'=>$origid));
            //TODO update workunit history here?
        }
    }

    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        'userid = '.$userid.' AND courseid = '.$courseid.' AND submitted=0'.
        'timein >= '.$start.' AND timeout > '.$end;

    $endunits = $DB->get_records_sql($sql);

    if($endunits){
        //if there are some (only should be 1, right?)
        //split them up to timein->$end and $end->timeout
        foreach($endunits as $unit){
            $origid = $unit->id;
            $timeout = $unit->timeout; 
            $timein = $unit->timein;

            unset($unit->id); 
            $unit->timeout = $start;

            $result = $DB->insert_record('block_timetracker_workunit', $unit);

            if(!$result) {
                print_error("Error splitting boundary work unit");
                return;
            }
            
            unset($unit->id);
            $unit->timein = $start;
            $unit->timeout = $timeout;
            
            $result = $DB->insert_record('block_timetracker_workunit', $unit);

            if(!$result) {
                print_error("Error splitting boundary work unit");
                return;
            }

            //delete the original
            $DB->delete_record('block_timetracker_workunit', array('id'=>$origid));
            //TODO update workunit history here?
        }
    }

}

/**
* Given a $unit (full of workunit table data), return an array of $unit objects
* That are split across the day boundary
* TO NOTE: Units that are split up have a 'partial' property that is set to True.
* @return an array of workunits split up into days
*/
function split_unit($unit){
    $splitunits = array();

    if(!is_object($unit)) return $splitunits;

    $timein = usergetdate($unit->timein);
    $timeout = usergetdate($unit->timeout);

    //check to see if in and out are on the same day
    if($timein['year'] == $timeout['year'] && 
        $timein['month'] == $timeout['month'] &&
        $timein['mday'] == $timeout['mday']){

        $newunit = new stdClass();
        $newunit->timein = $unit->timein;
        $newunit->timeout = $unit->timeout;
        $newunit->payrate = $unit->payrate;
        $newunit->lastedited = $unit->lastedited;
        $newunit->lasteditedby = $unit->lasteditedby;
        $newunit->id = $unit->id;
        $newunit->userid = $unit->userid;
        $newunit->courseid = $unit->courseid;
        $newunit->partial = 0;
        $newunit->timesheetid = $unit->timesheetid;
        $newunit->canedit = $unit->canedit;
        $newunit->submitted = $unit->submitted;
    
        $splitunits[] = $newunit;
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
        
            //add to array
            $unit->timeout = $endofday;

            $newunit = new stdClass();
            $newunit->timein = $unit->timein;
            $newunit->timeout = $unit->timeout;
            $newunit->payrate = $unit->payrate;
            $newunit->lastedited = $unit->lastedited;
            $newunit->lasteditedby = $unit->lasteditedby;
            $newunit->id = $unit->id;
            $newunit->userid = $unit->userid;
            $newunit->courseid = $unit->courseid;
            $newunit->partial = true;
            $newunit->timesheetid = $unit->timesheetid;
            $newunit->canedit = $unit->canedit;
            $newunit->submitted = $unit->submitted;
    
            $splitunits[] = $newunit;
    
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
    }
    return $splitunits;
}


/*
* uses get_split_units() to return an array of unit objects, none of
* which cross the day boundary. Note: id of these units may be shared.
* @return array of unit objects
*
*/
function get_split_month_work_units($userid, $courseid, $month, $year, $timesheetid=-1){
    $info = get_month_info($month, $year);

    return get_split_units($info['firstdaytimestamp'], $info['lastdaytimestamp'],
        $userid, $courseid, $timesheetid);
}


/**
* Given an object that holds all of the values necessary from block_timetracker_workunit,
* Add it to the workunit table.
* @return true if worked, false if failed
*/
function add_unit($unit, $hourlog=false){
    global $DB, $CFG, $OUTPUT;

    if(!is_object($unit)) return false;
    if(isset($unit->id)) unset($unit->id);

    $unitid = $DB->insert_record('block_timetracker_workunit', $unit);

    $url = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php');
    $url->params(array('id'=>$unit->courseid, 
        'userid'=>$unit->userid,
        'reportstart'=>($unit->timein-1),
        'reportend'=>($unit->timeout+1)));

    if($unitid){
        if($hourlog)
            add_to_log($unit->courseid, '', 'add work unit', '', 'TimeTracker add work unit');
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
}

/**
* Given an object that holds all of the values necessary from block_timetracker_workunit,
* add update it in the DB
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
function find_conflicts($timein, $timeout, $userid, $unitid=-1, $courseid=-1,
    $ispending=false){

    global $CFG, $COURSE, $DB;
    if($courseid == -1) $courseid = $COURSE->id;
    
    //check workunit table first
    $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE '.
        "$userid = userid AND $courseid = courseid AND (".
        "($timein >= timein AND $timein < timeout) OR ".
        "($timeout > timein AND $timeout <= timeout) OR ".
        "(timein >= $timein AND timein < $timeout))";
        
    if($unitid != -1 && !$ispending){
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
            get_string('datetimeformat', 'block_timetracker'),99,false);
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

    if($unitid != -1 && $ispending){
        $sql.=" AND id != $unitid"; 
    }

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
* Used in navigation
* @return array of tabobjects 
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
    $tabs[] = new tabobject('timesheets',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheet.php',
        $urlparams), 'Timesheets');

    
    $numalerts = '';
    if($canmanage){
        $manageurl = 
            new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php', 
            $urlparams);
        $manageurl->remove_params('userid');
        $tabs[] = new tabobject('manage', $manageurl, 'Manage Workers');
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
    global $COURSE, $DB;

    //before displaying anything, add any enrolled users NOT in the WORKERINFO table.
    //consider moving this to a 'refresh' link or something so it doesn't do it everytime?
    //TODO
    $config = get_timetracker_config($COURSE->id);
    $students = get_users_by_capability($context, 'mod/assignment:submit');
    foreach ($students as $student){
        if(!$DB->record_exists('block_timetracker_workerinfo',
            array('mdluserid'=>$student->id, 'courseid'=>$COURSE->id))){
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
                print_error("Error adding $student->firstname $student->lastname ".
                    "to TimeTracker");
            }
        }
    }

}

/*
* rounds to nearest 15 minutes (900 secs) by default. can be set
* using config_block_timetracker_round
*/
function round_time($totalsecs=0, $round=900){
    if($totalsecs <= 0) return 0;
    
    if($round > 0){
        $temp = $totalsecs % 3600;
        $disttoround = $temp % $round;
    
        if($disttoround >= ($round/2)) 
            $totalsecs = $totalsecs + ($round - $disttoround); //round up
        else 
            $totalsecs = $totalsecs - $disttoround; //round down
    }

    return $totalsecs;
}

/*
* @return number of hours in decimal format, rounded to the nearest config->round
*/
function get_hours($totalsecs=0, $courseid=-1){

    $round = get_rounding_config($courseid);
    $totalsecs = round_time($totalsecs, $round);
    $hrs = round($totalsecs/3600, 3);
    return ($hrs);
}


/**
* returns the $totalsecs as 'xx hour(s) xx minute(s)', rounded to the nearest 15 min
*/
function format_elapsed_time($totalsecs=0, $courseid=-1){
    if($totalsecs <= 0){
        return '0 hours 0 minutes';
    }

    $round = get_rounding_config($courseid);
    $totalsecs = round_time($totalsecs, $round);
    $hours = floor($totalsecs/3600);
    $minutes = ($totalsecs % 3600)/60;
    
    return $hours.' hour(s) and '.$minutes. ' minute(s)'; 
}

function get_rounding_config($courseid = -1){
    if($courseid == -1){
        $round = 900;
    } else {
        $config = get_timetracker_config($courseid);
        if(array_key_exists('round', $config)){
            $round = $config['round'];
        } else {
            $round = 900;
        }
    }
    return $round;
}

/**
* Calculate Total Earnings 
* @param $userid, $courseid
* @return total money earned
*/
function get_total_earnings($userid, $courseid){

    $workerunits = get_split_units(0, time(), $userid, $courseid);

    if(!$workerunits) return 0;
    $round = get_rounding_config($courseid);

    $earnings = 0;
    foreach($workerunits as $subunit){
        $hours = round_time($subunit->timeout - $subunit->timein, $round);
        $hours = round($hours/3600, 3);
        $earnings += $hours * $subunit->payrate;
    }

    return round($earnings, 2);

}

/**
* Determine if the course has alerts waiting
* @param $courseid id of the course
* @return 0 if no alerts are pending, # of alerts if they exist.
*/
function has_course_alerts($courseid){
    global $CFG, $DB;
    //check the alert* tables to see if there are any outstanding alerts:
    $sql = 'SELECT COUNT(*) FROM '.
        $CFG->prefix.'block_timetracker_alertunits'.
        ' WHERE courseid='.$courseid.
            ' ORDER BY alerttime';
    $numalerts = $DB->count_records_sql($sql);
    return $numalerts;

}


/**
* Generate the alert links for a course
* @param $courseid id of the course
* @return three-dimensional array of links. First index is the TT worker id, the second is 
* the alertid, while the third index is either 'approve', 'deny', or 'change' for each of
* the corresponding links
*/
function get_course_alert_links($courseid){
    global $CFG, $DB;
    //check the alert* tables to see if there are any outstanding alerts:
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_alertunits.* FROM '.
        $CFG->prefix.'block_timetracker_alertunits '.
        'WHERE courseid='.$courseid.
            ' ORDER BY alerttime';

    $alerts = $DB->get_recordset_sql($sql);
    $alertlinks = array();
    foreach ($alerts as $alert){
        $url = $CFG->wwwroot.'/blocks/timetracker/alertaction.php';
        
        $params = "?alertid=$alert->id";
        if($alert->todelete) $params.="&delete=1";

        $alertlinks[$alert->userid][$alert->id]['approve'] = $url.$params."&action=approve";
        $alertlinks[$alert->userid][$alert->id]['deny'] = $url.$params."&action=deny";
        $alertlinks[$alert->userid][$alert->id]['change'] = $url.$params."&action=change";
        $alertlinks[$alert->userid][$alert->id]['delete'] = $url.$params."&action=delete";

    }

    return $alertlinks;
}




/**
* Generate the alert links for a course
* @param $courseid id of the course
* @param $alerticon create the alert icon (using new pix_icon)
* @param $alertaction create the action of the alert (usually $OUTPUT->action_icon($alertsurl,$alertaction) 
* @return hyperlink with number of existing alerts
* Example: Manage Alerts (3)
*/
function get_alerts_link($courseid, $alerticon, $alertaction){
    global $CFG, $DB;
    //getnumalerts from $courseid
    $numalerts = '';
    $n = has_course_alerts($courseid);
    if($n > 0){
        $numalerts = '('.$n.')';
    }
    
    $urlparams['id'] = $courseid;
    $baseurl = $CFG->wwwroot.'/blocks/timetracker';
    $url = new moodle_url($baseurl.'/managealerts.php', $urlparams);
    $text = $alertaction.' <a href="'.$url. 'style="color: red">Manage Alerts '.$numalerts.'</a><br />';

    return $text;
}

/**
* Determine if the course has alerts waiting
* @param $courseid id of the course
* @return 0 if no alerts are pending, # of alerts if they exist.
*/
function has_unsigned_timesheets($courseid){
    global $CFG, $DB;
    //check the timesheet table to see if there are any unsigned timesheets:
    $numtimesheets = $DB->count_records('block_timetracker_timesheet',
        array('courseid'=>$courseid,
        'supervisorsignature'=>0));
    return $numtimesheets;

}

function get_timesheet_link($courseid, $timesheetsicon, $timesheetsaction){
    global $CFG, $DB;
    //getnumalerts from $courseid
    $numts = '';
    $n = has_unsigned_timesheets($courseid);
    if($n > 0){
        $numts = '('.$n.')';
    }

    $urlparams['id'] = $courseid;
    $baseurl = $CFG->wwwroot.'/blocks/timetracker';
    $url = new moodle_url($baseurl.'/supervisorsig.php', $urlparams);
    $text = $timesheetsaction.' <a href="'.$url. 'style="color: red">View
        Timesheets '.$numts.'</a><br /><br />';

    return $text;
}



/**
* Calculate Total Hours
* @param $workerunits is an array, each $subunit has $subunit->timein and $subunit->timeout
* @return total hous worked in decimal format rounded to the nearest interval.
*/
function get_total_hours($userid, $courseid){

    $workerunits = get_split_units(0, time(), $userid, $courseid);

    if(!$workerunits) return 0;

    $round = get_rounding_config($courseid);
    $total = 0;
    foreach($workerunits as $subunit){
        $total += round_time($subunit->timeout - $subunit->timein, $round);
    }

    return get_hours($total, $courseid);

}

/**
* Also calculates overtime (Weeks > 40 hours Mon-Sun)
* @return earnings (in dollars) for this time period
*
*/
function get_earnings($userid, $courseid, $start, $end, $processovt=1){

    global $DB;

    $units = get_split_units($start, $end, $userid, $courseid);
    if(!$units) return 0;

    $info = break_down_earnings($units, $processovt);
    return $info['earnings'];
}


/**
* Break down earnings into reghours, regearnings, ovthours, ovtearnings,hours,earnings
* All $unit in $units should be from the same userid/courseid
* @return array
*/
function break_down_earnings($units, $processovt = 1){
    global $DB;
    $info = array();
    $info['reghours'] = 0;
    $info['regearnings'] = 0;
    $info['ovthours'] = 0;
    $info['ovtearnings'] = 0;
    $info['hours'] = 0;
    $info['earnings'] = 0;

    if(!$units) return $info;
    $exampleunit = reset($units);

    $round = get_rounding_config($exampleunit->courseid);

    $worker = $DB->get_record('block_timetracker_workerinfo',
        array('id'=>$exampleunit->userid));

    if(!$worker) return $info;
    if(!$processovt){

        $earnings = 0;
        foreach($units as $unit){
            $hours = round_time($unit->timeout - $unit->timein, $round);
            $hours = round($hours/3600, 3);
            $info['hours'] += $hours;
            $info['reghours'] += $hours;
            $earnings += $hours * $unit->payrate;
        }
    
        $info['earnings'] = $info['regearnings'] = round($earnings, 2);
    } else {

        $earnings =  $weekhours = $prevweekday = $prevdate = 0;
    
        foreach($units as $unit){
            //weekday will be from 1 (Monday) to 7 (Sunday)
            //if weekday is < prevweekday, we're on a new week.

    
            $weekday = userdate($unit->timein, "%u");
            $date = userdate($unit->timein, "%Y%m%d");
            if($weekday < $prevweekday ||
                ($weekday == $prevweekday && $date != $prevdate)){
                $weekhours = 0;
            }
            $prevweekday = $weekday; 
            $prevdate = $date;
    
            $hours = round_time($unit->timeout - $unit->timein, $round);
            $hours = round($hours/3600, 3);
    
            if( ($hours + $weekhours) > 40){
                $ovthours = $reghours = 0; 
                if($weekhours > 40){
                    $ovthours = $hours;
                } else {
                    $reghours = 40 - $weekhours; 
                    $ovthours = $hours - $reghours;
                }
    
                $info['reghours'] += $reghours;
                $info['ovthours'] += $ovthours;
    
                $amt = $reghours * $unit->payrate;
                $info['regearnings'] += $amt;
                
                $ovtamt = $ovthours * ($worker->currpayrate * 1.5);
                $info['ovtearnings'] += $ovtamt;
    
            } else {
                $amt = $hours * $unit->payrate;
                $info['reghours'] += $hours;
                $info['regearnings'] += $amt;
            }
            $weekhours += $hours;
        }
    
        $info['regearnings'] = round($info['regearnings'], 2);
        $info['ovtearnings'] = round($info['ovtearnings'], 2);
        $info['earnings'] = round($info['regearnings']+$info['ovtearnings'], 2);
        $info['hours'] = $info['reghours'] + $info['ovthours'];

    }
    return $info;
}

/**
* @return earnings (in dollars) for this month
*
*/
function get_earnings_this_month($userid, $courseid, $month = -1, $year = -1){
    $currtime = usergetdate(time());

    if($month == -1 || $year == -1){
        $units = get_split_month_work_units($userid, $courseid, 
            $currtime['mon'], $currtime['year']);
    } else {
        $units = get_split_month_work_units($userid, $courseid, 
            $month, $year);
    }

    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $earnings = 0;
    foreach($units as $unit){
        $hours = round_time($unit->timeout - $unit->timein, $round);
        $hours = round($hours/3600, 3);
        $earnings += $hours * $unit->payrate;
    }
    return round($earnings, 2);
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
function get_month_info($month, $year){
    $monthinfo = array();
    
    $timestamp = make_timestamp($year, $month); //timestamp of midnight, first day of $month
    $monthinfo['firstdaytimestamp'] = $timestamp;
    $monthinfo['lastday'] = date('t', $timestamp);

    $thistime = usergetdate($timestamp);
    $monthinfo['dayofweek'] = $thistime['wday'];
    $monthinfo['monthname'] = $thistime['month'];

    $timestamp = make_timestamp($year, $month, $monthinfo['lastday'],23,59,59);
    $monthinfo['lastdaytimestamp'] = $timestamp; //23:59:59pm

    return $monthinfo;
}

/**
* @return hours (in decimal) for this defined period
*
*/
function get_hours_this_period($userid, $courseid, $start, $end){

    $units = get_split_units($start, $end, $userid, $courseid);

    $config = get_timetracker_config($courseid);


    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein, $round);
    }

    return get_hours($total, $courseid);
}


/**
* @return hours (in decimal) for this month
*
*/
function get_hours_this_month($userid, $courseid, $month = -1, $year = -1){
    $currtime = usergetdate(time());
    if($month == -1 || $year == -1){
        $units = get_split_month_work_units($userid, $courseid,
            $currtime['mon'], $currtime['year']);
    } else {
        $units = get_split_month_work_units($userid, $courseid,
            $month, $year);
    }

    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein, $round);
    }
    return get_hours($total, $courseid);
}

/**
* @return earnings (in dollars) for this calendar year
*
*/
function get_earnings_this_year($userid, $courseid){
    $currtime = usergetdate(time());

    $firstofyear = make_timestamp($currtime['year']); //defaults to jan 1 midnight
    $endofyear = make_timestamp($currtime['year'], 12, 31, 23, 59, 59);

    $units = get_split_units($firstofyear, $endofyear, $userid, $courseid);


    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $earnings = 0;
    foreach($units as $unit){
        $hours = round_time($unit->timeout - $unit->timein, $round);
        $hours = round($hours/3600, 3);
        $earnings += $hours * $unit->payrate;
    }

    return round($earnings, 2);
}

/**
* @return hours (in decimal) for this calendar year
*
*/
function get_hours_this_year($userid, $courseid){
    $currtime = usergetdate(time());

    $firstofyear = make_timestamp($currtime['year']); //defaults to jan 1 midnight
    $endofyear = make_timestamp($currtime['year'], 12, 31, 23, 59, 59);

    $units = get_split_units($firstofyear, $endofyear, $userid, $courseid);

     
    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein, $round);
    }
    return get_hours($total, $courseid);
}

/**
*
* @return array 'termstart' and 'termend'
*/
function get_term_boundaries($courseid){
    global $DB;
    $currtime = time();
    $year = date("Y");

    $terms = $DB->get_records('block_timetracker_term',
        array('courseid'=>$courseid), 'month, day');

    $termstart = 0;
    $termend = 0;
    if($terms){

        $term_times = array();
        $counter = 0;
        foreach($terms as $term){
            $tstart = mktime(0,0,0, $term->month, $term->day, $year);
            $term_times[] = $tstart; 
            if($counter == 0){
                $term_times[] = mktime(0,0,0, $term->month, $term->day, $year+1);
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
function get_hours_this_term($userid, $courseid=-1){

    $boundaries = get_term_boundaries($courseid);
    
    $units = get_split_units($boundaries['termstart'], $boundaries['termend'],
        $userid, $courseid);

    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein, $round);
    }
    return get_hours($total, $courseid);
}

function get_earnings_this_term($userid, $courseid){

    $boundaries = get_term_boundaries($courseid);
    
    $units = get_split_units($boundaries['termstart'], $boundaries['termend'],
        $userid, $courseid);

    if(!$units) return 0;
    $round = get_rounding_config($courseid);
    $earnings = 0;
    foreach($units as $unit){
        $hours = round_time($unit->timeout - $unit->timein);
        $hours = round($hours/3600, 3);
        $earnings += $hours * $unit->payrate;
    }
    return round($earnings, 2);
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
function get_worker_stats($userid, $courseid){
    global $DB;

    $stats['totalhours'] = get_total_hours($userid, $courseid);
    $stats['monthhours'] = get_hours_this_month($userid, $courseid);
    $stats['yearhours'] = get_hours_this_year($userid, $courseid);
    $stats['termhours'] = get_hours_this_term($userid, $courseid);

    $stats['totalearnings'] = get_total_earnings($userid, $courseid);
    $stats['monthearnings'] =get_earnings_this_month($userid, $courseid);
    $stats['yearearnings'] = get_earnings_this_year($userid, $courseid);
    $stats['termearnings'] = get_earnings_this_term($userid, $courseid);


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
    $round = get_rounding_config($courseid);
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
            $hours = round_time($u->timeout - $u->timein, $round);
            $hours = round($hours/3600, 3);
            $lu = 
                userdate($u->timein,get_string('datetimeformat','block_timetracker')).
                '<br />'.
                userdate($u->timeout,get_string('datetimeformat','block_timetracker')).
                '<br />'.
                number_format($hours, 2).' hours';
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
        $key = preg_replace('/block\_timetracker\_/','', $conf->name);
        $config[$key] = $conf->value;
    }

    return $config;
}



