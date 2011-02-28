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

class timetracker_reports_form  extends moodleform {

    function timetracker_reports_form($context,$userid = 0,$courseid=0, $reportstart=0, $reportend=0){
        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->reportstart = $reportstart;
        $this->reportend = $reportend;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $OUTPUT;


        $mform =& $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'general', 'Report time period'); 



        if($this->reportstart == 0){
            $startfrom = usergetdate(time()); 
        } else {
            $startfrom = usergetdate($this->reportstart);
        }

        if($this->reportend == 0){
            $goto = usergetdate(time()*30*24*60); //one month from now
        } else {
            $goto = usergetdate($this->reportend);
        }


        $mform->addElement('date_selector', 'timein', 'Report start date',$startfrom);
        $mform->addElement('date_selector', 'timeout', 'Report end date', $goto);
        $mform->addElement('submit', 'datechange', 'Get work units');


        //************** PENDING WORK UNITS SECTION ****************//
        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        //which workers to see?
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses
            $pendingunits = $DB->get_records('block_timetracker_pending', array());
        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course
            $pendingunits =
                $DB->get_records('block_timetracker_pending',
                array('userid'=>$this->userid, 'courseid'=>$this->courseid));
        } else { //if ($this->userid != 0) //specific user, this course
            $pendingunits = $DB->get_records('block_timetracker_pending',
                array('userid'=>$this->userid,'courseid'=>$courseid));
        }


        if($pendingunits){ //if they have them.
            $mform->addElement('header', 'general', 'Pending work units');
            $mform->addElement('html', '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">');
        
            $headers = 
                '<tr>
                    <thTime in</th>
                ';
            if($canmanage){
                    $headers .='<th>'.get_string('action').'</th>';
            }
            $headers .='</tr>';

            $mform->addElement('html',$headers);


            foreach($pendingunits as $pending){
                $row='<tr>';
                $row.='<td>'.userdate($pending->timein,get_string('strftimedatetime')).'</td>';

                if($canmanage){
                    $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                    $paramstring = "?id=$COURSE->id&userid=$worker->id&sesskey=".sesskey();
    
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
        } 

        //************** WORK UNITS SECTION ****************//

        //which workers to see?
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses
            $units = $DB->get_records('block_timetracker_workunit', array());
        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course
            $units =
                $DB->get_records('block_timetracker_workunit',
                array('userid'=>$this->userid, 'courseid'=>$this->courseid));
        } else { //specific user, this course
            $units = $DB->get_records('block_timetracker_workunit',
                array('userid'=>$this->userid,'courseid'=>$courseid));
        }


        if($units){ //if they have them.
            $mform->addElement('header', 'general', 'Pending work units');
            $mform->addElement('html', '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">');
        
            $headers = 
                '<tr>
                    <thTime in</th>
                    <th>Time out</th>
                    <th>Elapsed</th>
                ';
            if($canmanage){
                    $headers .='<th>'.get_string('action').'</th>';
            }
            $headers .='</tr>';

            $mform->addElement('html',$headers);


            $total = 0;
            foreach($nits as $unit){
                $row='<tr>';
                $row.='<td>'.userdate($unit->timein,get_string('strftimedatetime')).'</td>';
                $row.='<td>'.userdate($unit->timeout,get_string('strftimedatetime')).'</td>';
                $currelapsed = $unit->timeout - $unit->timein;  
                $total += $currelapsed;
                $row.='<td>'.format_elapsed_time($currelapsed).'</td>';

                if($canmanage){
                    $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                    $paramstring = "?id=$COURSE->id&userid=$worker->id&sesskey=".sesskey();
    
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
    
        $mform->addElement('hidden','courseid', $this->courseid);
        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','reportstart', $this->reportstart);
        $mform->addElement('hidden','reportend', $this->reportend);
    
    }

}

function format_elapsed_time($totalsecs=0){
    if($totalsecs <= 0){
        return '0 hours 0 minutes';
    }

    $hours = floor($totalsecs/3600);

    $totalsecs = $totalsecs % 3600;

    //round to the nearest 900 seconds (1/4 hour)
    if($totalsecs < 450) {
        $minutes = '0 minutes';
    } else if($totalsecs < 1350){
        $minutes = '15 minutes';
    } else if ($totalsecs < 2250){
        $minutes = '30 minutes';
    } else if ($totalsecs < 3150){
        $minutes = '45 minutes';
    } else {
        $minutes = '0 minues';
        $hours++;
    }
    
    return $hours.' hours and '.$minutes; 
    
}
