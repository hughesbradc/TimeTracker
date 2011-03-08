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
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_reports_form  extends moodleform {

    function timetracker_reports_form($context,$userid = 0,$courseid=0, $reportstart=0, $reportend=0){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->reportstart = $reportstart;
        $this->reportend = $reportend;
        //echo ("Constructor");
        //$this->reportstart = $reportstart;
        //$this->reportend = $reportend;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $OUTPUT;
        $mform =& $this->_form; // Don't forget the underscore! 

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        if($USER->id != $this->userid && !$canmanage){
            $mform->addElement('html','You do not have permission to view this user\'s work units.');
            return;
        }

        $mform->addElement('header', 'general', 'Report time period'); 

        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','sesskey', sesskey());
        if($this->reportstart == 0){
            $this->reportstart = time()-(60*60*24*31);
        }

        if($this->reportend == 0){
            $this->reportend = time();
        } 

        $mform->addElement('date_selector', 'reportstart', 'Report start date');
        $mform->setDefault('reportstart',$this->reportstart);

        $mform->addElement('date_selector', 'reportend', 'Report end date');
        $mform->setDefault('reportend',$this->reportend);

        $mform->addElement('submit', 'datechange', 'Get work units');



        //************** PENDING WORK UNITS SECTION ****************//
        //which workers to see?
        $endtime = $this->reportend + ((60*60*23)+60*59); //23:59
        $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_pending WHERE timein BETWEEN '.
            $this->reportstart.' AND '.$endtime.' ';
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses

            $pendingunits = $DB->get_records_sql($sql);

        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course

            $sql .= 'AND courseid='. $this->courseid;
            $pendingunits = $DB->get_records_sql($sql);

        } else { //if ($this->userid != 0) //specific user, this course
            
            $sql .= 'AND userid='.$this->userid. ' AND courseid='. $this->courseid;
            $pendingunits = $DB->get_records_sql($sql);

        }


        $mform->addElement('header', 'general', 'Pending work units');
        if(!$pendingunits){ //if they don't have them.
            $mform->addElement('html','No pending work units<br />');
        } else { //if they do have pending
            $mform->addElement('html', '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">');
        
            $headers = 
                '<tr>
                    <th>Time in</th>
                ';
            if($canmanage){
                    $headers .='<th>'.get_string('action').'</th>';
            }
            $headers .='</tr>';

            $mform->addElement('html',$headers);


            foreach($pendingunits as $pending){
                $row='<tr>';
                $row.='<td>'.userdate($pending->timein,get_string('datetimeformat','block_timetracker')).'</td>';

                if($canmanage){
                    $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                    $paramstring = "?id=$pending->courseid&userid=$pending->userid&sesskey=".sesskey().'&pendingid='.$pending->id;
    
                    $editurl = new moodle_url($baseurl.'/editpending.php'.$paramstring);
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
        
                    $deleteurl = new moodle_url($baseurl.'/deletepending.php'.$paramstring);
                    $deleteicon = new pix_icon('t/delete', get_string('delete'));
                    $deleteaction = $OUTPUT->action_icon(
                        $deleteurl, $deleteicon, 
                        new confirm_action('Are you sure you want to delete this pending work unit?'));
    
                    $row .= '<td style="text-align: center">'.$editaction . ' '.$deleteaction.'</td>';
    
                }
                $row .= '</tr>';
                $mform->addElement('html',$row);
            }
            $mform->addElement('html','</table>');
        } 

        //************** WORK UNITS SECTION ****************//

        //which workers to see?
        $endtime = $this->reportend + ((60*60*23)+60*59); //23:59
        $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE timeout BETWEEN '.
            $this->reportstart.' AND '.$endtime.' ';
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses
            $units = $DB->get_records_sql($sql);
        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course
            $sql .= 'AND courseid='.$this->courseid;
            $units =
                $DB->get_records_sql($sql);
        } else { //specific user, this course
            $sql .= ' AND courseid='.$this->courseid.' AND userid='.$this->userid;
            $units = $DB->get_records_sql($sql);
        }


        $mform->addElement('header', 'general', 'Completed work units');
        if(!$units){ //if they don't have them.
            $mform->addElement('html','No completed work units<br />');
        } else { //if they do have some
            $mform->addElement('html', '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">');
        
            $headers = 
                '<tr>
                    <th>Time in</th>
                    <th>Time out</th>
                    <th>Elapsed</th>
                ';
            if($canmanage){
                    $headers .='<th>'.get_string('action').'</th>';
            }
            $headers .='</tr>';

            $mform->addElement('html',$headers);


            $total = 0;
            foreach($units as $unit){
                $row='<tr>';
                $row.='<td style="text-align: center">'.userdate($unit->timein,get_string('datetimeformat','block_timetracker')).'</td>';
                $row.='<td style="text-align: center">'.userdate($unit->timeout,get_string('datetimeformat','block_timetracker')).'</td>';
                $currelapsed = $unit->timeout - $unit->timein;  
                $total += round_time($currelapsed);
                $row.='<td style="text-align: center">'.format_elapsed_time($currelapsed).'</td>';

                if($canmanage){
                    $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                    $paramstring = "?id=$unit->courseid&userid=$unit->userid&sesskey=".sesskey().'&unitid='.$unit->id;
    
                    $editurl = new moodle_url($baseurl.'/editworkunit.php'.$paramstring);
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
        
                    $deleteurl = new moodle_url($baseurl.'/deleteworkunit.php'.$paramstring);
                    $deleteicon = new pix_icon('t/delete', get_string('delete'));
                    $deleteaction = $OUTPUT->action_icon(
                        $deleteurl, $deleteicon, 
                        new confirm_action('Are you sure you want to delete this work unit?'));
    
                    $row .= '<td style="text-align: center">'.$editaction . ' '.$deleteaction.'</td>';
    
                }
                $row .= '</tr>';
                $mform->addElement('html',$row);
            }
            $mform->addElement('html','<tr><td colspan="4" style="text-align: right">Total: '.format_elapsed_time($total).'</td></tr></table>');

        } 
    
    
    }

    /*
    function definition_after_data(){
        //$mform =& $this->_form;
        //echo ("in def after data");
        //$this->reportstart = $mform->getElementValue('reportstart');
        //$this->reportend = $mform->getElementValue('reportend');
    }
    */

    function validation ($data){
        $errors = array();
        if($data['reportstart'] > $data['reportend']){
            $errors['reportstart'] = 'Start cannot be before end';    
        }

        return $errors;
        
    }
}
