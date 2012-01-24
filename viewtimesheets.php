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
 * This page will allow the user to view previous official timesheets. 
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('lib.php');

global $CFG, $COURSE, $USER, $DB;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);

$page = optional_param('page', 0, PARAM_INT);
$sortby = optional_param('sortby', 'lastname', PARAM_ALPHA);
$sorthow = optional_param('sorthow', 'ASC', PARAM_ALPHA);
$perpage = 12;

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$url = new moodle_url($CFG->wwwroot.'/blocks/timetracker/viewtimesheets.php',$urlparams);
$baseurl = $CFG->wwwroot.'/blocks/timetracker';

$userinfo = $DB->get_record('block_timetracker_workerinfo',array('id'=>$userid));
//Need to check to make sure the user exists and that
    //A.  I'm that user
    //B.  I'm a supervisor

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$PAGE->set_url($url);
$PAGE->set_pagelayout('base');
$strtitle = get_string('viewofficial', 'block_timetracker');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$indexparams['id'] = $courseid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

$canmanage = false;
if(has_capability('block/timetracker:manageworkers', $context)){
    $canmanage = true;
}

$maintabs = get_tabs($indexparams, $canmanage, $courseid);

$nextpage = $index;

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $url);
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();
$tabs = array($maintabs);
print_tabs($tabs, 'manage');

if(!$canmanage && $USER->id != $userinfo->mdluserid){
    print_error('notpermissible','block_timetracker');
} else {

    $totalcount = $DB->count_records('block_timetracker_timesheet',array('userid'=>$userid));
    $timesheets = $DB->get_records('block_timetracker_timesheet',array('userid'=>$userid),'','*',
        $page * $perpage,$perpage);
    
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $url, 'page');
    echo $OUTPUT->box_start();
    
    if(!$timesheets){
        echo '<b><center>There are no official timesheets for ' 
            .$userinfo->firstname .' '.$userinfo->lastname.'</center></b>';
    } else {
        // Generate data here
        echo '<table align="center" border="1" cellspacing="10px" cellpadding="5px" width="75%">';
        echo '
            <tr>
                <td style="font-weight: bold">Status</td>
                <td style="font-weight: bold; text-align: center">Amount Paid</td>
                <td style="font-weight: bold; text-align: center">Actions</td>
            </tr>';
        
        foreach ($timesheets as $timesheet){
        
            $amountpd = 0;
            $amountpd += $timesheet->regpay; 
            $amountpd += $timesheet->otpay;
            
            $viewparams['id'] = $courseid;
            $viewparams['userid'] = $userid;
            $viewparams['timesheetid'] = $timesheet->id;
            $viewtsurl = new moodle_url($baseurl.'/timesheet_fromid.php',$viewparams);
            
            echo '<tr>';
            if($timesheet->supervisorsignature == 0){
                echo '<td>Pending supervisor signature</td>';
                echo '<td align="center">$'.number_format(round($amountpd,2),2).'</td>';
                echo '<td align="center">'. $OUTPUT->action_icon($viewtsurl, 
                    new pix_icon('date', 'View Timesheet','block_timetracker')) .'</td>';
            } else if ($timesheet->submitted == 0){
                echo '<td>Processing</td>';
                echo '<td align="center">$'.number_format(round($amountpd,2),2).'</td>';
                echo '<td align="center">'. $OUTPUT->action_icon($viewtsurl, 
                    new pix_icon('date', 'View Timesheet','block_timetracker')) .'</td>';
            } else {
                echo '<td>Complete</td>';
                echo '<td align="center">$'.number_format(round($amountpd,2),2).'</td>';
                echo '<td align="center">'. $OUTPUT->action_icon($viewtsurl, 
                    new pix_icon('date', 'View Timesheet','block_timetracker')) .'</td>';
            }
            echo '</tr>';
        }
        
        
        echo '</table>';
    }
    
    echo $OUTPUT->box_end();
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $url, 'page');
    
    
    echo $OUTPUT->footer();
}
?>
