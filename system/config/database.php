<?php
/**
 * Database Configuration
 * 
 * Define your database connections here
 */

return [
    'default' => [
        'driver' => 'pdo_pgsql', // Options: mysqli, pdo_mysql, pdo_pgsql, pdo_sqlite
        'host' => 'localhost',
        'username' => 'postgres',
        'password' => '1234',
        'database' => 'khyle',
        'charset' => 'utf8',
        // For PDO, you can also specify DSN directly
        // 'dsn' => 'mysql:host=localhost;dbname=test',
    ]
];