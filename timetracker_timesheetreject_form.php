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
 * This form will allow the worker to submit an alert and correction to the supervisor of an error in a work unit.
 * The supervisor will be able to approve or deny the correction.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_timesheetreject_form  extends moodleform {

    function timetracker_timesheetreject_form($timesheetid){
        
        $this->timesheetid = $timesheetid;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 
        
        $timesheet = $DB->get_record('block_timetracker_timesheet', array('id'=>$this->timesheetid));
        $courseid = $timesheet->courseid;
        $userid = $timesheet->userid;
        error_log('courseid='.$courseid.' and userid='.$userid);

        //check to make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
/**        
        if($this->ispending){
            //Get from pending table
            $unit = $DB->get_record('block_timetracker_pending',array('id'=>$this->unitid));
        } else {
            //Get from workunit
            $unit = $DB->get_record('block_timetracker_workunit',array('id'=>$this->unitid));
        }

        if(!$unit){
            print_error('Unit does not exist for unit id of '.$this->unitid);
            return;
        }
*/     
        $userinfo = $DB->get_record('block_timetracker_workerinfo',
            array('id'=>$userid));
        
        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$courseid,'userid'=>$userid));

        if(get_referer(false)){
            $nextpage = get_referer(false);
        } else {
            $nextpage = $index;
        }

/**
        $mform->addElement('html', '<b>'); 
        $mform->addElement('html', get_string('to','block_timetracker'));
        $mform->addElement('html', '</b>'); 

        $teachers = get_users_by_capability($this->context, 'block/timetracker:manageworkers');
        if(!$teachers){
            print_error('No supervisor is enrolled in this course.  
            Please alert your Administrator.');
        }
        foreach ($teachers as $teacher) {
            if(is_enrolled($this->context, $teacher->id)){
                //!has_capability('moodle/category:manage',$this->context,$teacher) &&
                //is_enrolled($this->context, $teacher->id)){
                $mform->addElement('advcheckbox', 'teacherid['.$teacher->id.']', 
                    $teacher->firstname.' '.$teacher->lastname,  null, array('group'=>1));
            }
        }
        
        $this->add_checkbox_controller(1, null, null, 1);

        $mform->addElement('html', '<b>'); 
        $mform->addElement('html', get_string('subject','block_timetracker'));
        $mform->addElement('html', '</b>'); 
        $mform->addElement('html', get_string('subjecttext','block_timetracker',
            $userinfo->firstname.' '. $userinfo->lastname));
        $mform->addElement('html', '<br /><br />'); 
        $mform->addElement('html', get_string('existingunit','block_timetracker'));
        $mform->addElement('html', '<blockquote>'); 
        $mform->addElement('html', get_string('existingtimein','block_timetracker',
            userdate($unit->timein, get_string('datetimeformat','block_timetracker'))));
        
        if(!$this->ispending){
            //Time out and elapsed time
            $mform->addElement('html', '<br />'); 
            $mform->addElement('html',get_string('existingtimeout','block_timetracker',
                userdate($unit->timeout, get_string('datetimeformat','block_timetracker'))));
        
            $mform->addElement('html', '<br />'); 
            $mform->addElement('html',get_string('existingduration','block_timetracker',
                format_elapsed_time($unit->timeout - $unit->timein, $unit->courseid)));
        }

        $mform->addElement('html', '</blockquote><b>'); 
        $mform->addElement('html', get_string('data','block_timetracker'));
        $mform->addElement('date_time_selector','timeinerror','Time In: ',
            array('optional'=>false, 'step'=>1));

        $mform->setDefault('timeinerror',$unit->timein);
		$mform->addHelpButton('timeinerror','timein','block_timetracker');
        $mform->addElement('date_time_selector','timeouterror','Time Out: ',
            array('optional'=>false, 'step'=>1));
		$mform->addHelpButton('timeouterror','timeout','block_timetracker');

        $mform->addElement('hidden','origtimein', $unit->timein); 
        
        if(!$this->ispending){
            $mform->setDefault('timeouterror',$unit->timeout);
            $mform->addElement('hidden','origtimeout', $unit->timeout); 
        } else {
            $mform->setDefault('timeouterror',$unit->timein + (60 * 60 * 2));
        }
       
        if(!$this->ispending){
            $mform->addElement('checkbox', 'deleteunit', 
                get_string('deleteunit','block_timetracker'));
            $mform->addHelpButton('deleteunit', 'deleteunit', 'block_timetracker');
        }
*/
        
        $mform->addElement('hidden','timesheetid',$this->timesheetid);
        $mform->addElement('html',get_string('headername','block_timetracker', $userinfo->firstname
            .' '.$userinfo->lastname));
        $mform->addElement('html',get_string('headertimestamp','block_timetracker', 
            date("n/j/Y g:i:sa", $timesheet->workersignature)));
        $mform->addElement('html','<br /><br />');
        $mform->addElement('textarea', 'message', 
            get_string('rejectreason','block_timetracker'), 
            'wrap="virtual" rows="3" cols="75"');
        $mform->addRule('message', null, 'required', null, 'client', 'false');
        $mform->addElement('html', '</b>'); 
        $this->add_action_buttons(true,get_string('sendbutton','block_timetracker'));
/*        
        }
*/
    }   
    
    function validation ($data){
        global $OUTPUT;
        $errors = array();

        return $errors;
    }
}
