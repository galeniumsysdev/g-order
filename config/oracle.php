<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('DB_TNS', ''),
        'host'          => env('DB_ORACLE_HOST', '129.144.188.209'),
        'port'          => env('DB_ORACLE_PORT', '1521'),
        'database'      => env('DB_ORACLE_DATABASE', 'ebscrp'),
        'username'      => env('DB_ORACLE_USERNAME', 'APPS'),
        'password'      => env('DB_ORACLE_PASSWORD', 'apps'),
        'charset'        => env('DB_CHARSET', 'AL32UTF8'),
        'prefix'         => env('DB_PREFIX', ''),
        'prefix_schema'  => env('DB_SCHEMA_PREFIX', ''),
        'server_version' => env('DB_SERVER_VERSION', '11g'),
    ],
];
