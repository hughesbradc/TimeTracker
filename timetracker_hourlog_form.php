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

class timetracker_hourlog_form  extends moodleform {

    function timetracker_hourlog_form($context, $userid){
        $this->context = $context;
        $this->userid = $userid;
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

        $mform->addElement('header', 'general', get_string('hourlogtitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','id', $COURSE->id);
        if($canmanage){
            $mform->addElement('hidden','editedby', '0');
        }else{
            $mform->addElement('hidden','editedby', $this->userid);
        }

        $workunit = $DB->get_record('block_timetracker_workunit', array('id'=>$this->userid,'courseid'=>$COURSE->id));

        $mform->addElement('date_time_selector','timein','Time In: ');
        
        $mform->addElement('date_time_selector','timeout','Time Out: ');

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
