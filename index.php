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
require_once($CFG->libdir . '/tablelib.php');
require_once('lib.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = optional_param('userid',$USER->id, PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$PAGE->set_url($index);
//$PAGE->set_pagelayout('base');

$strtitle = 'TimeTracker';

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$PAGE->set_pagelayout('course');
//$PAGE->set_pagetype('course-view-' . $course->format);
//$PAGE->set_other_editing_capability('moodle/course:manageactivities');

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

echo $OUTPUT->header();
//echo $OUTPUT->heading($strtitle, 2);
$maintabs[] = new tabobject('home', $index, 'Main');
$maintabs[] = new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams), 'Reports');
if($canmanage){
    $maintabs[] = new tabobject('manage', new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 'Manage Workers');
}

$tabs = array($maintabs);
/*
$tabs = array(array(
    new tabobject('home', $index, 'main'),
    new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams), 'Reports'),
    new tabobject('manage', new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 'Manage Workers'),
    ));
*/

print_tabs($tabs, 'home');

if ($canmanage) { //supervisor
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    //echo $OUTPUT->box_start();
    //echo format_module_intro('assignment', $this->assignment, $this->cm->id);
    echo 'Welcome, supervisor!<br />'; 
    echo '<br />Below you will find the last 10 work units by your employees<br />';
    /*
    echo 'Here are two example clock in (green) and clock out (redish) icons<br />';

    $clockinicon = new pix_icon('clock_in','Clock in', 'block_timetracker');
    $clockinaction = $OUTPUT->action_icon($index, $clockinicon);

    $clockouticon = new pix_icon('clock_out','Clock out','block_timetracker');
    //print_object($clockouticon);
    $clockoutaction = $OUTPUT->action_icon($index, $clockouticon);

    $timeclockdataicon = new pix_icon('timeclock_data', 'Manage', 'block_timetracker');
    $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);

    echo $clockinaction.'<br />'.$clockoutaction.'<br />'.$timeclockdataaction.'<br />';
    */
    echo $OUTPUT->box_end();


    $user = $DB->get_record('user',array('id'=>$USER->id));
    if(!$user){
        print_error('User is not known to TimeTracker. Please register on the course main page');
    }
    echo '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">';
    echo '<tr><th colspan=5">Last 10 Work Units</th></tr>'."\n";
    echo '<tr>
            <th>Worker name</th>
            <th>Time in</th>
            <th>Time out</th>
            <th>Elapsed</th>
            <th>Action</th>
         ';

    $last10unitssql = 'SELECT '.$CFG->prefix.'block_timetracker_workerinfo.firstname, '.
        $CFG->prefix.'block_timetracker_workerinfo.lastname, '.$CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.$CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.userid='.$CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

    $last10units = $DB->get_recordset_sql($last10unitssql);

    if(!$last10units){
        echo '<tr><td colspan="5" style="text-align:center">No work units found</a></td></tr>';
    } else {
        foreach($last10units as $unit){
                $row='<tr>';
                $row.='<td style="text-align: center">'.$unit->firstname. ' '.$unit->lastname.'</td>';
                $row.='<td style="text-align: center">'.userdate($unit->timein,get_string('datetimeformat','block_timetracker')).'</td>';
                $row.='<td style="text-align: center">'.userdate($unit->timeout,get_string('datetimeformat','block_timetracker')).'</td>';
                $currelapsed = $unit->timeout - $unit->timein;  
                $row.='<td style="text-align: center">'.format_elapsed_time($currelapsed).'</td>';

                $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                $paramstring = "?id=$unit->courseid&userid=$unit->userid&sesskey=".sesskey().'&unitid='.$unit->id;
    
                $editurl = new moodle_url($baseurl.'/editworkunit.php'.$paramstring);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

                //$icon = $OUTPUT->pix_url('clock_in','timetracker');
                //$icon = new pix_icon('clock_in','Clock in','block_timetracker');
                //$editaction = $OUTPUT->action_icon($editurl, $icon);
        
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
    //print_object(get_worker_stats(1,2));

} else { //worker
    $user = $DB->get_record('block_timetracker_workerinfo',array('userid'=>$USER->id));
    if(!$user){
        print_error('User is not known to TimeTracker. Please register on the course main page');
    }
    $userUnits = $DB->get_records('block_timetracker_workunit',array('userid'=>$user->id),'timeout DESC','*',0,10);
    $userPending = $DB->get_records('block_timetracker_pending', array('userid'=>$user->id));

    //add clockin/clockout box
    if(!$userPending){
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>Clock in?</h2>';
        $clockinicon = new pix_icon('clock_in','Clock in', 'block_timetracker');
        $clockinurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php',$urlparams);
        $clockinurl->params(array('clockin'=>1));
        $clockinaction = $OUTPUT->action_icon($clockinurl, $clockinicon);
        echo "You are not currently clocked in. Click the green clock below to clock in now.<br />";
        echo $clockinaction;
        echo $OUTPUT->box_end();
    }


    //summary data
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<h2>Worker Summary Data</h2>';
    $stats = get_worker_stats($userid, $courseid);

    $statstable = new flexible_table('timetracker-display-worker-summary');
    $statstable->define_columns(array('perid', 'hvalue','evalue'));
    $statstable->define_headers(array('Period', 'Hours', 'Earnings'));
    $statstable->set_attribute('cellspacing', '0');
    $statstable->setup();

    $statstable->add_data(array(
        'This month',$stats['monthhours'],
        '$'.$stats['monthearnings']
        ));
    $statstable->add_data(array(
        'This term',$stats['termhours'],
        '$'.$stats['termearnings']
        ));
    $statstable->add_data(array(
        'This year',$stats['yearhours'],
        '$'.$stats['yearearnings']
        ));
    $statstable->add_data(array(
        'Total hours',$stats['totalhours'],
        '$'.$stats['totalearnings']
        ));

    $statstable->print_html();

    echo $OUTPUT->box_end();

    //clockin/clockout box
    if($userPending){
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>Pending Clock-ins</h2>';
        //show clockout and pending clock-in
        $table = new flexible_table('timetracker-display-worker-index');
    
        //$table->define_columns(array('timein', 'timeout', 'elapsed', 'action'));
        $table->define_columns(array('timein', 'action'));
        //$table->define_headers(array('Time in', 'Time out', 'Elapsed', 'Action'));
        $table->define_headers(array('Time in', 'Action'));
        //$table->define_headers(array(get_string('feed', 'block_rss_client'), get_string('actions', 'moodle')));
        
        $table->set_attribute('cellspacing', '0');
        //$table->set_attribute('id', '');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->column_class('timein', 'timein');
        //$table->column_class('timeout', 'timeout');
        //$table->column_class('elapsed', 'elapsed');
        $table->column_class('action', 'action');

        $table->setup();
        
        $clockouturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php', $urlparams);
        $clockouturl->params(array('clockout'=>1));
        foreach ($userPending as $pending){
            $clockouticon = new pix_icon('clock_out','Clock out','block_timetracker');
            $clockoutaction = $OUTPUT->action_icon($clockouturl, $clockouticon);
            $table->add_data(array(userdate($pending->timein,get_string('datetimeformat','block_timetracker')),$clockoutaction));
        }

        $table->print_html();

        echo $OUTPUT->box_end();
    } else {
        //show clock in

    }

    if($userUnits){
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>Last 10 Work Units</h2>';
        $table = new flexible_table('timetracker-display-worker-index');
    
        $table->define_columns(array('timein', 'timeout', 'elapsed', 'action'));
        $table->define_headers(array('Time in', 'Time out', 'Elapsed', 'Action'));
        //$table->define_headers(array(get_string('feed', 'block_rss_client'), get_string('actions', 'moodle')));
        
        $table->set_attribute('cellspacing', '0');
        //$table->set_attribute('id', '');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->column_class('timein', 'timein');
        $table->column_class('timeout', 'timeout');
        $table->column_class('elapsed', 'elapsed');
        $table->column_class('action', 'action');

        $table->setup();
        //$table->add_data;

        //$titlerow = new html_table_cell();
        //print_object($userUnits);
        foreach ($userUnits as $unit){

            $table->add_data(array(
                userdate($unit->timein,get_string('datetimeformat','block_timetracker')),
                userdate($unit->timeout,get_string('datetimeformat','block_timetracker')),
                format_elapsed_time($unit->timeout - $unit->timein),
                    'Not implemented yet'));
        }

        $table->print_html();


        echo $OUTPUT->box_end();
    }
}

echo $OUTPUT->footer();
