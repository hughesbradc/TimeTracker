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

    if ($oldversion < 2011030901) {

        // Define table block_timetracker_workerinfo to be created
        $table = new xmldb_table('block_timetracker_workerinfo');

        // Adding fields to table block_timetracker_workerinfo
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, 
            XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, 
            null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, 
            null, '0');
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('address', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('phonenumber', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('position', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('currpayrate', XMLDB_TYPE_NUMBER, '10, 3', null, XMLDB_NOTNULL, 
            null, '0');
        $table->add_field('timetrackermethod', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, 
            null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dept', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('idnum', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('comments', XMLDB_TYPE_CHAR, '1000', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table block_timetracker_workerinfo
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_timetracker_workerinfo
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // timetracker savepoint reached
        upgrade_block_savepoint(true, 2011030901, 'timetracker');

    }


    return true;
}
