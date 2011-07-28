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
 * eapendingapprove.php
 * This page will 'do magic' when a supervisor approves an error alert regarding a pending work unit.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');

require_login();

$id = required_param('id', PARAM_INTEGER); // Worker id
$courseid = required_param('courseid', PARAM_INTEGER);
$ti = required_param('timein', PARAM_INTEGER);
$to = required_param('timeout', PARAM_INTEGER);

$course = $DB->get_record('course', array('id' => $courseid), '*' MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$urlparams['id'] = $courseid;

$nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}


//********** TODO **********//
//Add check - if request has already been approved, display 'alreadyapproved' string.
//"This work unit has already been approved by {$a} (name and time)?


if (!$canmanage && $USER->id != $worker->mdluserid){
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {
    // Add the approved error correct into the 'workunit' table
    // userid (student), courseid, timein, timeout, payrate, lastedited, lasteditedby

    $payrate = 'SELECT payrate FROM '.$CFG->prefix.'block_timetracker_workerinfo WHERE id='. $id .';';
    $lastedited = time();
    $lasteditedby = ; //Supervisor's id

    $sql = 'INSERT INTO '.$CFG->prefix.'block_timetracker_workunit (userid, courseid, timein,
        timeout, payrate, lastedited, lasteditedby) ';
    $sql .= $id ', ', $courseid ', ', $ti ', ',$to ', ', $payrate ', ', $lastedited ', ',
    $lasteditedby ';';
    
    insert_record($sql);
    
    redirect($nexturl, get_string('approvedsuccess','block_timetracker'), 2);
}
?>
