<?php

$definitions = [
    'is_federation_pending_cache' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => false,
        'requireidentifiers' => [],
        'requirelockingread' => false,
        'requirelockingwrite' => true,
        'requirelockingbeforewrite' => false,
        'maxsize' => null,
        'overrideclass' => null,
        'overrideclassfile' => null,
        'datasource' => null,
        'datasourcefile' => null,
        'staticacceleration' => false,
        'staticaccelerationsize' => null,
        'ttl' => 0,
        'mappingsonly' => false,
        'invalidationevents' => [],
        'canuselocalstore' => false,
        'sharingoptions' => cache_definition::SHARING_DEFAULT,
        'defaultsharing' => cache_definition::SHARING_DEFAULT,
    ],
];