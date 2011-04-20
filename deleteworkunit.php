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
$unitid = required_param('unitid', PARAM_INTEGER);
$nextpage = optional_param('next',0,PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

if($courseid){
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

//assume we're coming from reports
$nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);
if($nextpage!=0){
    $nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);
}

if (!has_capability('block/timetracker:manageworkers', $context)) {
    print_error('notpermissible','block_timetracker',$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {
    if($unitid && confirm_sesskey()){
        $DB->delete_records('block_timetracker_workunit',array('id'=>$unitid));
    } else {
        print_error('errordeleting','block_timetracker', $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
    }
}

redirect($nexturl, 'Unit deleted', 1);
