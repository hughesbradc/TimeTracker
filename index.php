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

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);

$urlparams['id'] = $courseid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$index = new moodle_url('/blocks/timetracker/index.php', $urlparams);

$PAGE->set_url($index);
//$PAGE->set_pagelayout('base');

$strtitle = 'TimeTracker';

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

//print_object($urlparams);
//$timetrackerurl = new moodle_url('/blocks/timetracker/index.php',$urlparams);

//$PAGE->navbar->add(get_string('blocks'));
//$PAGE->navbar->add(get_string('pluginname', 'block_timetracker'), $timetrackerurl);
//$PAGE->navbar->add($strtitle);

$PAGE->set_pagelayout('course');
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->set_other_editing_capability('moodle/course:manageactivities');


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle, 2);

if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $user = $DB->get_record('user',array('id'=>$USER->id));
    if(!$user){
        print_error('User is not known to TimeTracker. Please register on the course main page');
    }
    echo 'Hello, '.$user->firstname.' '.$user->lastname.'!';
    echo '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">';
    echo '<tr><th colspan=5">Last 10 Work Units</th></tr>'."\n";
    echo '<tr>
            <th>Worker name</th>
            <th>Time in</th>
            <th>Time out</th>
            <th>Elapsed</th>
            <th>Action</th>
         ';
    $last10unitssql = "SELECT {$CFG->prefix}block_timetracker_workerinfo.firstname, {$CFG->prefix}block_timetracker_workerinfo.lastname,
        {$CFG->prefix}block_timetracker_workunit.* FROM {$CFG->prefix}block_timetracker_workerinfo,{$CFG->prefix}block_timetracker_workunit
        WHERE {$CFG->prefix}block_timetracker_workunit.userid={$CFG->prefix}block_timetracker_workerinfo.id
        AND {$CFG->prefix}block_timetracker_workunit.courseid=$courseid 
        ORDER BY {$CFG->prefix}block_timetracker_workunit.timeout DESC LIMIT 10";
    //print_object($last10unitssql);
    $last10units = $DB->get_recordset_sql($last10unitssql);
    //print_object($last10units);
    if(!$last10units){
        echo '<tr><td colspan="5" style="text-align:center">No work units found</a></td></tr>';
    } else {
        foreach($last10units as $unit){
                $row='<tr>';
                $row.='<td>'.$unit->firstname. ' '.$unit->lastname.'</td>';
                $row.='<td style="text-align: center">'.userdate($unit->timein,get_string('datetimeformat','block_timetracker')).'</td>';
                $row.='<td style="text-align: center">'.userdate($unit->timeout,get_string('datetimeformat','block_timetracker')).'</td>';
                $currelapsed = $unit->timeout - $unit->timein;  
                $row.='<td style="text-align: center">'.format_elapsed_time($currelapsed).'</td>';

                $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                $paramstring = "?id=$unit->courseid&userid=$unit->userid&sesskey=".sesskey().'&unitid='.$unit->id;
    
                $editurl = new moodle_url($baseurl.'/editworkunit.php'.$paramstring);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
        
                $deleteurl = new moodle_url($baseurl.'/deleteworkunit.php'.$paramstring);
                $deleteicon = new pix_icon('t/delete', get_string('delete'));
                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action('Are you sure you want to delete this work unit?'));
    
                $row .= '<td style="text-align: center">'.$editaction . ' '.$deleteaction.'</td>';
    
                $row .= '</tr>';
                echo $row."\n";
        }
    }
    echo '</table>';

} else { //worker
    $user = $DB->get_record('block_timetracker_workerinfo',array('userid'=>$USER->id));
    if(!$user){
        print_error('User is not known to TimeTracker. Please register on the course main page');
    }
    echo 'Hello, '.$user->firstname.' '.$user->lastname.'!';
}

echo $OUTPUT->footer();
