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

require_once(dirname(__FILE__) . '/../../../config.php');
require_once('../lib.php');
require_once('check_student_schedules.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$start = required_param('start', PARAM_INTEGER);
$end = required_param('end', PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$urlparams['start'] = $start;
$urlparams['end'] = $end;


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;



if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    print_error('This page is not accessible by a supervisor');
}

$worker = $DB->get_record('block_timetracker_workerinfo',array('id'=>$userid));

if(!$worker){
    print_error('usernotexist', 'block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$course->id);
}

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);
$index->remove_params('userid', 'start', 'end');

$timesheeturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheet.php', $urlparams);
$timesheeturl->remove_params('start', 'end');

$workersigurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/workersig.php', $urlparams);


$conflicts = check_worker_hours_for_conflicts($userid, $start, $end);
if(sizeof($conflicts) == 0){
    redirect($workersigurl);
}

$strtitle = get_string('pluginname','block_timetracker');

$PAGE->set_url($timesheeturl);
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

echo $OUTPUT->header();


$tabs = get_tabs($urlparams, false, $courseid);
$tabs = array($tabs);
print_tabs($tabs, 'home');


echo $OUTPUT->box_start();
    echo "<p><h3>There are work units in this time range that conflict with your scheduled class
    times. Please alert the below units to your supervisor and wait for them to be
    corrected before you submit a timesheet.</h3></p>";
    //echo '<p>'.$OUTPUT->action_link($timesheeturl, 'Timesheets').'</p>';
    
echo $OUTPUT->box_end();

$alertlink = new moodle_url($CFG->wwwroot.'/blocks/timetracker/alert.php');
//display any units that are conflicts, if they exist
echo $OUTPUT->box_start();

$htmldoc = '<table align="center" width="95%" style="border: 1px solid #000;">';
$htmldoc .= '<tr>
                <th colspan="3">Class Schedule Conflicts</th>
             </tr>
             <tr>
                <td style="font-weight: bold">Class Meeting</td>
                <td style="font-weight: bold">Work Unit</td>
                <td style="font-weight: bold; text-align: center">Action</td>
             </tr>';
foreach ($conflicts as $conflict){
    $htmldoc .= '<tr>';
    $htmldoc .= '<td>'.$conflict->conflictcourse.'</td>';
    $htmldoc .= '<td>'.$conflict->display.'</td>';
    $htmldoc .= '<td style="text-align: center">'.$OUTPUT->action_icon($conflict->alertlink, 
        new pix_icon('alert', 'Alert supervisor of error', 'block_timetracker')).
        '</td>';
    $htmldoc .= '</tr>';
}

$htmldoc .='</table>';

echo $htmldoc;

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
