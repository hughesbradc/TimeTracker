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

        //add_enrolled_users($this->context);

        $mform->addElement('header', 'general', get_string('manageworkers',
            'block_timetracker')); 

		$mform->addHelpButton('general','manageworkers','block_timetracker');


        $stractive = get_string('active', 'block_timetracker');
        $strfirstname = get_string('firstname', 'block_timetracker');
        $strlastname = get_string('lastname', 'block_timetracker');
        $stremail = get_string('email', 'block_timetracker');

        $me = new moodle_url(qualified_me());
        $me->params(array('reload'=>true));


        $canview = false;
        if(has_capability('block/timetracker:viewonly', $this->context))
            $canview = true;

        $canmanage = false;
        if(has_capability('block/timetracker:manageworkers', $this->context))
            $canmanage = true;

        if($canmanage){
            $reloadaction = $OUTPUT->action_icon($me, new pix_icon('refresh', 'Refresh worker list',
                'block_timetracker'));

            $mform->addElement('html', $reloadaction.' '.$OUTPUT->action_link($me, 
                'Refresh worker list'));
        }


        $mform->addElement('html', 
            '<table align="center" border="1" cellspacing="10px" '.
            'cellpadding="5px" width="75%">');
        
        $mform->addElement('html',
            '<tr>
                <td style="font-weight: bold">'.$stractive.'</td>
                <td style="font-weight: bold">Worker name</td>
                <td style="font-weight: bold">'.$stremail.'</td>
                <td style="font-weight: bold; text-align: center">'.
                get_string('action').'</td>
             </tr>');


        if(!$workers = $DB->get_records('block_timetracker_workerinfo',
            array('courseid'=>$COURSE->id),'lastname ASC, firstname ASC')){

            $mform->addElement('html',
                '<tr><td colspan="4" style="text-align: center">No workers registered'.
                '</td></tr></table>');

        } else {

            $canactivate = false;
            if (has_capability('block/timetracker:activateworkers', $this->context)) 
                $canactivate = true;


            foreach ($workers as $worker){ 
                $mform->addElement('html','<tr><td>'); 
                if($worker->active){
                    if($canactivate){
                        $mform->addElement('advcheckbox', 'activeid['.$worker->id.']','', 
                            null, array('checked="checked"','group' => 1));
                    } else {
                        $mform->addElement('advcheckbox', 'activeid['.$worker->id.']','', 
                            null, array('checked="checked"','disabled="disabled"',
                            'group' => 1));
                    }
                } else {
                    if($canactivate){
                        $mform->addElement('advcheckbox', 'activeid['.$worker->id.']','', 
                            null, array('group' => 1));
                    } else {
                        $mform->addElement('advcheckbox', 'activeid['.$worker->id.']', '', 
                            null, array('disabled="disabled"', 
                            'group' => 1));
                    }
                }

    
                $row='</td>';
                $row.='<td>'.$worker->lastname.', '.$worker->firstname.'</td>';
                $row.='<td>'.$worker->email.'</td>';

                $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 

                $urlparams['id'] = $COURSE->id;
                $urlparams['userid'] = $worker->id;
                $urlparams['sesskey'] = sesskey();

                $deleteurl = new moodle_url($baseurl.'/deleteworker.php', $urlparams);
                $deleteicon = new pix_icon('user_delete', get_string('delete'),
                    'block_timetracker');

                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action(
                    'Are you sure you want to delete this worker and all this worker\'s'.
                    ' work units?'));
    
                //don't need anymore
                unset($urlparams['sesskey']);
                
                $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',$urlparams);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                    get_string('edit'),'block_timetracker'));

                $reportsurl = new moodle_url($baseurl.'/reports.php', $urlparams);
                $reportsaction=$OUTPUT->action_icon($reportsurl, new pix_icon('report', 
                    'Reports','block_timetracker'));
    
                $adduniturl = new moodle_url($baseurl.'/addunit.php', $urlparams);
                $addunitaction = $OUTPUT->action_icon($adduniturl,
                    new pix_icon('clock_add', 
                    get_string('addentry', 'block_timetracker'), 'block_timetracker'));

                $timesheetsurl = new moodle_url($baseurl.'/viewtimesheets.php', $urlparams);
                $timesheetaction = $OUTPUT->action_icon($timesheetsurl,
                    new pix_icon('date', 'View signed timesheets', 'block_timetracker'));

                $row .= '<td style="text-align: center">';
                
                if($canmanage){
                    $row .= $editaction . ' ' . 
                        //$deleteaction. ' '. //comment out for now
                        $addunitaction.' '.
                        $reportsaction.
                    $timesheetaction;
                } else {
                    $row .= $editaction . ' ' .$reportsaction. ' ' .$timesheetaction;
                    //$row .= '&nbsp;';
                }
                $row .= '</td>';
    
    
                $row.='</tr>';
                $mform->addElement('html',$row);

    
                $mform->addElement('hidden','workerid['.$worker->id.']', $worker->id);
            }

            if($canactivate){
                $this->add_checkbox_controller(1,null,null,1);
            }
    
            $mform->addElement('html','</table>');
    
            $mform->addElement('hidden','id', $COURSE->id);
   
            if($canmanage){
                $this->add_action_buttons(false, 'Save Activation Changes');
            }
            
        }
    }

}
