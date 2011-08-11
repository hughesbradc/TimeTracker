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
 * This page will call for the spreadsheet timesheet to be generated. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('timetracker_timesheet_form.php');

global $CFG, $COURSE, $USER, $DB;

require_login();

$courseid = required_param('id', PARAM_INTEGER);

$urlparams['id'] = $courseid;

$timesheeturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheet.php',$urlparams);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($timesheeturl);
$PAGE->set_pagelayout('course');

$canmanage = false;
if(has_capability('block/timetracker:manageworkers', $context)){
    $canmanage = true;
}

$maintabs = get_tabs($urlparams, $canmanage);

$strtitle = get_string('timesheettitle','block_timetracker');
$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

//$indexparams['userid'] = $userid;
$indexparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_timesheet_form($context);

if($mform->is_cancelled()){
    //User clicked cancel
    $reportsurl = new
        moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams);
    redirect($reportsurl);
} else if($formdata=$mform->get_data()){
    $uid = $formdata->workerid[0];
    print_object($formdata->workerid);
    $cid = $formdata->id;
    $format = $formdata->fileformat;
    if($format == 'pdf'){
        redirect($CFG->wwwroot.'/blocks/timetracker/timesheet_pdf.php?id='.$cid.
            '&userid='.$uid.'&month='.$formdata->month.'&year='.$formdata->year);
    } else {
        redirect($CFG->wwwroot.'/blocks/timetracker/timesheet_xls.php?id='.$cid.
            '&userid='.$uid.'&month='.$formdata->month.'&year='.$formdata->year);
    }
} else {
    echo $OUTPUT->header();
    $tabs = array($maintabs);
    print_tabs($tabs, 'reports');
    $mform->display();
    echo $OUTPUT->footer();
}

