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
require_once($CFG->libdir . '/tablelib.php');
require_once('lib.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
//$userid = optional_param('userid',0, PARAM_INTEGER);

$urlparams['id'] = $courseid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;


$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
    $urlparams['userid']=0;
}

$canview = false;
if (has_capability('block/timetracker:viewonly', $context)) {
    $canview = true;
    $urlparams['userid']=0;
}

$worker = $DB->get_record('block_timetracker_workerinfo',array('mdluserid'=>$USER->id, 
    'courseid'=>$course->id));


if(!$canmanage && !$canview && !$worker){
    print_error('usernotexist', 'block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$course->id);
}

if(!($canmanage || $canview) && $USER->id != $worker->mdluserid){
    print_error('notpermissible', 'block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$course->id);
}
if($worker){
    $urlparams['userid'] = $worker->id;
    $userid = $worker->id;
}

$now = time();
$currdateinfo = usergetdate($now);

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$strtitle = get_string('pluginname','block_timetracker');

$PAGE->set_url($index);
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

echo $OUTPUT->header();


$tabs = get_tabs($urlparams, $canview, $courseid);
$tabs = array($tabs);
print_tabs($tabs, 'home');


if ($canmanage || $canview) { //supervisor
    /*
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<h2>Welcome, ' .$USER->firstname .'!</h2>'; 
    echo '<br />Below you will find the last 10 work units by your employees 
        as well as a roster of your workers and summary data for each.<br /><br />';
    echo $OUTPUT->box_end();

    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<table align="center" cellspacing="10px" 
                cellpadding="5px" width="95%" style="border: 1px solid #000;">';
    echo '<tr><th colspan=5">Last 10 Work Units</th></tr>'."\n";
    echo '<tr>
            <td style="font-weight: bold">Worker name</td>
            <td style="font-weight: bold">Time in</td>
            <td style="font-weight: bold">Time out</td>
            <td style="font-weight: bold">Elapsed</td>
            <td style="font-weight: bold; text-align: center">Action</td>
         ';

    $last10unitssql = 'SELECT '.$CFG->prefix.'block_timetracker_workerinfo.firstname, '.
        $CFG->prefix.'block_timetracker_workerinfo.lastname, '.
        $CFG->prefix.'block_timetracker_workunit.* FROM '.
        $CFG->prefix.'block_timetracker_workerinfo,'.
        $CFG->prefix.'block_timetracker_workunit WHERE '.
        $CFG->prefix.'block_timetracker_workunit.userid='.
        $CFG->prefix.'block_timetracker_workerinfo.id AND '.
        $CFG->prefix.'block_timetracker_workunit.courseid='.$courseid.' ORDER BY '.
        $CFG->prefix.'block_timetracker_workunit.timeout DESC LIMIT 10';

    $last10units = $DB->get_recordset_sql($last10unitssql);

    if(!$last10units){
        echo 
        '<tr><td colspan="5" style="text-align:center">No work units found</a></td></tr>';
    } else {
        foreach($last10units as $unit){
                $row='<tr>';
                $row.='<td><a href="'.
                    $CFG->wwwroot.'/blocks/timetracker/reports.php?id='.
                    $courseid.'&userid='.$unit->userid.'">'.
                    $unit->lastname. ', '.$unit->firstname.'</a></td>';
                $row.='<td>'.userdate($unit->timein,
                    get_string('datetimeformat','block_timetracker')).'</td>';
                $row.='<td>'.userdate($unit->timeout,
                    get_string('datetimeformat','block_timetracker')).'</td>';
                $currelapsed = $unit->timeout - $unit->timein;  
                $row.='<td>'.
                    //format_elapsed_time($currelapsed).'</td>';
                    get_hours($currelapsed, $courseid).' hour(s)</td>';

                $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 

                $urlparams['id'] = $unit->courseid;
                $urlparams['userid'] = $unit->userid;
                $urlparams['sesskey'] = sesskey();
                $urlparams['unitid'] = $unit->id;
                
                $unitdateinfo = usergetdate($unit->timein);
                if(!$unit->canedit){

                    //show greyed out icons and no URL
                    
                    $row .= '<td style="text-align: center">'.
                        html_writer::empty_tag('img', 
                            array('src' => 
                            $CFG->wwwroot.'/blocks/timetracker/pix/clock_edit_bw.png', 
                            'class' => 'icon')).' '.
                        html_writer::empty_tag('img', 
                            array('src' => 
                            $CFG->wwwroot.'/blocks/timetracker/pix/clock_delete_bw.png', 
                            'class' => 'icon')).
                        '</td>';
                }else {
                    $editurl = new moodle_url($baseurl.'/editunit.php', $urlparams);
                    $deleteurl = new moodle_url($baseurl.'/deleteworkunit.php', $urlparams);
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('clock_edit', 
                        get_string('edit'),'block_timetracker'));

                    $deleteicon = new pix_icon('clock_delete', 
                        get_string('delete'),'block_timetracker');
                    $deleteaction = $OUTPUT->action_icon(
                        $deleteurl, $deleteicon, 
                        new confirm_action('Are you sure you want to delete this work unit?'));
    
                    $row .= '<td style="text-align: center">'.$editaction . ' '.
                        $deleteaction.'</td>';
                }
                $row .= '</tr>';
                echo $row."\n";
        }
    }
    echo '</table>';
    echo $OUTPUT->box_end();
    */


    //now print out roster
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<table align="center" cellspacing="1px" 
                cellpadding="1px" width="95%" style="border: 1px solid #000;">';
    echo '<tr><th colspan="7">Worker Roster</th></tr>'."\n";
    echo '<tr>
            <td style="font-weight: bold">Worker name</td>
            <td style="font-weight: bold">Last unit</td>
            <td style="font-weight: bold; text-align: center">Current rate</td>
            <td style="font-weight: bold; text-align: center">Hours/Pay month</td>
            <td style="font-weight: bold; text-align: center">Hours/Pay term</td>
            <td style="font-weight: bold; text-align: center">Hours/Pay year</td>
            <td style="font-weight: bold; text-align: center">Total</td>
            </tr>
         ';
    
    $workers = get_workers_stats($courseid);
    if(!$workers){
        echo '<tr><td colspan="7" style="text-align: center">
            No workers at this time</td></tr>';
    } else {
        foreach($workers as $worker){
            $html = '<tr>';
            $html .= '<td>'.$worker->lastname.', '.$worker->firstname.'</td>';
            $html .= '<td>'.$worker->lastunit.'</td>';
            $html .= '<td style="text-align: center">$'.
                round($worker->currpayrate, 2).'</td>';
            $html .= '<td style="text-align: center">'.
                $worker->monthhours.' / $'.
                $worker->monthearnings
                .'</td>';

            if($worker->maxtermearnings > 0 && 
                ($worker->termearnings > $worker->maxtermearnings ||
                ($worker->maxtermearnings - $worker->termearnings) <= 50 && 
                $worker->termhours != 0)){

                $html .= '<td style="text-align:center"><span style="color: red">'.
                    $worker->termhours.' / $'.
                    $worker->termearnings
                    .'</span></td>';
            } else {
                $html .= '<td style="text-align: center">'.
                    $worker->termhours.' / $'.
                    $worker->termearnings
                    .'</td>';
            }

            $html .= '<td style="text-align: center">'.
                $worker->yearhours.' / $'.
                $worker->yearearnings
                .'</td>';
            $html .= '<td style="text-align: center">'.
                $worker->totalhours.' / $'.
                $worker->totalearnings
                .'</td>';

            $html .= '</tr>';
            echo $html;
        }
    }

    echo '</table>';
    echo $OUTPUT->box_end();

} else { //worker
    if(!$worker){
        print_error('User is not known to TimeTracker. '.
            'Please register on the course main page');
    }

    $userUnits = $DB->get_records('block_timetracker_workunit',
        array('userid'=>$worker->id),'timeout DESC','*',0,10);
    $userPending = $DB->get_records('block_timetracker_pending', 
        array('userid'=>$worker->id));


    //add clockin/clockout box
    /*
    if($worker->active == 0){
        echo get_string('notactiveerror','block_timetracker').'<br /><br />';
    } else {
    */
    if(!$userPending && $worker->timetrackermethod==0){ //timeclock
        $clockinicon = new pix_icon('clock_play','Clock in', 'block_timetracker');
        $clockinurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php',
            $urlparams);
        $clockinurl->params(array('clockin'=>1));
        $clockinaction = $OUTPUT->action_icon($clockinurl, $clockinicon);
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>';
        echo $clockinaction;
        echo ' Clock in?</h2>';
        echo "You are not currently clocked in. Click the icon to clock in now.<br />";
        echo $OUTPUT->box_end();
    } else if(!$userPending && $worker->timetrackermethod==1){ //hourlog
        $clockinicon = new pix_icon('clock_add','Add work unit', 'block_timetracker');
        $clockinurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php',
            $urlparams);
        $clockinaction = $OUTPUT->action_icon($clockinurl, $clockinicon);
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>';
        echo $clockinaction;
        echo ' Add Hours?</h2>';
        echo 'Would you like to add some hours now? Click the icon to '.
            'add work units.<br />';
        echo $OUTPUT->box_end();
    }
    //}


    //summary data
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<h2>Worker Summary Data</h2>';
    $stats = get_worker_stats($userid, $courseid);

    $statstable = new flexible_table('timetracker-display-worker-summary');
    $statstable->define_columns(array('period', 'hvalue','evalue'));
    $statstable->define_headers(array('Period', 'Hours', 'Earnings'));
    $statstable->define_baseurl($CFG->wwwroot.'/blocks/timetracker/index.php');
    $statstable->set_attribute('cellspacing', '0');
    //$statstable->set_attribute('width', '95%');
    $statstable->setup();

    $closetomax = false;
    if($worker->maxtermearnings > 0 && 
        ($stats['termearnings'] > $worker->maxtermearnings ||
        ($worker->maxtermearnings - $stats['termearnings']) <= 50 && 
        $stats['termhours'] != 0)){
        $closetomax = true;
    }

    $statstable->add_data(array(
        'This month',$stats['monthhours'],
        '$'.$stats['monthearnings']
        ));
    $statstable->add_data(array(
        'This term',$stats['termhours'],
        '$'.$stats['termearnings']
        ));
    $statstable->add_data(array(
        'This year',$stats['yearhours'],
        '$'.$stats['yearearnings']
        ));
    $statstable->add_data(array(
        'Total hours',$stats['totalhours'],
        '$'.$stats['totalearnings']
        ));

    $statstable->print_html();

    echo $OUTPUT->box_end();

    //clockin/clockout box
    if($userPending){
        echo $OUTPUT->box_start('generalbox boxaligncenter');
        echo '<h2>Pending Clock-in</h2>';

        $table = new flexible_table('timetracker-display-worker-index');
    
        $table->define_columns(array('timein', 'action'));
        $table->define_headers(array('Time in', 'Action'));
        
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('class', 'generaltable generalbox');
        $table->define_baseurl($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);
        //$table->set_attribute('width', '95%');

        $table->setup();
        
        $clockouturl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php', 
            $urlparams);
        $clockouturl->params(array('clockout'=>1));
        foreach ($userPending as $pending){
            $clockouticon = new pix_icon('clock_stop','Clock out','block_timetracker');
            $clockoutaction = $OUTPUT->action_icon($clockouturl, $clockouticon);

            $urlparams['ispending']=true;
            $urlparams['unitid'] = $pending->id;

            $alertlink= new moodle_url($CFG->wwwroot.'/blocks/timetracker/alert.php', 
                $urlparams);
            
            $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 

            $urlparams['id'] = $pending->courseid;
            $urlparams['userid'] = $pending->userid;
            $urlparams['sesskey'] = sesskey();
            $urlparams['unitid'] = $pending->id;

            $deleteurl = new moodle_url($baseurl.'/deletepending.php', $urlparams);
            $deleteicon = new pix_icon('clock_delete', get_string('delete'),
                'block_timetracker');
            $deleteaction = $OUTPUT->action_icon(
                $deleteurl, $deleteicon, 
                new confirm_action(
                'Are you sure you want to delete this pending work unit?'));
            $alerticon= new pix_icon('alert','Alert Supervisor of Error','block_timetracker');
            $alertaction= $OUTPUT->action_icon($alertlink, $alerticon);
            $table->add_data(array(userdate($pending->timein,get_string('datetimeformat',
                'block_timetracker')),$clockoutaction.' '.$deleteaction.' '.$alertaction));
        }
        
        unset($urlparams['ispending']);
        unset($urlparams['unitid']);
        $table->print_html();

        echo $OUTPUT->box_end();
    } else {
        //show clock in?

    }
}

echo $OUTPUT->footer();
