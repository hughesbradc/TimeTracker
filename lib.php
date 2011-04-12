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
* @return number of hours in decimal format
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
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';
    $workerunits = $DB->get_recordset_sql($sql);

    if(!$workerunits) return 0;

    $earnings = 0;
    foreach($workerunits as $subunit){
        $earnings += get_hours(round_time($subunit->timeout - $subunit->timein))*$subunit->payrate;
    }

    return round($earnings,2);

}

/**
* Calculate Total Hours
* @param $workerunits is an array, each $subunit has $subunit->timein and $subunit->timeout
* @return total hous worked in decimal format rounded to the nearest interval.
*/
function get_total_hours($userid, $courseid){
    global $CFG, $DB;
    $sql = 'SELECT '.$CFG->prefix.'block_timetracker_workerinfo.firstname, '.
        $CFG->prefix.'block_timetracker_workerinfo.lastname, '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';
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
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

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
    $monthinfo['lastday'] = date('t',strtotime($timestamp));

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
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

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
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

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
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workerinfo.id='.$userid.' AND '.$CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

    $units = $DB->get_recordset_sql($sql);

    if(!$units) return 0;
    $total = 0;
    foreach($units as $unit){
        $total += round_time($unit->timeout - $unit->timein);
    }
    return get_hours($total);
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
* $stats['monthhours']
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
    
    $stats = array();
    $stats['totalhours'] = get_total_hours($userid,$courseid);
    $stats['monthhours'] = get_hours_this_month($userid, $courseid);
    $stats['yearhours'] = get_hours_this_year($userid, $courseid);
    //TODO fix this
    $stats['termhours'] = $stats['yearhours'];

    $stats['totalearnings'] = get_total_earnings($userid,$courseid);
    $stats['monthearnings'] = get_earnings_this_month($userid,$courseid);
    $stats['yearearnings'] = get_earnings_this_year($userid,$courseid);
    //TODO fix this
    $stats['termearnings'] = $stats['yearearnings'];

    return $stats; 
}


/**
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
