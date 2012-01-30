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
// You should have received a copy of the GNU General Public License // along with Moodle.  If not, see <http://www.gnu.org/licenses/>.  
/**
 * This block will display a summary of hours and earnings for the worker.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
//require_once($CFG->libdir . '/tablelib.php');
//require_once('lib.php');
//require_once('timesheet_pdf.php');

//require_login();

//error_log("this is only a test!!");
//error_log("this is only a test!!");
//error_log("this is only a test!!");
//error_log("this is only a test!!");
//error_log("this is only a test!!");
//error_log("this is only a test!!");
error_log("this is only a test!!");
error_log("this is only a test!!");

phpinfo();


/*
$courses = get_courses(4, 'fullname ASC', 'c.id,c.shortname');


if($courses){

    $sql = 'SELECT * from mdl_block_timetracker_timesheet where courseid in (';
    $list = implode(",", array_keys($courses));    
    $sql .= $list.')';
    echo $sql;
}

*/



/*
$courseid = required_param('id', PARAM_INTEGER);
$userid = optional_param('userid',$USER->id, PARAM_INTEGER);

$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

$PAGE->set_url($index);

$strtitle = 'TimeTracker';

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);

$PAGE->set_pagelayout('base');

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

echo $OUTPUT->header();

//start here, using $mform->addElement... 
echo '
<style type="text/css">

table{
    width: 80%;
    height: 80%;
}
.calendar{
    border-left: 1px solid black;
    border-bottom: 1px solid black;
    border-top: 1px solid black;
}
table,div,td,th,tr{
    font-weight: normal;
    font-size: 18px;
    font-family: helvetica;
}

table{
   padding: 0;
   spacing: 0;
   border: 1px solid black;
   border-collapse: separate;
   margin-left: auto;
   margin-right: auto;
}

span.thirteen{
    font-weight: bold;
    font-size: 23px;
    font-family: helvetica;
}

span.ten{
    font-weight: bold;
    font-size: 20px;
    font-family: helvetica;
}

span.eight{
    font-weight: bold;
    font-size: 18px;
    font-family: helvetica;
}

span.seven{
    font-weight: bold;
    font-size: 17px;
    font-family: helvetica;
}

</style>
';

$pages = generate_html(1320120000, time(), 4, 112,1);

foreach($pages as $page){
    $page = str_replace('<font size="13">', '<span class="thirteen">',$page);
    $page = str_replace('<font size="10">', '<span class="ten">',$page);
    $page = str_replace('<font size="8">', '<span class="eight">',$page);
    $page = str_replace('<font size="7">', '<span class="seven">',$page);
    $page = str_replace('</font>', '</span>',$page);
    $page = str_replace('<hr style="height: 1px" />','', $page);
    echo $page;
    echo "\n\n\n";
}
//stop here
//echo '</div>';
*/


//echo $OUTPUT->footer();
