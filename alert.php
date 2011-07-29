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
 * This form will allow the worker to submit an alert and correction to the supervisor of an error in a work unit.
 * The supervisor will be able to approve or deny the correction.
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
$PAGE->set_pagelayout('course');

$workerrecord = $DB->get_record('block_timetracker_workerinfo', array('id'=>$userid,'courseid'=>$courseid));

if(!$workerrecord){
    echo "NO WORKER FOUND!";
    die;
}

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$strtitle = get_string('errortitle','block_timetracker',$workerrecord->firstname.' '.$workerrecord->lastname); 
$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$indexparams['userid'] = $userid;
$indexparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
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
    redirect($index);

} else if ($formdata=$mform->get_data()){
    // Data collection to send email to supervisor(s)
    $from = $DB->get_record('user',array('id'=>$USER->id));
    $subject = get_string('subjecttext','block_timetracker', $workerrecord->firstname.'
        '.$workerrecord->lastname.' - '.$course->shortname);

    // BUILD HYPERLINKS FOR EMAIL
    $delete = 0;
    if(isset($formdata->deleteunit))
        $delete = 1;
    
    $approvelink =  $CFG->wwwroot.'/blocks/timetracker/eaaction.php?userid='.$userid.'&id='
        .$courseid.'&ti='.$formdata->timeinerror.'&to='.$formdata->timeouterror.'&delete='.$delete;

    $changelink = $CFG->wwwroot.'/blocks/timetracker/eaaction.php?id='.$courseid.'&userid='.$userid.
        '&unitid='.$unitid;

    $denylink = $CFG->wwwroot.'/blocks/timetracker/eaaction.php?id='.$courseid.'&userid='.$userid.
        '&unitid='.$unitid;

    //***** PLAIN TEXT *****//
    $messagetext = get_string('emessage1','block_timetracker');
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage2','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessage3','block_timetracker', userdate($formdata->origtimein)); 
    if(!$ispending){ 
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessage4','block_timetracker', userdate($formdata->origtimeout)); 
        $messagetext .= get_string('br1','block_timetracker');
        $messagetext .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->origtimeout - $formdata->origtimein));
    }
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage5','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessage3','block_timetracker', userdate($formdata->timeinerror));
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessage4','block_timetracker', userdate($formdata->timeouterror));
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessageduration','block_timetracker', 
        format_elapsed_time($formdata->timeouterror - $formdata->timeinerror));
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessage6','block_timetracker', $formdata->message);
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('hr','block_timetracker');
    $messagetext .= get_string('emessageavailable','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessagedisclaimer','block_timetracker');
    $messagetext .= get_string('br2','block_timetracker');
    $messagetext .= get_string('emessageapprove','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessagechange','block_timetracker');
    $messagetext .= get_string('br1','block_timetracker');
    $messagetext .= get_string('emessagedeny','block_timetracker', $formdata->message);

    //***** HTML *****//
    $messagehtml = get_string('emessage1','block_timetracker');
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage2','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessage3','block_timetracker', userdate($formdata->origtimein)); 
    if(!$ispending){ 
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessage4','block_timetracker', userdate($formdata->origtimeout)); 
        $messagehtml .= get_string('br1','block_timetracker');
        $messagehtml .= get_string('emessageduration','block_timetracker', 
            format_elapsed_time($formdata->origtimeout - $formdata->origtimein));
    }
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage5','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessage3','block_timetracker', userdate($formdata->timeinerror));
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessage4','block_timetracker', userdate($formdata->timeouterror));
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessageduration','block_timetracker', 
        format_elapsed_time($formdata->timeouterror - $formdata->timeinerror));
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('emessage6','block_timetracker', $formdata->message);
    $messagehtml .= get_string('br2','block_timetracker');
    $messagehtml .= get_string('hr','block_timetracker');
    $messagehtml .= get_string('emessageavailable','block_timetracker');
    $messagehtml .= get_string('br1','block_timetracker');
    $messagehtml .= get_string('emessagedisclaimer','block_timetracker');
    $messagehtml .= get_string('br2','block_timetracker');
    
    // Approve link
    $messagehtml .= '<a href="'.$approvelink.'">';
    $messagehtml .= get_string('emessageapprove','block_timetracker');
    $messagehtml .= '</a>';
    
    $messagehtml .= get_string('br1','block_timetracker');
    
    // Change link
    $messagehtml .= '<a href="'.$changelink.'">';
    $messagehtml .= get_string('emessagechange','block_timetracker');
    $messagehtml .= '</a>';

    // Deny link
    $messagehtml .= '<a href="'.$denylink.'">';
    $messagehtml .= get_string('emessagedeny','block_timetracker');
    $messagehtml .= '</a>';

    // Move data from 'pending' or 'workunit' table into the 'alert_units' table
    if($ispending){
        // Pending Work Unit
        $alertunit =$DB->get_record('block_timetracker_pending',array('id'=>$unitid));
    } else {
        // Completed Work Unit
        $alertunit =$DB->get_record('block_timetracker_workunit',array('id'=>$unitid));
    }

    if($alertunit){
        unset($formdata->id);
        $alertunit->alerttime = time();
        $alertunit->payrate = $workerrecord->currpayrate;
        $alertid = $DB->insert_record('block_timetracker_alert_units', $alertunit);
    // Send the email to the selected supervisor(s)

        if($alertid){
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
                        $mailok = email_to_user($user, $from, $subject, $messagetext, $messagehtml); 

                        $res = $DB->insert_record('block_timetracker_alert_com',$alertcom); 

                        if (!$res){
                            print_error('cannot add teacher to alert_com');
                        }

                        // Delete the unit from the 'pending' or 'workunit' table since the data was
                        // inserted into the 'alert_units' table and any emails have been sent.
                        if($ispending && $mailok)
                            $DB->delete_records('block_timetracker_pending',array('id'=>$unitid));
                        if(!$ispending && $mailok)
                            $DB->delete_records('block_timetracker_workunit',array('id'=>$unitid));
                        if(!$mailok)
                            print_error("Error sending message to $user->firstname $user->lastname");
                    } else 
                        print_error("Failed mailing user $tid");
                }
            }
        } else {
            //print out an error saying we can't handle this alert
        }
    }

        $status = get_string('emessagesent','block_timetracker');
        redirect($index,$status,2);

    
    } else {
    //form is shown for the first time
    
    if($workerrecord->timetrackermethod==0){
        echo $OUTPUT->header();
        $maintabs[] = new tabobject('home', $index, 'Main');
        $maintabs[] = new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',
            $urlparams), 'Reports');
        $maintabs[] = new tabobject('alert', new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',
            $urlparams), 'Alert Supervisor');
        if($canmanage){
            $maintabs[] = new tabobject('manage', 
                new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 
                'Manage Workers');
        }
    } else {
        echo $OUTPUT->header();
        $maintabs[] = new tabobject('home', $index, 'Main');
        $maintabs[] = new tabobject('reports', new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',
            $urlparams), 'Reports');
        $maintabs[] = new tabobject('hourlog', new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',
            $urlparams), 'Hour Log');
        $maintabs[] = new tabobject('alert', new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',
            $urlparams), 'Alert Supervisor');
        if($canmanage){
            $maintabs[] = new tabobject('manage', 
                new moodle_url($CFG->wwwroot.'/blocks/timetracker/manageworkers.php',$urlparams), 
                'Manage Workers');
        }
    }
    
    $tabs = array($maintabs);
    print_tabs($tabs, 'alert');

    $mform->display();
    echo $OUTPUT->footer();
}
