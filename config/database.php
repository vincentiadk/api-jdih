<?php
return [
    'default' => 'accounts',

    'connections' => [
        'inlis' => [
            'driver'    => 'oracle',
            'tns'       => env('DB_INLIS_TNS', ''),
            'host'      => env('DB_INLIS_HOST', ''),
            'port'      => env('DB_INLIS_PORT', '1521'),
            'database'  => env('DB_INLIS_DATABASE', ''),
            'username'  => env('DB_INLIS_USERNAME', ''),
            'password'  => env('DB_INLIS_PASSWORD', ''),
            'charset'   => env('DB_INLIS_CHARSET', 'AL32UTF8'),
            'prefix'    => env('DB_INLIS_PREFIX', ''),
            'prefix_schema' => env('DB_INLIS_SCHEMA_PREFIX', ''),
            'edition'   => env('DB_EDITION', 'ora$base'),
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
        ],
    ]
];