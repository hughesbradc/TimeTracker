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
require('timetracker_hourlog_form.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = optional_param('id', 0, PARAM_INTEGER);

$urlparams['id'] = $courseid;

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/timetracker/hourlog.php');
$PAGE->set_pagelayout('base');

$strtitle = get_string('hourlogtitle','block_timetracker'); 
$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url('/blocks/timetracker/index.php',$urlparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_hourlog_form();

if ($mform->is_cancelled()){ //user clicked cancel

} else if ($formdata=$mform->get_data()){
	$numrecords = $DB->count_records('block_timetracker_workerinfo', array('userid'=>$USER->id));
    
    if($numrecords == 0){
        $DB->insert_record('block_timetracker_workerinfo', $formdata);
    }
    else {
        $DB->update_record('block_timetracker_workerinfo', $formdata);
    }

    //form submitted
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
} else {
    //form is shwon for the first time
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
