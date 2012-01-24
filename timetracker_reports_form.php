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

require_once ($CFG->libdir.'/formslib.php');
require_once ('lib.php');

class timetracker_reports_form  extends moodleform {

    function timetracker_reports_form($context,$userid = 0,$courseid=0, 
        $reportstart=0, $reportend=0){

        $this->context = $context;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->reportstart = $reportstart;
        $this->reportend = $reportend;
        parent::__construct();
    }

    function definition() {
        global $CFG, $USER, $DB, $OUTPUT;
        $mform =& $this->_form; // Don't forget the underscore! 

        //userid of 0 means we want to see every worker. -- this needs fixing. XXX

        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }

        $issiteadmin = false;
        if(has_capability('moodle/site:config',$this->context)){
            $issiteadmin = true;
        }

        if($this->userid == 0 && !$canmanage){
            print_error('notpermissible','block_timetracker',
                $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$this->courseid);
        }

        $now = time();

        if($this->userid == 0 && $canmanage){
            //supervisor -- show all!
            $workers =
                $DB->get_records('block_timetracker_workerinfo',
                    array('courseid'=>$this->courseid));
            if(!$workers){
               $mform->addElement('html','No workers found'); 
               return;
            }

        }  else {

            $user = $DB->get_record('block_timetracker_workerinfo',
                array('id'=>$this->userid));

            if(!$user && $user->id != $this->userid && !$canmanage){
                print_error('notpermissible','block_timetracker',
                    $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$this->courseid);
            }
        }

        /*
        $mform->addElement('header','general','Generate Monthly Timesheet');
        if($this->userid > 0){
            $mform->addElement('html','<center><a
            href="'.$CFG->wwwroot.'/blocks/timetracker/timesheet.php?id='.
            $this->courseid.'&userid='.
            $this->userid.'">Generate Monthly Timesheet</a></center>');
        } else {
            $mform->addElement('html','<center><a
            href="'.$CFG->wwwroot.'/blocks/timetracker/timesheet.php?id='.
            $this->courseid.'">Generate Monthly Timesheet</a></center>');
        }
        */

        $mform->addElement('header', 'general', 'Report time period'); 
        $mform->addElement('hidden','id', $this->courseid);
        $mform->addElement('hidden','userid', $this->userid);
        $mform->addElement('hidden','sesskey', sesskey());

        if($this->reportstart == 0 || $this->reportend == 0){
            $starttime = usergetdate($now);
            $starttime_mid = make_timestamp($starttime['year'], 
                $starttime['mon'] - 1, $starttime['mday']);
            $this->reportstart = $starttime_mid;

            $endtime = usergetdate($now);
            $endtime_mid = make_timestamp($endtime['year'], 
                $endtime['mon'], $endtime['mday'], 23, 59, 59);
            $this->reportend = $endtime_mid;
        } 

        $mform->addElement('date_time_selector', 'reportstart',
            get_string('startreport','block_timetracker'),
            array('optional'=>false, 'step'=>1));
        $mform->setDefault('reportstart',$this->reportstart);
        $mform->addHelpButton('reportstart', 'startreport', 'block_timetracker');

        $mform->addElement('date_time_selector', 'reportend', 
            get_string('endreport','block_timetracker'),
            array('optional'=>false, 'step'=>1));
        $mform->setDefault('reportend',$this->reportend);
		$mform->addHelpButton('reportend','endreport','block_timetracker');

        $mform->addElement('submit', 'datechange', 'Get work units');


        $baseurl = $CFG->wwwroot.'/blocks/timetracker'; 

