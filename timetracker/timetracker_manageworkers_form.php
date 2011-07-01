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

class timetracker_manageworkers_form  extends moodleform {

    function timetracker_manageworkers_form($context){
        $this->context = $context;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE, $OUTPUT;


        $mform =& $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'general', get_string('manageworkers','block_timetracker')); 
		$mform->addHelpButton('block_timetracker_general','general','block_timetracker');


        $stractive = get_string('active', 'block_timetracker');
        $strfirstname = get_string('firstname', 'block_timetracker');
        $strlastname = get_string('lastname', 'block_timetracker');
        $stremail = get_string('email', 'block_timetracker');

        $mform->addElement('html', '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="95%">');
        
        $mform->addElement('html',
            '<tr>
                <th>'.$stractive.'</th>
                <th>'.$strfirstname.'</th>
                <th>'.$strlastname.'</th>
                <th>'.$stremail.'</th>
                <th>Last work unit</th>
                <th>'.get_string('action').'</th>
             </tr>');

        if(!$workers = $DB->get_records('block_timetracker_workerinfo',array('courseid'=>$COURSE->id),'active DESC, lastname ASC')){
            $mform->addElement('html','<tr><td colspan="6" style="text-align: center">No workers registered</td></tr></table>');
        } else {

            $canactivate = false;
            if (has_capability('block/timetracker:activateworkers', $this->context)) 
                $canactivate = true;

            foreach ($workers as $worker){ $mform->addElement('html','<tr><td>'); if($worker->active){
                    if($canactivate){
                        $mform->addElement('checkbox', 'activeid['.$worker->id.']','','',array('checked="checked"'));
						$mform->addHelpButton('block_timetracker_activeid['.$worker->id.']','activeid['.$worker->id.']','block_timetracker');
                    } else {
                        $mform->addElement('checkbox', 'activeid['.$worker->id.']','','',array('checked="checked"','disabled="disabled"'));
                    }
                } else {
                    if($canactivate){
                        $mform->addElement('checkbox', 'activeid['.$worker->id.']');
                    } else {
                        $mform->addElement('checkbox', 'activeid['.$worker->id.']', '','',array('disabled="disabled"'));
                    }
                }
    
                $row='</td>';
                $row.='<td>'.$worker->lastname.'</td>';
                $row.='<td>'.$worker->firstname.'</td>';
                $row.='<td>'.$worker->email.'</td>';

                $row.='<td>';

                $lastworkunit =
                    $DB->get_records('block_timetracker_workunit',array('userid'=>$worker->id),'timeout DESC','*',0,1);
                if(!$lastworkunit){
                    $row .='None';
                } else {
                    //print_object($lastworkunit);
                    foreach ($lastworkunit as $u){
                        $elapsed = format_elapsed_time($u->timeout - $u->timein);
                        $row .='<b>Time in: </b>'.
                            userdate($u->timein, get_string('datetimeformat','block_timetracker')).'<br /><b>Time out: </b>'.
                            userdate($u->timeout,
                            get_string('datetimeformat','block_timetracker')).'<br /><b>Elapsed: </b>'.
                            $elapsed;
                    }
                }

                $row.='</td>';


                $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 
                $paramstring = "?id=$COURSE->id&userid=$worker->id&mdluserid=$worker->mdluserid&sesskey=".sesskey();
    
                $editurl = new moodle_url($baseurl.'/updateworkerinfo.php'.$paramstring);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    
                $reportsurl = new moodle_url($baseurl.'/reports.php'.$paramstring);
                $reportsaction=$OUTPUT->action_icon($reportsurl, new pix_icon('t/calendar', 'Reports'));
    
                $deleteurl = new moodle_url($baseurl.'/deleteworker.php'.$paramstring);
                $deleteicon = new pix_icon('t/delete', get_string('delete'));
                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action('Are you sure you want to delete this worker and all this worker\'s work units?'));
    
                $row .= '<td style="text-align: center">'.$editaction . ' ' . $reportsaction. ' '.$deleteaction.'</td>';
    
    
                $row.='</tr>';
                $mform->addElement('html',$row);
				$mform->addHelpButton('block_timetracker_$row','$row','block_timetracker');
    
                $mform->addElement('hidden','workerid['.$worker->id.']', $worker->id);
            }
    
            $mform->addElement('html','</table>');
    
            $mform->addElement('hidden','id', $COURSE->id);
    
            $this->add_action_buttons(true, 'Save Changes');
    
            }
    }

}
