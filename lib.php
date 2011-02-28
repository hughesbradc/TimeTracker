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


function format_elapsed_time($totalsecs=0){
    if($totalsecs <= 0){
        return '0 hours 0 minutes';
    }

    $hours = floor($totalsecs/3600);

    $totalsecs = $totalsecs % 3600;

    //round to the nearest 900 seconds (1/4 hour)
    if($totalsecs < 450) {
        $minutes = '0 minutes';
    } else if($totalsecs < 1350){
        $minutes = '15 minutes';
    } else if ($totalsecs < 2250){
        $minutes = '30 minutes';
    } else if ($totalsecs < 3150){
        $minutes = '45 minutes';
    } else {
        $minutes = '0 minues';
        $hours++;
    }
    
    return $hours.' hours and '.$minutes; 
    
}