        //************** PENDING WORK UNITS SECTION ****************//
        //which workers to see?
        /*
        $endtime = $this->reportend + ((60*60*23)+60*59); //23:59
        $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_pending WHERE timein BETWEEN '.
            $this->reportstart.' AND '.$endtime.' ';
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses

            //$pendingunits = $DB->get_records_sql($sql);
            //bad bad

        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course

            $sql .= 'AND courseid='. $this->courseid;

        } else { //if ($this->userid != 0) //specific user, this course
            
            $sql .= 'AND userid='.$this->userid. ' AND courseid='. $this->courseid;

        }
        $sql .= ' ORDER BY timein DESC';
        $pendingunits = $DB->get_records_sql($sql);


        $mform->addElement('header', 'general', 'Pending work units');
        if(!$pendingunits){ //if they don't have them.
            $mform->addElement('html','No pending work units<br />');
        } else { //if they do have pending
            $mform->addElement('html', 
                '<table align="center" border="1" cellspacing="10px" '.
                'cellpadding="5px" width="95%">');
        
            $headers = '<tr>';
            if($this->userid == 0) $headers .= '<td style="font-weight: bold">Name</td>';
            $headers .= '<td style="font-weight: bold">Time in</td>
                        <td style="font-weight: bold; text-align: center">Action</td>';
            $headers .='</tr>';

            $mform->addElement('html',$headers);


            foreach($pendingunits as $pending){
                $row='<tr>';
                if($this->userid == 0){
                    $row .='<td><a href="'.$baseurl.
                        '/reports.php?id='.$this->courseid.'&userid='.
                            $pending->userid.'">'.$workers[$pending->userid]->lastname.', '.
                        $workers[$pending->userid]->firstname.'</a></td>';
                }
                $row.='<td>'.userdate($pending->timein,
                    get_string('datetimeformat','block_timetracker')).'</td>';



                $urlparams['id'] = $pending->courseid;
                $urlparams['userid'] = $pending->userid;
                $urlparams['sesskey'] = sesskey();
                $urlparams['unitid'] = $pending->id;

                $urlparams['clockout'] = 1;

                $cout = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php',
                    $urlparams);
                $clockouticon = new pix_icon('clock_stop','Clock out','block_timetracker');
                $clockoutaction = $OUTPUT->action_icon($cout, $clockouticon);

                unset($urlparams['clockout']);

                $deleteurl = new moodle_url($baseurl.'/deletepending.php', $urlparams);
                $deleteicon = new pix_icon('clock_delete',
                    get_string('delete'),'block_timetracker');
                $deleteaction = $OUTPUT->action_icon(
                    $deleteurl, $deleteicon, 
                    new confirm_action('Are you sure you want to delete this '.
                    ' pending work unit?'));


                if($canmanage){
                    $urlparams['ispending'] = true;
                    $editurl = new moodle_url($baseurl.'/editunit.php', $urlparams);
                    $editaction = $OUTPUT->action_icon($editurl, 
                        new pix_icon('clock_edit', get_string('edit'),'block_timetracker'));
                    unset($urlparams['ispending']);
                }

                if($canmanage){
                    $actions = $clockoutaction.' '.$editaction.' '.$deleteaction;
                } else {
                    $actions = $clockoutaction.' '.$deleteaction;
                }

                $row .= '<td style="text-align: center">'.$actions.'</td>';
                $row .= '</tr>';
                $mform->addElement('html',$row);
            }
            $mform->addElement('html','</table>');
        } 
        */

        //************** WORK UNITS SECTION ****************//

        //TODO - courseid is never really 0, unless we're viewing from main page.
        //which workers to see?

        $workerdesc = 'Completed work units';
        //$endtime = strtotime ('+1 day ', $this->reportend) - 1;
        //$endtime = $this->reportend + ((60*60*23)+60*59); //23:59

