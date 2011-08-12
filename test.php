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

#$worker = $DB->get_record('block_timetracker_workerinfo',array('userid'=>$USER->id));
echo $OUTPUT->header();

//$conf = $DB->get_records('block_timetracker_config',array('courseid'=>2));
//print_object($conf);


$nowtime = time();
$checkout = time();
//echo $checkout;
//$checkin = $checkout - 186400;
$checkin = 1288084624;

echo 'Check in:  '. userdate($checkin,get_string('datetimeformat','block_timetracker')).'<br />';
echo 'Checkout: '. userdate($checkout,get_string('datetimeformat','block_timetracker')).'<br />';
echo '<br />';

//$totalsecs = $nowtime - $checkin;
//date_default_timezone_set('America/New_York');

//$tomidnight = (usergetmidnight($checkin)-1)  - ($checkin % 86400);
$tomidnight = (86400+(usergetmidnight($checkin)-1)  - ($checkin));
$currcheckin = $checkin;

while ($currcheckin < $checkout){
    $output = userdate($currcheckin, get_string('datetimeformat','block_timetracker'));
    $output .= ' to '.userdate($currcheckin+$tomidnight,get_string('datetimeformat','block_timetracker'));
    $output .= '<br />';

    //don't echo $output, but add to DB
    echo $output;

    //error_log(dst_offset_on($currcheckin).' dst on out: '.dst_offset_on($checkout));
    $in_off = dst_offset_on($currcheckin);
    $out_off = dst_offset_on($checkout);

    if($in_off != $out_off){
        //error_log("********* THEY ARE DIFFERENT: $in_off and $out_off");
    }

    $currcheckin += $tomidnight + 1;

    //update to midnight
    $tomidnight = 86400 + (usergetmidnight($currcheckin)-1)- ($currcheckin);
    //error_log('86399 + ' .usergetmidnight($currcheckin). ' - ' .$currcheckin .' = '.$tomidnight);

    if($tomidnight == -1){
        //error_log($currcheckin. ' ' . $checkout . ' ' .$tomidnight);
        //error_log(dst_offset_on($currcheckin).' dst on out: '.dst_offset_on($checkout));
        //$tomidnight += 86399;
        break;
    }

    if(($currcheckin+$tomidnight) > $checkout){
        $tomidnight = $checkout - $currcheckin;
    } 
}




//$info = get_month_info(3,2011);
//print_object($info);

//print_object($CFG->config_block_timetracker_default_max_earnings);
echo $OUTPUT->footer();
