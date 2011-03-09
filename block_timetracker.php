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

    function get_content() {
        global $CFG, $DB, $USER, $OUTPUT, $COURSE;
        $clockin = optional_param('clockin', 0,PARAM_INTEGER);
        $clockout = optional_param('clockout',0, PARAM_INTEGER);
        $courseid = $COURSE->id;
        $worker = $DB->get_record('block_timetracker_workerinfo', array('userid'=>$USER->id));

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        //echo $this->config->block_timetracker_default_max_earnings;
        if(!isset($this->config)){
            if (has_capability('block/timetracker:manageworkers', $this->context)) {
                $this->content->text='TimeTracker block must be configured before used.';
            } else {
                $this->content->text='TimeTracker is not yet configured. Contact your supervisor
                with this error';
            }
            return $this->content;

        } 
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $this->content->text  = '<a href="'.$CFG->wwwroot.'/blocks/timetracker/manageworkers.php?id='.$COURSE->id.'">Manage Workers</a>';
        } else {
	        $recordexists = $DB->record_exists('block_timetracker_workerinfo', array('userid'=>$USER->id,'courseid'=>$COURSE->id));

            if (!$recordexists){
                $link = '/blocks/timetracker/updateworkerinfo.php?id='.$COURSE->id;
                $action = null; 
                $this->content->text = '<center>';
                $this->content->text .= $OUTPUT->action_link($link, get_string('registerinfo', 'block_timetracker'), $action);
                $this->content->text .= '</center>';
            } else {
                  
                if($worker->active == 0){
                    $this->content->text = get_string('notactiveerror','block_timetracker');
                    //echo $OUTPUT->footer();
                    return $this->content;
                    //die;
                }           
            
            // Implement Icons - Timeclock Method
            if($worker->timetrackermethod == 0){
                $ttuserid = $worker->id;

            $pendingrecord = $DB->record_exists('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
            if(!$pendingrecord){ 
                //$action = null;
                $urlparams['userid']=$ttuserid;
                $urlparams['id']=$courseid;
                $urlparams['clockin']=1;
                $indexparams['userid'] = $ttuserid;
                $indexparams['id'] = $courseid;
                $link = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php', $urlparams);
                $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

                // Clock In Icon
                $this->content->text .= '<div style="text-align: center">';
                $clockinicon = new pix_icon('clock_in','Clock in', 'block_timetracker');
                $clockinaction = $OUTPUT->action_icon($link, $clockinicon);
    
                $timeclockdataicon = new pix_icon('timeclock_data', 'Manage', 'block_timetracker');
                $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
    
                $this->content->text .= $clockinaction. $timeclockdataaction.'<br />';
                $this->content->text .= '</div>';
                }


            if($pendingrecord){ 
                //$action = null;
                $urlparams['userid']=$ttuserid;
                $urlparams['id']=$courseid;
                $urlparams['clockout']=1;
                $indexparams['userid'] = $ttuserid;
                $indexparams['id'] = $courseid;
                $link = new moodle_url($CFG->wwwroot.'/blocks/timetracker/timeclock.php', $urlparams);
                $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);
    
                $this->content->text .= '<div style="text-align: center">';
                
                $clockouticon = new pix_icon('clock_out','Clock out','block_timetracker');
                $clockoutaction = $OUTPUT->action_icon($link, $clockouticon);
                $timeclockdataicon = new pix_icon('timeclock_data', 'Manage', 'block_timetracker');
                $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
                $this->content->text .= $clockoutaction. $timeclockdataaction.'<br />';
                
                $this->content->text .= '<b>';
                //$this->content->text .= get_string('pendingtimestamp','block_timetracker');
                $this->content->text .= '</b>';
                $pendingtimestamp= $DB->get_record('block_timetracker_pending', array('userid'=>$ttuserid,'courseid'=>$courseid));
                $this->content->text .= 'Clock in: '.userdate($pendingtimestamp->timein,get_string('datetimeformat','block_timetracker')).'<br />';
                $this->content->text .= '<br />';
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
                $link = new moodle_url($CFG->wwwroot.'/blocks/timetracker/hourlog.php', $urlparams);
                $index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $indexparams);

                // Clock In Icon
                $this->content->text .= '<div style="text-align: center">';
                $clockinicon = new pix_icon('clock_in','Add work unit', 'block_timetracker');
                $clockinaction = $OUTPUT->action_icon($link, $clockinicon);
    
                $timeclockdataicon = new pix_icon('timeclock_data', 'Manage', 'block_timetracker');
                $timeclockdataaction = $OUTPUT->action_icon($index, $timeclockdataicon);
    
                $this->content->text .= $clockinaction. $timeclockdataaction.'<br />';
                $this->content->text .= '</div>';
                $this->content->text .= '<hr>';
                }

            }


           if($recordexists){     
                if($this->config->block_timetracker_show_month_hours ||
                $this->config->block_timetracker_show_term_hours ||
                $this->config->block_timetracker_show_ytd_hours ||
                $this->config->block_timetracker_show_total_hours) {

			        $this->content->text .= '<span style=font-weight:bold; ">'.get_string('hourstitle','block_timetracker').'</span>';

                    $stats = get_worker_stats($ttuserid,$COURSE->id);

					if ($this->config->block_timetracker_show_month_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
                        $this->content->text .= $stats['monthhours'];
                        
					}
					
                    if ($this->config->block_timetracker_show_term_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalterm', 'block_timetracker');
                        $this->content->text .= $stats['termhours'];
					
            
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
					$this->content->text .= '<br />';
					$this->content->text .= '<span style="font-weight:bold; ">'.get_string('earningstitle','block_timetracker').'</span>';

					if ($this->config->block_timetracker_show_month_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
                        $this->content->text .= '$'.$stats['monthearnings'];
					}
                    
					if ($this->config->block_timetracker_show_term_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalterm', 'block_timetracker');
                        $this->content->text .= '$'.$stats['termearnings'];
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
                    
                    if($clockin == 1){
                    //protect against refreshing a 'clockin' screen
                    $pendingrecord= $DB->record_exists('block_timetracker_pending',array('userid'=>$ttuserid,'courseid'=>$courseid));
                        if(!$pendingrecord){
                            $cin = new stdClass();
                            $cin->userid = $ttuserid;
                            $cin->timein = time();
                            $cin->courseid = $courseid;
                            $DB->insert_record('block_timetracker_pending', $cin);
                        }

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
                /* 
                $this->content->text .= '<div style="text-align: center">';
                $this->content->text .= '<br />'; 
                $this->content->text .= '<a href="'.$CFG->wwwroot.'/blocks/timetracker/index.php?id='.$COURSE->id.'">';
                $this->content->text .= get_string('manage','block_timetracker');
				$this->content->text .= '</a>';
				$this->content->text .= '</div>';
                */
			}
		}
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
    * Override the instance_config_save method
    */
    function instance_config_save($data, $nolongerused = false){
        parent::instance_config_save($data, $nolongerused);
        global $DB, $COURSE;
        foreach ($data as $name => $value){
            $rec = $DB->get_record('block_timetracker_config',array('courseid'=>$COURSE->id,'name'=>$name));

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
}
