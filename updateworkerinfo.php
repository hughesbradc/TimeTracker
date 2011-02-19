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
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once('../../config.php');
require('timetracker_updateworkerinfo_form.php');

global $CFG, $COURSE, $USER;

require_login();

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/timetracker/updateworkerinfo.php');

$mform = new timetracker_updateworkerinfo_form();
if ($formdata=$mform->get_data()){
          //you need this section if you have a cancel button on your form
    echo $OUTPUT->header();
    //$mform->display();
    print_object($formdata);
    echo $OUTPUT->footer();
} else {
    //this branch is where data wasn't validated correctly.    
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
  
}
