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

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$pendingid = required_param('pendingid', PARAM_INTEGER);
$nextpage = optional_param('next',0,PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

//assume we're coming from reports
$nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);
if($nextpage!=0){
    $nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);
}

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$worker = $DB->get_record('block_timetracker_workerinfo',array('mdluserid'=>$USER->id,'courseid'=>$courseid));

if (!$canmanage && $USER->id != $worker->mdluserid){
    print_error('notpermissible','block_timetracker',$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {
    if($userid && $courseid && $pendingid && confirm_sesskey()){
        $DB->delete_records('block_timetracker_pending',array('id'=>$pendingid));
    } else {
        print_error('errordeleting','block_timetracker', $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
    }
}
redirect($nexturl,'Pending work unit has been deleted', 1);
