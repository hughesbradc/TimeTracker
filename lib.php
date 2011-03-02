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
