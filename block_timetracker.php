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
 * This block will display a summary of hours and arnings for the worker.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

 require_once($CFG->dirroot.'/blocks/timetracker/lib.php');
 require_once('lib.php');

 class block_timetracker extends block_base {

    function init() {
        $this->title = get_string('blocktitle', 'block_timetracker');
    }

    function preferred_width() {
        return 210;
    }

    function get_content() {
        global $CFG, $DB, $USER, $OUTPUT, $COURSE;
        //$this->config = get_timetracker_config($COURSE->id);
        $clockin = optional_param('clockin', 0,PARAM_INTEGER);
        $clockout = optional_param('clockout',0, PARAM_INTEGER);
        $courseid = $COURSE->id;
        $worker = $DB->get_record('block_timetracker_workerinfo', 
            array('mdluserid'=>$USER->id,'courseid'=>$COURSE->id));
        
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        //echo $this->config->block_timetracker_default_max_earnings;
        if(!isset($this->config)){
            if (has_capability('block/timetracker:manageworkers', $this->context)) {
                $this->content->text='TimeTracker block must be configured before used.';
            } else {
                $this->content->text=
                    '<span style="color: red">
                    TimeTracker is not yet configured. Contact your supervisor
                    with this error</span>';
            }
            return $this->content;

        }
        $baseurl = $CFG->wwwroot.'/blocks/timetracker';
        
        $canmanage = false;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $canmanage = true;
        }
        
        $canview = false;
        if(has_capability('block/timetracker:viewonly', $this->context)){
            $canview = true;
        }

        if($canmanage || $canview){
            //if config is setup to show term hours/earnings AND
            //terms are not configured, provide a link to the terms page.
            if($this->config->block_timetracker_show_term_hours == 1 ||
                $this->config->block_timetracker_show_term_earnings == 1){

                $numterms = $DB->count_records('block_timetracker_term',
                    array('courseid'=>$COURSE->id));
                if($numterms == 0){
                    $this->content->text .= '<b><center><a style="color: red" href="'.
                        $CFG->wwwroot.'/blocks/timetracker/terms.php?id='.$COURSE->id.
                        '">**Configure terms**</center></b></a>';
                    $this->content->text .= "<br /><br />";
                }
            }

            $hasalerts = has_course_alerts($COURSE->id);
            if(has_capability('moodle/site:config', $this->context)){
                $hasalerts = has_course_alerts($COURSE->id);
            }
            
            $indexparams['id'] = $courseid;
            $this->content->text .= '<div style="text-align: left">';

            //check to see if the supervisor needs to manage
            if($hasalerts && $canmanage){
                $alertsurl = new moodle_url($baseurl.'/managealerts.php', $indexparams);
                $alerticon= new pix_icon('alert','Manage Alerts', 'block_timetracker');
                $alertaction= $OUTPUT->action_icon($alertsurl, $alerticon);
                $this->content->text .= get_alerts_link($COURSE->id, $alerticon, $alertaction);
            }

            $hastimesheets = has_unsigned_timesheets($COURSE->id);
            if(has_capability('moodle/site:config', $this->context)){
                $hastimesheets = has_unsigned_timesheets($COURSE->id);
            }
            
            if($hastimesheets && $canmanage){
                $timesheetsurl = new moodle_url($baseurl.'/supervisorsig.php', $indexparams);
                $timesheetsicon = new pix_icon('alert','Sign Timesheets','block_timetracker');
                $timesheetsaction = $OUTPUT->action_icon($timesheetsurl, $timesheetsicon);
                $this->content->text .= 
                    get_timesheet_link($COURSE->id, $timesheetsicon, $timesheetsaction);
            }

            $index = new moodle_url($baseurl.'/index.php', $indexparams);
            $timeclockdataicon = new pix_icon('manage', 'Manage', 'block_timetracker');
            $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
    
            $this->content->text .= $timeclockdataaction.' '.
                $OUTPUT->action_link($index, 'Main').'<br />';

            $reportsurl = new moodle_url($baseurl.'/reports.php', $indexparams);
            $reportsaction=$OUTPUT->action_icon($reportsurl, new pix_icon('report', 
                'Reports','block_timetracker'));

            $this->content->text .= $reportsaction.' '.
                $OUTPUT->action_link($reportsurl, 'Reports').'<br />';

            $timesheeturl = new moodle_url($baseurl.'/timesheet.php', $indexparams);
            $timesheetaction=$OUTPUT->action_icon($timesheeturl, 
                new pix_icon('date', 'Timesheets','block_timetracker')); 

            $this->content->text .= $timesheetaction.' '.
                $OUTPUT->action_link($timesheeturl, 'Timesheet').'<br />';

            $manageurl = new moodle_url($baseurl.'/manageworkers.php', 
                array('id'=>$COURSE->id));
            $manageaction=$OUTPUT->action_icon($manageurl, 
                new pix_icon('user_group', 'Manage workers', 'block_timetracker')); 

            $this->content->text .= $manageaction.' '.
                $OUTPUT->action_link($manageurl, 'Manage workers').'<br />';


            $numtimeclock = $DB->count_records('block_timetracker_workerinfo',
                array('courseid'=>$courseid, 'timetrackermethod'=>0));

            if($numtimeclock > 0){
                //show anyone who has a pending:
                $pendingunits = $DB->get_records('block_timetracker_pending', 
                    array('courseid'=>$courseid));
                $this->content->text .= "<br />Currently Clocked In:";
                $this->content->text .='<ul>';
                if(!$pendingunits){ 
                    $this->content->text .= '<li>None</li>';
                } else {
                    $workers = $DB->get_records('block_timetracker_workerinfo');
                    foreach ($pendingunits as $pending){
                        $this->content->text .='<li><a href="'.
                            $CFG->wwwroot.'/blocks/timetracker/reports.php?id='.$courseid.
                            '&userid='.$pending->userid.'">'.
                            $workers[$pending->userid]->lastname.', '.
                            $workers[$pending->userid]->firstname.' '.
                            '</a>'.
                            userdate($pending->timein,get_string('timeformat',
                                'block_timetracker')).
                            '</li>'."\n";
                    }
                }                    
            } 

            $this->content->text .='</ul>';

        } else { //worker

            //Worker doesn't exist yet, or has missing data
            if (!$worker){// || ($worker->address=='0')){
                //print_object($worker);
                $link =
                    '/blocks/timetracker/updateworkerinfo.php?id='.$COURSE->id.
                    '&userid='.$worker->id;
                $action = null; 
                $this->content->text = '<center>';
                $this->content->text .= '<span style="color: red">'.
                    $OUTPUT->action_link($link, 
                    get_string('registerinfo', 'block_timetracker'), $action);
                $this->content->text .= '</span></center>';
                return $this->content;
            } else {
                  
                /*
                if($worker->active == 0){
                    $this->content->text = get_string('notactiveerror','block_timetracker');
                    return $this->content;
                }           
                */
            
                // Implement Icons - Timeclock Method
                if($worker->timetrackermethod == 0){
                    $ttuserid = $worker->id;
    
                    $pendingrecord = $DB->get_record('block_timetracker_pending', 
                        array('userid'=>$ttuserid,'courseid'=>$courseid));
    
                    $urlparams['userid']=$ttuserid;
                    $urlparams['id']=$courseid;
                    $indexparams['userid'] = $ttuserid;
                    $indexparams['id'] = $courseid;
                    $index = new moodle_url($baseurl.'/index.php', $indexparams);

                    if(!$pendingrecord){ 
    
                        $urlparams['clockin']=1;
    
                        $timeclockurl = new moodle_url($baseurl.'/timeclock.php', $urlparams);
                        $timeclockicon = new pix_icon('clock_play','Clock in', 
                            'block_timetracker');
                        $timeclockdesc=' Clock-in';

                    } else {

                        $urlparams['clockout']=1;
                        $urlparams['ispending']=true;
                        $urlparams['unitid']=$pendingrecord->id;

                        $timeclockurl = new moodle_url($CFG->wwwroot.
                            '/blocks/timetracker/timeclock.php', $urlparams);
                        $timeclockicon = new pix_icon('clock_stop','Clock in', 
                            'block_timetracker');

                        $timeclockdesc=' Clock-out';

                        $alertlink= new moodle_url($baseurl.'/alert.php', $urlparams);

                        $alerticon= new pix_icon('alert','Alert Supervisor of Error',
                            'block_timetracker');
                        $alertaction=
                            $OUTPUT->action_icon($alertlink, $alerticon);


                    }
    
                    //Icons
                    $timeclockaction = $OUTPUT->action_icon($timeclockurl, $timeclockicon);
            
                    $timeclockdataicon = new pix_icon('manage', 'Manage', 
                        'block_timetracker');
                    $timeclockdataaction = $OUTPUT->action_icon($index, 
                        $timeclockdataicon);
    
                    $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',
                        $indexparams);
                    $editurl->params(array('mdluserid'=>$USER->id));
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                        get_string('edit'),'block_timetracker'));

                    $reportsurl = new moodle_url($baseurl.'/reports.php', $indexparams);
                    $reportsurl->params(array('userid'=>$worker->id));
                    $reportsaction=$OUTPUT->action_icon($reportsurl, 
                        new pix_icon('report', 'Reports','block_timetracker'));

                    $timesheeturl = new moodle_url($baseurl.'/timesheet.php', 
                        $indexparams);
                    $timesheeturl->params(array('userid'=>$worker->id));
                    $timesheetaction=$OUTPUT->action_icon($reportsurl, 
                        new pix_icon('date', 'Timesheets','block_timetracker')); 
            
                    $this->content->text .= '<div style="text-align: left">';
                    $this->content->text .= 
                        $timeclockdataaction.' <a href="'.$index.'">Main</a><br />'.
                        $timeclockaction.' <a href="'.$timeclockurl.
                        '">'.$timeclockdesc.'</a><br />'.
                        $reportsaction. ' <a href="'.$reportsurl.'">Reports</a><br />'.
                        $timesheetaction.' <a href="'.$timesheeturl.'">Timesheets</a><br />'.
                        $editaction.' <a href="'.$editurl.'"> Edit my info</a><br /><br />';
    
                    $this->content->text .= '</div>';

                    if($pendingrecord){ 
                             
                        $pendingtimestamp= $DB->get_record('block_timetracker_pending', 
                            array('userid'=>$ttuserid,'courseid'=>$courseid));
                        $this->content->text .= 'Clock-in:<br />'.$alertaction.' '.
                            userdate($pendingtimestamp->timein,
                            get_string('datetimeformat','block_timetracker')).'<br />';
                    }

                    $this->content->text .= '<hr>';

                } else if($worker->timetrackermethod == 1){ //Hourlog Method
                    $ttuserid = $worker->id;

                    $urlparams['userid']=$ttuserid;
                    $urlparams['id']=$courseid;
                    $indexparams['userid'] = $ttuserid;
                    $indexparams['id'] = $courseid;
                    
                    $hourlogurl = new moodle_url($CFG->wwwroot.
                        '/blocks/timetracker/hourlog.php', $urlparams);
                    $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', 
                        $indexparams);
    
                    // Clock In Icon
                    $this->content->text .= '<div style="text-align: left">';
                    $clockinicon = new pix_icon('clock_add','Add work unit', 
                        'block_timetracker');
                    $clockinaction = $OUTPUT->action_icon($hourlogurl, $clockinicon);
        
                    $timeclockdataicon = new pix_icon('manage', 
                        'Manage', 'block_timetracker');
                    $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);

                    $reportsurl = new moodle_url($baseurl.'/reports.php', $indexparams);
                    $reportsurl->params(array('userid'=>$worker->id));
                    $reportsaction=$OUTPUT->action_icon($reportsurl, 
                        new pix_icon('report', 'Reports','block_timetracker'));
    
                    $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',$indexparams);
                    $editurl->params(array('mdluserid'=>$USER->id));
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                        get_string('edit'),'block_timetracker'));
    
                    $timesheeturl = new moodle_url($baseurl.'/timesheet.php', 
                        $indexparams);
                    $timesheeturl->params(array('userid'=>$worker->id));
                    $timesheetaction=$OUTPUT->action_icon($reportsurl, 
                        new pix_icon('date', 'Timesheets', 'block_timetracker')); 

                    $this->content->text .= '<div style="text-align: left">';
                    $this->content->text .= 
                        $timeclockdataaction.' <a href="'.$index.'">Main</a><br />'.
                        $clockinaction.' <a href="'.$hourlogurl.'">Add work unit</a><br />'.
                        $reportsaction. ' <a href="'.$reportsurl.'">Reports</a><br />'.
                        $timesheetaction.' <a href="'.$timesheeturl.'">Timesheets</a><br />'.
                        $editaction.' <a href="'.$editurl.'"> Edit my info</a><br /><br />';
    
                    $this->content->text .= '</div>';
                    $this->content->text .= '<hr>';
                }

                $stats = get_worker_stats($ttuserid, $COURSE->id);

                //calculate if this user is within $50 of reaching maxtermearnings
                $closetomax = false;
                if($worker->maxtermearnings > 0 &&  
                    ($stats['termearnings'] > $worker->maxtermearnings || 
                    ($worker->maxtermearnings - $stats['termearnings']) <= 50)){
                    $closetomax = true; 
                }

                $this->content->text .= '<br /><span style="font-weight: bold;">'.
                    '*Official term earnings: $'.
                    number_format(get_official_earnings_this_term($ttuserid, $COURSE->id), 2).
                    '</span><br /><br />';
                $this->content->text .= '<span style="font-size: x-small">*This represents
                hours for which you have already been paid</span>';
                $this->content->text .= '<hr />';

                if($this->config->block_timetracker_show_month_hours ||
                    $this->config->block_timetracker_show_term_hours ||
                    $this->config->block_timetracker_show_ytd_hours ||
                    $this->config->block_timetracker_show_total_hours) {

                    $this->content->text .= '<span style="font-weight: bold">'.
                        get_string('hourstitle','block_timetracker').'</span>';


					if ($this->config->block_timetracker_show_month_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
                        $this->content->text .= $stats['monthhours'];
                    }
					
                    if ($this->config->block_timetracker_show_term_hours &&
                            $worker->maxtermearnings > 0){

						$this->content->text .= '<br />';
                        if($closetomax){
                            $this->content->text .= 
                                '<span style="color: red; font-weight:bold">';
                        }
						$this->content->text .= get_string('totalterm', 'block_timetracker');
                        $this->content->text .= $stats['termhours']; 
                        
					    $remearnings = $worker->maxtermearnings - $stats['termearnings'];

                        $remhours = $remearnings/$worker->currpayrate;

                        if($remhours < 0) $remhours = 0;

                        $this->content->text .= '<br />';
                        $this->content->text .= get_string('remaining', 'block_timetracker');
                        $this->content->text .= round($remhours, 2);
                        
                        if($closetomax){
                            $this->content->text .= '</span>';
                        }
				    }	
            
					if ($this->config->block_timetracker_show_ytd_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalytd', 'block_timetracker');
                        $this->content->text .= $stats['yearhours'];
					}
					
                    if ($this->config->block_timetracker_show_total_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('total', 'block_timetracker');
                        $this->content->text .= $stats['totalhours'];
					}
                
      
                    
                    
				if ($this->config->block_timetracker_show_month_earnings ||
				    $this->config->block_timetracker_show_term_earnings ||
				    $this->config->block_timetracker_show_ytd_earnings ||
				    $this->config->block_timetracker_show_total_earnings) {


					$this->content->text .= '<br /><br />';
					$this->content->text .= '<span style="font-weight: bold">'.
                        get_string('earningstitle','block_timetracker').'</span>';


					if ($this->config->block_timetracker_show_month_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= 
                            get_string('totalmonth', 'block_timetracker');
                        $this->content->text .= '$'.
                            $stats['monthearnings'];
					}
                    
					if ($this->config->block_timetracker_show_term_earnings &&
                            $worker->maxtermearnings > 0){
						$this->content->text .= '<br />';
                        if($closetomax){
                            $this->content->text .= 
                            '<span style="color: red; font-weight:bold">';
                        }
						$this->content->text .= 
                            get_string('totalterm', 'block_timetracker');
                        $this->content->text .= '$'.
                            $stats['termearnings'];
					    
                        $remearnings = $worker->maxtermearnings - $stats['termearnings'];
                        if($remearnings < 0) $remearnings = 0;
                        $this->content->text .= '<br />';
                        $this->content->text .= get_string('remaining', 'block_timetracker');
                        $this->content->text .= '$' .round($remearnings, 2);
                        
                        if($closetomax){
                            $this->content->text .= '</span>';
                        }
					}
                    
					if ($this->config->block_timetracker_show_ytd_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= 
                            get_string('totalytd', 'block_timetracker');
                        $this->content->text .= '$'.
                            $stats['yearearnings'];
					}
                    
					if ($this->config->block_timetracker_show_total_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('total', 'block_timetracker');
                        $this->content->text .= '$'.
                            $stats['totalearnings'];
					}
				}

                $ttuserid = $worker->id;
                    
                if($clockin == 1) {
                    //protect against refreshing a 'clockin' screen
                    $pendingrecord= $DB->record_exists('block_timetracker_pending',
                        array('userid'=>$ttuserid,'courseid'=>$courseid));

                        if(!$pendingrecord){
                            $cin = new stdClass();
                            $cin->userid = $ttuserid;
                            $cin->timein = time();
                            $cin->courseid = $courseid;
                            $DB->insert_record('block_timetracker_pending', $cin);
                        }

                    } else if ($clockout == 1){
                        $cin = $DB->get_record('block_timetracker_pending', 
                            array('userid'=>$ttuserid,'courseid'=>$courseid));
                        if($cin){

                            $cin->timeout = time();
                            $cin->lastedited = time();
                            $cin->lasteditedby = $USER->id;

                            unset($cin->id);

                            $worked = add_unit($cin);
                            if($worked){
                                $DB->delete_records('block_timetracker_pending', 
                                    array('userid'=>$ttuserid,'courseid'=>$courseid));
                            } else {
                                print_error('couldnotclockout', 'block_timetracker', 
                                    $CFG->wwwroot.'/blocks/timetracker/timeclock.php?id='.
                                    $courseid.'&userid='.$ttuserid);
                            }
                        }
                    }
                }
		    }
        }
        $this->content->text .= '</div>';
	    return $this->content;
    }


    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }
   
   /**
     * do we need to do anything here?
     * @return boolean true if all feeds were retrieved succesfully
     */
    function cron() {
        global $CFG, $DB; 

        /*
        $lastcron = $DB->get_field('block', 'lastcron', 
            array('name'=>$this->title));
        
        $numemails = 0;
        $sql = 'SELECT DISTINCT courseid from '.$CFG->prefix.
            'block_timetracker_timesheet WHERE supervisorsignature=0';
        
        $courseids = $DB->get_records_sql($sql);
        foreach($courseids as $course){
            $numemails += send_timesheet_reminders_to_supervisors($course->courseid);    
        }

        mtrace('Number of emails sent: '.$numemails.'<br />');

        $DB->set_field('block', 'lastcron', time(),
            array('name'=>$this->title));
        */


        /*

        global $CFG, $DB;

        $days = 7;
        $now = time();
        $limit = $now - (7 * 60 * 60 * 24);
        $thistime = usergetdate($now);
        $monthinfo = get_month_info ($thistime['month'], $thistime['year']);
        
        $sql = 'SELECT DISTINCT courseid from mdl_block_timetracker_alertunits '.
            'WHERE alerttime < '.$limit.
            ' AND alerttime between '.$monthinfo['firstdaytimestamp'] .' AND '.
            $now.'  ORDER BY courseid, alerttime ASC';
        
        $courses = $DB->get_records_sql($sql);

        $emails = 0;
        
        foreach($courses as $course){
        
            $id = $course->courseid;
            $courseinfo = $DB->get_record('course', array('id'=>$id));
            $context = get_context_instance(CONTEXT_COURSE, $id);
            $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
            //print_object($teachers);
        
            $coursealerts = $DB->get_records('block_timetracker_alertunits', array(
                'courseid'=>$id));
        
        
            $num = sizeof($coursealerts);
            mtrace($num.' alerts for course '.$courseinfo->shortname);
        
            if($num == 1){
                $body = "Hello!\n\nYou have $num work unit alert ".
                    "that requires your attention for $courseinfo->shortname.\n\n";
                $subj = $num.' Work Unit Alerts for '.$courseinfo->shortname;
            } else {
                $body = "Hello!\n\nYou have $num work unit alerts ".
                    "that require your attention for $courseinfo->shortname.\n\n";
                $subj = $num.' Work Unit Alert(s) for '.$courseinfo->shortname;
            }

            $body.= "To visit the TimeTracker Alerts page, either click the below ".
                "link or copy/paste it into your browser window.\n\n".
                $CFG->wwwroot.'/blocks/timetracker/managealerts.php?id='.$id. 
                "\n\n".
                "Thanks for your timely attention to this matter";
        
            $body_html = format_text($body);
            $body = format_text_email($body_html, 'FORMAT_HTML');
        
            foreach($coursealerts as $alert){
            
                $alertcoms = $DB->get_records('block_timetracker_alert_com', array(
                    'alertid'=>$alert->id));
                foreach($alertcoms as $com){
                    if(array_key_exists($com->mdluserid, $teachers)){
                        $user = $DB->get_record('user', array('id'=>$com->mdluserid));
                        email_to_user($user, $user, $subj, $body, $body_html);
                        //email_to_user($user, $user, $subj, $body);
                        $emails++;
                    }
                }
        
            }
        }
        mtrace($emails.' reminder emails sent');
        */
    }


    /**
    * override the load instance to use our config tables rather than theirs;
    */
    function _load_instance($instance, $page){
        parent::_load_instance($instance, $page);
        global $COURSE;

        $config = get_timetracker_config($COURSE->id);
        if($config){
            $myconfig = new stdClass();
            foreach($config as $key=>$value){
                $key = 'block_timetracker_'.$key;
                $myconfig->$key = $value;
            }
            //print_object($myconfig);
            $this->config = $myconfig;
        }

    }

    /**
    * Override the instance_config_save method
    */
    function instance_config_save($data, $nolongerused = false){
        parent::instance_config_save($data, $nolongerused);
        global $DB, $COURSE;
        foreach ($data as $name => $value){
            $rec = $DB->get_record('block_timetracker_config',
                array('courseid'=>$COURSE->id,'name'=>$name));

            $conf = new stdClass;
            $conf->name = $name;
            $conf->value = $value;
            $conf->courseid= $COURSE->id;

            if($rec){
                $conf->id = $rec->id;
                $DB->update_record('block_timetracker_config',$conf);
            } else {
                $DB->insert_record('block_timetracker_config',$conf);
            }

        }
    }

    //
    function instance_delete() {
        //remove the necessary data
        global $DB, $COURSE;
        $DB->delete_records('block_timetracker_workerinfo',
            array('courseid'=>$COURSE->id));

        $DB->delete_records('block_timetracker_alertunits',
            array('courseid'=>$COURSE->id));
        $alertunits = $DB->get_records('block_timetracker_alertunits',
            array('courseid'=>$COURSE->id));
        if($alertunits){
            foreach ($alertunits as $au){
                $DB->delete_records('block_timetracker_alert_com',
                    array('alertid'=>$au->id));
            }
            $DB->delete_records('block_timetracker_alertunits',
                array('courseid'=>$COURSE->id));
        }

        $DB->delete_records('block_timetracker_workunit',
            array('courseid'=>$COURSE->id));

        $DB->delete_records('block_timetracker_pending',
            array('courseid'=>$COURSE->id));

        $DB->delete_records('block_timetracker_term',
            array('courseid'=>$COURSE->id));

        $DB->delete_records('block_timetracker_config',
            array('courseid'=>$COURSE->id));
    }
}
