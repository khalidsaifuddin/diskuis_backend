<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlsrv'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_PG_HOST', '127.0.0.1'),
            'port' => env('DB_PG_PORT', '5432'),
            'database' => env('DB_PG_DATABASE', 'forge'),
            'username' => env('DB_PG_USERNAME', 'forge'),
            'password' => env('DB_PG_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_2' => [
            'driver' => 'pgsql',
            'host' => env('DB_2_HOST', 'localhost'),
            'port' => env('DB_2_PORT', '1433'),
            'database' => env('DB_2_DATABASE', 'forge'),
            'username' => env('DB_2_USERNAME', 'forge'),
            'password' => env('DB_2_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

         'sqlsrv_3' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_3_HOST', 'localhost'),
            'port' => env('DB_3_PORT', '1433'),
            'database' => env('DB_3_DATABASE', 'forge'),
            'username' => env('DB_3_USERNAME', 'forge'),
            'password' => env('DB_3_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
         
        'sqlsrv_4' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_4_HOST', 'localhost'),
            'port' => env('DB_4_PORT', '1433'),
            'database' => env('DB_4_DATABASE', 'forge'),
            'username' => env('DB_4_USERNAME', 'forge'),
            'password' => env('DB_4_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_spm' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_SPM_HOST', 'localhost'),
            'port' => env('DB_SPM_PORT', '1433'),
            'database' => env('DB_SPM_DATABASE', 'forge'),
            'username' => env('DB_SPM_USERNAME', 'forge'),
            'password' => env('DB_SPM_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_pmp' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_PMP_HOST', 'localhost'),
            'port' => env('DB_PMP_PORT', '1433'),
            'database' => env('DB_PMP_DATABASE', 'forge'),
            'username' => env('DB_PMP_USERNAME', 'forge'),
            'password' => env('DB_PMP_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_pmp_dev' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_PMP_DEV_HOST', 'localhost'),
            'port' => env('DB_PMP_DEV_PORT', '1433'),
            'database' => env('DB_PMP_DEV_DATABASE', 'forge'),
            'username' => env('DB_PMP_DEV_USERNAME', 'forge'),
            'password' => env('DB_PMP_DEV_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_sbb' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_SBB_HOST', 'localhost'),
            'port' => env('DB_SBB_PORT', '1433'),
            'database' => env('DB_SBB_DATABASE', 'forge'),
            'username' => env('DB_SBB_USERNAME', 'forge'),
            'password' => env('DB_SBB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'sqlsrv_27' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_27_HOST', 'localhost'),
            'port' => env('DB_27_PORT', '1433'),
            'database' => env('DB_27_DATABASE', 'forge'),
            'username' => env('DB_27_USERNAME', 'forge'),
            'password' => env('DB_27_PASSWORD', 'r00t_DB#$%2016'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'predis'),
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

];
