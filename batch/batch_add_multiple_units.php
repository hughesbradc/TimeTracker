<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$courses = get_courses(4, 'fullname ASC', 'c.id,c.shortname');
//echo sizeof($courses)." courses\n";

$round = new stdClass();
$round->name = 'block_timetracker_round';
$round->value = '0';

foreach($courses as $course){
    $id = $course->id;
    $round->courseid = $course->id;
    echo ("Updating $course->shortname\n");

    if($DB->record_exists('block_timetracker_config',
        array('courseid'=>$id, 'name'=>'block_timetracker_round'))){

        error_log("Entry already exists"); 

        $entry = $DB->get_record('block_timetracker_config',
            array('courseid'=>$id, 'name'=>'block_timetracker_round'));
        $entry->value = '0';

        $res = $DB->update_record('block_timetracker_config', $entry);
        if(!$res){
            error_log("Failed updating record for $course->shortname");
        }

    } else {

        $res = $DB->insert_record('block_timetracker_config', $round);

        if(!$res){
            error_log("Error inserting for $course->shortname");
        }
    }
}
