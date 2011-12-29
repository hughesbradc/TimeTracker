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
 * This block will display a summary of hours and earnings for the worker.
 *
 * @package    TimeTracker
 * @copyright  Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('timesheet_pdf.php');

require_login();

$courseid = required_param('id', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$timesheetid = required_param('timesheetid', PARAM_INTEGER);


$urlparams['id'] = $courseid;
$urlparams['userid'] = $userid;
$index = new moodle_url($CFG->wwwroot.'/blocks/timetracker/index.php', $urlparams);

generate_pdf_from_timesheetid($timesheetid, $userid, $courseid);

//redirect($index, 2);
