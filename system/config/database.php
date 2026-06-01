<?php

return [
    'default' => [
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'YOUR_DATABASE',
        'charset'  => 'utf8',
    ],

    'sqlite' => [
        'driver'   => 'sqlite',
        'database' => __DIR__ . '/../../site/database.sqlite',
    ],

    'pgsql' => [
        'driver'   => 'pgsql',
        'host'     => '127.0.0.1',
        'port'     => 5432,
        'username' => 'root',
        'password' => '',
        'database' => 'YOUR_DATABASE',
        'charset'  => 'utf8',
    ],

    'migrations' => [
        'table' => 'migrations',
    ],
];
