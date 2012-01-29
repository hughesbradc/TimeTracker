<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB;

$limit = time() - (7 * 60 * 60 * 24);

$sql = 'SELECT DISTINCT courseid from mdl_block_timetracker_alertunits WHERE alerttime > '.$limit.
    ' ORDER BY courseid, alerttime ASC';

$courses = $DB->get_records_sql($sql);

foreach($courses as $course){

    $id = $course->courseid;
    $courseinfo = $DB->get_record('course', array('id'=>$id));
    $context = get_context_instance(CONTEXT_COURSE, $id);
    $teachers = get_users_by_capability($context, 'block/timetracker:manageworkers');
    //print_object($teachers);

    $coursealerts = $DB->get_records('block_timetracker_alertunits', array(
        'courseid'=>$id));


    $num = sizeof($coursealerts);

    $subj = 'You have '.$num.' work unit alert(s) for '.$courseinfo->shortname;
    $body = "Hello!\n\nYou have $num work unit alert(s) that require your attention for $courseinfo->shortname.\n\n".
        "To visit the TimeTracker Alerts page, either click the below link or copy/paste ".
        "it into your browser window.\n\n".
        //'<a href="'.$CFG->wwwroot.'/blocks/timetracker/managealerts.php?id='.$id.'">'.
        $CFG->wwwroot.'/blocks/timetracker/managealerts.php?id='.$id. 
        //'</a>'."\n\n\n".
        "\n\n".
        "Thanks for your timely attention to this matter";

    $body_html = format_text($body);
    $body = format_text_email($body_html, 'FORMAT_HTML');

    foreach($coursealerts as $alert){
    
        $alertcoms = $DB->get_records('block_timetracker_alert_com', array(
            'alertid'=>$alert->id));
        foreach($alertcoms as $com){
            if(array_key_exists($com->mdluserid, $teachers)){
                $user = $DB->get_record('user', array('id'=>$com->mdluserid));
                email_to_user($user, $user, $subj, $body, $body_html);
                //email_to_user($user, $user, $subj, $body);
            }
        }

    }
}




