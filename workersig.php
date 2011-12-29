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
 * This form will allow the worker to sign a timesheet electronically.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require('timetracker_workersig_form.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$worker = $DB->get_record('block_timetracker_workerinfo', array('id'=>$userid,'courseid'=>$courseid));

$PAGE->set_url(new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/workersig.php',$urlparams));
$PAGE->set_pagelayout('base');

$strtitle = get_string('timesheet','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

if(!$worker){
    echo 'This worker does not exist in the database.';
} else {
    $mform = new timetracker_workersig_form($courseid, $userid);

    if ($mform->is_cancelled()){ //user clicked cancel
        //redirect($nextpage);
        redirect($index, $urlparams);

    } else if ($formdata=$mform->get_data()){
    
        $indexparams['userid'] = $ttuserid;
        $indexparams['id'] = $courseid;
        $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);
    
        //redirect($nextpage);
    
    } else {
        //form is shown for the first time
        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
}
