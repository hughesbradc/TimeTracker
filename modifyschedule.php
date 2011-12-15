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
 * This page will 'do magic' when a supervisor approves an error alert regarding a pending work unit.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once('schedules.php');

global $CFG, $COURSE, $USER;

require_login();

$scheduleid = required_param('scheduleid', PARAM_INTEGER); 
$action = required_param('action', PARAM_ALPHA);

$urlparams['id'] = $scheduleid;
$urlparams['action'] = $action;

$catid = 2;
$context = get_context_instance(CONTEXT_COURSECAT, $catid); 
$PAGE->set_context($context);

//$nextpage = ;

$PAGE->set_url($url);
$PAGE->set_pagelayout('base');
$strtitle = 'TimeTracker : Report Generator';

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)){ //supervisor
    $canmanage = true;
}

if(!$canmanage){
    print_error('notpermissible','block_timetracker');
}

if($action == 'add'){
    $DB->insert_record('block_timetracker_schedules', NEED VARIABLE HERE));
} else if ($action == 'delete'){ 
    $DB->delete_records('block_timetracker_schedules', array('id'=>$scheduleid));
} else { //edit 
    $sql = '';

?>
