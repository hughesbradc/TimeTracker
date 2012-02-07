<?php


define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB, $USER;
/**
    The purpose of this script is to  transfer a worker(and associated hours) from one
    course to another.

    CSV file needs the following format
        studentEmail,fromCourseID,toCourseID,supername,department
*/

//$file='/tmp/onemore.csv';
//$file='transferAriel.csv';
$file='transfer.csv';

$count = 0;
if(($handle = fopen($file, "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $email          = strtolower($data[0]); 
        $fromcourseid   = $data[1];
        $tocourseid     = $data[2];
        $supername      = $data[3];
        $department     = $data[4];

        $worker = $DB->get_record('block_timetracker_workerinfo',
            array('email'=>$email, 'courseid'=>$fromcourseid));
        if(!$worker){
            echo "Invalid data for worker $email\n";
            continue;
        }

        $count++;

        //delete all of the alert units for this user
        $alerts = $DB->get_records('block_timetracker_alertunits',
            array('userid'=>$worker->id));

        foreach($alerts as $alert){
            $DB->delete_records('block_timetracker_alert_com',
                array('alertid'=>$alert->id));
        }

        //delete all alerts
        $DB->delete_records('block_timetracker_alertunits',
            array('userid'=>$worker->id));

        //TODO -- when we upgrade to digital signatures, will need to update the courseid
        //in all of those tables as well

        //update all of the work units
        $DB->set_field('block_timetracker_workunit', 'courseid', $tocourseid,
            array('userid'=>$worker->id, 'courseid'=>$fromcourseid));

        //update all of the work units
        $DB->set_field('block_timetracker_workunit', 'courseid', $tocourseid,
            array('userid'=>$worker->id, 'courseid'=>$fromcourseid));

        //update the workerinfo field
        $worker->courseid       = $tocourseid;
        $worker->supervisor     = $supername;
        $worker->dept     = $department;

        $DB->update_record('block_timetracker_workerinfo', $worker);


        //get the TO course context
        $context = get_context_instance(CONTEXT_COURSE, $tocourseid);


        //get the FROM course context
        $context = get_context_instance(CONTEXT_COURSE, $fromcourseid);

        //un-enroll from first course, if enrolled.
        if(is_enrolled($context, $worker->mdluserid)){
            $manual = enrol_get_plugin('manual');

            $instances = enrol_get_instances($fromcourseid, false);
            foreach($instances as $instance){
                    if($instance->enrol == 'manual'){
                        $winner = $instance;
                        break;
                    }
            }
            if(isset($winner)){
                $manual->unenrol_user($winner, $worker->mdluserid);
            } else {
                echo "Cannot unenroll $worker->firstname $worker->lastname\n";
            }
        } else {
            echo "$worker->firstname $worker->lastname is NOT enrolled in FROM course\n";
        }


        //enroll in second course, if NOT enrolled
        if(!is_enrolled($context, $worker->mdluserid)){
            $manual = enrol_get_plugin('manual');

            $instances = enrol_get_instances($tocourseid, false);
            foreach($instances as $instance){
                    if($instance->enrol == 'manual'){
                        $winner = $instance;
                        break;
                    }
            }

            if(isset($winner)){
                $manual->enrol_user($winner, $worker->mdluserid, NULL, time());
            }else{
                echo "Cannot enroll $worker->firstname $worker->lastname\n";
            }
        } else {
            echo "$worker->firstname $worker->lastname is already enrolled in TO course\n";
        }


    }
}
echo "Handled $count transfers\n";
