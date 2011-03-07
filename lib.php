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
function get_elapsed_time($totalsecs=0){
    if($totalsecs <=0) return 0;
    
    $temp = $totalsecs % 3600;
    $distto900 = $temp % 900;
    if($distto900 > 449) 
        $totalsecs = $totalsecs + (900 - $distto900); //round up
    else 
        $totalsecs = $totalsecs - $distto900; //round down

    return $totalsecs;

    
}

/**
* returns the $totalsecs as 'xx hour(s) xx minute(s)', rounded to the nearest 15 min
*/
function format_elapsed_time($totalsecs=0){
    if($totalsecs <= 0){
        return '0 hours 0 minutes';
    }

    $totalsecs = get_elapsed_time($totalsecs);
    $hours = floor($totalsecs/3600);
    $minutes = ($totalsecs % 3600)/60;
    
    return $hours.' hour(s) and '.$minutes. ' minute(s)'; 
}

/**
* Calculate Total Hours
* @param $workerunits is an array, each $subunit has $subunit->timein and $subunit->timeout
*/
function get_total_hours($workerunits){

    if(!$workerunits) return 0;

}


/**
*
*/
function get_user_stats($userid,$courseid){

    $units = $DB->get_recordset_sql(get_string('allunits','block_timetracker', $userid, $courseid));
    if(!$units) return '';
    
    $totalhours=0;

}
