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

global $SESSION;

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$unitid = required_param('unitid', PARAM_INTEGER);

$eunitid = optional_param('eunitid', 0, PARAM_INTEGER);
$estart = optional_param('estart', 0, PARAM_INTEGER);
$eend = optional_param('eend', 0, PARAM_INTEGER);
$eispending = optional_param('eispending', false, PARAM_BOOL);


$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

if(isset($_SERVER['HTTP_REFERER'])){
    $nextpage = $_SERVER['HTTP_REFERER'];
} else {
    $nextpage = $index;
}
//if we posted to ourself from ourself
if(strpos($nextpage, curr_url()) !== false){
    $nextpage = $SESSION->lastpage;
} else {
    $SESSION->lastpage = $nextpage;
}

error_log($nextpage);

//check to see if from editunit
if($nextpage == $CFG->wwwroot.'/blocks/timetracker/editunit.php'){
    $nextpage = $nextpage.
        '?id='.$courseid.
        '&userid='.$userid.
        '&unitid='.$eunitid.
        '&start='.$estart.
        '&end='.$eend.
        '&ispending='.$eispending;
//} else if($nextpage == $CFG->wwwroot.'/blocks/timetracker/reports.php'){
          

}
error_log($nextpage);


if($courseid){
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

if (!has_capability('block/timetracker:manageworkers', $context)) {
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {
    if($unitid && confirm_sesskey()){
        $DB->delete_records('block_timetracker_workunit',array('id'=>$unitid));
    } else {
        print_error('errordeleting','block_timetracker', 
            $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
    }
    unset($SESSION->afterdelete);
}

redirect($nextpage, 'Unit deleted', 1);
