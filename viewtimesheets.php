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
$baseurl = $CFG->wwwroot.'/blocks/timetracker';
$url = new moodle_url($baseurl.'/viewtimesheets.php',$urlparams);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$userinfo = $DB->get_record('block_timetracker_workerinfo',array('id'=>$userid));
$canmanage = false;
if(has_capability('block/timetracker:manageworkers', $context)){
    $canmanage = true;
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('base');
$strtitle = get_string('viewofficial', 'block_timetracker');
if($canmanage){
    $strtitle .= ' - '.$userinfo->firstname.' '.$userinfo->lastname;
}

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);


//Need to check to make sure the user exists and that
    //A.  I'm that user OR
    //B.  I'm a supervisor
if(!$canmanage && $USER->id != $userinfo->mdluserid)
    print_error('notpermissible','block_timetracker');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $url);
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();

$tabs = get_tabs($urlparams, $canmanage, $courseid);
$tabs = array($tabs);

/* FIX THIS AT SOME POINT
$timesheetsub=array();
$timesheetsub[] = new tabobject('signed', '#', 'Previously submitted timesheets', false);
$tabs[] = $timesheetsub;
*/
print_tabs($tabs, 'timesheet');

$index = new moodle_url($baseurl.'/index.php', $urlparams);
$index->remove_params('userid');
$nextpage = $index;

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
            <td style="font-weight: bold;">Worker Signature</td>
            <td style="font-weight: bold;">Supervisor Signature</td>
            <td style="font-weight: bold;">Processed</td>
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
            echo '<td>'.userdate($timesheet->workersignature,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
            echo '<td>Pending</td>';
            echo '<td>Pending</td>';
            echo '<td align="center">$'.number_format(round($amountpd,2),2).'</td>';
            echo '<td align="center">'. $OUTPUT->action_icon($viewtsurl, 
                new pix_icon('date', 'View Timesheet','block_timetracker'));
            if($canmanage){
                $signthemurl = new moodle_url($baseurl.'/supervisorsig.php', $viewparams);
                $signthemurl->remove_params('userid', 'timesheetid');
                echo ' '.$OUTPUT->action_icon($signthemurl, 
                    new pix_icon('sign', 'Sign timesheet', 'block_timetracker'));
            }
                
            echo '</td>';
        } else if ($timesheet->submitted == 0){
            echo '<td>'.userdate($timesheet->workersignature,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
            echo '<td>'.userdate($timesheet->supervisorsignature,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
            echo '<td>Pending</td>';
            echo '<td align="center">$'.number_format(round($amountpd,2),2).'</td>';
            echo '<td align="center">'. $OUTPUT->action_icon($viewtsurl, 
                new pix_icon('date', 'View Timesheet','block_timetracker')) .'</td>';
        } else {
            echo '<td>'.userdate($timesheet->workersignature,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
            echo '<td>'.userdate($timesheet->supervisorsignature,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
            echo '<td>'.userdate($timesheet->submitted,
                get_string('datetimeformat', 'block_timetracker')) .'</td>';
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
?>
