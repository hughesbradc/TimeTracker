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

class timetracker_testsig_form extends moodleform {
   function timetracker_testsig_form($context,$courseid,$mdluserid){
       $this->context = $context;
       $this->courseid = $courseid;
       $this->mdluserid = $mdluserid;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform =& $this->_form;

        $mform->addElement('header','general',
            get_string('timesheet','block_timetracker'));

        $worker = $DB->get_record('block_timetracker_workerinfo',
            array('courseid'=>$this->courseid,'mdluserid'=>$this->mdluserid));

        $signature = $mform->addElement('html','You must sign your timesheet exactly as your name appears:
        '.$worker->firstname .' '.$worker->lastname);
        $mform->addElement('text','signature',get_string('signature','block_timetracker'));
        
        $this->add_action_buttons(true,get_string('signbutton','block_timetracker'));
    }

    function validation($data){
        /*
        $name = $worker->firstname .' '.$worker->lastname;
        if($name != $signature){
            echo 'Your signature does not exactly match your name as displayed.';
        }
        */
    }
 }
?>