        $endtime = strtotime('+59 seconds', $this->reportend);
        $sql = 'SELECT * FROM '.$CFG->prefix.'block_timetracker_workunit WHERE (timeout '.
            'BETWEEN '.$this->reportstart.' AND '.$endtime.' '.
            'OR timein BETWEEN '.$this->reportstart.' AND '.$endtime.') ';
        if($this->userid==0 && $this->courseid == 0){ //see all workers, all courses
            $units = $DB->get_records_sql($sql);
        } else if ($this->userid==0 && $this->courseid!=0){ //see all workers, this course
            $sql .= 'AND courseid='.$this->courseid;
            $workerdesc .= ' for all workers';
        } else { //specific user, this course
            if($canmanage){
                $allurl = new moodle_url($baseurl.'/reports.php',
                    array('id'=>$this->courseid,
                    'userid'=>0,
                    'repstart'=>$this->reportstart,
                    'repend'=>$this->reportend));
                $workerdesc .= ' for '.$user->firstname.' '.
                    $user->lastname.' [ '.$OUTPUT->action_link($allurl, 'see all').' ]';
            }
            $sql .= ' AND courseid='.$this->courseid.' AND userid='.$this->userid;
        }

        $sql .= ' ORDER BY timein DESC';
        //error_log($sql);
        $units = $DB->get_records_sql($sql);

