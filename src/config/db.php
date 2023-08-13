<?php

declare(strict_types=1);


return [
    'connections' => [
        'default' => [
            'db_type' => 'mysql',
            'db_host' => 'localhost',
            'db_user' => 'db',
            'db_password' => 'db',
            'db_database' => 'db',
            'db_attributes' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
            'db_charset' => 'utf8',
        ],
    ],
];