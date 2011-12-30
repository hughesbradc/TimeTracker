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
 * This form will allow the worker to submit an alert and correction to the supervisor of an error in a work unit.
 * The supervisor will be able to approve or deny the correction.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('../lib.php');

class timetracker_reportgenerator_form extends moodleform {

    function timetracker_reportgenerator_form($reportstart=0, $reportend=0, $catid){
        
        //$this->context = $context;
        $this->reportstart = $reportstart;
        $this->reportend = $reportend;
        $this->catid = $catid;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 

        //check reportend make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
        
        /*
        $canmanage = false;
        if(has_capability('block/timetracker:manageworkers',$this->context)){
            $canmanage = true;
        }
        */
        
        //$userinfo = $DB->get_record('block_timetracker_workerinfo',
        //    array('id'=>$this->userid));
        
        /*
        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$this->courseid,'userid'=>$this->userid));
        */
        $index = new moodle_url($CFG->wwwroot.'blocks/timetracker/');

        /*
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($nextpage,'You do not have permission to generate this report.',1);
        }
        */
        $categoryinfo = $DB->get_record('course_categories', array('id'=>$this->catid));
        if(!$categoryinfo)
            $mform->addElement('header', 'general','Report Generator');
        else 
            $mform->addElement('header', 'general','Report Generator for '.
                $categoryinfo->name);
        
        $now = time();
        if($this->reportstart == 0 || $this->reportend == 0){
            $starttime = usergetdate($now);
            $starttime_mid = make_timestamp($starttime['year'], 
                $starttime['mon'] - 1, $starttime['mday']);
            $this->reportstart = $starttime_mid;

            $endtime = usergetdate($now);
            $endtime_mid = make_timestamp($endtime['year'], 
                $endtime['mon'], $endtime['mday']);
            $this->reportend = $endtime_mid;
        } 

    $buttonarray=array();
    $buttonarray[] = &$mform->createElement('submit', 'conflicts', 'Conflicts');
    $buttonarray[] = &$mform->createElement('submit', 'earningsactive', 
        'Earnings - active workers only');

    $buttonarray[] = &$mform->createElement('submit', 'earningsall', 
        'Earnings - all workers');

            $mform->addElement('html',
                'Please provide a date and time range for the report(s) you
                wish to generate.');
            $mform->addElement('date_selector','reportstart','Start Date: ',
                array('optional'=>false, 'step'=>1));    
            $mform->setDefault('reportstart',$this->reportstart);
            $mform->addElement('date_selector','reportend','End Date: ',
                array('optional'=>false, 'step'=>1));    
            $mform->setDefault('reportend',$this->reportend);

            $mform->addElement('hidden','catid', $this->catid);
            //$mform->addElement('hidden','userid', $this->userid);

            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
    }   

    function validation ($data){
        $errors = array();
        if($data['reportstart'] > $data['reportend']){
            $errors['reportstart'] = 'The begin date cannot be after the end date.';
        } else if ($data['reportend'] < $data['reportstart']){
            $errors['reportend'] = 'The end date cannot be before the begin date.';
        }
        return $errors;
    }

}
