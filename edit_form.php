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
 * Form for editing TimeTracker block instances.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_timetracker_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER;

        // Fields for editing block contents.
        $mform->addElement('header', 'configheader', get_string('defaultworkerconfig','block_timetracker'));
        
        $mform->addElement('text','config_block_timetracker_default_max_earnings',get_string('maxtermearnings','block_timetracker'));
        $mform->setDefault('config_block_timetracker_default_max_earnings',0);
        
        $mform->addElement('text','config_block_timetracker_curr_pay_rate',get_string('currpayrate','block_timetracker'));
        $mform->setDefault('config_block_timetracker_curr_pay_rate',0);
        
        $mform->addElement('select','config_block_timetracker_trackermethod',get_string('trackermethod','block_timetracker'),array('Timeclock','Hourlog'));
        
        $mform->addElement('header', 'displayheader', get_string('workerdisplaysettings','block_timetracker'));

        $mform->addElement('selectyesno', 'config_block_timetracker_show_total_hours', get_string('showtotalhours', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_total_hours', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_term_hours', get_string('showtermhours', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_term_hours', 1);
        
        $mform->addElement('selectyesno', 'config_block_timetracker_show_month_hours', get_string('showmonthhours', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_month_hours', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_ytd_hours', get_string('showytdhours', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_ytd_hours', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_month_earnings', get_string('showmonthearnings', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_month_earnings', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_term_earnings', get_string('showtermearnings', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_term_earnings', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_ytd_earnings', get_string('showytdearnings', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_ytd_earnings', 1);

        $mform->addElement('selectyesno', 'config_block_timetracker_show_total_earnings', get_string('showtotalearnings', 'block_timetracker'));
        $mform->setDefault('config_block_timetracker_show_total_earnings', 1);

    }

    function validation ($data){
        $errors = array();

        if($data['config_block_timetracker_default_max_earnings'] < 0){
            echo('in the first if');
            $errors['config_block_timetracker_default_max_earnings'] = 'The default maximum earnings must be zero or greater.';    
        }

        if($data['config_block_timetracker_curr_pay_rate'] < 0){
            echo('in the second if');
            $errors['config_block_timetracker_curr_pay_rate'] = 'The current pay rate must be zero or greater.';    
        }
        return $errors;
        
    }

}
