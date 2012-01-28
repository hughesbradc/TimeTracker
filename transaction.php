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
 * This form will allow administration to batch sign timesheets electronically and export to payroll.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require('timetracker_transaction_form.php');

require_login();

$categoryid = required_param('id', PARAM_INTEGER);

//$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_category_by_id($categoryid);
$context = $PAGE->context;

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

$courses = get_courses($categoryid, 'fullname ASC', 'c.id,c.shortname');

if($courses){
    $sql = 'SELECT * from mdl_block_timetracker_workerinfo where courseid in (';
    $list = implode(",", array_keys($courses));   
    $sql .= $list.')';
    $workers = $DB->get_records_sql($sql);    
} else {
    print_error('nocourseserror','block_timetracker');
}

if($workers){
    $sql = 'SELECT * from '.$CFG->prefix.'block_timetracker_timesheet where userid in (';
    $list = implode(",", array_keys($workers));
    $sql .= $list.')';
} else {
    print_error('noworkerserror','block_timetracker');
}

$urlparams['id'] = $categoryid;
$PAGE->set_url(new moodle_url($CFG->wwwroot.
    '/blocks/timetracker/transaction.php',$urlparams));
$PAGE->set_pagelayout('base');

$indexparams['id'] = 1;
$index = new moodle_url($CFG->wwwroot.'/index.php',$indexparams);

$strtitle = get_string('signheader','block_timetracker'); 
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->set_pagelayout('base');

$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname','block_timetracker'), $index);
$PAGE->navbar->add($strtitle);

if(!$canmanage){
    print_error('notpermissible','block_timetracker');
}

if(!$workers){
    echo 'No users are enrolled in your course.';
} else {
    $mform = new timetracker_transaction_form($categoryid);

    if ($mform->is_cancelled()){ //user clicked cancel
        //redirect($nextpage);
        redirect($index, $indexparams);

    } else if ($formdata=$mform->get_data()){
    
        /*
         * TODO Set transactionid in timesheet table
         * TODO Set submitted, description, mdluserid, and category id in transactn table
         */
        
        //Create a new transaction
        $newtransaction = new stdClass();
        $newtransaction->submitted = time();
        $newtransaction->description = $formdata->description;
        $newtransaction->mdluserid = $USER->id;
        $newtransaction->categoryid = $categoryid;
        
        $transactionid = $DB->insert_record('block_timetracker_transactn', $newtransaction);

        $courses = get_courses($categoryid, 'fullname ASC', 'c.id,c.shortname');
        if($courses){
            $sql = 'SELECT * from '.$CFG->prefix.'block_timetracker_timesheet where submitted=0 AND courseid in (';
            $list = implode(",", array_keys($courses));   
            $sql .= $list.')';
            $timesheets = $DB->get_records_sql($sql);
        }
        
        foreach($timesheets as $timesheet){
            if($formdata->signid[$timesheet->id] == 1){
                $timesheet->transactionid = $transactionid; 
                $DB->update_record('block_timetracker_timesheet',$timesheet);
            }
        }

    } else {
        //form is shown for the first time
        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
}
