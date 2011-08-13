<?php

define('CLI_SCRIPT', true);
require_once('../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

global $CFG, $DB, $USER;

/*
    0 - dept (long)
    1 - shortname (short)
    2 - title
    3 - Supervisor(s)
*/

if(($handle = fopen("/tmp/jobs.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $full = 'Work-Study - '.$data[0];
        $short = 'WorkStudy_'.$data[1];

        $cid = create_cwsp_course($full, $short, 3);


        //after creating course
        if($cid){
            $course = $DB->get_record('course',array('id'=>$cid));
            $course->shortname = $short;
            $course->fullname = $full;
            $DB->update_record('course', $course);

            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_supname'));

            $curr->value = $data[3];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for supervisor';
            }

            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_institution'));
            $curr->value = 'Mars Hill College';
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for institution';
            }

            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_position'));
            $curr->value = $data[2];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for position';
            }

            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_department'));
            $curr->value = $data[0];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for department';
            }

            

        } else {
            echo 'couldn\'t crease course for '.$data[0]."\n";
        }
    }
    fclose($handle);
}
 
function create_cwsp_course($fullname, $shortname, $categoryid = 2){
    global $CFG, $USER, $DB;

    //$backupdir = $CFG->dataroot.'/temp/backup/35d6415125249264f1bf81ae131874e2';
    $backupdir = '35d6415125249264f1bf81ae131874e2';
    $transaction = $DB->start_delegated_transaction();
    echo $fullname.' '.$shortname."\n";
    
    // Create new course
    $courseid = restore_dbops::create_new_course($fullname, $shortname, $categoryid);
    
    // Restore backup into course
    $controller = new restore_controller($backupdir, $courseid, 
        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, 2,
        backup::TARGET_NEW_COURSE);
    
    $controller->execute_precheck();
    $controller->execute_plan();
    
    // Commit
    $transaction->allow_commit();

    return $courseid;

}

