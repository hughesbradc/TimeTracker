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
require_once('timetracker_manageworkers_form.php'); //get lib.php from here.


require_login();


$courseid = required_param('id', PARAM_INTEGER);

$urlparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

if($courseid){
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    //print_object($course);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

//print_object($context);
//error_log("in manage workers and $COURSE->id");


$canmanage = false;

if (has_capability('block/timetracker:manageworkers', $context)) {
    $canmanage = true;
} else {
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
}

$maintabs = get_tabs($urlparams, $canmanage, $courseid);

$manageworkerurl = new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/manageworkers.php', $urlparams);

if(isset($_SERVER['HTTP_REFERER'])){
    $nextpage = $_SERVER['HTTP_REFERER'];
} else {
    $nextpage = $manageworkerurl;
}

$PAGE->set_url($manageworkerurl);
$PAGE->set_pagelayout('base');

$strtitle = get_string('manageworkertitle','block_timetracker');

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

//print_object($urlparams);
$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

//$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);


$mform = new timetracker_manageworkers_form($PAGE->context);

if ($mform->is_cancelled()){ //user clicked 'cancel'

    // this seems to send a courseID of 0 to index.php, when, as best I can tell
    // $urlparams has the correct id. TODO
    redirect($nextpage); 
} else if($formdata = $mform->get_data()){
    //print_object($formdata);

    if (has_capability('block/timetracker:activateworkers', $context)) {
        $workers = $DB->get_records('block_timetracker_workerinfo',
            array('courseid'=>$COURSE->id));
        //print_object($workers);
    
        foreach($formdata->workerid as $idx){
            if(($formdata->activeid[$idx]==1 && $workers[$idx]->active==0) ||  //not the same
            ($formdata->activeid[$idx]==0 && $workers[$idx]->active == 1)){ //not the same
    
                $workers[$idx]->active = $formdata->activeid[$idx];
                //print_object($workers[$idx]);
                //print_object($workers[$idx]);
                add_to_log($courseid, '','update status',
                    //$CFG->wwwroot.'/blocks/timetracker/'.
                    'manageworkers.php?id='.$courseid,
                    'Change Status for '.$workers[$idx]->firstname.' '.
                    $workers[$idx]->lastname.' to '.$workers[$idx]->active.
                    ' course '.$courseid);
    
                $DB->update_record('block_timetracker_workerinfo', $workers[$idx]);
            }
        }
    }

    //echo $OUTPUT->heading($strtitle, 2);
    //content goes here
    redirect($nextpage, 'Changes saved successfully',1);

} else {

    echo $OUTPUT->header();
    $tabs = array($maintabs);
    print_tabs($tabs, 'manage');
    //echo $OUTPUT->heading($strtitle, 2);
    #$PAGE->print_header('Manage worker info', 'Manage worker info');
    $mform->display();
    echo $OUTPUT->footer();
}

