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
require_once('timetracker_reports_form.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = optional_param('userid', -1, PARAM_INTEGER);
$reportstart = optional_param('repstart', 0,PARAM_INTEGER);
$reportend = optional_param('repend', 0,PARAM_INTEGER);


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$worker =
    $DB->get_record('block_timetracker_workerinfo',
    array('mdluserid'=>$USER->id,'courseid'=>$courseid));

if($userid == -1 && $worker) $userid = $worker->id;
else if ($userid == -1 && $canmanage) $userid = 0;


$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

if($reportstart) $urlparams['repstart'] = $reportstart;
if($reportend) $urlparams['repend'] = $reportend;

$reportsurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);

$PAGE->set_url($reportsurl);
$PAGE->set_pagelayout('base');
$strtitle = 'TimeTracker : Reports';

if( $userid != 0){
    $w = $DB->get_record('block_timetracker_workerinfo',array('id'=>$userid));
    if($w){
        $strtitle .= ' for '.$w->firstname.' '.$w->lastname;
    }
} else {
    $strtitle .= ' for all workers';
}


$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);
$PAGE->navbar->add(get_string('pluginname', 'block_timetracker'), $index);
$PAGE->navbar->add($strtitle);
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);


$mform = new timetracker_reports_form($PAGE->context,
    $userid, $courseid, $reportstart, $reportend);

if ($mform->is_cancelled()){ //user clicked 'cancel'

    redirect($reportsurl); 

} else if($formdata = $mform->get_data()){

    $urlparams['repstart'] = $formdata->reportstart;
    $urlparams['repend'] = $formdata->reportend;

    $reportsurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);
    redirect($reportsurl);

} else {
    //echo $OUTPUT->heading($strtitle, 2);
    echo $OUTPUT->header();

    $maintabs = get_tabs($urlparams, $canmanage, $courseid);
    $tabs = array($maintabs);

    print_tabs($tabs, 'reports');
    $mform->display();
    echo $OUTPUT->footer();
}

