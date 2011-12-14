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

class timetracker_changealert_form extends moodleform {
    function timetracker_changealert_form($context, $alertid){
        $this->context = $context;
        $this->alertid = $alertid;
        parent::__construct();
    }

    function definition(){
        global $CFG, $USER, $DB, $COURSE;

        $mform =& $this->_form; // Don't forget the underscore!

        $canmanage = false;
        if(has_capability('block/timetracker:manageworkers',$this->context)){
            $canmanage = true;
        }
    
        $alertunit = $DB->get_record('block_timetracker_alertunits', 
            array('id'=>$this->alertid));
        $userinfo = $DB->get_record('block_timetracker_workerinfo', 
            array('id'=>$alertunit->userid));
            
        $index  = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',
            array('id'=>$alertunit->courseid,'userid'=>$alertunit->userid));
        if(get_referer(false)){
            $nextpage = get_referer(false);
        } else {
            $nextpage = $index;
        }
        if(!$canmanage && $USER->id != $userinfo->mdluserid){
            redirect($nextpage, 'You do not have permission to change this alert.', 1);
        } else {
        
            $mform->addElement('hidden', 'userid', $alertunit->userid);
            $mform->addElement('hidden', 'courseid', $alertunit->courseid);
            $mform->addElement('hidden', 'payrate', $alertunit->payrate);
            $mform->addElement('hidden', 'lasteditedby', $USER->id);
            $mform->addElement('hidden', 'alertid', $alertunit->id);
            $mform->addElement('hidden', 'action', $alertunit->id);
            $mform->addElement('header', 'general',  get_string('changealert',
                'block_timetracker', $userinfo->firstname.' '.$userinfo->lastname));
            $mform->addElement('html', get_string('emessage2','block_timetracker'));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessage3','block_timetracker', 
                userdate($alertunit->origtimein)));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessage4','block_timetracker',
                userdate($alertunit->origtimeout)));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessageduration','block_timetracker',
                format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein,
                $alertunit->courseid)));
            $mform->addElement('html', get_string('br2','block_timetracker'));
            $mform->addElement('html', get_string('emessage5','block_timetracker'));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessage3','block_timetracker', 
                userdate($alertunit->timein)));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessage4','block_timetracker',
                userdate($alertunit->timeout)));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessageduration','block_timetracker',
                format_elapsed_time($alertunit->timeout - $alertunit->timein,
                $alertunit->courseid)));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('html', get_string('emessage6','block_timetracker',
                $alertunit->message));
            $mform->addElement('html', get_string('br2','block_timetracker'));
            $mform->addElement('html', get_string('changeto','block_timetracker'));
            $mform->addElement('html', get_string('br1','block_timetracker'));
            $mform->addElement('date_time_selector','timein',
                get_string('timeinerror','block_timetracker'));
            $mform->setDefault('timein',$alertunit->timein);
            $mform->addHelpButton('timein','timein','block_timetracker');
            $mform->addElement('date_time_selector','timeout',
                get_string('timeouterror','block_timetracker'));
            $mform->setDefault('timeout',$alertunit->timeout);
            $mform->addHelpButton('timeout','timeout','block_timetracker');
            $mform->addElement('checkbox', 'deleteunit', get_string('deleteunit','block_timetracker'));
            $mform->addHelpButton('deleteunit', 'deleteunit', 'block_timetracker');
            $this->add_action_buttons(true, get_string('savebutton','block_timetracker'));
        }
    }

    function validation ($data){
        global $OUTPUT;
        $errors = array();

        if($data['timeout'] > time()){ 
            $errors['timeout'] = 'Time cannot be set in the future.';
        } else if($data['timein'] > $data['timeout']){
            $errors['timein'] = 'Time out cannot be set before time in.';
        } else if(!has_capability('block/timetracker:manageoldunits', $this->context) && 
            expired($data['timein'])){
            $errors['timein'] = 'You are not authorized to add work units this far in the
            past. See an administrator for assistance';
        } else {

            $conflicts = find_conflicts($data['timein'],$data['timeout'],$data['userid']);
            if(sizeof($conflicts) > 0){
                $errormsg = 'Work unit conflicts with existing unit(s):<br />';
                $errormsg .= '<table>';
                foreach($conflicts as $conflict){
                    $errormsg .= '<tr>';
                    $editaction = $OUTPUT->action_icon($conflict->editlink, new
                        pix_icon('clock_edit', get_string('edit'),'block_timetracker'));
    
                    $deleteaction = $OUTPUT->action_icon(
                        $conflict->deletelink, new pix_icon('clock_delete',
                        get_string('delete'), 'block_timetracker'),
                        new confirm_action('Are you sure you want to delete this '.
                        ' conflicting work unit?'));
    
    
                    $errormsg .= '<td>'.$conflict->display.'</td><td>';
                    /*
                    if($conflict->editlink != '#') //not a pending clock-in
                        $errormsg .= ' '.$editaction;
    
                    $errormsg .= ' '.$deleteaction.'</td></tr>';
                    */
                }
                $errormsg .= '</table>';
                $errors['timein'] = $errormsg;
            }
        }
        return $errors;
    }
}
?>
