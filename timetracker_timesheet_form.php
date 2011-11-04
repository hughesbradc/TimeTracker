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

    function timetracker_timesheet_form($context){
        $this->context = $context;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;
        $mform =& $this->_form; // Don't forget the underscore! 

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        $mform->addElement('header','general','Generate Monthly Timesheet');

        // Collect all of the workers under the supervisor

        $mform->addElement('hidden','id',$COURSE->id);    
        if($canmanage) {
            $workerlist = array();
            $workers =
                $DB->get_records('block_timetracker_workerinfo',array('courseid'=>$COURSE->id),
                'lastname ASC');
            foreach($workers as $worker){
                $workerlist[$worker->id] = $worker->firstname.' '.$worker->lastname;
            }
            $select = &$mform->addElement('select','workerid',
                get_string('workerid','block_timetracker'), $workerlist, 'size="5"');
            $select->setMultiple(true);
            $mform->addHelpButton('workerid','workerid','block_timetracker');
            $mform->addRule('workerid', null, 'required', null, 'client', 'false');
        } else {
            $worker =
                $DB->get_record('block_timetracker_workerinfo',array('mdluserid'=>$USER->id,
                'courseid'=>$COURSE->id));
            if(!$worker){
                print_error('Worker does not exist.');        
            }
            $mform->addElement('hidden','workerid',$worker->id);    
        }



        //$mform->addElement('button','selectall','Select All Students','disabled');
        //$mform->addElement('button','selectall','Select All Students');



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

        $mform->addElement('select', 'month', 
            get_string('month','block_timetracker'), $months);
        $mform->setDefault('month', date("m"));
        $mform->addHelpButton('month','month','block_timetracker');

        $sql = 'SELECT timein FROM '.$CFG->prefix.
            'block_timetracker_workunit ORDER BY timein LIMIT 1';
        $earliestyear = $DB->get_record_sql($sql);

        $earliestyear = date("Y", $earliestyear->timein);
        if(!$earliestyear) $earliestyear = date("Y"); 
        
        $years = array();
        foreach(range($earliestyear,date("Y")) as $year){
            $years[$year] = $year;
        }
        if(empty($years)) $years[date("Y")] = date("Y");

        $mform->addElement('select', 'year', get_string('year','block_timetracker'), $years);
        $mform->addHelpButton('year','year','block_timetracker');
        $mform->setDefault('year', date("Y"));
        

        if($canmanage){
            // Show File Format Dropdown
            $formats = array(
                'pdf' => 'PDF',
                'xls' => 'XLS');
            $mform->addElement('select', 'fileformat', 
                get_string('fileformat','block_timetracker'), $formats);
            $mform->addHelpButton('fileformat','fileformat','block_timetracker');
        } else {
            $mform->addElement('hidden','fileformat','pdf');    
        }

        $this->add_action_buttons(true,get_string('generatebutton','block_timetracker'));
    }

    function validation($data){
        $errors = array();

        return $errors;
    }
}
?>
