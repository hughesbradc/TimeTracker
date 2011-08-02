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
 * eapendingapprove.php
 * This page will 'do magic' when a supervisor approves an error alert regarding a pending work unit.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

require_login();

$alertid = required_param('alertid', PARAM_INTEGER); // Worker id
$action = required_param('action', PARAM_ALPHA);
$alertunit = $DB->get_record('block_timetracker_alertunits', array('id'=>$alertid));
if(!$alertunit){
    //TODO Fix this to go to a pretty error page stating that the unit no longer needs action
    print_error('Alert unit no long exists');
}

$courseid = $alertunit->courseid;



$course = $DB->get_record('course', array('id' => $alertunit->courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$urlparams['id'] = $alertunit->courseid;

$nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
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
        /*
        print($alertunit->userid);
        print('<br />');
        print($alertunit->courseid);
        print('<br />');
        print($alertunit->timein);
        print('<br />');
        print($alertunit->timeout);
        print('<br />');
        print($alertunit->todelete);
        print('<br />');
        print($action);
        print('<br />');
        */
    if($action == 'approve'){
        if($alertunit->todelete == 1){
            $DB->delete_record('block_timetracker_alertunits', array('id'=>$alertid));
        } else {
        
            //Add to 'workunit' table and delete from 'alertunits' and notify everyone
            $alertunit->lastedited = time();
            $alertunit->lasteditedby = $USER->id;
            $result = $DB->insert_record('block_timetracker_workunit', $alertunit);
        
            //Send email to all users in 'alert_com'
            if(!$result){
                print_error('Something happened');       
            }
        }
        
        $users = $DB->get_records('block_timetracker_alert_com', array('alertid'=>$alertid));
       
        $from = $USER; 
        $subject = get_string('approvedsubject','block_timetracker', $worker->firstname.'
        '.$worker->lastname.' in '.$course->shortname);
        
        $messagetext = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('amessage2','block_timetracker');
        $messagetext .= get_string('br2','block_timetracker'); 
        $messagetext .= get_string('emessage2','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage3','block_timetracker', userdate($alertunit->origtimein));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage4','block_timetracker', userdate($alertunit->origtimeout));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagetext .= get_string('br2','block_timetracker');
        $messagetext .= get_string('approveddata','block_timetracker');
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage3','block_timetracker', userdate($alertunit->timein));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessage4','block_timetracker', userdate($alertunit->timeout));
        $messagetext .= get_string('br1','block_timetracker'); 
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));

        $messagehtml = get_string('amessage1','block_timetracker', $USER->firstname.'
            '.$USER->lastname); 
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('amessage2','block_timetracker');
        $messagehtml .= get_string('br2','block_timetracker'); 
        $messagehtml .= get_string('emessage2','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage3','block_timetracker', userdate($alertunit->origtimein));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage4','block_timetracker', userdate($alertunit->origtimeout));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->origtimeout - $alertunit->origtimein));
        $messagehtml .= get_string('br2','block_timetracker');
        $messagehtml .= get_string('approveddata','block_timetracker');
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage3','block_timetracker', userdate($alertunit->timein));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessage4','block_timetracker', userdate($alertunit->timeout));
        $messagehtml .= get_string('br1','block_timetracker'); 
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($alertunit->timeout - $alertunit->timein));
        
        foreach ($users as $user){
            if($USER->id != $user->id){
                print("USER id is: $USER->id <br />");
                print("user->id is $user->id");
                // Get email from each in moodle table
                $emailto = $DB->get_record('user', array('id'=>$user->mdluserid));
                if($emailto){
                    email_to_user($emailto, $from, $subject, $messagetext, $messagehtml);
                }
            } 
    
            $DB->delete_records('block_timetracker_alert_com', array('mdluserid'=>$user->id));
        }
        $DB->delete_records('block_timetracker_alertunits', array('id'=>$alertunit->id));
    } else if ($action == 'deny'){
        print('You clicked deny!');
        //Delete from 'alertunits' and notify everyone
    } else {
        print('You either clicked change, or you didn\'t meet the other two conditions.');
        //What do we do here?
    }
    
    //$sql = 'INSERT INTO '.$CFG->prefix.'block_timetracker_workunit (userid, courseid, timein,
    //    timeout, payrate, lastedited, lasteditedby) ';
    //$sql .= $userid ', ', $courseid ', ', $ti ', ',$to ', ', $payrate ', ', $lastedited ', ',
    //$lasteditedby ';';
    
    //insert_record($sql);
    
    //redirect($nexturl, get_string('approvedsuccess','block_timetracker'), 2);
}
?>
