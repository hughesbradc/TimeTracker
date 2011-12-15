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
 * This page will allow Financial Aid to manipulate student schedules in TimeTracker
 * for use with conflicting work units.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */


//TODO - Add only Financial Aid capability to see this page.  Make pretty.  

require_once (dirname(__FILE__) . '/../../config.php');
require_once ($CFG->libdir.'/tablelib.php');
require_once ('lib.php');

global $CFG, $DB, $USER, $OUTPUT;

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

//*************** VIEW ALL STUDENT SCHEDULES IN AN HTML TABLE ***************//

$sql = 'SELECT '.$CFG->prefix.'block_timetracker_schedules.*, firstname, lastname FROM ' 
    .$CFG->prefix.'block_timetracker_schedules,'.$CFG->prefix.'block_timetracker_workerinfo '.
    ' WHERE '.$CFG->prefix.'block_timetracker_schedules.email='.$CFG->prefix.
    'block_timetracker_workerinfo.email ORDER BY lastname, firstname';
$courses = $DB->get_records_sql($sql);


if(!$courses){
    echo 'There are no student schedules in the database.';
} else {
    echo '<table align="center" cellspacing="10px" cellpadding="5px" width="95%" 
        style="border: 1px solid #000000">';
    
    echo '<tr><td style="font-weight: bold; text-align: left">Last Name</td>
            <td style="font-weight: bold; text-align: left">First Name</td>
            <td style="font-weight: bold; text-align: left">Course</td>
            <td style="font-weight: bold; text-align: left">Days</td>
            <td style="font-weight: bold; text-align: left">Time</td>
            <td style="font-weight: bold; text-align: left">Actions</td>
            </tr>';

    foreach($courses as $course){
        $row='<tr>';
            echo '<td>'.$course->lastname .'</td>';
            echo '<td>'.$course->firstname .'</td>';
            echo '<td>'.$course->course_code .'</td>';
            echo '<td>'.$course->days .'</td>';
            echo '<td>'.$course->begin_time .' to ' .$course->end_time .'</td>';

            //SQL statements to edit or delete a class from a student's schedule
            $modifyurl = new moodle_url($baseurl.'/modifyschedule.php', $urlparams); 

            //Icons and actions
            $addicon = $OUTPUT->action_icon($modifyurl, new pix_icon
                ('clock_add',get_string('addentry','block_timetracker'), 'block_timetracker'));
            $editaction = $OUTPUT->action_icon($modifyurl, new pix_icon('clock_edit',
                get_string('edit'),'block_timetracker'));
            $deleteicon = new pix_icon('clock_delete', get_string('delete'),'block_timetracker');
            $deleteaction = $OUTPUT->action_icon($modifyurl, $deleteicon, new confirm_action('Are you sure you
                want to delete this course from '.$course->firstname .' '.$course->lastname .'\'s schedule?'));
           
            //Add action icons to the last column in the table
            echo '<td>'.$editaction.' '.$deleteaction .'</td>';
        $row.='</tr>';
    
        echo $row; 
    }
    echo '</table>'; 
}
?>
