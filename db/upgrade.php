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
 * This file keeps track of upgrades to the TimeTracker block
 *
 * @package TimeTracker
 * @copyright 2011 Marty Gilbert, Brad Hughes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_timetracker_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2011120601) {


    }            

    if ($oldversion < 2011121901) {

         // Define table block_timetracker_timesheet to be created
        $table = new xmldb_table('block_timetracker_timesheet');

        // Adding fields to table block_timetracker_timesheet
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);
        $table->add_field('submitted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);
        $table->add_field('workersignature', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('supervisorsignature', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('supermdlid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null,
            null, null);
        $table->add_field('transactionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_timetracker_timesheet
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_timesheet
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } 


        upgrade_block_savepoint(true, 2011121901, 'timetracker');

    }            

    if ($oldversion < 2011121901) {


         // Define field canedit to be added to block_timetracker_workunit
        $table = new xmldb_table('block_timetracker_workunit');
        $field = new xmldb_field('canedit', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null,
            null, '1', 'lasteditedby');

        // Conditionally launch add field canedit
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


         // Define field timesheetid to be added to block_timetracker_workunit
        $table = new xmldb_table('block_timetracker_workunit');
        $field = new xmldb_field('timesheetid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            null, null, '0', 'canedit');

        // Conditionally launch add field timesheetid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field id to be added to block_timetracker_workunit
        $table = new xmldb_table('block_timetracker_workunit');
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);

        // Conditionally launch add field id
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

          // Define table block_timetracker_transactn to be created
        $table = new xmldb_table('block_timetracker_transactn');

        // Adding fields to table block_timetracker_transactn
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('submitted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null,
            null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null,
            null);
        $table->add_field('mdluserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null,
            null, '0');

        // Adding keys to table block_timetracker_transactn
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_transactn
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

         // Define table block_timetracker_txdetail to be created
        $table = new xmldb_table('block_timetracker_txdetail');

        // Adding fields to table block_timetracker_txdetail
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);
        $table->add_field('hours', XMLDB_TYPE_NUMBER, '10, 2', XMLDB_UNSIGNED, null, null,
            null);
        $table->add_field('pay', XMLDB_TYPE_NUMBER, '10, 2', XMLDB_UNSIGNED, null, null,
            null);
        $table->add_field('paycode', XMLDB_TYPE_CHAR, '50', null, null, null, 'Reg');
        $table->add_field('transactionid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, null);

        // Adding keys to table block_timetracker_txdetail
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_txdetail
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        upgrade_block_savepoint(true, 2011121901, 'timetracker');

    }            

    return true;
}
