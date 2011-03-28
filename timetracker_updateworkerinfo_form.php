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
* This block will display a summary of hours and earnings for the worker.
*
* @package    Block
* @subpackage TimeTracker
* @copyright  2011 Marty Gilbert & Brad Hughes
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
*/

require_once("$CFG->libdir/formslib.php");

class timetracker_updateworkerinfo_form extends moodleform {
   function timetracker_updateworkerinfo_form($context,$courseid,$mdluserid){
       $this->context = $context;
       $this->courseid = $courseid;
       $this->mdluserid = $mdluserid;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE;

        $mform =& $this->_form;

        $mform->addElement('header','general',get_string('updateformheadertitle','block_timetracker'));

        $sql = "SELECT name,value FROM {$CFG->prefix}block_timetracker_config WHERE courseid=$this->courseid";

        //defaults
        $payrate = 7.50;
        $maxearnings = 750;
        $trackermethod = 0;

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        $config = $DB->get_records_sql($sql);
        if($config){        
            $payrate = $config['block_timetracker_curr_pay_rate']->value;
            $maxearnings = $config['block_timetracker_default_max_earnings']->value;
            $trackermethod = $config['block_timetracker_trackermethod']->value;
        }

        $mform->addElement('hidden','mdluserid', $this->mdluserid);
        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','courseid', $this->courseid);
        $mform->addElement('hidden','maxearnings',$maxearnings);
        

        $worker = $DB->get_record('block_timetracker_workerinfo',array('mdluserid'=>$this->mdluserid));
        if(!$worker){
            $worker = $DB->get_record('user',array('id'=>$this->mdluserid));
        } else {
            $mform->addElement('hidden','userid',$worker->id);
        }

        $opstring='readonly="readonly"';
        if($canmanage){
            $opstring = '';
        }

        $mform->addElement('text','firstname',get_string('firstname','block_timetracker'), $opstring);
        $mform->setDefault('firstname',$worker->firstname);

        $mform->addElement('text','lastname',get_string('lastname','block_timetracker'), $opstring);
        $mform->setDefault('lastname',$worker->lastname);
        
        $mform->addElement('text','email',get_string('email','block_timetracker'), $opstring);
        $mform->setDefault('email',$worker->email);

        $mform->addElement('text','address',get_string('address','block_timetracker'));
        $mform->addRule('address', null, 'required', null, 'client', 'false');
        $mform->setDefault('address', $worker->address);
        $mform->addElement('text','phone',get_string('phone','block_timetracker'));
   
        if ($canmanage){
            $mform->addElement('text','currpayrate',get_string('currpayrate','block_timetracker'));
            $mform->setDefault('currpayrate',$payrate);

            $mform->addElement('select','timetrackermethod','Tracking Method',array(0=>'TimeClock',1=>'Hourlog'));
            $mform->setDefault('timetrackermethod',$trackermethod);
        } else {
            $mform->addElement('text','currpayrate',get_string('currpayrate','block_timetracker'), 'readonly="readonly"');
            $mform->setDefault('currpayrate',$payrate);
            $mform->addElement('hidden','timetrackermethod', $trackermethod);
        }
        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

     function definition() {
         global $CFG, $USER, $DB, $COURSE;

         $mform =& $this->_form;

         $mform->addElement('header','general',get_string('updateformheadertitle','block_timetracker'));

         $config = $DB->get_records('block_timetracker_config',array('courseid'=>$this->courseid));

         $mform->addElement('hidden','userid', $USER->id);
         $mform->addElement('hidden','id', $this->courseid);
         $mform->addElement('hidden','courseid', $this->courseid);
         //if($config && $config['config_block_timetracker_curr_pay_rate']
         $mform->addElement('hidden','maxearnings','750');
         

         //$worker = $DB->get_record('block_timetracker_workerinfo',array('id'=>$USER->id));
         $worker = $DB->get_record('user',array('id'=>$USER->id));

         $mform->addElement('text','firstname',get_string('firstname','block_timetracker'), 'readonly="readonly"');
         $mform->setDefault('firstname',$worker->firstname);

         $mform->addElement('text','lastname',get_string('lastname','block_timetracker'), 'readonly="readonly"');
         $mform->setDefault('lastname',$worker->lastname);
         
         $mform->addElement('text','email',get_string('email','block_timetracker'), 'readonly="readonly"');
         $mform->setDefault('email',$worker->email);

         $mform->addElement('text','address',get_string('address','block_timetracker'));
         $mform->addRule('address', null, 'required', null, 'client', 'false');
         $mform->setDefault('address', $worker->address);
         $mform->addElement('text','phone',get_string('phone','block_timetracker'));
    
         if (has_capability('block/timetracker:manageworkers', $this->context)) {
             $mform->addElement('text','currpayrate',get_string('currpayrate','block_timetracker'));
             //$mform->setDefault('currpayrate',$CFG->block_timetracker_curr_pay_rate);
             //TODO
             $mform->setDefault('currpayrate','7.75');

         } else {
             $mform->addElement('text','currpayrate',get_string('currpayrate','block_timetracker'), 'readonly="readonly"');
             //$mform->setDefault('currpayrate',$CFG->block_timetracker_curr_pay_rate);
             //TODO
             $mform->setDefault('currpayrate','7.75');
         }
         $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
     }

     function validation($data) {

     }
 }
?>
