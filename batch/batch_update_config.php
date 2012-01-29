<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
*/
global $CFG, $DB, $USER;

$courses = get_courses(5, 'fullname ASC', 'c.id,c.shortname');


foreach($courses as $course){


    $config = $DB->get_record('block_timetracker_config', array(
        'courseid'=>$course->id, 'name'=>'block_timetracker_show_term_earnings'));
    if($config){
        /*
        $config->value=0;
        //print_object($config);
        $DB->update_record('block_timetracker_config', $config);
    
    
        $config = $DB->get_record('block_timetracker_config', array(
            'courseid'=>$course->id, 'name'=>'block_timetracker_show_term_hours'));
        $config->value=0;
        $DB->update_record('block_timetracker_config', $config);
    
    
        $config = $DB->get_record('block_timetracker_config', array(
            'courseid'=>$course->id, 'name'=>'block_timetracker_default_max_earnings'));
        $config->value=0;
        $DB->update_record('block_timetracker_config', $config);
        */

        $config = $DB->get_record('block_timetracker_config', array(
            'courseid'=>$course->id, 'name'=>'block_timetracker_round'));
        $config->value=0;
        $DB->update_record('block_timetracker_config', $config);

    }
    //$sql = 'UPDATE '.$CFG->prefix.'block_timetracker_workerinfo SET maxtermearnings=0 WHERE courseid='.$course->id;

    //$DB->execute($sql);
    //error_log($sql);
    
}
