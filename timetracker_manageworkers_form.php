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

class timetracker_manageworkers_form  extends moodleform {

    function timetracker_manageworkers_form($context){
        $this->context = $context;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $COURSE, $OUTPUT;


        $mform =& $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'general', get_string('manageworkers','block_timetracker')); 


        if(!$workers = $DB->get_records('block_timetracker_workerinfo',array(),'active DESC, lastname ASC')){
            print_error('noworkers','block_timetracker',$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
        }

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
                <th>Action</th>
             </tr>');

        $canactivate = false;
        if (has_capability('block/timetracker:activateworkers', $this->context)) 
            $canactivate = true;

        foreach ($workers as $worker){ $mform->addElement('html','<tr><td>'); if($worker->active){
                if($canactivate){
                    $mform->addElement('checkbox', 'activeid['.$worker->id.']','','',array('checked="checked"'));
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

            $paramstring = "?id=$COURSE->id&userid=$worker->id&sesskey=".sesskey();

            /*
            $row.='<td>
                [<a href="reports.php'.$paramstring.'">Reports</a>]
                [<a href="updateworkerinfo.php'.$paramstring.'">Update</a>]
                [<a href="delete.php'.$paramstring.'">Delete</a>]
            </td>';
            */
            $editurl = new moodle_url('/blocks/timetracker/updateworkerinfo.php'.$paramstring);
            $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

            $deleteurl = new moodle_url('/blocks/timetracker/deleteworker.php'.$paramstring);
            $deleteicon = new pix_icon('t/delete', get_string('delete'));
            $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action('Are you sure you want to delete this worker and all this worker\'s work units?'));

            $row .= '<td>'.$editaction . ' ' . $deleteaction.'</td>';


            $row.='</tr>';
            $mform->addElement('html',$row);

            $mform->addElement('hidden','workerid['.$worker->id.']', $worker->id);
        }

        $mform->addElement('html','</table>');

        $mform->addElement('hidden','cid', $COURSE->id);

        $this->add_action_buttons(true, 'Save Changes');

    }

}
        
