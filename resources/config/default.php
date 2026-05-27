<?php

declare(strict_types=1);

use Asterios\Core\Asterios;
use Asterios\Core\Env;

$basePath = Asterios::getBasePath() . DIRECTORY_SEPARATOR;

$env = (new Env($basePath . '.env'));

return [
    'app_version' => $env->get('APP_VERSION', '0.0.0-alpha'),
    'default' => [
        'timezone' => 'Europe/Berlin',
    ],
    'errors' => [
        'display' => true,
    ],
    'security' => [
        'input_filter' => [
            'xss_clean',
        ],
        'output_filter' => [
            'xss_clean',
        ],
    ],
    'mail' => [
        'content_type' => 'text/html',
        'charset' => 'UTF-8',
        'encoding' => '7bit',
        'eol' => 'LF',
        'template' => [
            'delimiter' => [
                'open' => '{{',
                'close' => '}}',
            ],
        ],
    ],
    'logger' => [
        'log_dir' => $basePath . $env->get('LOG_DIRECTORY', 'logs') . DIRECTORY_SEPARATOR,
        'log_file' => $env->get('LOG_FILENAME', 'application'),
    ],
];