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


    return true;
}
