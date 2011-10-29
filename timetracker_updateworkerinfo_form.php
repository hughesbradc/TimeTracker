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
require_once('lib.php');

class timetracker_updateworkerinfo_form extends moodleform {
   function timetracker_updateworkerinfo_form($context,$courseid,$mdluserid){
       $this->context = $context;
       $this->courseid = $courseid;
       $this->mdluserid = $mdluserid;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform =& $this->_form;

        $mform->addElement('header','general',
            get_string('updateformheadertitle','block_timetracker'));

        //TODO defaults -- shouldn't need these, because config should always be set.
        $payrate = 7.50;
        $maxearnings = 750;
        $trackermethod = 0;

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }
         
        $worker = $DB->get_record('block_timetracker_workerinfo',
            array('courseid'=>$this->courseid,'mdluserid'=>$this->mdluserid));

        if(!$worker){ //set the config defaults from config table
            $config = get_timetracker_config($this->courseid);
            $payrate = $config['curr_pay_rate'];
            $maxearnings = $config['default_max_earnings'];
            $trackermethod = $config['trackermethod'];
            $department= $config['department'];
            $position= $config['position'];
            $budget= $config['budget'];
            $institution= $config['institution'];
            $supname= $config['supname'];
            $idnum = $USER->username;

        } else {
            $idnum = $worker->idnum;
            $payrate = $worker->currpayrate;
            $maxearnings = $worker->maxtermearnings;
            $trackermethod = $worker->timetrackermethod;
            $department = $worker->dept;
            $position = $worker->position;
            $budget = $worker->budget;
            $institution = $worker->institution;
            $supname = $worker->supervisor;
        }

        $mform->addElement('hidden','mdluserid', $this->mdluserid);
        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','courseid', $this->courseid);
        $mform->addElement('hidden','maxearnings',$maxearnings);
        

        if(!$worker){
            $worker = $DB->get_record('user',array('id'=>$this->mdluserid));
        } else {
            $mform->addElement('hidden','userid',$worker->id);
        }

        $opstring='readonly="readonly"';
        if($canmanage){
            $opstring = '';
        }

        $mform->addElement('text','firstname',
            get_string('firstname','block_timetracker'), $opstring);
        $mform->setDefault('firstname',$worker->firstname);
		$mform->addHelpButton('firstname','firstname','block_timetracker');

        $mform->addElement('text','lastname',
            get_string('lastname','block_timetracker'), $opstring);
        $mform->setDefault('lastname',$worker->lastname);
		$mform->addHelpButton('lastname','lastname','block_timetracker');
        
        $mform->addElement('text','email',get_string('email','block_timetracker'), $opstring);
        $mform->setDefault('email',$worker->email);
		$mform->addHelpButton('email','email','block_timetracker');

        $mform->addElement('text','idnum',get_string('idnum','block_timetracker'),
            $opstring);
        $mform->setDefault('idnum',$idnum);
        $mform->addHelpButton('idnum','idnum','block_timetracker');
        
        $mform->addElement('text','address',get_string('address','block_timetracker'));
        $mform->addRule('address', null, 'required', null, 'client', 'false');
		$mform->addHelpButton('address','address','block_timetracker');

        if($worker->address != '0'){
            $mform->setDefault('address', $worker->address);
        }

        $mform->addElement('text','phonenumber',get_string('phone','block_timetracker'));
		$mform->addHelpButton('phonenumber','phone','block_timetracker');
        $mform->setDefault('phonenumber', $worker->phonenumber);

        $mform->addElement('text','maxtermearnings',
            get_string('maxtermearnings','block_timetracker'), $opstring);
        $mform->setDefault('maxtermearnings',$maxearnings);
        $mform->addHelpButton('maxtermearnings','maxtermearnings','block_timetracker');

        $mform->addElement('text','currpayrate',
            get_string('currpayrate','block_timetracker'), $opstring);
        $mform->setDefault('currpayrate',$payrate);
		$mform->addHelpButton('currpayrate','currpayrate','block_timetracker');

        $mform->addElement('text','institution',
            get_string('institution','block_timetracker'), $opstring);
        $mform->setDefault('institution',$institution);
		$mform->addHelpButton('institution','institution','block_timetracker');
        
        $mform->addElement('text','dept',get_string('department','block_timetracker'), 
            $opstring);
        $mform->setDefault('dept',$department);
		$mform->addHelpButton('dept','department','block_timetracker');
        
        $mform->addElement('text','position',get_string('position','block_timetracker'),
            $opstring);
        $mform->setDefault('position',$position);
		$mform->addHelpButton('position','position','block_timetracker');
        
        $mform->addElement('text','budget',get_string('budget','block_timetracker'),
            $opstring);
        $mform->setDefault('budget',$budget);
		$mform->addHelpButton('budget','budget','block_timetracker');
        
        $mform->addElement('text','supervisor',
            get_string('supervisor','block_timetracker'), $opstring);
        $mform->setDefault('supervisor',$supname);
		$mform->addHelpButton('supervisor','supname','block_timetracker');
   
        if ($canmanage){
            $mform->addElement('select','timetrackermethod',
                get_string('trackermethod','block_timetracker'),
                array(0=>get_string('timeclocktitle','block_timetracker'),
                1=>get_string('hourlogheader','block_timetracker')), $opstring);
            $mform->setDefault('timetrackermethod',$trackermethod);
		    $mform->addHelpButton('timetrackermethod','trackermethod','block_timetracker');
        } else {
            $mform->addElement('hidden','timetrackermethod', $trackermethod);
        }
        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

     function validation($data) {

     }
 }
?>
