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
 * This page will allow the user to clock in and clock out. 
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once ('lib.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$ttuserid = required_param('userid',PARAM_INTEGER);
$clockin = optional_param('clockin', 0,PARAM_INTEGER);
$clockout = optional_param('clockout',0, PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $ttuserid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$timeclockurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php',$urlparams);

$PAGE->set_url($timeclockurl);
$PAGE->set_pagelayout('base');


$strtitle = get_string('timeclocktitle','block_timetracker'); 
$PAGE->set_title($strtitle);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timeclockurl);
$PAGE->navbar->add($strtitle);

echo $OUTPUT->header();


$workerrecord = $DB->get_record('block_timetracker_workerinfo', array('id'=>$ttuserid,'courseid'=>$courseid));
if(!$workerrecord){
    echo "NO WORKER FOUND!";
    die;
}

if($workerrecord->active == 0){
    print_string('notactiveerror','block_timetracker');
    echo '<br />';
    echo $OUTPUT->footer();
    die;
} else {
    if($clockin == 1){
        $cin = new stdClass();
        $cin->userid = $ttuserid;
        $cin->timein = time();
        $cin->courseid = $courseid;
        $DB->insert_record('block_timetracker_pending', $cin);
    } else if ($clockout == 1){
        $cin = $DB->get_record('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
            if($cin){

                $cin->timeout = time();
                $cin->lastedited = time();
                $cin->lasteditedby = $ttuserid;
    
                unset($cin->id);
    
                $worked = $DB->insert_record('block_timetracker_workunit',$cin);
                    if($worked){
                        $DB->delete_records('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
                    } else {
                        print_error('couldnotclockout', 'block_timetracker', $CFG->wwwroot.'/blocks/timetracker/timeclock.php?id='.$courseid.'&userid='.$ttuserid);
                }
            }
        }
}


$pendingrecord= $DB->count_records('block_timetracker_pending', array('userid'=>$ttuserid));
if($pendingrecord == 0){ 
    $action = null;
    //$link = '/blocks/timetracker/timeclock.php';
    $urlparams['clockin']=1;
    $link = new moodle_url('/blocks/timetracker/timeclock.php', $urlparams);
    
    echo '<b>';
    echo print_string('clockedout','block_timetracker');
    echo '<br />';
    echo '</b>';
    echo $OUTPUT->action_link($link, get_string('clockinlink', 'block_timetracker'), $action);
    echo '<br />';
} else {
    $action = null;
    $urlparams['clockout']=1;
    $link = new moodle_url('/blocks/timetracker/timeclock.php', $urlparams);
    
    echo '<b>';
    echo print_string('clockedin','block_timetracker');
    echo '<br />';
    echo print_string('pendingtimestamp','block_timetracker');
    echo '</b>';
    $pendingtimestamp= $DB->get_record('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
    echo 'Clock in:
    '.userdate($pendingtimestamp->timein,get_string('strftimedatetimeshort','langconfig')).'<br />';
    echo $OUTPUT->action_link($link, get_string('clockoutlink', 'block_timetracker'), $action);
    echo '<br />';
}

$numrecords = $DB->count_records('block_timetracker_workunit', array('userid'=>$ttuserid));
if($numrecords == 0){
    //DB CALL - No previous punches, show message.
    echo '<br />';
    echo '<hr>';
    echo '<b>';
    echo print_string('previousentries','block_timetracker');
    echo '</b><br />';
    echo print_string('noprevious','block_timetracker'); 

} else {
    
    echo '<br />';
    echo '<hr>';
    echo '<b>';
    echo print_string('previousentries','block_timetracker');
    echo '</b><br />';
    $last10workunits = $DB->get_records('block_timetracker_workunit', array('userid'=>$ttuserid), 'timeout DESC','*',0,10);
    
    $str = '<table><tr><th>Clock in</th><th>Clock out</th><th>Elapsed</th></tr>';
    foreach($last10workunits as $workunit){
        $str .= '<tr>'; 
        $str .= '<td>'.userdate($workunit->timein, get_string('strftimedatetimeshort','langconfig')).'</td>';
        $str .= '<td>'.userdate($workunit->timeout, get_string('strftimedatetimeshort','langconfig')).'</td>';
        $str .= '<td>'.format_elapsed_time($workunit->timeout - $workunit->timein).'</td>';
        //$str .= '<td>'.userdate($workunit->timeout, get_string('strftimedatetimeshort','langconfig')).'</td>';
        $str .= '</tr>'; 
    }
    $str .='</table>';
    echo $str;


}

echo $OUTPUT->footer();

