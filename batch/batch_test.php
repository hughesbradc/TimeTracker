<?php

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB;

$roleid = $DB->get_field('role', 'id', array(
    'archetype'=>'student'));

echo $roleid."\n";
print_object($CFG);
