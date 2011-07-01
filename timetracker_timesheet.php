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
 * This form will call for the timesheet to be generated. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_timesheet_form  extends moodleform {

    function timetracker_timesheet_form($context,$userid = 0,$courseid=0){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $OUTPUT;
        $mform =& $this->_form; // Don't forget the underscore! 

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        if($this->userid==0 && !$canmanage){
            print_error('notpermissible','block_timetracker',$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$this->courseid);
        }

        if($this->userid == 0 && $canmanage){
            //supervisor -- show all!
            $workers =
            $DB->get_records('block_timetracker_workerinfo',array('courseid'=>$this->courseid));
            if(!$workers){
               $mform->addElement('html','No workers found'); 
               return;
            }
            //add the workers to a drop down list

        }  else {

            $usersid = $DB->get_record('block_timetracker_workerinfo',array('id'=>$this->userid), 'id');
            //add a hidden field to store userid (bet it's below)

            $mform->addElement('header', 'general', 'Calendar');
            $mform->addElement('hidden','id', $this->courseid);
            $mform->addElement('hidden','userid', $userid->id);
            
            if(!$usersid && $usersid->id != $this->userid && !$canmanage){
                print_error('notpermissible','block_timetracker',$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$this->courseid);
            }
        }

        $mform->addElement('select', 'calendar_month', 'Month', array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'));
        //TODO Will eventually look at the earliest record in the database and generate year from that
        //record to the current year
        $mform->addElement('select', 'calendar_year', 'Year', array('2011'));


