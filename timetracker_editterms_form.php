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
 * This form will allow the user to input the date, time, and duration of their workunit. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_editterms_form extends moodleform {

    function timetracker_editterms_form($context){
        $this->context = $context;
        parent::__construct();
    }

    function definition() {
        global $CFG, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore! 

        $mform->addElement('hidden','id',$COURSE->id);

        //check to make sure that if $this->userid != $USER->id that they have
        //the correct capability TODO
        $canmanage = false;
        if(has_capability('block/timetracker:manageworkers',$this->context)){
            $canmanage = true;
        }

        $canview = false;
        if(has_capability('block/timetracker:viewonly',$this->context)){
            $canview = true;
        }
        
        if(!$canmanage && !$canview){
            print_error('Insufficient permission to edit this workunit');
            return;
        }

        $terms = $DB->get_records('block_timetracker_term', 
            array('courseid'=>$COURSE->id),'month, day');

        $days = array();
        for($i = 1; $i <= 31; $i++){
            $days[$i] = $i;
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

        //default values
        $termmonth = array();
        $termday = array();

        //jan 1
        $termname[0] = 'Spring';
        $termmonth[0] = 1;
        $termday[0] = 1;
        //jun 1
        $termname[1] = 'Summer';
        $termmonth[1] = 5;
        $termday[1] = 15;
        //aug 1
        $termname[2] = 'Fall';
        $termmonth[2] = 8;
        $termday[2] = 1;

        //Should allow them to add/delete as many terms as necessary
        if($terms){
            $counter = 0;
            foreach ($terms as $term){
                $mform->addElement('hidden','term'.$counter,$term->id);
                $termname[$counter]=$term->name;
                $termmonth[$counter]=$term->month; 
                $termday[$counter]=$term->day; 
                $counter++;
                if($counter > 2) break; //only allow 3 terms -- XXX fix this
            }
             
        }

        for($i = 0; $i < 3; $i++){
            $mform->addElement('header', 'general', 'Term '.($i+1)); 

            //$mform->addElement('text','termname'.$i, 'Term name', $termname[$i]); 
            $mform->addElement('text','termname'.$i, 'Term name'); 
            $mform->setDefault('termname'.$i , $termname[$i]);
            $mform->addRule('termname'.$i, null, 'required', null, 'client', 'false');

            $mform->addElement('select', 'month'.$i, 'Start month', $months);
            $mform->setDefault('month'.$i, $termmonth[$i]);

            $mform->addElement('select', 'day'.$i, 'Start day', $days);
            $mform->setDefault('day'.$i, $termday[$i]);

        }
	    
        if($canmanage){
            $this->add_action_buttons(true,get_string('savebutton','block_timetracker'));
        }
    }

    function validation ($data){
        $errors = array();
        
        $curryear = date("Y"); 
        $time1 = mktime(0,0,0,$data['month0'],$data['day0'], $curryear);
        $time2 = mktime(0,0,0,$data['month1'],$data['day1'], $curryear);
        $time3 = mktime(0,0,0,$data['month2'],$data['day2'], $curryear);

        if($time2 > $time3)
            $errors['month2'] = 'Term 3 cannot come before term 2';
        if($time1 > $time2)
            $errors['month1'] = 'Term 2 cannot come before term 1';

        $hasthirty = array(2,4,6,9,11); //months with 31
        for($i=0; $i<3; $i++){
            if(in_array($data['month'.$i],$hasthirty) && $data['day'.$i] > 30){
                $errors['month'.$i] = 'Month cannot have more than 30 days';
                break;
            }
            if($data['month'.$i] == 2 && $data['month'.$i] > 28) {
               $errors['month'.$i] = 'Month cannot have more than 28 days'; 
               break;
            }
        }

        return $errors;
        
    }
}
