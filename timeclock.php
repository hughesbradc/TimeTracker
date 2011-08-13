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

$workerrecord = $DB->get_record('block_timetracker_workerinfo', 
    array('id'=>$ttuserid,'courseid'=>$courseid));

if(!$workerrecord){ print_error("NO WORKER FOUND!"); die; }

if($workerrecord->active == 0){ $status = get_string('notactiveerror','block_timetracker'); } else
    if($clockin == 1){ $status = 'Clock in successful';
        //protect against refreshing a 'clockin' screen
        $pendingrecord= $DB->record_exists('block_timetracker_pending',
        array('userid'=>$ttuserid,'courseid'=>$courseid)); 
        if(!$pendingrecord){ 
            $cin = new stdClass(); 
            $cin->userid = $ttuserid; 
            $cin->timein = time(); 
            $cin->courseid = $courseid;
        }
        
        $clockincheck = $DB->insert_record('block_timetracker_pending', $cin); 
        
        if($clockincheck){
            add_to_log($COURSE->id, 'TimeTracker', 'Clock-in', 'index.php?id='.$COURSE->id, 
                'Worker clocked in');
        } else {
            add_to_log($COURSE->id, 'TimeTracker', 'Clock-in failed', 'index.php?id='.$COURSE->id, 
                'Worker attempted to clock in, but something happened.');
            print_error('Something happened and TimeTracker was unable to clock you in. This error
            has been logged.  Please see your supervisor.')
        }
        

} else if ($clockout == 1){
    $status = 'Clock out successful';

    $cin = $DB->get_record('block_timetracker_pending', 
        array('userid'=>$ttuserid,'courseid'=>$courseid));
    if($cin){
        $nowtime = time();

        $timein = usergetdate($cin->timein);
        $timeout = usergetdate($nowtime);

        //timein && timeout are same day
        $cin->lastedited = $nowtime;
        $cin->lasteditedby = $ttuserid;
        $cin->payrate = $workerrecord->currpayrate;
        unset($cin->id);

        if($timein['year'] == $timeout['year'] && 
            $timein['month'] == $timeout['month'] &&
            $timein['mday'] == $timeout['mday']){

            $cin->timeout = $nowtime;

            $worked = $DB->insert_record('block_timetracker_workunit',$cin);

            if($worked){
                $DB->delete_records('block_timetracker_pending', 
                    array('userid'=>$ttuserid,'courseid'=>$courseid));
                add_to_log($COURSE->id, 'TimeTracker', 'Clock-out', 'index.php?id='.$COURSE->id, 
                    'Worker clocked out');
            } else {
                add_to_log($COURSE->id, 'TimeTracker', 'Clock-out failed', 'index.php?id='.$COURSE->id, 
                    'Worker attempted to clock out, but something happened.');
                print_error('Something happened and TimeTracker was unable to clock you out. This error
                    has been logged.  Please see your supervisor.')
            }
        } else { //spans multiple days
            $tomidnight = 86400 + usergetmidnight($cin->timein) - 1 - ($cin->timein);
            $currcheckin = $cin->timein;
            while ($currcheckin < $nowtime){
                $cin->timeout = $currcheckin + $tomidnight;
                $worked = $DB->insert_record('block_timetracker_workunit', $cin);
                if(!$worked){
                    print_error('couldnotclockout', 'block_timetracker', 
                        $CFG->wwwroot.'/blocks/timetracker/timeclock.php?id='.
                            $courseid.'&userid='.$ttuserid);
                    return;
                }

                $currcheckin += $tomidnight + 1;
                $tomidnight = 86400 + (usergetmidnight($currcheckin)-1)- ($currcheckin);
                if(($currcheckin+$tomidnight) > $nowtime){
                    $tomidnight = $nowtime - $currcheckin;
                } 
            }
            $DB->delete_records('block_timetracker_pending', 
                array('userid'=>$ttuserid,'courseid'=>$courseid));
        }
    }
} 

redirect($index,$status,1);

