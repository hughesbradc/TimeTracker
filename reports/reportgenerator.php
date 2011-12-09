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

require_once('../../../config.php');
require('timetracker_reportgenerator_form.php');

global $CFG, $COURSE, $USER;

require_login();

//$courseid = required_param('id', PARAM_INTEGER);
//$userid = required_param('userid', PARAM_INTEGER);
$reportstart = optional_param('reportstart', 0,  PARAM_INTEGER);
$reportend = optional_param('reportend', 0, PARAM_INTEGER);

$url = new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports/reportgenerator.php');

$context = get_context_instance(CONTEXT_SYSTEM); 
$PAGE->set_context($context);

$PAGE->set_url($url);
$PAGE->set_pagelayout('base');
$strtitle = 'TimeTracker : Report Generator';

$finaid = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //financial aid
    $finaid = true;
}

if($finaid){

$PAGE->set_title('Report Generator');
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php');

$nextpage = $index;

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'));
$PAGE->navbar->add($strtitle);

$mform = new timetracker_reportgenerator_form($reportstart, $reportend);

if ($mform->is_cancelled()){ 
    //user clicked cancel
    redirect($nextpage);

} else if ($formdata=$mform->get_data()){
    $genConflicts = false;
    
    if(isset($formdata->conflicts)){
        $urlparams['catid'] = 2;
        $urlparams['start'] = $formdata->reportstart;
        $urlparams['end'] = strtotime('+ 1 day ', $formdata->reportend) - 1;
        $conflictsurl = new moodle_url($CFG->wwwroot.
            '/blocks/timetracker/reports/studentschedules.php',$urlparams);
        redirect($conflictsurl);
    } 
    
    if(isset($formdata->earningsactive)){
        //req'd: catid, active
        $urlparams['catid'] = 1;
        $urlparams['active'] = 1;
        $earningsurl = new moodle_url($CFG->wwwroot.
            '/blocks/timetracker/reports/batch_earnings.php',$urlparams);
        redirect($earningsurl);
    }
    if(isset($formdata->earningsall)){
        //req'd catid
        $urlparams['catid'] = 1;
        $earningsurl = new moodle_url($CFG->wwwroot.
            '/blocks/timetracker/reports/batch_earnings.php',$urlparams);
        redirect($earningsurl);
    }


} else {
    //form is shown for the first time
    
    echo $OUTPUT->header();
    //$maintabs = get_tabs($urlparams, $canmanage);
    //$maintabs = get_tabs($urlparams, $canmanage, $courseid);
    //print_object($urlparams);

    //$tab = new tabobject('reportparams',
    //    new moodle_url($CFG->wwwroot.'/blocks/timetracker/reports/reportgenerator.php'),
    //    'Report Parameters');
    
    //print_tabs($tab, 'reportparams');

    $mform->display();
    echo $OUTPUT->footer();
}

} else {
    print_error('You do not have permission to access the report generator.');
}


