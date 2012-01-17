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
$start = required_param('start', PARAM_INT);
$end = required_param('end', PARAM_INT);

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

$strtitle = get_string('signtsheading','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

if($canmanage){
    print_error('supsignerror','block_timetracker');
}
if(!$worker){
    echo 'This worker does not exist in the database.';
} else if($worker->mdluserid != $USER->id){
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
} else {
    $mform = new timetracker_workersig_form($courseid, $userid, $start, $end);

    if ($mform->is_cancelled()){ //user clicked cancel
        //redirect($nextpage);
        redirect($index, $urlparams);

    } else if ($formdata=$mform->get_data()){
       /*
        Look for units that straddle the pay period boundary
        TODO Check for course conflicts against submitted work units
        Create timesheet entry
        Assign all of the work units the timesheet id
        Set all of the work units canedit=0
       */
        
        split_boundary_units($start, $end, $userid, $courseid);
       
        $units = $DB->get_records_select('block_timetracker_workunit','timein BETWEEN '.$start.' AND '.
            $end.' AND timeout BETWEEN '.$start .' AND '.$end, 
            array('userid'=>$userid, 'courseid'=>$courseid,'submitted'=>0));

        $earnings = break_down_earnings($units);

        //Create entry in timesheet table
        $newtimesheet = new stdClass();
        $newtimesheet->userid = $userid;
        $newtimesheet->courseid = $courseid;
        $newtimesheet->submitted = time();
        $newtimesheet->workersignature = time();
        $newtimesheet->reghours = $earnings['reghours'];
        $newtimesheet->regpay = $earnings['regpay'];
        $newtimesheet->othours = $earnings['othours'];
        $newtimesheet->otpay = $earnings['otpay'];
       
        $timesheetid = $DB->insert_record('block_timetracker_timesheet', $newtimesheet);
            
        foreach ($units as $unit){
            $unit->timesheetid = $timesheetid; 
            $unit->canedit = 0;
            $DB->update_record('block_timetracker_workunit', $unit);    
        }
        
    
    } else {
        //form is shown for the first time
        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
}
