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
        $this->year = $year;
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

        if($canmanage) {
            print("in canmanage 'if'");
            $workerlist = array();
            $workers =
                $DB->get_records('block_timetracker_workerinfo',array('courseid'=>$this->courseid),
                'lastname DESC');
            print_object($workers);
            foreach($workers as $worker){
                $workerlist[$worker->id] = $worker->firstname.' '.$worker->lastname;
            }
            print_object($workerlist);
            $mform->addElement('select', 'workerid', 'Workers', $workerlist);
        } else {
            $mform->addElement('hidden','workerid',$USER->id);    
        }

        $months = array(
            1 =>'January',
            2=>'February',
            3=>'March',
            4=>'April',
            5=>'May',
            6=>'June',
            7=>'July',
            8=>'August',
            9=>'September',
            10=>'October',
            11=>'November',
            12=>'December');

        $mform->addElement('select', 'month', 'Month', $months);
        //TODO Will eventually look at the earliest record in the database and generate year from that
        //record to the current year
        

        $sql = 'SELECT timein FROM '.$CFG->prefix.'block_timetracker_workunit ORDER BY timein LIMIT 1';
        $earliestyear = $DB->get_record_sql($sql);
        $sql = 'SELECT timeout FROM '.$CFG->prefix.'block_timetracker_workunit ORDER BY timeout DESC LIMIT 1';
        $latestyear = $DB->get_record_sql($sql);
        
        $latestyear = date("Y", $latestyear->timeout);
        $earliestyear = date("Y", $earliestyear->timein);
        
        $years = array();
        foreach(range($earliestyear,$latestyear) as $year){
            $years[$year] = $year;
        }
        if(!empty($years))
            $mform->addElement('select', 'year', 'Year', $years);

        $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
    }

    function validation($data){
        $errors = array();

        return $errors;
    }
}
?>
