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
 * This form will allow the supervisor to batch sign timesheets electronically.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require('timetracker_supervisorsig_form.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}


$urlparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$maintabs = get_tabs($urlparams, $canmanage, $courseid);

$workers = $DB->get_records('block_timetracker_workerinfo', array('courseid'=>$courseid));

$PAGE->set_url(new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/supervisorsig.php',$urlparams));
$PAGE->set_pagelayout('base');

$strtitle = get_string('signheader','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

if(!$canmanage){
    print_error('notpermissible','block_timetracker');
}

if(!$workers){
    echo 'No users are enrolled in your course.';
} else {
    $mform = new timetracker_supervisorsig_form($courseid);

    if ($mform->is_cancelled()){ //user clicked cancel
        //redirect($nextpage);
        redirect($index, $urlparams);

    } else if ($formdata=$mform->get_data()){
    
        $indexparams['id'] = $courseid;
        $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);
    
        //redirect($nextpage);
    
    } else {
        //form is shown for the first time
        echo $OUTPUT->header();
        $tabs=array($maintabs);
        print_tabs($tabs, 'timesheets');
        $mform->display();
        echo $OUTPUT->footer();
    }
}
