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
 * This page will allow the worker to input the date, time, and duration of a workunit.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require('timetracker_editunit_form.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$unitid = required_param('unitid', PARAM_INTEGER);
$start = optional_param('start', 0, PARAM_INTEGER);
$end = optional_param('end', 0, PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$urlparams['unitid'] = $unitid;
$urlparams['start'] = $start;
$urlparams['end'] = $end;

$hourlogurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',$urlparams);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($hourlogurl);
$PAGE->set_pagelayout('course');

$workerrecord = $DB->get_record('block_timetracker_workerinfo', array('id'=>$userid,'courseid'=>$courseid));

if(!$workerrecord){
    echo "NO WORKER FOUND!";
    die;
}

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

if($USER->id != $workerrecord->mdluserid && !$canmanage){
    print_error('You do not have permissions to add hours for this user');
} else if(!$canmanage && $workerrecord->timetrackermethod==0){
    redirect($index,$status,2);
}

$strtitle = get_string('editunittitle','block_timetracker',$workerrecord->firstname.' '.$workerrecord->lastname); 
$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$indexparams['userid'] = $userid;
$indexparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_editunit_form($context, $userid, $courseid,$unitid,$start,$end);

if($workerrecord->active == 0){
    echo $OUTPUT->header();
    print_string('notactiveerror','block_timetracker');
    echo '<br />';
    echo $OUTPUT->footer();
    die;
}

if ($mform->is_cancelled()){ //user clicked cancel

} else if ($formdata=$mform->get_data()){

        $formdata->courseid = $formdata->id;
        unset($formdata->id);
        $formdata->id = $formdata->unitid;
        $formdata->payrate = $workerrecord->currpayrate;
        $formdata->lastedited = time();
        $formdata->lasteditedby = $formdata->editedby;

        //print_object($formdata);
        $DB->update_record('block_timetracker_workunit', $formdata);

        $status = 'Workunit edited successfully.'; 
        redirect($index,$status,2);

} else {
    //form is shown for the first time
    echo $OUTPUT->header();
    $maintabs[] = new tabobject('home', $index, 'Main');
    $maintabs[] = new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams), 'Reports');
    $maintabs[] = new tabobject('editunit', new moodle_url($CFG->wwwroot.'/blocks/timetracker/editunit.php',$urlparams), 'Edit Workunit');

    if($canmanage){
        $maintabs[] = new tabobject('manage', new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 'Manage Workers');
    }
    
    $tabs = array($maintabs);
    print_tabs($tabs, 'editunit');

    $mform->display();
    echo $OUTPUT->footer();
}

