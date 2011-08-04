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

    function timetracker_timesheet_form($context,$userid = 0,$courseid=0, $month, $year){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->month = $month;
        $this->year = $year
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $OUTPUT;
        $mform =& $this->_form; // Don't forget the underscore! 

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        // Collect all of the workers under the supervisor

        //$workerlist = $DB->get_records('block_timetracker_
       
        if($canmanage) {
            $mform->addElement('select', 'calendar_workers', 'Workers', array(
        }

        $mform->addElement('select', 'calendar_month', 'Month', 
            array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'));
        //TODO Will eventually look at the earliest record in the database and generate year from that
        //record to the current year
        
        $earliestyear = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE workerid='.
            $this->userid ' DESC LIMIT 1';

        $mform->addElement('select', 'calendar_year', 'Year', array(''));
