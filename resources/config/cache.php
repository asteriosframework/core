<?php declare(strict_types=1);

use Asterios\Core\Asterios;
use Asterios\Core\Env;

$env = new Env(Asterios::getBasePath() . DIRECTORY_SEPARATOR . '.env');

return [
    'default' => [
        'driver' => $env->get('CACHE_DRIVER', 'file'),
        'prefix' => $env->get(
            'CACHE_PREFIX',
            'asterios:'
        ),
        'serializer' => $env->get(
            'CACHE_SERIALIZER',
            'php'
        ),
        'default_ttl' => (int) $env->get(
            'CACHE_TTL_DEFAULT',
            '3600'
        ),
    ],
    'chain' => [
        'drivers' => array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        $env->get(
                            'CACHE_CHAIN',
                            'file'
                        )
                    )
                )
            )
        ),
    ],
    'redis' => [
        'host' => $env->get(
            'CACHE_REDIS_HOST',
            '127.0.0.1'
        ),
        'port' => (int) $env->get(
            'CACHE_REDIS_PORT',
            '6379'
        ),
        'timeout' => (float) $env->get(
            'CACHE_REDIS_TIMEOUT',
            '2.0'
        ),
        'database' => (int) $env->get(
            'CACHE_REDIS_DATABASE',
            '0'
        ),
        'password' => $env->get(
            'CACHE_REDIS_PASSWORD',
            ''
        ),
    ],
    'file' => [
        'path' => $env->get(
            'CACHE_FILE_PATH',
            Asterios::getBasePath()
            . DIRECTORY_SEPARATOR
            . 'storage'
            . DIRECTORY_SEPARATOR
            . 'cache'
        ),
    ],
    'mysql' => [
        'config_group' => $env->get(
            'CACHE_MYSQL_CONFIG_GROUP',
            'default'
        ),
        'table' => $env->get(
            'CACHE_MYSQL_TABLE',
            'cache_entries'
        ),
    ],

];
