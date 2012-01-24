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
 * This page will call for the spreadsheet timesheet to be generated. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('timetracker_timesheet_form.php');
require_once('timesheet_pdf.php');
require_once('timesheet_xls.php');

global $CFG, $COURSE, $USER, $DB;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = optional_param('userid', -1, PARAM_INTEGER);

$urlparams['id'] = $courseid;
if($userid > -1)
    $urlparams['userid'] = $userid;

$timesheeturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timesheet.php',$urlparams);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($timesheeturl);
$PAGE->set_pagelayout('base');

$canmanage = false;
if(has_capability('block/timetracker:manageworkers', $context)){
    $canmanage = true;
}


$strtitle = get_string('timesheettitle','block_timetracker');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

//$indexparams['userid'] = $userid;
$indexparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php', $indexparams);

$nextpage = $index;

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_timesheet_form($context, $userid);

if($mform->is_cancelled()){
    //User clicked cancel
    $reportsurl = new
        moodle_url($CFG->wwwroot.'/blocks/timetracker/reports.php',$urlparams);
    redirect($reportsurl);
} else if($formdata=$mform->get_data()){

    $official  = false;
    if(isset($formdata->official)){
        $official = true;
    } 

    $cid = $formdata->id;
    $format = $formdata->fileformat;
    if(isset($formdata->entiremonth)){
        $monthinfo = get_month_info($formdata->month, $formdata->year);
        $start = make_timestamp($formdata->year, $formdata->month, 1, 0, 0, 0);
        $end =  make_timestamp($formdata->year, $formdata->month,
            $monthinfo['lastday'], 23, 59, 59);
    } else {
        $start = $formdata->startday;
        $end = strtotime('+ 1 day ', $formdata->endday) - 1;
    }

    if(!is_array($formdata->workerid) || count($formdata->workerid)==1){ // a single id?

        if(is_array($formdata->workerid)){
	        $uid = $formdata->workerid[0];
        } else {
	        $uid = $formdata->workerid;
        }
        if($official){
            $urlparams['id'] = $formdata->id;
            $urlparams['userid'] = $formdata->workerid;
            $urlparams['start'] = $start;
            $urlparams['end'] = $end;
            $workersigpage = new moodle_url($CFG->wwwroot.'/blocks/timetracker/workersig.php',$urlparams);
            redirect($workersigpage);
        }
        
        if($format == 'pdf'){
            generate_pdf($start, $end, $uid, $cid);
        } else {
            generate_xls($formdata->month, $formdata->year, $uid, $cid);
                
        }
    } else { //have multiple selected

        //create all the timesheets
        $files = array();
        $basepath = $CFG->dataroot.'/temp/timetracker/'.$cid.'_'.$USER->id.'_'.sesskey();

        $status = check_dir_exists($basepath,true);
        if (!$status) {
            print_error('Error creating backup temp directories. Exiting.');
            return;
        }

        if($format == 'pdf'){
            foreach($formdata->workerid as $id){
                /*
                $monthinfo = get_month_info($formdata->month, $formdata->year);
                $start = make_timestamp($formdata->year, $formdata->month, 1, 0, 0, 0);
                $end =  make_timestamp($formdata->year, $formdata->month,
                    $monthinfo['lastday'], 23, 59, 59);
                */
                $fn = generate_pdf($start, $end, $id, $cid, 'F', $basepath);
                $files[$fn] = $basepath.'/'.$fn;
            }
        } else if ($format == 'xls') {
            foreach($formdata->workerid as $id){
                $fn = generate_xls($formdata->month, $formdata->year, $id, $cid, 
                    'F', $basepath);
                $files[$fn] = $basepath.'/'.$fn;
            }
        }
    
        //zip them up, give them to the user
        $fn = $formdata->year.'_'.($formdata->month<10?'0'.
            $formdata->month:$formdata->month).'Timesheets.zip';
        $zipfile = $basepath.'/'.$fn;
    
        $zippacker = get_file_packer('application/zip');
        $zippacker->archive_to_pathname($files, $zipfile);
            
        send_file($basepath.'/'.$fn, $fn, 'default', '0', false, false, '', true);
        fulldelete($basepath);
    }
    
} else {
    echo $OUTPUT->header();

    $tabs = get_tabs($urlparams, $canmanage, $courseid);
    $tabs = array($tabs);

    $timesheetsub = array();
    if($canmanage){
        $num = has_unsigned_timesheets($courseid);
        if($num > 0){
            $supersigurl = new
                moodle_url($CFG->wwwroot.'/blocks/timetracker/supervisorsig.php', $urlparams);
            $desc = 'Sign timesheets - ('.$num.')';
            $timesheetsub[] = new tabobject('supersig', $supersigurl, $desc);
        }
    } else {
        $myself = $DB->get_record('block_timetracker_workerinfo',
            array('mdluserid'=>$USER->id));
        $urlparams['userid'] = $myself->id;
        $submittedurl = new moodle_url($CFG->wwwroot.
            '/blocks/timetracker/viewtimesheets.php', $urlparams);
        $timesheetsub[] = new tabobject('submitted', 
            $submittedurl, 'Previously submitted timesheets');
    }
    $tabs[] = $timesheetsub;


    print_tabs($tabs, 'timesheets');

    $mform->display();
    echo $OUTPUT->footer();
}

