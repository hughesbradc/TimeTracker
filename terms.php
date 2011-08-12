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
 * This page will allow the worker to input the date, time, and duration of a workunit.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require_once('timetracker_editterms_form.php');

global $CFG, $COURSE, $USER;

require_login();

$courseid = required_param('id', PARAM_INTEGER);

//error_log("In terms.php and Course is $courseid");
//error_log("In terms.php line 36 and COURSE is $COURSE->id");

$urlparams['id'] = $courseid;
$urlparams['userid'] = $USER->id;

$termsURL = new moodle_url($CFG->wwwroot.'/blocks/timetracker/terms.php',$urlparams);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

//error_log("In terms.php line 47 and COURSE is $COURSE->id");

$PAGE->set_url($termsURL);
$PAGE->set_pagelayout('base');

if (!has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    print_error(get_string('notpermissible','block_timetracker'));
}
$canmanage = true;

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$strtitle = get_string('terms_title','block_timetracker');

$PAGE->set_title($strtitle);

$timetrackerurl = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php',$urlparams);

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $timetrackerurl);
$PAGE->navbar->add($strtitle);

$mform = new timetracker_editterms_form($context);

if ($mform->is_cancelled()){ //user clicked cancel
    redirect($index,'');
} else if ($formdata=$mform->get_data()){
        print_object($formdata);
        //add/update items to the DB
        $term = new stdClass();
        $term->courseid = $courseid;
        //error_log("in terms.php and $term->courseid");

        for($i=0; $i<3; $i++){
            $tn = 'termname'.$i;
            $mn = 'month'.$i;
            $d = 'day'.$i;
            $t = 'term'.$i;
            $term->name = $formdata->$tn;
            $term->month = $formdata->$mn;
            $term->day = $formdata->$d;

            if(isset($formdata->$t)){
                //error_log('Inserting a term');
                //do update, not insert
                $term->id = $formdata->$t;
                $DB->update_record('block_timetracker_term',$term);
            } else {
                //error_log('updating a term');
                //do insert, not update.
                unset($term->id);
                $DB->insert_record('block_timetracker_term', $term);
            }
        }

        redirect($index,'');
} else {
    //form is shown for the first time
    echo $OUTPUT->header();
    $maintabs = get_tabs($urlparams, $canmanage, $courseid); 
    $tabs = array($maintabs);
    print_tabs($tabs,'terms');

    $mform->display();
    echo $OUTPUT->footer();
}


