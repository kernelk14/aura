<?php
/**
 * Database Configuration
 * 
 * Define your database connections here
 */

return [
    'default' => [
        'driver' => 'mysqli', // Options: mysqli, pdo_mysql, pdo_pgsql, pdo_sqlite
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'database' => 'YOUR_DATABASE',
        'charset' => 'utf8',
        // For PDO, you can also specify DSN directly
        // 'dsn' => 'mysql:host=localhost;dbname=test',
    ]
];
