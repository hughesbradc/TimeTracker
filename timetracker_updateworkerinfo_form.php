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
     
     function definition() {
         global $CFG, $USER, $DB;

         $mform =& $this->_form;

         $mform->addElement('header','general',get_string('updateformheadertitle','block_timetracker'));

         $mform->addElement('hidden','id',$USER->id);

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
     
         $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
     }

     function validation($data) {

     }
 }
?>
