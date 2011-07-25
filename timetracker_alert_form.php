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

class timetracker_alert_form  extends moodleform {

    function timetracker_alert_form($context, $userid, $courseid, $unitid, $ispending=false){
        
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->unitid = $unitid;
        $this->ispending = $ispending;
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
        
        $userinfo = $DB->get_record('block_timetracker_workerinfo',array('id'=>$this->userid));
        
        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$this->courseid,'userid'=>$this->userid));
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($index,'No permission to add hours',2);
        }

        $mform->addElement('header', 'general', get_string('errortitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','unitid', $this->unitid);
        $mform->addElement('hidden','ispending', $this->ispending);

        if($canmanage){
        
        } else {
            $mform->addElement('hidden','editedby', $this->userid);
        
        $mform->addElement('html', '<b>'); 
        $mform->addElement('html', get_string('to','block_timetracker'));
        $mform->addElement('html', '</b>'); 

        $teachers = get_users_by_capability($this->context, 'block/timetracker:manageworkers');
        foreach ($teachers as $teacher) {
            $mform->addElement('advcheckbox', 'teacherid['.$teacher->id.']', 
                $teacher->firstname.' '.$teacher->lastname,  null, array('group' => 1, 'checked="checked"'));
        }
        
        $this->add_checkbox_controller(1, null, null, 1);

        $mform->addElement('html', '<b>'); 
        $mform->addElement('html', get_string('subject','block_timetracker'));
        $mform->addElement('html', '</b>'); 
        $mform->addElement('html', get_string('subjecttext','block_timetracker',$userinfo->firstname.' '.
            $userinfo->lastname));
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
                format_elapsed_time($unit->timeout - $unit->timein)));
        }

        $mform->addElement('html', '</blockquote><b>'); 
        $mform->addElement('html', get_string('data','block_timetracker'));
        $mform->addElement('date_time_selector','timeinerror','Time In: ');
        $mform->setDefault('timeinerror',$unit->timein);
		$mform->addHelpButton('timeinerror','timein','block_timetracker');
        $mform->addElement('date_time_selector','timeouterror','Time Out: ');
		$mform->addHelpButton('timeouterror','timeout','block_timetracker');

        $mform->addElement('hidden','origtimein', $unit->timein); 
        
        if(!$this->ispending){
            $mform->setDefault('timeouterror',$unit->timeout);
            $mform->addElement('hidden','origtimeout', $unit->timeout); 
        } else {
            $mform->setDefault('timeouterror',$unit->timein + (60 * 60 * 2));
        }
        
        $mform->addElement('checkbox', 'deleteunit', get_string('deleteunit','block_timetracker'));
        $mform->addHelpButton('deleteunit', 'deleteunit', 'block_timetracker');

        $mform->addElement('textarea', 'message', 
            get_string('messageforerror','block_timetracker'), 'wrap="virtual" rows="6" cols="75"');
		$mform->addHelpButton('message','messageforerror','block_timetracker');
        $mform->addRule('message', null, 'required', null, 'client', 'false');
        $mform->addElement('html', '</b>'); 

        $this->add_action_buttons(true,get_string('sendbutton','block_timetracker'));
        }
    }   

    function validation ($data){
        $errors = array();

        $teachers = $data['teacherid'];
        $firstteach = -1;
        foreach($teachers as $teacherid=>$selectedval){
            //if($firstteach == -1) $firstteach = $teacherid; 
            if($selectedval==1){ 
                return $errors;
            }
            $firstteach = $teacherid;
        }
        //if it gets here, we had no teachers selected. Use the first teacherid value to
        //place the error
        $errors['teacherid['.$firstteach.']'] = 'You must select at least one supervisor.';

        if($data['timeout'] > time()){
            $errors['timeout'] = 'Time cannot be set in the future.';
        }

        /*
        if($data['timein'] > $data['timeout']){
            $errors['timein'] = 'Your time out cannot be before you clocked in.';
        }
        */

        return $errors;
    }
}
