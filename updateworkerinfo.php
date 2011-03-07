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

$worker = $DB->get_record('block_timetracker_workerinfo', array('userid'=>$USER->id));
if($worker){
    $ttuserid = $worker->id;
}


$courseid = required_param('id', PARAM_INTEGER);

$urlparams['id'] = $courseid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url(new moodle_url('/blocks/timetracker/updateworkerinfo.php',$urlparams));
$PAGE->set_pagelayout('base');

$strtitle = get_string('updateformheadertitle','block_timetracker'); 
$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url('/blocks/timetracker/index.php',$urlparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_updateworkerinfo_form($context, $courseid);

if ($mform->is_cancelled()){ //user clicked cancel

} else if ($formdata=$mform->get_data()){
	$worker = $DB->get_record('block_timetracker_workerinfo', array('userid'=>$USER->id,'courseid'=>$courseid));
    if(!$worker){
        unset($formdata->id);
        $ttuserid = $DB->insert_record('block_timetracker_workerinfo', $formdata);
    } else {
        $formdata->id = $worker->id;
        $DB->update_record('block_timetracker_workerinfo', $formdata);
    }

    $indexparams['userid'] = $ttuserid;
    $indexparams['id'] = $courseid;
    $index = new moodle_url('/blocks/timetracker/index.php', $indexparams);

    redirect($index);
    
} else {
    //form is shwon for the first time
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
