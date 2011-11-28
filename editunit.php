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
 * This page will allow the worker to input the date, time, and duration of a workunit.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('lib.php');
require('timetracker_editunit_form.php');

global $CFG, $COURSE, $USER, $SESSION;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$unitid = required_param('unitid', PARAM_INTEGER);
$start = optional_param('start', 0, PARAM_INTEGER);
$end = optional_param('end', 0, PARAM_INTEGER);
$ispending = optional_param('ispending', 0, PARAM_BOOL);

//For redirect nightmare -- HACK!
$camefrom = optional_param('next', '', PARAM_ALPHA);
$prevunitid = optional_param('eunitid', -1, PARAM_INTEGER); //editunit
$astart = optional_param('astart', 0, PARAM_INTEGER); //addunit
$aend = optional_param('aend', 0, PARAM_INTEGER); //addunit

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$urlparams['unitid'] = $unitid;
$urlparams['timein'] = $start;
$urlparams['timeout'] = $end;
$urlparams['ispending'] = $ispending;

$nextdata['next'] = $camefrom;
$nextdata['eunitid'] = $prevunitid;
$nextdata['astart'] = $astart;
$nextdata['aend'] = $aend;


//Define URLs for use in this page
$edituniturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/editunit.php');
$edituniturl->params($urlparams);
    //$urlparams);
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php');
$index->params(array('id'=>$courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;
$PAGE->set_url($edituniturl);

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

//redirect madness
if($camefrom){
    $baseurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/'.$camefrom.'.php');
    $baseurl->params(array('id'=>$courseid,
        'userid'=>$userid));  
    $nextpage = $baseurl;
    if($astart != 0){
        $nextpage->params(array(
            'start'=>$astart));
    }
    if($aend != 0){
        $nextpage->params(array(
            'end'=>$aend));
    }
    if($camefrom == 'editunit'){
        if($prevunitid != -1){
            $nextpage->params(array('unitid'=>$prevunitid));
        }
    }
} else {
    if(get_referer(false)){
        $nextpage = new moodle_url(get_referer(false));
    } else {
        $nextpage = $index;
    }
    
    //if we posted to ourself from ourself
    if(strpos($nextpage, qualified_me()) !== false){
        $nextpage = new moodle_url($SESSION->lastpage);
    } else {
        $SESSION->lastpage = $nextpage;
    }
        
    if (isset($SESSION->fromurl) &&
        !empty($SESSION->fromurl)){
        $nextpage = new moodle_url($SESSION->fromurl);
        unset($SESSION->fromurl);
    }
}

//error_log("In editunit and next is: $nextpage");

if($USER->id != $workerrecord->mdluserid && !$canmanage){
    print_error('You do not have permissions to add hours for this user');
} else if(!$canmanage && $workerrecord->timetrackermethod==0){
    redirect($nextpage, $status,1);
}

$strtitle = get_string('editunittitle','block_timetracker',
    $workerrecord->firstname.' '.$workerrecord->lastname); 

$PAGE->set_pagelayout('base');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_editunit_form($context, $userid,
    $courseid, $unitid, $start, $end, $ispending);

if($workerrecord->active == 0){
    echo $OUTPUT->header();
    print_string('notactiveerror','block_timetracker');
    echo '<br />';
    echo $OUTPUT->footer();
    die;
}

if ($mform->is_cancelled()){ //user clicked cancel
    redirect($nextpage);
} else if ($formdata=$mform->get_data()){

    $formdata->courseid = $formdata->id;
    unset($formdata->id);
    $formdata->id = $formdata->unitid;
    $formdata->lastedited = time();
    $formdata->lasteditedby = $formdata->editedby;

    //print_object($formdata);
    //$DB->update_record('block_timetracker_workunit', $formdata);
    update_unit($formdata);

    $status = 'Work unit edited successfully.'; 
    //error_log($nextpage);
    unset($nextpage->unitid);
    redirect($nextpage,$status,1);

} else {
    //form is shown for the first time
    //error_log('edit unit: '.qualified_me());
    echo $OUTPUT->header();
    $tabs = get_tabs($urlparams, $canmanage, $courseid);  
    $tabs[] = new tabobject('editunit',
        new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php#', $urlparams),
        'Edit Work Unit');
    $tabs = array($tabs);
    print_tabs($tabs,'editunit');
    
    $mform->set_data($nextdata);
    $mform->display();
    echo $OUTPUT->footer();
}


