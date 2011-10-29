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
 * This page will 'do magic' when a supervisor approves an error alert regarding a pending work unit.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once('timetracker_changealert_form.php');

global $CFG, $COURSE, $USER;

require_login();

$alertid = required_param('alertid', PARAM_INTEGER); // Worker id
$action = required_param('action', PARAM_ALPHA);
$alertunit = $DB->get_record('block_timetracker_alertunits', array('id'=>$alertid));

if(!$alertunit){
    //TODO Fix this to go to a pretty error page stating that the unit no longer needs action
    echo $OUTPUT->header();
    print_error('Alert unit no long exists');
}
$urlparams['id'] = $alertunit->courseid;
$urlparams['action'] = $action;
$urlparams['alertid'] = $alertid;

$courseid = $alertunit->courseid;

$indexparams['id'] = $courseid; 
$managealerts = new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/managealerts.php', $indexparams);
$alertaction = new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/alertaction.php', $urlparams);

/*
if(isset($_SERVER['HTTP_REFERER'])){
    $nextpage = $_SERVER['HTTP_REFERER'];
} else {
    $nextpage = $managealerts;
}
*/
$nextpage = $managealerts;

$course = $DB->get_record('course', array('id' => $alertunit->courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$PAGE->set_url($alertaction);
$PAGE->set_pagelayout('base');
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

if(!$canmanage){
    print_error('notpermissible','block_timetracker');
}

//********** TODO **********//
//Add check - if request has already been approved, display 'alreadyapproved' string.
//"This work unit has already been approved by {$a} (name and time)?

$worker = $DB->get_record('block_timetracker_workerinfo',array('id'=>$alertunit->userid));

if (!$canmanage && $USER->id != $worker->mdluserid){
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {

    $payrate = 'SELECT payrate FROM '.$CFG->prefix.'block_timetracker_workerinfo WHERE id='.
        $alertunit->userid .';';
    $lastedited = time();
    $lasteditedby = $USER->id; //Supervisor's id
    
    $alertcom = $DB->get_records('block_timetracker_alert_com', array('alertid'=>$alertid));
       
    if($action == 'approve'){
        if($alertunit->todelete == 1){
            $DB->delete_records('block_timetracker_alertunits', array('id'=>$alertid));
        } else {
            //Add to 'workunit' table and delete from 'alertunits' and notify everyone
            $alertunit->lastedited = time();
            $alertunit->lasteditedby = $USER->id;
            //$result = $DB->insert_record('block_timetracker_workunit', $alertunit);

            $conflicts = find_conflicts($alertunit->timein, $alertunit->timeout,
                $alertunit->userid);  
            if(sizeof($conflicts) == 0){

                $result = add_unit($alertunit);
        
                if(!$result){
                    print_error('Something bad happened :(');       
                }
            } else {
                redirect($managealerts, 'Cannot approve this unit; it conflicts with
                    an existing work unit!', 3);
            }
        }
        
        $from = $USER; 

        // Email worker and any other supervisor(s) that the work unit has been approved
       
        $subject = get_string('approvedsubject','block_timetracker', $worker->firstname.'
        '.$worker->lastname.' in '.$course->shortname);
        
        //********** PLAIN TEXT **********//
        $messagetext = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('amessage2','block_timetracker');
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('emessage2','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage4','block_timetracker', 
            userdate($alertunit->origtimeout));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagetext .= get_string('br2','block_timetracker');
        $messagetext .= get_string('approveddata','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        
        if($alertunit->todelete == 1){
            $messagetext .= get_string('unitdeleted','block_timetracker');
            $messagetext .= get_string('br1','block_timetracker');
            $messagetext .= get_string('emessage3','block_timetracker',
                userdate($alertunit->timein));
            $messagetext .= get_string('br1','block_timetracker');
            $messagetext .= get_string('emessage4','block_timetracker', 
                userdate($alertunit->timeout));
            $messagetext .= get_string('br1','block_timetracker'); 
            $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));
        }

        //********** HTML **********//
        $messagehtml = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('amessage2','block_timetracker');
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('emessage2','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage4','block_timetracker', 
            userdate($alertunit->origtimeout));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagehtml .= get_string('br2','block_timetracker');
        $messagehtml .= get_string('approveddata','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        
        if($alertunit->todelete == 1){
            $messagehtml .= get_string('unitdeleted','block_timetracker');
        } else {
            $messagehtml .= get_string('emessage3','block_timetracker',
                userdate($alertunit->timein));
            $messagehtml .= get_string('br1','block_timetracker');
            $messagehtml .= get_string('emessage4','block_timetracker', 
                userdate($alertunit->timeout));
            $messagehtml .= get_string('br1','block_timetracker'); 
            $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));
        }
       
        foreach ($alertcom as $alertcomentry){
            if($USER->id != $alertcomentry->mdluserid){
                // Get email address from each in moodle table
                $emailto = $DB->get_record('user', array('id'=>$alertcomentry->mdluserid));
                if($emailto){
                    email_to_user($emailto, $from, $subject, $messagetext, $messagehtml);
                }
            } 
            // Remove record(s) from the 'alert_com' table
            $DB->delete_records('block_timetracker_alert_com', 
                array('alertid'=>$alertcomentry->alertid,
                'mdluserid'=>$alertcomentry->mdluserid));
        }
        $DB->delete_records('block_timetracker_alertunits', array('id'=>$alertid));

        $status = get_string('approvesuccess','block_timetracker');
        redirect($nextpage, $status, 2);
    } else if ($action == 'deny'){
        $DB->delete_records('block_timetracker_alertunits', array('id'=>$alertunit->id));
        if($alertunit->origtimeout == 0){
            /* 
             * Set the timein and timeout as the same value so that this will ensure the unit
             * doesn't show on the monthly report and add to the 'workunit' table so that data 
             * isn't lost.
            */
            unset($alertunit->id);
            $alertunit->timein = $alertunit->origtimein;
            $alertunit->timeout = $alertunit->origtimein;

            //$DB->insert_record('block_timetracker_workunit', $alertunit);

            add_unit($alertunit);
        } else {

            // Add work unit back to 'workunit' table
            unset($alertunit->id);
            $alertunit->timein = $alertunit->origtimein;
            $alertunit->timeout = $alertunit->origtimeout;
            //$DB->insert_record('block_timetracker_workunit', $alertunit);

            $conflicts = find_conflicts($alertunit->timein, $alertunit->timeout,
                $alertunit->userid);  
            if(sizeof($conflicts) == 0){
                $result = add_unit($alertunit);
                if(!$result){
                    print_error('Something bad happened :(');       
                }
            } else {
                redirect($managealerts, 'Cannot approve this unit; it conflicts with
                    an existing work unit!', 3);
            }
        }

        // Email worker and any other supervisor(s) that the work unit has been denied
        $from = $USER; 
        $subject = get_string('denysubject','block_timetracker', $worker->firstname.'
        '.$worker->lastname.' in '.$course->shortname);
        
        //********** PLAIN TEXT **********//
        $messagetext = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('dmessage1','block_timetracker');
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('emessage2','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage4','block_timetracker', 
            userdate($alertunit->origtimeout));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagetext .= get_string('br2','block_timetracker');
        $messagetext .= get_string('approveddata','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        
        if($alertunit->todelete == 1){
            $messagetext .= get_string('unitdeleted','block_timetracker');
        } else {
            $messagetext .= get_string('emessage3','block_timetracker',
                userdate($alertunit->timein));
            $messagetext .= get_string('br1','block_timetracker');
            $messagetext .= get_string('emessage4','block_timetracker', 
                userdate($alertunit->timeout));
            $messagetext .= get_string('br1','block_timetracker'); 
            $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));
        }

        //********** HTML **********//
        $messagehtml = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('dmessage1','block_timetracker');
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('emessage2','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage4','block_timetracker',     
            userdate($alertunit->origtimeout));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagehtml .= get_string('br2','block_timetracker');
        $messagehtml .= get_string('approveddata','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        
        if($alertunit->todelete == 1){
            $messagehtml .= get_string('unitdeleted','block_timetracker');
        } else {
            $messagehtml .= get_string('emessage3','block_timetracker',
            userdate($alertunit->timein));
            $messagehtml .= get_string('br1','block_timetracker');
            $messagehtml .= get_string('emessage4','block_timetracker', 
                userdate($alertunit->timeout));
            $messagehtml .= get_string('br1','block_timetracker'); 
            $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));
        }
        
        foreach ($alertcom as $alertcomentry){
            if($USER->id != $alertcomentry->mdluserid){
                //print("USER id is: $USER->id <br />");
                //print("user->id is $alertcomentry->mdluserid <br />");
                // Get email from each in moodle tablecorrectioncorrectioo
                $emailto = $DB->get_record('user', array('id'=>$alertcomentry->mdluserid));
                if($emailto){
                    email_to_user($emailto, $from, $subject, $messagetext, $messagehtml);
                }
            } 
            // Remove record(s) from the 'alert_com' table
            $DB->delete_records('block_timetracker_alert_com', 
                array('alertid'=>$alertcomentry->alertid,
                'mdluserid'=>$alertcomentry->mdluserid));
        }
        $status = get_string('denysuccess','block_timetracker');
        redirect($nextpage, $status, 2);
    } else if ($action == 'delete'){ 
        //TODO Need to fix these blocks so we can reduce redundant code
    
        // Email worker and any other supervisor(s) that the alert has been deleted
        $from = $USER; 
        $subject = get_string('deletedsubject','block_timetracker', $worker->firstname.'
            '.$worker->lastname.' in '.$course->shortname);
        
        //********** PLAIN TEXT **********//
        $messagetext = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('deletemessage1','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage4','block_timetracker', 
            userdate($alertunit->origtimeout));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagetext .= get_string('br2','block_timetracker');

        //********** HTML **********//
        $messagehtml = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('deletemessage1','block_timetracker');
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('emessage3','block_timetracker', 
            userdate($alertunit->origtimein));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage4','block_timetracker', 
            userdate($alertunit->origtimeout));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagehtml .= get_string('br2','block_timetracker');
        
        foreach ($alertcom as $alertcomentry){
            if($USER->id != $alertcomentry->mdluserid){
                $emailto = $DB->get_record('user', array('id'=>$alertcomentry->mdluserid));
                if($emailto){
                    email_to_user($emailto, $from, $subject, $messagetext, $messagehtml);
                }
            } 

            // Remove record(s) from the 'alert_com' table
            $DB->delete_records('block_timetracker_alert_com', 
                array('alertid'=>$alertcomentry->alertid,
                'mdluserid'=>$alertcomentry->mdluserid));
        }
        //remove the alertunits entry
        $result = $DB->delete_records('block_timetracker_alertunits',
            array('id'=>$alertunit->id));

        if($result){
            $status = get_string('alertdeletesuccess','block_timetracker', 
                $worker->firstname.' '.$worker->lastname.' in '.$course->shortname);
        } else {
            $status = get_string('alertdeletefailure','block_timetracker', 
                $worker->firstname.' '.$worker->lastname.' in '.$course->shortname);
        }

        redirect($nextpage, $status, 2);
    
    } else {
        // Supervisor wishes to change data in the error alert
        $mform = new timetracker_changealert_form($context, $alertid);

        if ($mform->is_cancelled()){ 
            //user clicked cancel
            redirect($nextpage);

        } else if ($formdata=$mform->get_data()){
            //Form is submitted, add the unit to the 'workunit' database; 
            //email worker and any other
            //supervisors that the alert has been completed.
            if(isset($formdata->deleteunit)){
                $DB->delete_records('block_timetracker_alertunits', array('id'=>$alertid));
            } else {
                $formdata->lastedited = time();

                $insertok = add_unit($formdata);
                //$insertok = $DB->insert_record('block_timetracker_workunit', $formdata);
                if(!$insertok) print_error('Error updating new work unit.');

                $from = $USER; 

                // Email worker and any other supervisor(s) that the work unit 
                //has been approved
       
                $subject = get_string('approvedsubject','block_timetracker', 
                    $worker->firstname.'
                    '.$worker->lastname.' in '.$course->shortname);
            
                //********** PLAIN TEXT **********//
                $messagetext = get_string('amessage1','block_timetracker', $USER->firstname.'
                    '.$USER->lastname); 
                $messagetext .= get_string('br2','block_timetracker'); 
                $messagetext .= get_string('amessage2','block_timetracker');
                $messagetext .= get_string('br2','block_timetracker'); 
                $messagetext .= get_string('emessagechange','block_timetracker');
                $messagetext .= get_string('br1','block_timetracker'); 
                $messagetext .= get_string('emessage3','block_timetracker', 
                    userdate($alertunit->origtimein));
                $messagetext .= get_string('br1','block_timetracker'); 
                $messagetext .= get_string('emessage4','block_timetracker', 
                    userdate($alertunit->origtimeout));
                $messagetext .= get_string('br1','block_timetracker'); 
                $messagetext .= get_string('emessageduration','block_timetracker', 
                    format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
                $messagetext .= get_string('br2','block_timetracker');
                $messagetext .= get_string('approveddata','block_timetracker');
                $messagetext .= get_string('br1','block_timetracker'); 
                
                if($alertunit->todelete == 1){
                    $messagetext .= get_string('unitdeleted','block_timetracker');
                } else {
                    $messagetext .= get_string('emessage3','block_timetracker',
                        userdate($alertunit->timein));
                    $messagetext .= get_string('br1','block_timetracker');
                    $messagetext .= get_string('emessage4','block_timetracker', 
                        userdate($alertunit->timeout));
                    $messagetext .= get_string('br1','block_timetracker'); 
                    $messagetext .= get_string('emessageduration','block_timetracker', 
                    format_elapsed_time($alertunit->timeout - $alertunit->timein));
                }
        
                //********** HTML **********//
                $messagehtml = get_string('amessage1','block_timetracker', $USER->firstname.'
                    '.$USER->lastname); 
                $messagehtml .= get_string('br2','block_timetracker'); 
                $messagehtml .= get_string('amessage2','block_timetracker');
                $messagehtml .= get_string('br2','block_timetracker'); 
                $messagehtml .= get_string('emessage2','block_timetracker');
                $messagehtml .= get_string('br1','block_timetracker'); 
                $messagehtml .= get_string('emessage3','block_timetracker', 
                    userdate($alertunit->origtimein));
                $messagehtml .= get_string('br1','block_timetracker'); 
                $messagehtml .= get_string('emessage4','block_timetracker', 
                    userdate($alertunit->origtimeout));
                $messagehtml .= get_string('br1','block_timetracker'); 
                $messagehtml .= get_string('emessageduration','block_timetracker', 
                    format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
                $messagehtml .= get_string('br2','block_timetracker');
                $messagehtml .= get_string('approveddata','block_timetracker');
                $messagehtml .= get_string('br1','block_timetracker'); 
                
                if($alertunit->todelete == 1){
                    $messagehtml .= get_string('unitdeleted','block_timetracker');
                } else {
                    $messagehtml .= get_string('emessage3','block_timetracker',
                        userdate($alertunit->timein));
                    $messagehtml .= get_string('br1','block_timetracker');
                    $messagehtml .= get_string('emessage4','block_timetracker', 
                        userdate($alertunit->timeout));
                    $messagehtml .= get_string('br1','block_timetracker'); 
                    $messagehtml .= get_string('emessageduration','block_timetracker', 
                    format_elapsed_time($alertunit->timeout - $alertunit->timein));
                }
            
                foreach ($alertcom as $alertcomentry){
                    if($USER->id != $alertcomentry->mdluserid){
                        // Get email address from each in moodle table
                        $emailto = $DB->get_record('user', 
                            array('id'=>$alertcomentry->mdluserid));
                        if($emailto){
                            email_to_user($emailto, $from, $subject, 
                                $messagetext, $messagehtml);
                        }
                    } 
                    // Remove record(s) from the 'alert_com' table
                    $DB->delete_records('block_timetracker_alert_com', 
                        array('alertid'=>$alertcomentry->alertid,
                        'mdluserid'=>$alertcomentry->mdluserid));
                }

                // Remove record(s) from the 'alert_com' table
                $DB->delete_records('block_timetracker_alertunits', 
                    array('id'=>$alertunit->id));
            }
            redirect($nextpage);
        } else {
            //form is shown for the first time
            echo $OUTPUT->header();
            $maintabs = get_tabs($urlparams, $canmanage, $courseid);
            $tabs = array($maintabs);
            print_tabs($tabs, 'alerts');
            $mform->display();
            echo $OUTPUT->footer();
        }
    
    }
}

?>
