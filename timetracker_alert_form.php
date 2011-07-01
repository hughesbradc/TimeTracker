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

class timetracker_alert_form  extends moodleform {

    function timetracker_alert_form($context, $userid, $courseid){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 

        //check to make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
        $canmanage = false;
        if(has_capability('block/timetracker:manageworkers',$this->context)){
            $canmanage = true;
        }
        

        $userinfo = $DB->get_record('block_timetracker_workerinfo',array('id'=>$this->userid));

        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',array('id'=>$this->courseid,'userid'=>$this->userid));
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($index,'No permission to add hours',2);
        }
        

        $mform->addElement('header', 'general', get_string('errortitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','id', $this->courseid);

        if($canmanage){
        
        }else{
            $mform->addElement('hidden','editedby', $this->userid);
        
        //TODO Pull work unit to be fixed //$workunit = $DB->get_record('block_timetracker_workunit', array('id'=>$this->userid,'courseid'=>$this->courseid));
        $mform->addElement('html', get_string('to','block_timetracker'));
        $mform->addElement('html', '<br /><br />'); 
        $mform->addElement('html', get_string('subject','block_timetracker',$userinfo->firstname.'
        '.$userinfo->lastname));
        $mform->addElement('html', '<br /><br />'); 
        $mform->addElement('html', get_string('data','block_timetracker'));
        $mform->addElement('html', '<blockquote><blockquote><blockquote><blockquote>');
            $mform->addElement('html', get_string('date','block_timetracker'));
            $mform->addElement('html', '<br /><br />'); 
            $mform->addElement('html', get_string('timeinerror','block_timetracker'));
        $mform->addElement('html', '</blockquote></blockquote></blockquote></blockquote>');
        $mform->addElement('date_time_selector','timeout','Time Out: ');
		$mform->addHelpButton('timeout','timeout','block_timetracker');
	
        $mform->addElement('textarea', 'message', get_string('messageforerror','block_timetracker'), 'wrap="virtual" rows="6" cols="75"');
		$mform->addHelpButton('message','messageforerror','block_timetracker');

        $this->add_action_buttons(true,get_string('sendbutton','block_timetracker'));
        }
    }   

    function validation ($data){
        
    }
}
