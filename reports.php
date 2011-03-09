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
$userid = optional_param('userid', 0, PARAM_INTEGER);
$reportstart = optional_param('repstart', 0,PARAM_INTEGER);
$reportend = optional_param('repend', 0,PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
if($reportstart) $urlparams['repstart'] = $reportstart;
if($reportend) $urlparams['repend'] = $reportend;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$reportsurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);

$PAGE->set_url($reportsurl);
$PAGE->set_pagelayout('course');
$strtitle = 'TimeTracker : Reports';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$PAGE->navbar->add(get_string('pluginname', 'block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$worker = $DB->get_record('block_timetracker_workerinfo',array('mdluserid'=>$USER->id));

echo $OUTPUT->header();
$maintabs[] = new tabobject('home', $index, 'Main');
$maintabs[] = new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams), 'Reports');
if($worker && $worker->timetrackermethod==1){
    $maintabs[] = new tabobject('hourlog', new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',$urlparams), 'Hour Log');
}

if($canmanage){
    $maintabs[] = new tabobject('manage', new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 'Manage Workers');
}

$tabs = array($maintabs);
print_tabs($tabs, 'reports');


$mform = new timetracker_reports_form($PAGE->context,$userid,$courseid,$reportstart,$reportend);

if ($mform->is_cancelled()){ //user clicked 'cancel'

    redirect($index); 

} else if($formdata = $mform->get_data()){

    $urlparams['repstart'] = $formdata->reportstart;
    $urlparams['repend'] = $formdata->reportend;

    $reportsurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $urlparams);
    redirect($reportsurl);

} else {
    //echo $OUTPUT->heading($strtitle, 2);
    $mform->display();
    echo $OUTPUT->footer();
}