        $mform->addElement('header', 'general', $workerdesc); 
        if(!$units){ //if they don't have them.
            $mform->addElement('html','No completed work units<br />');
        } else { //if they do have some
            $mform->addElement('html', '
                <table align="center" cellspacing="10px" 
                cellpadding="5px" width="95%" style="border: 1px solid #000;" >');
        
            $headers = '<tr>';
            if($canmanage){
                $headers .='<td style="font-weight: bold">Name</td>';

            }
            $headers .=
                    '<td style="font-weight: bold; text-align: center">Time in/out</td>'.
                    //'<td style="font-weight: bold; text-align: center">Time out</td>'.
                    '<td style="font-weight: bold; text-align: center">Elapsed</td>';
            $headers .='<td style="font-weight: bold; text-align: center">'.
                get_string('action').'</td>';
            $headers .='</tr>';

            $mform->addElement('html',$headers);

            $total = 0;

            //print_object($workers);
            foreach($units as $unit){
                
                $row='<tr>';
                if($this->userid == 0){
                    $userurl = new moodle_url($baseurl.'/reports.php');
                    $userurl->params(array(
                        'id'=>$this->courseid,
                        'userid'=>$unit->userid,
                        'repstart'=>$this->reportstart,
                        'repend'=>$this->reportend));
                    $row .='<td>'.$OUTPUT->action_link($userurl,
                        $workers[$unit->userid]->lastname.', '.
                        $workers[$unit->userid]->firstname).
                        '</td>';
                } else if($canmanage){
                    $row .='<td>'.
                        $user->lastname.', '.
                        $user->firstname.'</td>';
                }

                //print them here? print all, w/br?
                if($unit->timein < $this->reportstart ||
                    $unit->timeout > $endtime){

                    /*
                    error_log("rstart: ".
                        userdate($this->reportstart, 
                        '%m/%d/%y %I:%M:%S %p'));
                    error_log("utin: ".
                        userdate($unit->timein,
                        '%m/%d/%y %I:%M:%S %p'));
                    error_log("rend: ".
                        userdate($this->reportend,
                        '%m/%d/%y %I:%M:%S %p'));
                    error_log("utout: ".
                        userdate($unit->timeout,
                        '%m/%d/%y %I:%M:%S %p'));
                    */

                    $singleurl = new moodle_url($baseurl.'/reports.php');

                    $singleurl->params(array(
                        'id'=>$this->courseid,
                        'userid'=>$unit->userid,
                        'repstart'=>($unit->timein - 1),
                        'repend'=>($unit->timeout +1)));

                    $splitunits = split_unit($unit);
                    $row.='<td style="text-align: center">';
                    foreach($splitunits as $subunit){
                        $straddle = false;
                        if($subunit->timein < $this->reportstart || 
                            $subunit->timeout > $endtime){
                            $straddle = true;
                        }

                        

                        /*
                        if($straddle){
                            error_log("rstart: ".
                                userdate($this->reportstart, 
                                '%m/%d/%y %I:%M:%S %p'));
                            error_log('in: '.
                                userdate($subunit->timein, 
                                get_string('datetimeformat', 'block_timetracker')));
                            error_log("rend: ".
                                userdate($endtime,
                                '%m/%d/%y %I:%M:%S %p'));
                            error_log('out: '.
                                userdate($subunit->timeout, 
                                get_string('datetimeformat', 'block_timetracker')));
                        }
                        */

                        if(!$straddle){
                            $row.= userdate($subunit->timein,
                                get_string('datetimeformat','block_timetracker'));
                            $row.= ' to '.userdate($subunit->timeout,
                                get_string('datetimeformat','block_timetracker'));
                            $row .='<br />';
                        } else {
                            if($subunit->timein > $this->reportstart){

                                if($subunit->timeout < $endtime)
                                    $end = $subunit->timeout;
                                 else 
                                    $end = $endtime;

                                $row .= userdate($subunit->timein,
                                    get_string('datetimeformat', 'block_timetracker'));
                                $row .= ' to '.userdate($end,
                                    get_string('datetimeformat', 'block_timetracker'));
                                $row .= '<br />';

                            } else {
                                if($subunit->timein > $this->reportstart)
                                    $start = $subunit->timein;
                                else
                                    $start = $this->reportstart;

                                $row .= userdate($start,
                                    get_string('datetimeformat', 'block_timetracker'));
                                $row .= ' to '.userdate($subunit->timeout,
                                    get_string('datetimeformat', 'block_timetracker'));
                                $row .= '<br />';
                            }
                        }
                    }

                    $row .= 'Partial unit. ';
                    $row .= $OUTPUT->action_link($singleurl, '[ view complete work unit ]');
                    $row .= '</td>'."\n";
    
                    $row .= '<td style="text-align: center">';
                    foreach($splitunits as $subunit){
                        $straddle = false;
                        if($subunit->timein < $this->reportstart || 
                            $subunit->timeout > $endtime){
                            $straddle = true;
                        }
                        
                        if(!$straddle){
                            $currelapsed = $subunit->timeout - $subunit->timein;  
                            $hrs = get_hours($currelapsed, $subunit->courseid);

                            $total += $hrs;

                            $row .= $hrs.' hour(s)';
                            $row .= '<br />';
                        } else {

                            if($subunit->timein > $this->reportstart){
                                if($subunit->timeout < $endtime)
                                    $end = $subunit->timeout;
                                else
                                    $end = $endtime;

                                $currelapsed = $end - $subunit->timein;

                            } else {
                                if($subunit->timein > $this->reportstart)
                                    $start = $subunit->timein;
                                else
                                    $start = $this->reportstart;

                                $currelapsed = $subunit->timeout - $start;
                            }

                            $hrs = get_hours($currelapsed, $subunit->courseid);
                            $total += $hrs;

                            $row .= $hrs.' hour(s)';
                            $row .= '<br />';
                        }
    
                    }
                    $row .= '</td>'."\n";
                } else { //unit occurs all in one repstart-repend
                    $row.='<td style="text-align: center">';
                    $row.= userdate($unit->timein,
                        get_string('datetimeformat','block_timetracker'));
                    $row.= ' to '.userdate($unit->timeout,
                        get_string('datetimeformat','block_timetracker'));
                    $row .= '</td>';
                    
                    $row .= '<td style="text-align: center">';

                    $currelapsed = $unit->timeout - $unit->timein;  
                    $hrs = get_hours($currelapsed, $unit->courseid);

                    $total += $hrs;

                    $row .= $hrs.' hour(s)</td>';
    
                }

                if($canmanage){

                    $urlparams['id'] = $unit->courseid;
                    $urlparams['userid'] = $unit->userid;
                    $urlparams['sesskey'] = sesskey();
                    $urlparams['unitid'] = $unit->id;

                    $unitdateinfo = usergetdate($unit->timein);

                    if ($unit->timesheetid && !$unit->submitted){
                        //show greyed out icons and no URL
                        $row .= '<td style="text-align: center">'.
                            html_writer::empty_tag('img', 
                                array('src' => 
                                $CFG->wwwroot.'/blocks/timetracker/pix/wait.png', 
                                'class' => 'icon')).
                            '</td>';
                    } else if ($unit->timesheetid && $unit->submitted) {
                        $row .= '<td style="text-align: center">'.
                            html_writer::empty_tag('img', 
                                array('src' => 
                                $CFG->wwwroot.'/blocks/timetracker/pix/certified.png', 
                                'class' => 'icon')).
                            '</td>';
                    } else if(!$unit->canedit){
                        
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
                    } else {
                 
            
                        $deleteurl = new moodle_url($baseurl.'/deleteworkunit.php', 
                            $urlparams);
                        $deleteicon = new pix_icon('clock_delete',
                            get_string('delete'),'block_timetracker');
                        $deleteaction = $OUTPUT->action_icon(
                            $deleteurl, $deleteicon, 
                            new confirm_action(
                            'Are you sure you want to delete this work unit?'));

                        //error_log (($now - $unit->timein)-(86400*35));
                        $editurl = new
                            moodle_url($baseurl.'/editunit.php', $urlparams);
                        $editurl->remove_params('sesskey');
                        $editaction = $OUTPUT->action_icon($editurl, 
                            new pix_icon('clock_edit', 
                            get_string('edit'),'block_timetracker'));
        
                        $row .= '<td style="text-align: center">'.$editaction . ' '.
                            $deleteaction.'</td>';
                    }
    
                } else {
                    $urlparams['id'] = $this->courseid;
                    $urlparams['userid'] = $unit->userid;
                    $urlparams['sesskey'] = sesskey();
                    $urlparams['unitid'] = $unit->id;

                    $unitdateinfo = usergetdate($unit->timein);
                    if ($unit->timesheetid && !$unit->submitted){
                        //show greyed out icons and no URL
                        $row .= '<td style="text-align: center">'.
                            html_writer::empty_tag('img', 
                                array('src' => 
                                $CFG->wwwroot.'/blocks/timetracker/pix/wait.png', 
                                'class' => 'icon')).
                            '</td>';
                    } else if ($unit->timesheetid && $unit->submitted) {
                        $row .= '<td style="text-align: center">'.
                            html_writer::empty_tag('img', 
                                array('src' => 
                                $CFG->wwwroot.'/blocks/timetracker/pix/certified.png', 
                                'class' => 'icon')).
                            '</td>';
                    } else if(!$unit->canedit){
                        
                        //show greyed out icons and no URL
                        $alertaction = 
                            html_writer::empty_tag('img', 
                            array('src' => 
                            $CFG->wwwroot.'/blocks/timetracker/pix/alert_bw.gif', 
                            'class' => 'icon'));
                    }else {
                    
                        $alerturl = new moodle_url($baseurl.'/alert.php', $urlparams);
                        $alerticon = new pix_icon('alert', 'Alert Supervisor of Error',
                            'block_timetracker');
                        $alertaction = $OUTPUT->action_icon($alerturl, $alerticon);
                        $row .='<td style="text-align:center">'.$alertaction.'</td>';

                    }

                }
                $row .= '</tr>';
                $mform->addElement('html',$row);
            }
            $finalrow = '<tr>';
            if($canmanage){
                $finalrow .= '<td>&nbsp;</td>';
            }
            $finalrow.=
                    '<td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center; border-top: 1px solid black"><b>Total: 
                    </b>'.
                    round($total, 3).' hour(s)</td>
                    <td>&nbsp;</td></tr></table>';
            $mform->addElement('html',$finalrow);

        } 
    
    }

    function validation ($data){
        $errors = array();
        if($data['reportstart'] > $data['reportend']){
            $errors['reportstart'] = 'Start cannot be before end';    
        }

        return $errors;
        
    }
}
