<?php

use App\Config\Env;

require_once __DIR__ . '/Env.php';

$root = dirname(__DIR__, 2);
$env = Env::load($root . DIRECTORY_SEPARATOR . '.env');

return [
    'db' => [
        'host' => isset($env['hostname']) ? $env['hostname'] : '127.0.0.1',
        'port' => isset($env['port']) ? $env['port'] : '3306',
        'name' => isset($env['database']) ? $env['database'] : '',
        'user' => isset($env['username']) ? $env['username'] : '',
        'pass' => isset($env['password']) ? $env['password'] : '',
        'charset' => 'utf8mb4',
    ],
];
