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
        global $CFG, $USER, $DB, $COURSE;


        $mform =& $this->_form; // Don't forget the underscore! 

        $mform->addElement('header', 'general', get_string('manageworkers','block_timetracker')); 


        if(!$workers = $DB->get_records('block_timetracker_workerinfo',array(),'lastname ASC')){
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
             </tr>');

        $canactivate = true;
        if (has_capability('block/timetracker:activateworkers', $this->context)) 
            $canactivate = false;

        foreach ($workers as $worker){
            $mform->addElement('html','<tr><td>'); 
            if($worker->active){
                if($canactivate){
                    $mform->addElement('checkbox', 'activeid['.$worker->id.']','','',array('checked="checked"'));
                } else {
                    $mform->addElement('checkbox', 'activeid['.$worker->id.']','','',array('checked="checked"','disabled="disabled"'));
                }
            } else {
                if($canactivate){
                    $mform->addElement('checkbox', 'activeid['.$worker->id.']');
                } else {
                    $mform->addElement('checkbox', 'activeid['.$worker->id.']', array('disabled="disabled"'));
                }
            }

            $row='</td>';
            $row.='<td>'.$worker->lastname.'</td>';
            $row.='<td>'.$worker->firstname.'</td>';
            $row.='<td>'.$worker->email.'</td>';


            $row.='</tr>';
            $mform->addElement('html',$row);

            $mform->addElement('hidden','workerid['.$worker->id.']', $worker->id);
        }

        $mform->addElement('html','</table>');

        $mform->addElement('hidden','cid', $COURSE->id);

        $this->add_action_buttons(true, 'Save Changes');

    }

}
        
