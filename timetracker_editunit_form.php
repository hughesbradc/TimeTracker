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
require_once ('lib.php');

class timetracker_editunit_form extends moodleform {

    function timetracker_editunit_form($context, $userid, $courseid, $unitid, 
        $start=0, $end=0,$ispending=false){

        $this->context = $context;
        $this->userid = $userid;
        $this->unitid = $unitid;
        $this->courseid = $courseid;
        $this->start = $start;
        $this->end = $end;
        $this->ispending=$ispending;
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

        $userinfo = $DB->get_record('block_timetracker_workerinfo',
            array('id'=>$this->userid));

        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;

        }

        if($this->ispending){
            //error_log("getting a pending unit # $this->unitid");
            $unit = $DB->get_record('block_timetracker_pending',array('id'=>$this->unitid));
            //print_object($unit);
        } else {
            $unit = $DB->get_record('block_timetracker_workunit',array('id'=>$this->unitid));
        }

        if(!$unit){
            print_error('Unit does not exist: '.$this->unitid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$this->courseid,'userid'=>$this->userid));
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($index,'No permission to add hours', 1);
        }
        
        $mform->addElement('header', 'general', 
            get_string('editunittitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        /** HIDDEN FIELDS **/
        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','unitid', $this->unitid);
        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','ispending', $this->ispending);
        //edited by supervisor
        $mform->addElement('hidden','editedby', $USER->id);
        /** END HIDDEN FIELDS **/
        

        /** EXISTING DATA **/
        $mform->addElement('html',get_string('existingunit','block_timetracker'));
        $mform->addElement('html','<blockquote>');
        $mform->addElement('html', get_string('existingtimein','block_timetracker',
            userdate($unit->timein, get_string('datetimeformat','block_timetracker'))));

        if(!$this->ispending){
            $mform->addElement('html','<br />');
            $mform->addElement('html',get_string('existingtimeout','block_timetracker',
                userdate($unit->timeout, get_string('datetimeformat','block_timetracker'))));
            $mform->addElement('html','<br /><b>');
            $mform->addElement('html',get_string('existingduration','block_timetracker',
                format_elapsed_time($unit->timeout - $unit->timein)));
        }
        $mform->addElement('html','</blockquote>');
        /** END EXISTING DATA **/


        $mform->addElement('date_time_selector','timein','Time In: ',
            array('optional'=>false,'step'=>1));
		$mform->addHelpButton('timein','timein','block_timetracker');
        if($this->start!=0){
            $mform->setDefault('timein',$this->start);
        } else {
            $mform->setDefault('timein',$unit->timein);
        }
        
        if(!$this->ispending){
            $mform->addElement('date_time_selector','timeout','Time Out: ',
                array('optional'=>false,'step'=>1));
		    $mform->addHelpButton('timeout','timeout','block_timetracker');
            if($this->end!=0){
                $mform->setDefault('timeout',$this->end);
            } else {
                $mform->setDefault('timeout',$unit->timeout);
            }
            $mform->addElement('text', 'payrate', 'Payrate $');
            $mform->setDefault('payrate', $unit->payrate);
            $mform->addRule('payrate', 'Numeric values only', 'numeric',
               null, 'server', false, false);
        }
		
        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

    function validation ($data){
        global $COURSE;

        $errors = array();
        

        if(isset($data['timeout'])){
            if($data['timein'] > $data['timeout']){
                $errors['timein'] = 'Time in cannot be before time out';    
            }
    
            if($data['timein'] > time() || $data['timeout'] > time()){
                $errors['timein'] = 'Time cannot be set in the future';    
            }
    
            if(overlaps($data['timein'],$data['timeout'],$data['userid'],$data['unitid'])){

                $errors['timein'] = 'Work unit overlaps with existing workunit';
            }

            if(!isset($data['payrate']) || $data['payrate'] == ''){
                $errors['payrate'] = 'Payrate cannot be empty';
            }
        }


        return $errors;
        
    }
}
