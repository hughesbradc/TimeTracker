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
 * This form will allow the user to input the date, time, and duration of their workunit. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');

class timetracker_editunit_form extends moodleform {

    function timetracker_editunit_form($context, $userid, $courseid, $unitid, $start=0, $end=0){
        $this->context = $context;
        $this->userid = $userid;
        $this->unitid = $unitid;
        $this->courseid = $courseid;
        $this->start = $start;
        $this->end = $end;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 

        //check to make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
        if(!has_capability('block/timetracker:manageworkers',$this->context)){
            print_error('Insufficient permission to edit this workunit');
            return;
        }

        $canmanage = true;

        $userinfo = $DB->get_record('block_timetracker_workerinfo',array('id'=>$this->userid));
        $workunit = $DB->get_record('block_timetracker_workunit',array('id'=>$this->unitid));

        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',array('id'=>$this->courseid,'userid'=>$this->userid));
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($index,'No permission to add hours',2);
        }
        
        $mform->addElement('header', 'general', get_string('editunittitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','unitid', $this->unitid);
        $mform->addElement('hidden','id', $this->courseid);

        //edited by supervisor
        $mform->addElement('hidden','editedby', '0');

        $mform->addElement('date_time_selector','timein','Time In: ',array('optional'=>false,'step'=>1));
		$mform->addHelpButton('block_timetracker_timein','timein','block_timetracker');
        if($this->start!=0){
            $mform->setDefault('timein',$this->start);
        } else {
            $mform->setDefault('timein',$workunit->timein);
        }
        
        $mform->addElement('date_time_selector','timeout','Time Out: ',array('optional'=>false,'step'=>1));
		$mform->addHelpButton('block_timetracker_timeout','timeout','block_timetracker');
        if($this->end!=0){
            $mform->setDefault('timeout',$this->end);
        } else {
            $mform->setDefault('timeout',$workunit->timeout);
        }
		
        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

    function validation ($data){
        $errors = array();
        if($data['timein'] > $data['timeout']){
            $errors['timein'] = 'Time in cannot be before time out';    
        }

        if($data['timein'] > time() || $data['timeout'] > time()){
            $errors['timein'] = 'Time cannot be set in the future';    
        }

        return $errors;
        
    }
}
