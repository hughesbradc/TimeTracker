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

    if ($oldversion < 2011030902) {

        // Define table block_timetracker_config to be created
        $table = new xmldb_table('block_timetracker_config');

        // Adding fields to table block_timetracker_config
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_timetracker_config
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_config
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011030902, 'timetracker');
    }

    if ($oldversion < 2011030903) {

        // Rename field userid on table block_timetracker_workerinfo to mdluserid
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0', 'id');

        // Launch rename field userid
        $dbman->rename_field($table, $field, 'mdluserid');

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011030903, 'timetracker');
    }

    if ($oldversion < 2011041114) {

        // Define field budget to be added to block_timetracker_workerinfo
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('budget', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '0','dept');

        // Conditionally launch add field budget
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field supervisor to be added to block_timetracker_workerinfo
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('supervisor', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, 
            null, '0', 'budget');

        // Conditionally launch add field supervisor
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field institution to be added to block_timetracker_workerinfo
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('institution', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, 
            '0', 'supervisor');

        // Conditionally launch add field institution
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Changing type of field currpayrate on table block_timetracker_workerinfo to number
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('currpayrate', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, 
            null, '0', 'position');

        // Launch change of type for field currpayrate
        $dbman->change_field_type($table, $field);

         // Define field maxtermearnings to be added to block_timetracker_workerinfo
        $table = new xmldb_table('block_timetracker_workerinfo');
        $field = new xmldb_field('maxtermearnings', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, 
            null, '0', 'comments');

        // Conditionally launch add field maxtermearnings
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

         // Changing type of field payrate on table block_timetracker_workunit to number
        $table = new xmldb_table('block_timetracker_workunit');
        $field = new xmldb_field('payrate', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, 
            null, null, 'timeout');

        // Launch change of type for field payrate
        $dbman->change_field_type($table, $field);
    

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011041114, 'timetracker');
 
    }

    if ($oldversion < 2011072800) {

        // Define table block_timetracker_alert_units to be created
        $table = new xmldb_table('block_timetracker_alert_units');

        // Adding fields to table block_timetracker_alert_units
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('timein', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,
            null);
        $table->add_field('timeout', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null,
            null);
        $table->add_field('payrate', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL,
            null, null);
        $table->add_field('lastedited', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL,
            null, null);
        $table->add_field('lasteditedby', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('alerttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL,
            null, null);

        // Adding keys to table block_timetracker_config
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_alert_units
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011072800, 'timetracker');
    }


 if ($oldversion < 2011072800) {

        // Define table block_timetracker_alert_com to be created
        $table = new xmldb_table('block_timetracker_alert_com');

        // Adding fields to table block_timetracker_alert_com
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL,
            XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');
        $table->add_field('alertid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_timetracker_config
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_alert_com
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011072800, 'timetracker');
    }




    return true;
}
