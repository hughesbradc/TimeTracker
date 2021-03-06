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
        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        $canview = false;
        if (has_capability('block/timetracker:viewonly', $this->context)) {
            $canview = true;
        }

        if($canview && !$canmanage){
            $urlparams['id'] = $COURSE->id;
            $nextpage = new moodle_url(
                $CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);
            redirect($nextpage, 
                'You do not have permission to view alerts for this course. <br />
                Redirecting you now.', 2);
        }

        $mform->addElement('header', 'general', 
            get_string('managealerts','block_timetracker')); 
		$mform->addHelpButton('general','managealerts','block_timetracker');


        $strname = get_string('workername', 'block_timetracker');
        $strprev = get_string('previous', 'block_timetracker');
        $strproposed = get_string('proposed', 'block_timetracker');
        $strmsg = get_string('message', 'block_timetracker');

        if(!has_course_alerts($COURSE->id)){
            $mform->addElement('html','<div style="text-align:center">');
            $mform->addElement('html',get_string('noalerts','block_timetracker'));
            $mform->addElement('html','</div>'); 
            return;
        } else {

            $mform->addElement('html', 
            '<table align="center" border="1" cellspacing="10px" '.
            'cellpadding="5px" width="95%">');
        
            $tblheaders=
                '<tr>
                    <td><span style="font-weight: bold">'.$strname.'</span></td>
                    <td><span style="font-weight: bold">'.$strprev.'</span></td>
                    <td><span style="font-weight: bold">'.$strproposed.'</span></td>
                    <td><span style="font-weight: bold">'.$strmsg.'</span></td>';
            if($canmanage)
                $tblheaders .= '<td style="text-align: center">'.
                    '<span style="font-weight: bold">'.
                    get_string('action').'</span></td>';
            $tblheaders .= '</tr>';

            $mform->addElement('html',$tblheaders);


            $alertlinks=get_course_alert_links($COURSE->id);
            //print_object($alertlinks);
    
            if($canmanage){
                $alerts = $DB->get_records('block_timetracker_alertunits', 
                    array('courseid'=>$COURSE->id), 'alerttime');
            } else {
                $ttuserid = $DB->get_field('block_timetracker_workerinfo',
                    'id', array('mdluserid'=>$USER->id,'courseid'=>$COURSE->id));
                if(!$ttuserid) print_error('Error obtaining mdluserid from workerinfo for '.
                    $USER->id);
                $alerts = $DB->get_records('block_timetracker_alertunits',
                    array('courseid'=>$COURSE->id,'userid'=>$ttuserid));
            }
        }    

        foreach ($alerts as $alert){ 
            $worker = $DB->get_record('block_timetracker_workerinfo',
                array('id'=>$alert->userid));

            $mform->addElement('html','<tr>'); 
            $row ='<td>'.$worker->lastname.', '.$worker->firstname .
                '<br />Submitted: '.
                userdate($alert->alerttime, get_string('datetimeformat',
                'block_timetracker')).
                '</td>';
            $row.='<td>In: '.userdate($alert->origtimein, 
                get_string('datetimeformat','block_timetracker'));

            if($alert->origtimeout > 0){
                $row.='<br />Out: '.userdate($alert->origtimeout, 
                    get_string('datetimeformat','block_timetracker'));
                $row.='<br />Elapsed: '.format_elapsed_time(
                    $alert->origtimeout - $alert->origtimein, $alert->courseid);
            } else {
                $row.= '';
            }
            $row .='</td>';

            if($alert->todelete == 0){
                $row.='<td>In: '.userdate($alert->timein, 
                    get_string('datetimeformat','block_timetracker'));

                $row.='<br />Out: '.userdate($alert->timeout, 
                    get_string('datetimeformat','block_timetracker'));

                $row.='<br />Elapsed: '.format_elapsed_time(
                    $alert->timeout - $alert->timein, $alert->courseid);

                $row.='</td>';
            } else {
                $row.='<td><span style="color: red">User requests removal</span></td>';
            }

            $row.='<td>'.nl2br($alert->message).'</td>';
    
            if($canmanage){

                $editurl = new moodle_url($alertlinks[$worker->id][$alert->id]['change']);
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('clock_edit', 
                    'Edit proposed work unit','block_timetracker'));
    
                $approveurl = new moodle_url($alertlinks[$worker->id][$alert->id]['approve']);
                $checkicon = new pix_icon('approve',
                    'Approve the proposed work unit','block_timetracker');
                if($alert->todelete){
                    $approveaction=$OUTPUT->action_icon($approveurl, $checkicon,
                    new confirm_action('Are you sure you want to delete this work unit
                    as requested by the worker?'));
                } else {
                    $approveaction=$OUTPUT->action_icon($approveurl, $checkicon);
                }

                $deleteurl = new moodle_url($alertlinks[$worker->id][$alert->id]['delete']);
                $deleteicon = new pix_icon('delete',
                    'Delete this alert', 'block_timetracker');
                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action(
                    'Are you sure you want to delete this alert?'));
        
                $denyurl = new moodle_url($alertlinks[$worker->id][$alert->id]['deny']);
                $denyicon = new pix_icon('clock_delete',
                    'Deny and restore original work unit','block_timetracker');

                $denyaction = $OUTPUT->action_icon(
                    $denyurl, $denyicon, 
                    new confirm_action(
                    'Are you sure you want to deny this alert unit?<br />The work unit 
                    will be re-inserted into the worker\'s record as it originally
                    appeared.'));

                $row .= '<td style="text-align: center">'.
                    $approveaction . ' ' . $deleteaction. ' '.
                    $editaction. ' '.$denyaction.'</td>';
            }
    
            $row.='</tr>';
            $mform->addElement('html',$row);
    
        }
    
        $mform->addElement('html','</table>');
    
        //$this->add_action_buttons(true, 'Save Changes');

        if($canmanage){
            $mform->addElement('header','general',
                'Alert Action Legend');
            $legend ='
                <img src="'.$CFG->wwwroot.'/blocks/timetracker/pix/approve.png" />
                Approve the proposed work unit <br />
                <img src="'.$CFG->wwwroot.'/blocks/timetracker/pix/delete.png" />
                Delete the alert and the original/proposed work units <br />
                <img src="'.$CFG->wwwroot.'/blocks/timetracker/pix/clock_edit.png" />
                Edit the proposed work unit before approval<br />
                <img src="'.$CFG->wwwroot.'/blocks/timetracker/pix/clock_delete.png" />
                Deny the proposed work unit and re-add the original work unit';
            $mform->addElement('html',$legend);
        }
    
    }

}
