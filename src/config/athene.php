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
        'migrations' => [
            'table_storage' => [
                'table_name' => 'doctrine_migration_versions',
                'version_column_name' => 'version',
                'version_column_length' => 191,
                'executed_at_column_name' => 'executed_at',
                'execution_time_column_name' => 'execution_time',
            ],

            'migrations_paths' => [
                'Migrations' => __DIR__ . '/../Database/Migrations',
                'Component\Migrations' => __DIR__ . '/../Component/Migrations',
            ],

            'all_or_nothing' => true,
            'transactional' => true,
            'check_database_platform' => true,
            'organize_migrations' => 'none',
            'connection' => null,
            'em' => null,
        ],
    ],
];