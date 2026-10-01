<?php
$observers = array(
    array(
        'eventname' => '\core\event\user_created',
        'includefile' => '/auth/apoa/lib.php',
        'callback' => 'auth_apoa_user_created',
    ),
    array(
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\auth_apoa\observer::user_enrolment_changed',
    ),
    array(
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\auth_apoa\observer::user_enrolment_changed',
    ),
);