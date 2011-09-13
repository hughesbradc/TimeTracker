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
        //error_log("in block_tt and $COURSE->id");
        //error_log("in block_tt and cid is $courseid");
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
                    'TimeTracker is not yet configured. Contact your supervisor
                    with this error';
            }
            return $this->content;

        }
        $baseurl = $CFG->wwwroot.'/blocks/timetracker';
        if (has_capability('block/timetracker:manageworkers', $this->context)) {

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

            $hasalerts = has_alerts($USER->id,$COURSE->id);
            if(has_capability('moodle/site:config',$this->context)){
                $hasalerts = has_course_alerts($COURSE->id);
            }

            //check to see if the supervisor needs to manage
            if($hasalerts){
                $this->content->text .= '<b><center><a style="color: red" href="'.
                    $CFG->wwwroot.'/blocks/timetracker/managealerts.php?id='.$COURSE->id.
                    '">**Manage Alerts**</center></b></a>';
                $this->content->text .= "<br /><br />";
            }

            $indexparams['id'] = $courseid;
            $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', 
                $indexparams);
            
            $this->content->text .= '<div style="text-align: center">';
            $timeclockdataicon = new pix_icon('manage', 'Manage', 'block_timetracker');
            $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
    
            $this->content->text .= $timeclockdataaction.'</div><br />';
            
            $this->content->text .= '<div style="text-align: left">';

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
            } else {
               $this->content->text .= '<center>';
               $this->content->text .= 'Click the icon above to manage/view worker data'; 
               $this->content->text .= '</center>';
            }
            $this->content->text .='</ul>';

        } else {

            //Worker doesn't exist yet, or has missing data
            if (!$worker || ($worker->address=='0')){
                //print_object($worker);
                $link =
                    '/blocks/timetracker/updateworkerinfo.php?id='.$COURSE->id.
                    '&mdluserid='.$USER->id;
                $action = null; 
                $this->content->text = '<center>';
                $this->content->text .= $OUTPUT->action_link($link, 
                    get_string('registerinfo', 'block_timetracker'), $action,
                    array('style'=>'color: red'));
                $this->content->text .= '</center>';
                return $this->content;
            } else {
                  
                if($worker->active == 0){
                    $this->content->text = get_string('notactiveerror','block_timetracker');
                    return $this->content;
                }           
            
            // Implement Icons - Timeclock Method
            if($worker->timetrackermethod == 0){
                $ttuserid = $worker->id;

                $pendingrecord = $DB->get_record('block_timetracker_pending', 
                    array('userid'=>$ttuserid,'courseid'=>$courseid));

                if(!$pendingrecord){ 

                    $urlparams['userid']=$ttuserid;
                    $urlparams['id']=$courseid;
                    $urlparams['clockin']=1;

                    $indexparams['userid'] = $ttuserid;
                    $indexparams['id'] = $courseid;

                    $link = new moodle_url($baseurl.'/timeclock.php', $urlparams);

                    $index = new moodle_url($baseurl.'/index.php', $indexparams);
                     
                    // Clock In Icon
                    $this->content->text .= '<div style="text-align: center">';
                    //$clockinicon = new pix_icon('clock_in','Clock in', 'block_timetracker');
                    $clockinicon = new pix_icon('clock_play','Clock in', 'block_timetracker');
                    $clockinaction = $OUTPUT->action_icon($link, $clockinicon);
        
                    $timeclockdataicon = new pix_icon('manage', 'Manage', 'block_timetracker');
                    $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);

                    $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',$indexparams);
                    $editurl->params(array('mdluserid'=>$USER->id));
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                        get_string('edit'),'block_timetracker'));
        
                    $this->content->text .= $clockinaction. ' '.$timeclockdataaction.' '.
                        $editaction.'<br />';

                    $this->content->text .= '</div>';

                } else {
                    $urlparams['userid']=$ttuserid;
                    $urlparams['id']=$courseid;
                    $urlparams['clockout']=1;
                    $urlparams['ispending']=true;
                    $urlparams['unitid']=$pendingrecord->id;
                    $indexparams['userid'] = $ttuserid;
                    $indexparams['id'] = $courseid;
                    
                    $link = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php', 
                        $urlparams);
                    $alertlink= new moodle_url($CFG->wwwroot.'/blocks/timetracker/alert.php', 
                        $urlparams);
                    $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', 
                        $indexparams);
        
                    $this->content->text .= '<div style="text-align: center">';
                    
                    $clockouticon = new pix_icon('clock_stop','Clock out','block_timetracker');
                    $clockoutaction = $OUTPUT->action_icon($link, $clockouticon);

                    $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',$indexparams);
                    $editurl->params(array('mdluserid'=>$USER->id));
                    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                        get_string('edit'),'block_timetracker'));
                    
                    $timeclockdataicon = new pix_icon('manage', 'Manage', 'block_timetracker');
                    $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
                    
                    $alerticon= new pix_icon('alert','Alert Supervisor of Error',
                        'block_timetracker');
                    $alertaction= $OUTPUT->action_icon($alertlink, $alerticon);
                    
                    $this->content->text .= $clockoutaction. ' '.
                        $timeclockdataaction. ' '.$alertaction.' '.$editaction.'<br /><br />';
    
                    $this->content->text .= '<b>';
                    $this->content->text .= '</b>';
                    $pendingtimestamp= $DB->get_record('block_timetracker_pending', 
                        array('userid'=>$ttuserid,'courseid'=>$courseid));
                    $this->content->text .= 'Clock in: '.userdate($pendingtimestamp->timein,
                        get_string('datetimeformat','block_timetracker')).'<br />';
                    $this->content->text .= '</div>';
                    $this->content->text .= '<hr>';
                }
            }

            // Implement Icons - Hourlog Method
            if($worker->timetrackermethod == 1){ 
                $ttuserid = $worker->id;

                $urlparams['userid']=$ttuserid;
                $urlparams['id']=$courseid;
                $indexparams['userid'] = $ttuserid;
                $indexparams['id'] = $courseid;
                
                $link = new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php', 
                    $urlparams);
                $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', 
                    $indexparams);

                // Clock In Icon
                $this->content->text .= '<div style="text-align: center">';
                $clockinicon = new pix_icon('clock_add','Add work unit', 'block_timetracker');
                $clockinaction = $OUTPUT->action_icon($link, $clockinicon);
    
                $timeclockdataicon = new pix_icon('manage', 'Manage', 'block_timetracker');
                $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);

                $editurl = new moodle_url($baseurl.'/updateworkerinfo.php',$indexparams);
                $editurl->params(array('mdluserid'=>$USER->id));
                $editaction = $OUTPUT->action_icon($editurl, new pix_icon('user_edit', 
                    get_string('edit'),'block_timetracker'));

                //goes here MJG
    
                $this->content->text .= $clockinaction. ' '.$timeclockdataaction.' '.
                    $editaction.'<br />';
                $this->content->text .= '</div>';
                $this->content->text .= '<hr>';
                }
            }


            if($worker){     
                $stats = get_worker_stats($ttuserid, $COURSE->id);

                //calculate if this user is within $50 of reaching maxtermearnings
                $closetomax = false;
                if($worker->maxtermearnings == 0 ||
                    $stats['termearnings'] > $worker->maxtermearnings || 
                    ($worker->maxtermearnings - $stats['termearnings']) <= 50){
                    $closetomax = true; 
                }

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
					
                    if ($this->config->block_timetracker_show_term_hours){
						$this->content->text .= '<br />';
                        if($closetomax){
                            $this->content->text .= '<span style="color: red">';
                        }
						$this->content->text .= get_string('totalterm', 'block_timetracker');
                        $this->content->text .= $stats['termhours'];
                        if($closetomax){
                            $this->content->text .= '</span>';
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
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
                        $this->content->text .= '$'.$stats['monthearnings'];
					}
                    
					if ($this->config->block_timetracker_show_term_earnings){
						$this->content->text .= '<br />';
                        if($closetomax){
                            $this->content->text .= '<span style="color: red">';
                        }
						$this->content->text .= get_string('totalterm', 'block_timetracker');
                        $this->content->text .= '$'.$stats['termearnings'];
                        if($closetomax){
                            $this->content->text .= '</span>';
                        }
					}
                    
					if ($this->config->block_timetracker_show_ytd_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalytd', 'block_timetracker');
                        $this->content->text .= '$'.$stats['yearearnings'];
					}
                    
					if ($this->config->block_timetracker_show_total_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('total', 'block_timetracker');
                        $this->content->text .= '$'.$stats['totalearnings'];
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
                            $cin->lasteditedby = $ttuserid;

                            unset($cin->id);

                            $worked = $DB->insert_record('block_timetracker_workunit',$cin);
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
        //global $CFG, $DB, $USER;

        //what would we need to do? Send reminders if last day of month?

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
        error_log('in before_delete()');
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
