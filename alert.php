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
 * This form will allow the worker to submit an alert and correction to the supervisor of an error in a 
 * work unit. The supervisor will be able to approve, change, or deny the correction.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require('timetracker_alert_form.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$unitid = required_param('unitid', PARAM_INTEGER);
$ispending = optional_param('ispending',false,PARAM_BOOL);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$urlparams['unitid'] = $unitid;
$urlparams['ispending'] = $ispending;

$alerturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/alert.php',$urlparams);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($alerturl);
$PAGE->set_pagelayout('base');

$workerrecord = $DB->get_record('block_timetracker_workerinfo', 
    array('id'=>$userid,'courseid'=>$courseid));

if(!$workerrecord){
    echo "NO WORKER FOUND!";
    die;
}

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}


$strtitle = get_string('errortitle','block_timetracker',
    $workerrecord->firstname.' '.$workerrecord->lastname); 
$PAGE->set_title($strtitle);

unset($urlparams['ispending']);
unset($urlparams['unitid']);

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$nextpage = $index;
/*
if(isset($_SERVER['HTTP_REFERER'])){
    $nextpage = $_SERVER['HTTP_REFERER'];
} else {
    $nextpage = $index;
}
if(strpos($nextpage, qualified_me()) !== false){
    $nextpage = $SESSION->lastpage;
} else {
    $SESSION->lastpage = $nextpage;
}

if($nextpage == $CFG->wwwroot.'/blocks/timetracker/hourlog.php'){
    $nextpage .=
        '?id='.$courseid.
        '&userid='.$userid;
}
*/

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_alert_form($context, $userid, $courseid,
    $unitid, $ispending);

if($workerrecord->active == 0){
    echo $OUTPUT->header();
    print_string('notactiveerror','block_timetracker');
    echo '<br />';
    echo $OUTPUT->footer();
    die;
}

