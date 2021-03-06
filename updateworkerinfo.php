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
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require('timetracker_updateworkerinfo_form.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = optional_param('userid', -1, PARAM_INTEGER);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$canview = false;
if (has_capability('block/timetracker:viewonly', $context)) {
    $canview = true;
}

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

if(get_referer(false)){
    $nextpage = get_referer(false);
} else {
    $nextpage = $index;
}

//if we posted to ourself from ourself
if(strpos($nextpage, qualified_me()) !== false){
    $nextpage = $SESSION->lastpage;
} else {
    $SESSION->lastpage = $nextpage;
}
//$nextpage = $index;

$worker = $DB->get_record('block_timetracker_workerinfo', array('id'=>$userid));

if($worker){
    $userid=$worker->id;
    $index->params(array('userid'=>$userid));

    $ttuserid = $worker->id;
    if($USER->id != $worker->mdluserid && !($canmanage || $canview)){
        print_error('notpermissible', 'block_timetracker',$index);
    }
}

$PAGE->set_url(new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/updateworkerinfo.php',$urlparams));
$PAGE->set_pagelayout('base');

$strtitle = get_string('updateformheadertitle','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_updateworkerinfo_form($context, $courseid, $worker->mdluserid);

if ($mform->is_cancelled()){ //user clicked cancel
    redirect($nextpage);

} else if ($formdata=$mform->get_data()){

    if(!$worker){
        //adding
        unset($formdata->id);
        $ttuserid = $DB->insert_record('block_timetracker_workerinfo', $formdata);
    } else {
        //updating
        $formdata->id = $worker->id;
        $DB->update_record('block_timetracker_workerinfo', $formdata);
    }

    $indexparams['userid'] = $ttuserid;
    $indexparams['id'] = $courseid;
    $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

    redirect($nextpage);
    
} else {
    //form is shown for the first time
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
