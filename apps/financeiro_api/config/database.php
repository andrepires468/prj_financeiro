<?php

return [
  'connections' => [
    'home' => [
      'driver' => 'mysql',
      'host' => env('DB_HOST'),
      'port' => env('DB_PORT'),
      'database' => env('DB_DATABASE'),
      'username' => env('DB_USERNAME'),
      'password' => env('DB_PASSWORD'),
      'unix_socket' => env('DB_SOCKET', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'strict' => true,
      'engine' => null,
      'options'   => [
        PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA'),
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
      ],
    ],
  ]
];
