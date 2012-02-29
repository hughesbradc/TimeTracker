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
 * This form will allow a supervisor to input the date, time, and duration of a worker's  work unit. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_addunit_form  extends moodleform {

    function timetracker_addunit_form($context, $userid, $courseid, $start=0, $end=0){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->timein = $start;
        $this->timeout = $end;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 

        //check to make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
        if(!has_capability('block/timetracker:manageworkers',$this->context)){
            print_error('notpermissible', 'block_timetracker');
        }

        $canmanagepayrate = false;
        if(has_capability('block/timetracker:managepayrate',$this->context)){
            $canmanagepayrate = true;
        }

        $userinfo = $DB->get_record('block_timetracker_workerinfo',
            array('id'=>$this->userid));

        if(!$userinfo){
            print_error('Worker info does not exist for workerinfo id of '.$this->userid);
            return;
        }

        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$this->courseid,'userid'=>$this->userid));

        $mform->addElement('header', 'general', 
            get_string('addunittitle','block_timetracker', 
            $userinfo->firstname.' '.$userinfo->lastname));

        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','id', $this->courseid);
    
        $mform->addElement('hidden','editedby', $USER->id);
    
        $workunit = $DB->get_record('block_timetracker_workunit', 
            array('id'=>$this->userid,'courseid'=>$this->courseid));
    
        $mform->addElement('date_time_selector','timein','Time in: ',
            array('optional'=>false,'step'=>1));
		$mform->addHelpButton('timein','timein','block_timetracker');
        if($this->timein != 0){
            $mform->setDefault('timein', $this->timein);
        }
        
        $mform->addElement('date_time_selector','timeout','Time out: ',
            array('optional'=>false,'step'=>1));
		$mform->addHelpButton('timeout','timeout','block_timetracker');
        if($this->timeout != 0){
            $mform->setDefault('timeout',$this->timeout);
        }

        if($canmanagepayrate){
            $mform->addElement('text', 'payrate', 'Pay rate $');
            $mform->setDefault('payrate', $userinfo->currpayrate);
		    $mform->addHelpButton('payrate','payrate','block_timetracker');

            $mform->addRule('payrate', 'Numeric values only', 'numeric',
                null, 'server', false, false);
        } else {
            $mform->addElement('hidden', 'payrate', $userinfo->currpayrate);
        }
		
        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

    function validation ($data){
        global $OUTPUT, $SESSION;
        $errors = array();
        if($data['timein'] > $data['timeout']){
            $errors['timein'] = 'Time in cannot be before time out';    
        } else if($data['timein'] > time() || $data['timeout'] > time()){
            $errors['timein'] = 'Time cannot be set in the future';    
        } else {

            $conflicts = find_conflicts($data['timein'],$data['timeout'],$data['userid']);
            if(sizeof($conflicts) > 0){

                $params['userid'] = $data['userid'];
                $params['id'] = $data['id'];
                $params['timein'] = $data['timein'];
                $params['timeout'] = $data['timeout'];

                /*
                $next = new moodle_url(qualified_me(), $params);
                $SESSION->fromurl = $next;
                */

                $errormsg = 'Work unit conflicts with existing unit(s):<br />';
                $errormsg .= '<table>';
                foreach($conflicts as $conflict){
                    $errormsg .= '<tr>';

                    $extras = '&next=addunit&astart='.$data['timein'].
                        '&aend='.$data['timeout'];

                    $conflict->editlink .= $extras;

                    $editaction = $OUTPUT->action_icon(
                        $conflict->editlink, 
                        new pix_icon('clock_edit', 'Edit unit', 'block_timetracker'));

                    $conflict->deletelink .= $extras;
        
                    $deleteaction = $OUTPUT->action_icon(
                        $conflict->deletelink, new pix_icon('clock_delete',
                        get_string('delete'), 'block_timetracker'),
                        new confirm_action('Are you sure you want to delete this '.
                        ' conflicting work unit?'));
        
                    $errormsg .= '<td>'.$conflict->display.'</td><td>';
                    if($conflict->editlink != '#') //not a pending clock-in
                        $errormsg .= ' '.$editaction;
        
                    $errormsg .= ' '.$deleteaction.'</td></tr>';
                }
                $errormsg .= '</table>';
                $errors['timein'] = $errormsg;
            }
        }
        return $errors;
    }
}
