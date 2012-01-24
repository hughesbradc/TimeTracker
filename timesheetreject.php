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
 * This form will allow a supervisor or administrator to reject a timesheet 
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require('timetracker_timesheetreject_form.php');

global $CFG, $COURSE, $USER;

require_login();

$timesheetid = required_param('timesheetid', PARAM_INTEGER);

$timesheet = $DB->get_record('block_timetracker_timesheet', array('id'=>$timesheetid));
$courseid = $timesheet->courseid;
$userid = $timesheet->userid;

$urlparams['timesheetid'] = $timesheetid;
$alerturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheetreject.php',$urlparams);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

if(!$canmanage){
    print_error('notpermissible','block_timetracker');
} else {

    $PAGE->set_url($alerturl);
        $PAGE->set_pagelayout('base');
    $PAGE->set_title(get_string('rejecttstitle','block_timetracker'));
    $PAGE->set_heading(get_string('rejecttstitle','block_timetracker'));
    
    $workerrecord = $DB->get_record('block_timetracker_workerinfo', 
        array('id'=>$userid,'courseid'=>$courseid));
    
    if(!$workerrecord){
        echo "NO WORKER FOUND!";
        die;
    }
    
    $strtitle = get_string('rejecttstitle','block_timetracker',
        $workerrecord->firstname.' '.$workerrecord->lastname); 
    $PAGE->set_title($strtitle);
    
    $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);
    $index->param('id', $courseid);
    
    $nextpage = $index;
    /*
    if(isset($_SERVER['HTTP_REFERER'])){
        $nextpage = $_SERVER['HTTP_REFERER'];
    } else {
        $nextpage = $index;
    }
    if(strpos($nextpage, qualified_me()) !== false){
        $nextpage = $SESSION->lastpage;
    } else {
        $SESSION->lastpage = $nextpage;
    }
    
    if($nextpage == $CFG->wwwroot.'/blocks/timetracker/hourlog.php'){
        $nextpage .=
            '?id='.$courseid.
            '&userid='.$userid;
    }
    */
    
    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
    $PAGE->navbar->add($strtitle);
    
    $mform = new timetracker_timesheetreject_form($timesheetid); 
    
    $timesheet = $DB->get_record('block_timetracker_timesheet', array('id'=>$timesheetid));
    
    if ($mform->is_cancelled()){ 
        //user clicked cancel
        redirect($nextpage);
    
    } else if ($formdata=$mform->get_data()){
        $timesheetid = $formdata->timesheetid;
        // Data collection to send email to supervisor(s)
        //$from = $DB->get_record('user',array('id'=>$USER->id));
        $from = $USER;
        $subject = get_string('tssubject','block_timetracker');
    
        //***** PLAIN TEXT *****//
        $messagetext = $workerrecord->firstname.':';
        $messagetext .= get_string('br2','block_timetracker');
        $messagetext .= get_string('remessage1','block_timetracker',
            date("n/j/Y g:i:sa", $timesheet->workersignature));
        $messagetext .= get_string('remessagesup','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= $formdata->message;
        $messagetext .= get_string('br2','block_timetracker');
        $messagetext .= get_string('instruction','block_timetracker');
    
        //***** HTML *****//
        $messagehtml = $workerrecord->firstname.':';
        $messagehtml .= get_string('br2','block_timetracker');
        $messagehtml .= get_string('remessage1','block_timetracker',
            date("n/j/Y g:i:sa", $timesheet->workersignature));
        $messagehtml .= get_string('remessagesup','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= $formdata->message; 
        $messagehtml .= get_string('br2','block_timetracker');
        $messagehtml .= get_string('instruction','block_timetracker');
        
        //Set all units to be editable by user and supervisor
        $DB->set_field('block_timetracker_workunit','canedit',1,
            array('timesheetid'=>$timesheet->id));
        //Reset all of the units to without a timesheet id
        $DB->set_field('block_timetracker_workunit','timesheetid',0,
            array('timesheetid'=>$timesheet->id));
        //Remove the timesheet entry from the table
        $DB->delete_records('block_timetracker_timesheet',array('id'=>$timesheet->id));
        
        //Build the email and send to the worker
        $ttuser = $DB->get_record('block_timetracker_workerinfo',array('id'=>$userid));
        error_log('before getting user');
        $user = $DB->get_record('user',array('id'=>$ttuser->mdluserid));
        error_log('after getting user');
        //print_object($user);
        
        $mailok = email_to_user($user, $from, $subject, $messagetext, $messagehtml); 
        if(!$mailok){
            print_error("Error sending message to $user->firstname $user->lastname");
        } 
    
        $status = get_string('remessagesent','block_timetracker');
        redirect($nextpage, $status,1);
    
    } else {
        //form is shown for the first time
        
        echo $OUTPUT->header();
        $maintabs = get_tabs($urlparams, $canmanage, $courseid);
        //print_object($urlparams);
    
        $tabs = array($maintabs);
        
        print_tabs($tabs, 'timesheets');
    
        $mform->display();
        echo $OUTPUT->footer();
    }
}
