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
 * This page will allow the user to clock in and clock out. 
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once ('lib.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$ttuserid = required_param('userid',PARAM_INTEGER);
$clockin = optional_param('clockin', 0,PARAM_INTEGER);
$clockout = optional_param('clockout',0, PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $ttuserid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$timeclockurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php',$urlparams);
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

//$PAGE->set_url($timeclockurl);
//$PAGE->set_pagelayout('base');


//$strtitle = get_string('timeclocktitle','block_timetracker'); 
//$PAGE->set_title($strtitle);

//$PAGE->navbar->add(get_string('blocks'));
//$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
//$PAGE->navbar->add($strtitle);

//echo $OUTPUT->header();
//
$workerrecord = $DB->get_record('block_timetracker_workerinfo', array('id'=>$ttuserid,'courseid'=>$courseid));

if(!$workerrecord){
    print_error("NO WORKER FOUND!");
    die;
}

if($workerrecord->active == 0){
    $status = get_string('notactiveerror','block_timetracker');
} else if($clockin == 1){
        $status = 'Clock in successful';
        //protect against refreshing a 'clockin' screen
        $pendingrecord= $DB->record_exists('block_timetracker_pending',array('userid'=>$ttuserid,'courseid'=>$courseid));
        if(!$pendingrecord){
            $cin = new stdClass();
            $cin->userid = $ttuserid;
            $cin->timein = time();
            $cin->courseid = $courseid;
            $DB->insert_record('block_timetracker_pending', $cin);
        }

} else if ($clockout == 1){
    $status = 'Clock out successful';
    $cin = $DB->get_record('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
    if($cin){
        $cin->payrate = $workerrecord->currpayrate;
        $cin->timeout = time();
        $cin->lastedited = time();
        $cin->lasteditedby = $ttuserid;

        unset($cin->id);

        $worked = $DB->insert_record('block_timetracker_workunit',$cin);
        if($worked){
            $DB->delete_records('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));

        } else {

            print_error('couldnotclockout', 'block_timetracker', 
                $CFG->wwwroot.'/blocks/timetracker/timeclock.php?id='.$courseid.'&userid='.$ttuserid);

        }
    }
} 

redirect($index,$status,2);
