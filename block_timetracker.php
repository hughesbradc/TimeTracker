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

 class block_timetracker extends block_base {

    function init() {
        $this->title = get_string('blocktitle', 'block_timetracker');
    }

    /**
    function preferred_width() {
        return 210;
    }

    function applicable_formats() {
        return array('all' => true, 'tag' => false);   // Needs work to make it work on tags MDL-11960
    }

    function specialization() {
        // After the block has been loaded we customize the block's title display
        if (!empty($this->config) && !empty($this->config->title)) {
            // There is a customized block title, display it
            $this->title = $this->config->title;
        } else {
            // No customized block title, use localized remote news feed string
            $this->title = get_string('remotenewsfeed', 'block_timetracker');
        }
    }

    */
    
    function get_content() {
        global $CFG, $DB, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        if (has_capability('block/timetracker:manageworkers', $this->context)) {
            $this->content->text = 'You have manage capabilities!';
        } else {
	        $numrecords = $DB->count_records('block_timetracker_workerinfo', array('userid'=>$USER->id));
            if ($numrecords == 0){
                $this->content->text .= '<a href="/workerinfo.php">';
				$this->content->text .= '<center>'.get_string('registerinfo','block_timetracker').'</center>';
                $this->content->text .= '</a>';
            } else {
                if ($this->config->block_timetracker_show_month_hours ||
                $this->config->block_timetracker_show_term_hours ||
                $this->config->block_timetracker_show_ytd_hours ||
                $this->config->block_timetracker_show_total_hours) {
			        $this->content->text .= '<span style=font-weight:bold; ">'.get_string('hourstitle','block_timetracker').'</span>';

//            		$this->content->text = 'EPIC FAIL! You do not have editing capabilities.';
					if ($this->config->block_timetracker_show_month_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
					}
					
                    if ($this->config->block_timetracker_show_term_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalterm', 'block_timetracker');
					}
            
					if ($this->config->block_timetracker_show_ytd_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalytd', 'block_timetracker');
					}
					
                    if ($this->config->block_timetracker_show_total_hours){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('total', 'block_timetracker');
					}
       
					if ($this->config->block_timetracker_show_month_earnings ||
					$this->config->block_timetracker_show_term_earnings ||
					$this->config->block_timetracker_show_ytd_earnings ||
					$this->config->block_timetracker_show_total_earnings) {
						$this->content->text .= '<br />';
						$this->content->text .= '<span style="font-weight:bold; ">'.get_string('earningstitle','block_timetracker').'</span>';
					}

					if ($this->config->block_timetracker_show_month_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalmonth', 'block_timetracker');
					}

					if ($this->config->block_timetracker_show_term_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalterm', 'block_timetracker');
					}

					if ($this->config->block_timetracker_show_ytd_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('totalytd', 'block_timetracker');
					}

					if ($this->config->block_timetracker_show_total_earnings){
						$this->content->text .= '<br />';
						$this->content->text .= get_string('total', 'block_timetracker');
					}

					$this->content->text .= '<br />'; 
					$this->content->text .= '<a href="/workerhome.php">';
					$this->content->text .= '<center>'.get_string('manage','block_timetracker').'</center>';
					$this->content->text .= '</a>';
					return $this->content;
				}
			}
		}
    }


    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return false;
    }
   
   /**
     * cron - goes through all feeds and retrieves them with the cache
     * duration set to 0 in order to force the retrieval of the item and
     * refresh the cache
     *
     * @return boolean true if all feeds were retrieved succesfully
     */
    function cron() {
        global $CFG, $DB, $USER;
   /**     require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        // We are going to measure execution times
        $starttime =  microtime();

        // And we have one initial $status
        $status = true;

        // Fetch all site feeds.
        $rs = $DB->get_recordset('block_timetracker');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');
            // Fetch the rss feed, using standard simplepie caching
            // so feeds will be renewed only if cache has expired
            @set_time_limit(60);

            $feed =  new moodle_simplepie();
            // set timeout for longer than normal to be agressive at
            // fetching feeds if possible..
            $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                mtrace ('error');
                mtrace ('SimplePie failed with error:'.$feed->error());
                $status = false;
            } else {
                mtrace ('ok');
            }
            $counter ++;
        }
        $rs->close();

        // Show times
        mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        // And return $status
        return $status;
        */

    }
}
