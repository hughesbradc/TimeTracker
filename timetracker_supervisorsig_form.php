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
 * This form will allow the supervisor to batch sign timesheets electronically.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once("$CFG->libdir/formslib.php");
require_once('lib.php');

class timetracker_supervisorsig_form extends moodleform {
   function timetracker_supervisorsig_form($courseid){
       $this->courseid = $courseid;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE, $USER, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('header','general',
            get_string('signheader','block_timetracker'));
        
        $mform->addElement('hidden','id',$this->courseid);

        $timesheets = $DB->get_records('block_timetracker_timesheet',
            array('courseid'=>$this->courseid, 'supervisorsignature'=>0));
        
        if(!$timesheets){
            $mform->addElement('html',get_string('notstosign','block_timetracker'));
        } else {
            $mform->addElement('html','<table align="center" border="1" cellspacing="10px"
                cellpadding="5px width="75%">');
            $mform->addElement('html','<tr>
                    <td style="font-weight: bold; text-align:center">Select</td>
                    <td style="font-weight: bold; text-align:center">Worker Name</td>
                    <td style="font-weight: bold; text-align:center">Total Hours</td>
                    <td style="font-weight: bold; text-align:center">Total Pay</td>
                    <td style="font-weight: bold; text-align:center">Actions</td>
                </tr>');

            foreach ($timesheets as $timesheet){
                $mform->addElement('html','<tr><td style="text-align: center">');
                $mform->addElement('advcheckbox', 'signid['.$timesheet->id.']','',
                    null, array('group' => 1));
                $mform->addElement('html','</td><td>');
                
                $worker = $DB->get_record('block_timetracker_workerinfo',
                    array('id'=>$timesheet->userid));
                $mform->addElement('html',$worker->firstname .' '.$worker->lastname);
                $mform->addElement('html','</td><td>');
    
                $hours = 0;
                $pay = 0;
                $hours += $timesheet->reghours;
                $hours += $timesheet->othours;
                $pay += $timesheet->regpay;
                $pay += $timesheet->otpay;
    
                $mform->addElement('html',number_format(round($hours,2),2));
                $mform->addElement('html','</td><td>');
                $mform->addElement('html',number_format(round($pay,2),2));
                $mform->addElement('html','</td><td style="text-align: center">');
                
                $viewparams['id'] = $this->courseid;
                $viewparams['userid'] = $worker->id;
                $viewparams['timesheetid'] = $timesheet->id;
                $viewurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheet_fromid.php',
                    $viewparams);
                $viewaction = $OUTPUT->action_icon($viewurl, new pix_icon('date','View Timesheet',
                    'block_timetracker'));
                
                $editsql =$DB->get_records('block_timetracker_workunit',
                    array('timesheetid'=>$timesheet->id),'timein ASC');
                
                $first = reset($editsql);
                if(sizeof($editsql) > 1){
                    $last = end($editsql);
                } else {
                    $last = $first;
                }
                $editparams['timesheetid'] = $timesheet->id;
                //$editparams['repstart'] = $first->timein;
                //$editparams['repend'] = $last->timeout;
                $editurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheetreject.php', 
                    $editparams);
                $editicon = new pix_icon('date_edit', get_string('edit'),'block_timetracker');
                $editaction = $OUTPUT->action_icon($editurl, $editicon,
                    new confirm_action(get_string('editwarning','block_timetracker')));
                $mform->addElement('html',$viewaction);
                $mform->addElement('html',' ');
                $mform->addElement('html',$editaction);
                $mform->addElement('html','</tr>');
            }
        $mform->addElement('html','<tr><td align="center" colspan="5">');
        $this->add_checkbox_controller(1);
        $mform->addElement('html','</td></tr>');
        $mform->addElement('html','</table>');
        $mform->addElement('html', get_string('supervisorstatement','block_timetracker'));
        $mform->addElement('checkbox','supervisorsig',get_string('clicktosign','block_timetracker'));

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit',
            'signbutton',get_string('signbuttonsup','block_timetracker'));
        $mform->addGroup($buttonarray, 'buttonar','',array(' '), false);
        
        $mform->disabledIf('buttonar','supervisorsig');
    
        }
        
    }

    function validation($data){
    }
}
?>
