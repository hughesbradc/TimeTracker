<?php

$capabilities = array(

    'block/timetracker:manageworkers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT
        )
    ),

    'block/timetracker:managepayrate' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT
        )
    ),

    'block/timetracker:activateworkers' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT
        )
    ),

    'block/timetracker:manageid' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'guest' => CAP_PREVENT
        )
    )

);
