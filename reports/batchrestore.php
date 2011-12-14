<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

global $CFG, $DB, $USER;

//need this 
// $CFG->keeptempdirectoriesonbackup = true;
// in config.php to keep the backup directory available
// located in $CFG->dataroot/temp/backup

/*
    0 - dept (long)
    1 - shortname (short)
    2 - position title
    3 - Supervisor(s)
    4 - budget #
*/

if(($handle = fopen("biweekly.csv", "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        /*
        $full = 'Work-Study - '.$data[0];
        $short = 'WorkStudy_'.$data[1];
        */
        $full = $data[0];
        $short = $data[1];
        //echo $full. ' '.$short."\n";
        //continue;


        //$cid = create_cwsp_course($full, $short, 2);//CWSP
        //$cid = create_cwsp_course($full, $short, 4);//departmental
        $cid = create_cwsp_course($full, $short, 5);//bi-weekly


        //after creating course
        if($cid){
            $course = $DB->get_record('course',array('id'=>$cid));
            $course->shortname = $short;
            $course->fullname = $full;
            $course->visible = 1;
            $course->visibleold = 1;


            $DB->update_record('course', $course);

            //set the supervisor name
            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_supname'));
            $curr->value = $data[3];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for supervisor';
            }

            //set the institution name
            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_institution'));
            $curr->value = 'Mars Hill College';
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for institution';
            }

            //set the position title
            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_position'));
            $curr->value = $data[2];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for position';
            }

            //set the department
            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_department'));
            $curr->value = $data[0];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for department';
            }

            //set the budget #
            $curr = $DB->get_record('block_timetracker_config',
                array('courseid'=>$cid, 'name'=>'block_timetracker_budget'));
            $curr->value = $data[4];
            if(!$DB->update_record('block_timetracker_config', $curr)){
                echo 'cannot update config for department';
            }


            

        } else {
            echo 'couldn\'t create course for '.$data[0]."\n";
        }
    }
    fclose($handle);
}
 
function create_cwsp_course($fullname, $shortname, $categoryid = 2){
    global $CFG, $USER, $DB;

    //need this 
    // $CFG->keeptempdirectoriesonbackup = true;
    // in config.php to keep the directory available

	//$backupdir = 'fde3a9c54543ef236d60ba0fa4aba028'; //CWSP
    $backupdir = 'ef521c9d85221eac4b596380655f0918'; //departmental
    $backupdir = 'e993969ee7787cdc1c5cd4f309c1902d'; //bi-weekly

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

