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
 * This page will allow a supevisor to input the date, time, and duration of a workunit for a
 * student.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require('timetracker_addunit_form.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

$url = new moodle_url($CFG->wwwroot.'/blocks/timetracker/addunit.php',$urlparams);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($url);
$PAGE->set_pagelayout('base');

$workerrecord = $DB->get_record('block_timetracker_workerinfo', 
    array('id'=>$userid,'courseid'=>$courseid));

if(!$workerrecord){
    echo "NO WORKER FOUND!";
    die;
}

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
    //print_error('nopermissionserror', 'block_timetracker');
   
}

$strtitle = get_string('addunittitle','block_timetracker',
    $workerrecord->firstname.' '.$workerrecord->lastname); 

$PAGE->set_title($strtitle);

$urlparams['userid'] = $userid;
$urlparams['id'] = $courseid;
$manage = new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php', $urlparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $manage);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_addunit_form($context, $userid, $courseid);

if($workerrecord->active == 0){
    echo $OUTPUT->header();
    print_string('notactiveerror','block_timetracker');
    echo '<br />';
    echo $OUTPUT->footer();
    die;
}

if ($mform->is_cancelled()){ //user clicked cancel
    //TODO Redirect user to the home page

} else if ($formdata=$mform->get_data()){
        $formdata->courseid = $formdata->id;
        unset($formdata->id);
        $formdata->payrate = $workerrecord->currpayrate;
        $formdata->lastedited = time();
        $formdata->lasteditedby = $formdata->editedby;
        $DB->insert_record('block_timetracker_workunit', $formdata);
    $status = 'Work unit added successfully.'; 
    redirect($manage,$status,1);

} else {
    //form is shown for the first time
    echo $OUTPUT->header();
    $tabs = get_tabs($urlparams, $canmanage, $courseid);
    
    $tabs = array($tabs);
    print_tabs($tabs, 'manage');

    $mform->display();
    echo $OUTPUT->footer();
}


