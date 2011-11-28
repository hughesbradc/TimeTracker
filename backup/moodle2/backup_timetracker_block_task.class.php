<?php

// Because it exists (must)
require_once($CFG->dirroot . '/blocks/timetracker/backup/moodle2/backup_timetracker_stepslib.php'); 

// Because it exists (optional)
require_once($CFG->dirroot . '/blocks/timetracker/backup/moodle2/backup_timetracker_settings.php');
 
/**
 * timetracker backup task that provides all the settings and steps to perform one
 * complete backup of the block
 */
class backup_timetracker_block_task extends backup_block_task {
 
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        //error_log("in define_my_steps");
        $this->add_step(new backup_timetracker_block_structure_step(
            'timetracker_structure', 'timetracker.xml'));
    }

    public function get_fileareas() {
        return array();
    }

    public function get_configdata_encoded_attributes() {
        return array(); // We need to encode some attrs in configdata
    }

    static public function encode_content_links($content) {
        return $content; // No special encoding of links
    }
}
