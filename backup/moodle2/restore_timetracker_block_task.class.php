<?php

require_once($CFG->dirroot.'/blocks/timetracker/backup/moodle2/restore_timetracker_stepslib.php');
/**
 * choice restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */

class restore_timetracker_block_task extends restore_block_task {
 
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }
 
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        //error_log('adding step now in clas');
        $this->add_step(new restore_timetracker_block_structure_step('timetracker_structure',
            'timetracker.xml'));
    }
    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }

}

