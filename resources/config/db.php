<?php declare(strict_types=1);

use Asterios\Core\Asterios;
use Asterios\Core\Env;

$env = (new Env(Asterios::getBasePath() . DIRECTORY_SEPARATOR . '.env'));

return [
    'default' => [
        'host' => $env->get('DB_HOST', 'db'),
        'username' => $env->get('DB_USERNAME', 'db'),
        'password' => $env->get('DB_PASSWORD', 'db'),
        'database' => $env->get('DB_DATABASE', 'db'),
        'charset' => $env->get('DB_CHARSET', 'db'),
    ],
];
