<?php

declare(strict_types=1);

return [
    'athene' => [
        'is_dev_mode' => true,
        'model_path' => __DIR__ . '/../Models',
        'connections' => [
            'default' => [
                'dbname' => 'db',
                'user' => 'db',
                'password' => 'db',
                'host' => 'db',
                'driver' => 'pdo_mysql'
            ],
        ],
    ],
];