if ($mform->is_cancelled()){ 
    //user clicked cancel
    redirect($nextpage);

} else if ($formdata=$mform->get_data()){
    // Data collection to send email to supervisor(s)
    $from = $DB->get_record('user',array('id'=>$USER->id));
    $subject = get_string('subjecttext','block_timetracker', $workerrecord->firstname.'
        '.$workerrecord->lastname.' in '.$course->shortname);

    // BUILD HYPERLINKS FOR EMAIL
    $delete = 0;
    if(isset($formdata->deleteunit))
        $delete = 1;
    

    //***** PLAIN TEXT *****//
    $messagetext = get_string('emessage1','block_timetracker');
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage2','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessage3','block_timetracker', 
        userdate($formdata->origtimein)); 
    if(!$ispending){ 
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessage4','block_timetracker', 
            userdate($formdata->origtimeout)); 
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->origtimeout - $formdata->origtimein));
    }
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage5','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    
    if($delete == 1){
        $messagetext .= get_string('emessagedelete','block_timetracker');
    } else {
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessage3','block_timetracker', 
            userdate($formdata->timeinerror));
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessage4','block_timetracker', 
            userdate($formdata->timeouterror));
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->timeouterror - $formdata->timeinerror));
    }
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage6','block_timetracker', $formdata->message);
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('hr','block_timetracker');
    $messagetext .= get_string('emessageavailable','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessagedisclaimer','block_timetracker');
    $messagetext .= get_string('br2','block_timetracker');

    //***** HTML *****//
    $messagehtml = get_string('emessage1','block_timetracker');
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage2','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessage3','block_timetracker', 
        userdate($formdata->origtimein)); 
    if(!$ispending){ 
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessage4','block_timetracker', 
            userdate($formdata->origtimeout)); 
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->origtimeout - $formdata->origtimein));
    }
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage5','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    
    if($delete == 1){
        $messagehtml .= get_string('emessagedelete','block_timetracker');
    } else {
        $messagehtml .= get_string('emessage3','block_timetracker', 
            userdate($formdata->timeinerror));
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessage4','block_timetracker', 
            userdate($formdata->timeouterror));
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->timeouterror - $formdata->timeinerror));
    }
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage6','block_timetracker', $formdata->message);
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('hr','block_timetracker');
    $messagehtml .= get_string('emessageavailable','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessagedisclaimer','block_timetracker');
    $messagehtml .= get_string('br2','block_timetracker');

    // Get data from 'pending' or 'workunit' table to put into the 'alertunits' table
    if($ispending){
        // Pending Work Unit
        $alertunit = $DB->get_record('block_timetracker_pending',array('id'=>$unitid));
    } else {
        // Completed Work Unit
        $alertunit = $DB->get_record('block_timetracker_workunit',array('id'=>$unitid));
    }


    if($alertunit){

        unset($formdata->id);
        $alertunit->alerttime = time();
        $alertunit->payrate = $workerrecord->currpayrate;
        $alertunit->origtimein = $alertunit->timein;
        if(!$ispending)
            $alertunit->origtimeout = $alertunit->timeout;

        $alertunit->timein = $formdata->timeinerror;
        $alertunit->timeout = $formdata->timeouterror;
        $alertunit->message = $formdata->message;

        if($delete == 1)
            $alertunit->todelete = 1;

        $alertid = $DB->insert_record('block_timetracker_alertunits', $alertunit);
        
        if(!$ispending){
            $DB->delete_records('block_timetracker_pending',array('id'=>$unitid));
        } else {
            $DB->delete_records('block_timetracker_workunit',array('id'=>$unitid));
        }


        // Send the email to the selected supervisor(s)

        if($alertid){
            $linkbase = $CFG->wwwroot.'/blocks/timetracker/alertaction.php?alertid='.
                $alertid.'&delete='.$delete;
            $approvelink = $linkbase.'&action=approve';
            $deletelink = $linkbase.'&action=delete';
            $changelink = $linkbase.'&action=change';
            $denylink = $linkbase.'&action=deny';
    
            // Approve link
            $messagehtml .= '<a href="'.$approvelink.'">';
            $messagehtml .= get_string('emessageapprove','block_timetracker');
            $messagehtml .= '</a> - Approve the work unit as proposed';
            
            $messagehtml .= get_string('br1','block_timetracker');

            // Delete link
            $messagehtml .= '<a href="'.$deletelink.'">';
            $messagehtml .= get_string('emessagedelete','block_timetracker');
            $messagehtml .= '</a> - Delete this alert and remove the work unit';
            
            $messagehtml .= get_string('br1','block_timetracker');
            
            // Change link
            $messagehtml .= '<a href="'.$changelink.'">';
            $messagehtml .= get_string('emessagechange','block_timetracker');
            $messagehtml .= '</a> - Change the proposed work unit before approval';
        
            $messagehtml .= get_string('br1','block_timetracker');
            
            // Deny link
            $messagehtml .= '<a href="'.$denylink.'">';
            $messagehtml .= get_string('emessagedeny','block_timetracker');
            $messagehtml .= '</a> - Deny the propsed work unit and re-insert the original';

            $alertcom = new stdClass();
            $alertcom->alertid = $alertid;
            $alertcom->mdluserid = $USER->id;

            //Insert student record into 'alert_com'
            $res = $DB->insert_record('block_timetracker_alert_com',$alertcom); 

            if (!$res){
                print_error('cannot add student to alert_com');
            }

            foreach($formdata->teacherid as $tid=>$checkvalue){
                //print_object($tid);
            
                if($checkvalue == 1){ //box was checked?
                    $user = $DB->get_record('user',array('id'=>$tid));
                    $alertcom->mdluserid = $tid;
                    //insert alertcom into db
                    //print('emailing user: '.$tid);
                    if($user){
                        $mailok = email_to_user($user, $from, $subject, 
                            $messagetext, $messagehtml); 
                            
                        $res = $DB->insert_record('block_timetracker_alert_com',$alertcom); 

                        if (!$res){
                            print_error('cannot add teacher to alert_com');
                        }

                        // Delete the unit from the 'pending' or 'workunit' table 
                        //since the data was inserted into the 'alertunits' table 
                        //and any emails have been sent.
                        if($ispending && $mailok)
                            $DB->delete_records('block_timetracker_pending',
                                array('id'=>$unitid));
                        if(!$ispending && $mailok)
                            $DB->delete_records('block_timetracker_workunit',
                                array('id'=>$unitid));
                        if(!$mailok)
                            print_error(
                                "Error sending message to $user->firstname $user->lastname");
                    } else 
                        print_error("Failed mailing user $tid");
                }
            }
        } else {
            //print out an error saying we can't handle this alert
        }
    }

        $status = get_string('emessagesent','block_timetracker');
        redirect($nextpage, $status,1);

    
    } else {
    //form is shown for the first time
    
    echo $OUTPUT->header();
    $maintabs = get_tabs($urlparams, $canmanage, $courseid);
    //print_object($urlparams);

    $maintabs[] = new tabobject('postalert',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php#', $urlparams),
        'Post alert');
    
    $tabs = array($maintabs);
    print_tabs($tabs, 'postalert');

    $mform->display();
    echo $OUTPUT->footer();
}
