<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

global $CFG, $DB, $USER;

/*
    0 - userid

    Enrolls users listed in the 'WorkersOnly' group in WS-Main
*/

$now = time();

$count = 0;
if(($handle = fopen("/tmp/workers.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $userid = $data[0];
        //$short = 'WorkStudy_'.$data[1];
        $groupid= 2;

        $entry = new stdClass();
        $entry->userid=$userid;
        $entry->groupid=$groupid;
        $entry->timeadded=$now;

        $exists = $DB->record_exists('groups_members',array('userid'=>$userid));
        if(!$exists){
            $result = $DB->insert_record('groups_members',$entry); 
            //print_object($entry);
            if(!$result){
                echo "Not adding $entry->userid to table\n";
            } else {
                $count++;
                //echo "Added user $entry->userid to groups\n";
            }
        } else {
            echo "Already exists at $exists\n";
        }
        //break;
    }
    fclose($handle);
}

echo "added $count to the group in the DB\n";
