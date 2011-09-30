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

$strtitle = 'TimeTracker';

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$PAGE->set_pagelayout('base');

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

echo $OUTPUT->header();

$checkin = 1288084624 + (60 * 60 * 24 * 138)-(60*79);
$checkout = $checkin + (60 * 60 * 24 * 8)-(60*60*17);
//$checkout = time();

echo 'Check in:  '. userdate($checkin,
    get_string('datetimeformat','block_timetracker')).'<br />';
echo 'Checkout: '. userdate($checkout,
    get_string('datetimeformat','block_timetracker')).'<br />';
echo '<br />';

$endofday = (86400+(usergetmidnight($checkin)-1));
$currcheckin = $checkin;

if(date("H",$endofday) == 22){
    $endofday += 60 * 60;
} else if (date("H",$endofday) == 0){
    $endofday -= 60 * 60;
}

while ($currcheckin < $checkout){

    //add to $DB
    $output = userdate($currcheckin, get_string('datetimeformat','block_timetracker'));
    $output .= ' to '.userdate($endofday,
        get_string('datetimeformat','block_timetracker'));
    $output .= ', for '.format_elapsed_time($endofday - $currcheckin);
    $output .= '<br />';
    //don't echo $output, but add to DB
    echo $output;

    //update checkin and checkout
    //update to midnight
    //$currcheckin += $tomidnight + 1;
    $currcheckin = $endofday + 1;

    //find next 23:59:59
    $endofday = 86400 + (usergetmidnight($currcheckin)-1);

    //because I can't get dst_offset_on to work!
    $usersdate = usergetdate($endofday);
    if($usersdate['hours'] == 22){ 
        $endofday += 60 * 60;
    } else if ($usersdate['hours'] == 0){
        $endofday -= 60 * 60;
    }

    //if not a full day, don't go to 23:59:59 
    //but rather checkout time
    if($endofday > $checkout){
        error_log("not a full day");
        $endofday = $currcheckin + ($checkout - $currcheckin);
    } 
    //break;

}

echo $OUTPUT->footer();
