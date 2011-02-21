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
require_once('timetracker_manageworkers_form.php');

require_login();

$courseid = optional_param('id', 0, PARAM_INTEGER);

$urlparams['id'] = $courseid;


if($courseid){
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

$PAGE->set_url('/blocks/timetracker/manageworkers.php', $urlparams);
$PAGE->set_pagelayout('base');

$strtitle = get_string('manageworkertitle','block_timetracker');

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$timetrackerurl = new moodle_url('/blocks/timetracker/index.php?id='.$courseid);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_timetracker'), $timetrackerurl);
#$PAGE->navbar->add(get_string('managefeeds', 'block_rss_client'));
$PAGE->navbar->add($strtitle);


#$PAGE->set_title($strtitle);
#$PAGE->set_heading($strtitle);

#$settingsurl = new moodle_url('/blocks/timetracker/manageworkers.php?id='.$courseid);

#$PAGE->navbar->add(get_string('blocks'));
#$PAGE->navbar->add(get_string('manageworkertitle', 'block_timetracker'), $settingsurl);
#$PAGE->navbar->add(get_string('managefeeds', 'block_rss_client'));
#$PAGE->navbar->add($strtitle);

$mform = new timetracker_manageworkers_form();

if($formdata = $mform->get_data()){

} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);
    #$PAGE->print_header('Manage worker info', 'Manage worker info');
    $mform->display();
    echo $OUTPUT->footer();
    die;
}

