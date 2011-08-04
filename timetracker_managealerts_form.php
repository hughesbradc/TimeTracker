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

class timetracker_managealerts_form  extends moodleform {

    function timetracker_managealerts_form($context){
        $this->context = $context;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE, $OUTPUT;


        $mform =& $this->_form; // Don't forget the underscore! 
        if (!has_capability('block/timetracker:manageworkers', $this->context)) {
            $mform->addElement('html','You don\'t have permission to view alerts'); 
            return;
        }

        $isadmin = false;
        if(has_capability('moodle/site:config',$this->context)){
            $isadmin = true;
        }

        $mform->addElement('header', 'general', get_string('managealerts','block_timetracker')); 
		$mform->addHelpButton('general','managealerts','block_timetracker');


        $strfirstname = get_string('firstname', 'block_timetracker');
        $strlastname = get_string('lastname', 'block_timetracker');
        $strprev = get_string('previous', 'block_timetracker');
        $strproposed = get_string('proposed', 'block_timetracker');

        $mform->addElement('html', 
            '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="95%">');
        
        $mform->addElement('html',
            '<tr>
                <th>'.$strfirstname.'</th>
                <th>'.$strlastname.'</th>
                <th>'.$strprev.'</th>
                <th>'.$strproposed.'</th>
                <th>'.get_string('action').'</th>
             </tr>');

        if(!$isadmin && !has_alerts($USER->id,$COURSE->id)){
            $mform->addElement('html',
                '<tr><td colspan="6" style="text-align: center">'.
                get_string('noalerts','block_timetracker').'</td></tr></table>');
        } else {
            if($isadmin && !has_course_alerts($COURSE->id)){
                $mform->addElement('html',
                    '<tr><td colspan="6" style="text-align: center">'.
                    get_string('noalerts','block_timetracker').'</td></tr></table>');
                return;
            }
            if($isadmin)
                $alertlinks=get_course_alert_links($COURSE->id);
            else 
                $alertlinks=get_alert_links($USER->id, $COURSE->id); 
            $alerts = $DB->get_records('block_timetracker_alertunits', 
                array('courseid'=>$COURSE->id), 'alerttime');


            foreach ($alerts as $alert){ 
                $worker = $DB->get_record('block_timetracker_workerinfo',
                    array('id'=>$alert->userid));

                $mform->addElement('html','<tr>'); 
                $row ='<td>'.$worker->lastname.'</td>';
                $row.='<td>'.$worker->firstname.'</td>';
                $row.='<td>In: '.userdate($alert->origtimein, 
                    get_string('datetimeformat','block_timetracker'));

                if($alert->origtimein > 0){
                    $row.='<br />Out: '.userdate($alert->origtimeout, 
                        get_string('datetimeformat','block_timetracker'));
                    $row.='<br />Elapsed: '.format_elapsed_time($alert->origtimeout -
                        $alert->origtimein);
                } else {
                    $row.= '';
                }

                $row .='</td>';
                $row.='<td>In: '.userdate($alert->timein, 
                    get_string('datetimeformat','block_timetracker'));

                $row.='<br />Out: '.userdate($alert->timeout, 
                    get_string('datetimeformat','block_timetracker'));

                $row.='<br />Elapsed: '.format_elapsed_time($alert->timeout -
                    $alert->timein);

                $row.='</td>';

                $editurl = new moodle_url($alertlinks[$worker->id]['change']);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    
                $approveurl = new moodle_url($alertlinks[$worker->id]['approve']);
                $checkicon = new pix_icon('check','Approve','block_timetracker');
                $approveaction=$OUTPUT->action_icon($approveurl, $checkicon);
    
                $deleteurl = new moodle_url($alertlinks[$worker->id]['deny']);
                $deleteicon = new pix_icon('t/delete', get_string('delete'));
                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action(
                    'Are you sure you want to delete this work unit?'));
    
                $row .= '<td style="text-align: center">'.
                    $approveaction . ' ' . $deleteaction. ' '.$editaction.'</td>';
    
                $row.='</tr>';
                $mform->addElement('html',$row);
    
            }
    
            $mform->addElement('html','</table>');
    
            //$this->add_action_buttons(true, 'Save Changes');
    
            }
    }

}
