<?php
$observers = array(
    array(
        'eventname' => '\core\event\user_created',
        'includefile' => '\auth\apoa\lib.php',
        'callback' => 'auth_apoa_user_created',
    ),
);