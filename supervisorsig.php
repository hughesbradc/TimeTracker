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

$canview = false;
if (has_capability('block/timetracker:viewonly', $context)) { //supervisor
    $canview = true;
}

$urlparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$maintabs = get_tabs($urlparams, $canview, $courseid);

$workers = $DB->get_records('block_timetracker_workerinfo', array('courseid'=>$courseid));

$PAGE->set_url(new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/supervisorsig.php',$urlparams));
$PAGE->set_pagelayout('base');

$strtitle = get_string('signheader','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

if(!$canmanage && !$canview){
    print_error('notpermissible','block_timetracker');
}

if(!$workers){
    echo 'No users are enrolled in your course.';
} else {
    $mform = new timetracker_supervisorsig_form($courseid, $context);

    if ($mform->is_cancelled()){ //user clicked cancel
        //redirect($nextpage);
        redirect($index);

    } else if ($formdata=$mform->get_data()){

        $reparams['id'] = $courseid;
        $reurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/supervisorsig.php', $reparams);
        
        if(!in_array(1, $formdata->signid)){
            $status = 'You have not selected any timesheets to be signed. <br />
                Redirecting you back to the supervisor signature page.';    
        } else {
    
            /*
            * Set supervisorsignature in timesheet table 
            * Set supermdlid in timesheet table
            */
            
            $timesheets = $DB->get_records('block_timetracker_timesheet',
                array('courseid'=>$COURSE->id, 'supervisorsignature'=>0));
    
            foreach($timesheets as $timesheet){
                if($formdata->signid[$timesheet->id] == 1){
                    $timesheet->supervisorsignature = time();
                    $timesheet->supermdlid = $USER->id;
                    $DB->update_record('block_timetracker_timesheet',$timesheet);
                }
            }
            $status = 'You have successfully signed the selected timesheet(s).';
        }
        
        redirect($reurl, $status, 2);
    
    } else {
        //form is shown for the first time
        echo $OUTPUT->header();
        $tabs=array($maintabs);
        print_tabs($tabs);
        $mform->display();
        echo $OUTPUT->footer();
    }
}
