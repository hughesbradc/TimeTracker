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
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('lib.php');
require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->libdir/excellib.class.php");
require_once('../../lib/moodlelib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/

function generate_xls (){

    global $CFG, $DB, $USER;

    //find all workers
    $workers = $DB->get_records('block_timetracker_workerinfo');

    foreach($workers as $worker){
        //demo courses, et. al.
        if($worker->courseid >= 73 && $worker->courseid <= 76){
            continue;
        }

        $earnings = get_earnings_this_term($worker->id,$worker->courseid);
    
        $course = $DB->get_record('course', array('id'=>$worker->courseid));
    
        $remaining = $worker->maxtermearnings - $earnings;

    //Formatting
    $format_bold =& $workbook->add_format();
    $format_bold->set_bold();

    
    //Create worksheet
    $worksheet = array();
    $worksheet[1] =& $workbook->add_worksheet('Earnings');

    // Set column widths
    $worksheet[1]->set_column(0,8,10.57);

    //Write header data
    $worksheet[1]->write_string(0,0,'Department', $format_bold);
    $worksheet[1]->write_string(1,0,'Worker Name', $format_bold);
    $worksheet[1]->write_string(2,0,'Earnings This Term', $format_bold);
    $worksheet[1]->write_string(3,0,'Maximum Term Earnings', $format_bold);
    $worksheet[1]->write_string(4,0,'Amount Remaining', $format_bold);

    //Write data to spreadsheet



    //Finish worksheet and workbook and close
    $workbook->close();
    return $fn;

}











//Print results in a table
echo '<table cellspacing="10" cellpadding="5" width="85%">';
    echo '<tr>';
    echo '<td><b>Department</b></td>';
    echo '<td><b>Worker Name</b></td>';
    echo '<td><b>Earnings This Term</b></td>';
    echo '<td><b>Maximum Term Earnings</b></td>';   
    echo '<td><b>Amount Remaining</b></td>';
    echo '</tr>';


        echo '<tr><td>'.$course->shortname.'</td><td>'.$worker->lastname.', '
            .$worker->firstname.'</td><td>'.$earnings.'</td><td>'.$worker->maxtermearnings
            .'</td><td>'.$remaining.'</td></tr>';
    }

echo '</table>';

?>
