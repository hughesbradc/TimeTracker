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

if(isset($_SERVER['HTTP_REFERER'])){
    $nextpage = $_SERVER['HTTP_REFERER'];
} else {
    $nextpage = $index;
}

$workerrecord = $DB->get_record('block_timetracker_workerinfo', 
    array('id'=>$ttuserid,'courseid'=>$courseid));

if(!$workerrecord){
    print_error("NO WORKER FOUND!");
    die;
}

if($workerrecord->active == 0){
    $status = get_string('notactiveerror','block_timetracker');
} else if($clockin == 1){
        $status = 'Clock in successful';
        //protect against refreshing a 'clockin' screen
        $pendingrecord= $DB->record_exists('block_timetracker_pending',
            array('userid'=>$ttuserid,'courseid'=>$courseid));
        if(!$pendingrecord){
            $cin = new stdClass();
            $cin->userid = $ttuserid;
            $cin->timein = time();
            $cin->courseid = $courseid;
            $cisuccess = $DB->insert_record('block_timetracker_pending', $cin);
            if($cisuccess){
                add_to_log($COURSE->id, '', 'add clock-in', 
                "blocks/timetracker/index.php?id=$course->id&userid=$USER->id",
                'TimeTracker clock-in.');
            } else {
                print_error('You tried to clock-in, but something went wrong.  
                    We have logged the error.  Please contact your supervisor.');
                add_to_log($COURSE->id, '', 'error adding clock-in', ''.$COURSE->id, 
                    'ERROR:  TimeTracker clock-in failed.');

            }
        }

} else if ($clockout == 1){
    $status = 'Clock out successful';

    $cin = $DB->get_record('block_timetracker_pending', 
        array('userid'=>$ttuserid,'courseid'=>$courseid));

    if($cin){
        $nowtime = time();

        //timein && timeout are same day
        $cin->lastedited = $nowtime;
        $cin->timeout = $nowtime;
        $cin->lasteditedby = $USER->id;
        $cin->payrate = $workerrecord->currpayrate;
        unset($cin->id);

        $worked = add_unit($cin);

        if($worked){
            $DB->delete_records('block_timetracker_pending', 
                array('userid'=>$ttuserid,'courseid'=>$courseid));
        } else {
            print_error(
                'You tried to clock-out, but something went wrong.  We have logged the
                error.  Please contact your supervisor.');
        }
    } else { 
        $status = 'No matching clock-in. Work unit not recorded';
        add_to_log($COURSE->id, '', 'error finding clock-in', ''.$COURSE->id, 
            'ERROR:  No Matching clock-in.');
    }
} 

redirect($nextpage, $status,1);

