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

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');


require_login();

global $SESSION;

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$unitid = required_param('unitid', PARAM_INTEGER);

//For redirect nightmare -- HACK!
$camefrom = optional_param('next', '', PARAM_ALPHA);
$prevunitid = optional_param('eunitid', -1, PARAM_INTEGER); //editunit
$astart = optional_param('astart', 0, PARAM_INTEGER); //addunit
$aend = optional_param('aend', 0, PARAM_INTEGER); //addunit

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$nextdata['next'] = $camefrom;
$nextdata['eunitid'] = $prevunitid;
$nextdata['astart'] = $astart;
$nextdata['aend'] = $aend;

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

//error_log('in delete: '.$SESSION->fromurl);
if (isset($SESSION->fromurl) &&
    !empty($SESSION->fromurl)){
    //error_log($SESSION->fromurl);
    $nextpage = new moodle_url($SESSION->fromurl);
    unset($SESSION->fromurl);
}

if($courseid){
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

if (!has_capability('block/timetracker:manageworkers', $context)) {
    print_error('notpermissible','block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
} else {
    if($unitid && confirm_sesskey()){
        $DB->delete_records('block_timetracker_workunit',array('id'=>$unitid));
        //error_log("Deleted unit id: $unitid");
    } else {
        print_error('errordeleting','block_timetracker', 
            $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id);
    }
}

redirect($nextpage, 'Unit deleted', 1);
