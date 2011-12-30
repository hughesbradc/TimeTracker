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
* This form will allow the worker to sign a timesheet electronically. 
*
* @package    Block
* @subpackage TimeTracker
* @copyright  2011 Marty Gilbert & Brad Hughes
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
*/

require_once("$CFG->libdir/formslib.php");
require_once('lib.php');

class timetracker_workersig_form extends moodleform {
   function timetracker_workersig_form($courseid,$userid){
       $this->courseid = $courseid;
       $this->userid = $userid;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE, $USER;

        $mform =& $this->_form;

        $mform->addElement('header','general',
            get_string('timesheet','block_timetracker'));

        $mform->addElement('html', get_string('workerstatement','block_timetracker'));
        $mform->addElement('checkbox','workersig',get_string('clicktosign','block_timetracker'));
       
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit',
            'signbutton',get_string('signbutton','block_timetracker'));
        $mform->addGroup($buttonarray, 'buttonar','',array(' '), false);
        
        $mform->disabledIf('buttonar','workersig');
        
    }

    function validation($data){
    }
}
?>
